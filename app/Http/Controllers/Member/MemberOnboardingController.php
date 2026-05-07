<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\ModelInformationSubmittedMail;
use App\Models\ModelProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class MemberOnboardingController extends Controller
{
    public function edit(): View
    {
        return view('member.onboarding.edit', [
            'profile' => $this->profile(),
            'platformOptions' => $this->platformOptions(),
            'equipmentOptions' => $this->equipmentOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'stage_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->format('Y-m-d')],
            'phone' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'timezone' => ['nullable', 'string', 'max:120'],
            'platforms' => ['nullable', 'array', 'max:12'],
            'platforms.*' => ['string', 'max:80'],
            'equipment' => ['nullable', 'array', 'max:12'],
            'equipment.*' => ['string', 'max:80'],
            'availability' => ['required', 'string', 'max:5000'],
            'goals' => ['required', 'string', 'max:5000'],
            'experience_notes' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'discord_username' => ['nullable', 'string', 'max:255'],
            'discord_user_id' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = $this->profile();
        $profile->forceFill([
            ...$validated,
            'platforms' => $validated['platforms'] ?? [],
            'equipment' => $validated['equipment'] ?? [],
            'information_submitted_at' => $profile->information_submitted_at ?? now(),
        ])->save();

        $profile->refresh()->load('user');
        $this->sendConfirmation($profile);

        return redirect()
            ->route('member.dashboard')
            ->with('status', __('Model Information Form submitted. The onboarding team can now request verification when ready.'));
    }

    private function profile(): ModelProfile
    {
        return auth()->user()->modelProfile()->firstOrCreate([]);
    }

    private function platformOptions(): array
    {
        return [
            'Chaturbate',
            'Stripchat',
            'OnlyFans',
            'Fansly',
            'Babestation',
            'LiveJasmin',
            'BongaCams',
            'Cam4',
            'CamSoda',
            'MyFreeCams',
            'Flirt4Free',
            'Streamate',
            'TikTok',
            'Instagram',
        ];
    }

    private function equipmentOptions(): array
    {
        return [
            'Phone',
            'Laptop',
            'Desktop PC',
            'Webcam',
            'Ring light',
            'Softbox lighting',
            'Microphone',
            'Stable internet',
            'Private room',
        ];
    }

    private function sendConfirmation(ModelProfile $profile): void
    {
        try {
            Mail::to($profile->user->email)->send(new ModelInformationSubmittedMail($profile));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
