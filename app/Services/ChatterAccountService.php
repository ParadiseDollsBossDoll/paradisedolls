<?php

namespace App\Services;

use App\Mail\ChatterInvitationMail;
use App\Models\ChatterPayRate;
use App\Models\ChatterProfile;
use App\Models\ChatterRequest;
use App\Models\ChatterRoleAssignment;
use App\Models\ChatterWorkRole;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatterAccountService
{
    /** @param array<string, mixed> $data */
    public function create(array $data, User $admin, ?ChatterRequest $request = null): User
    {
        $user = DB::transaction(function () use ($data, $admin, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'password' => Hash::make(Str::random(48)),
                'role' => 'chatter',
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            ChatterProfile::create([
                'user_id' => $user->id,
                'timezone' => $data['timezone'],
                'employment_status' => ChatterProfile::STATUS_ACTIVE,
                'started_at' => now(),
            ]);

            ChatterPayRate::create([
                'user_id' => $user->id,
                'base_rate_pence' => $data['base_rate_pence'],
                'overtime_threshold_minutes' => $data['overtime_threshold_minutes'],
                'overtime_multiplier_bps' => $data['overtime_multiplier_bps'],
                'night_premium_bps' => $data['night_premium_bps'],
                'weekend_premium_bps' => $data['weekend_premium_bps'],
                'night_starts_at' => $data['night_starts_at'],
                'night_ends_at' => $data['night_ends_at'],
                'effective_from' => $data['effective_from'],
                'created_by' => $admin->id,
            ]);

            $chatterRole = ChatterWorkRole::query()->firstOrCreate(
                ['slug' => 'chatter'],
                ['name' => 'Chatter', 'is_active' => true, 'sort_order' => 10],
            );
            ChatterRoleAssignment::create([
                'user_id' => $user->id,
                'chatter_work_role_id' => $chatterRole->id,
                'hourly_rate_pence' => $data['base_rate_pence'],
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            if ($request) {
                $request->forceFill([
                    'status' => ChatterRequest::STATUS_APPROVED,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ])->save();
            }

            return $user;
        });

        $this->sendInvitation($user);

        return $user;
    }

    public function sendInvitation(User $user): void
    {
        $token = Password::broker()->createToken($user);
        $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

        Mail::to($user->email)->queue(new ChatterInvitationMail($user, $url));
        $user->notify(new SystemNotification(
            title: __('Your chatter workspace is ready'),
            body: __('Set your password from the secure invitation email, then use the time tracker to clock in.'),
            actionUrl: route('login', absolute: false),
            category: 'chatter_invitation',
        ));
    }

    public function delete(User $user): string
    {
        if (! $user->isChatter()) {
            throw new AuthorizationException('Only chatter accounts can be deleted from chatter management.');
        }

        $name = $user->name;
        $profilePhotoPath = $user->profile_photo_path;

        DB::transaction(function () use ($user): void {
            ChatterRequest::query()->where('email', Str::lower($user->email))->delete();
            DB::table('password_reset_tokens')->where('email', Str::lower($user->email))->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->notifications()->delete();
            $user->delete();
        });

        if (filled($profilePhotoPath)) {
            Storage::disk('public')->delete($profilePhotoPath);
        }

        return $name;
    }
}
