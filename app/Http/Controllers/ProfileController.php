<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ModelEmailSyncService;
use App\Support\CommunityPresence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, ModelEmailSyncService $emailSyncService): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->safe()->except(['profile_photo', 'remove_profile_photo']);
        $previousEmail = $user->email;

        if ($emailSyncService->emailIsUsedByAnotherApplication($user, $validated['email'])) {
            throw ValidationException::withMessages([
                'email' => __('This email is already used by another application.'),
            ]);
        }

        $user->fill($validated);

        if (strcasecmp($previousEmail, $user->email) !== 0) {
            $user->email_verified_at = null;
        }

        $previousPhotoPath = $user->profile_photo_path;
        $newPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');

            if (! is_string($newPhotoPath) || ! Storage::disk('public')->exists($newPhotoPath)) {
                throw ValidationException::withMessages([
                    'profile_photo' => __('The photo could not be saved. Please try again.'),
                ]);
            }

            $user->profile_photo_path = $newPhotoPath;
        } elseif ($request->boolean('remove_profile_photo')) {
            $user->profile_photo_path = null;
        }

        try {
            $user->save();
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                $this->deleteProfilePhoto($newPhotoPath);
            }

            throw $exception;
        }

        if ($user->wasChanged('email')) {
            DB::table('password_reset_tokens')
                ->whereIn('email', array_values(array_unique(array_filter([
                    $previousEmail,
                    $user->email,
                ]))))
                ->delete();

            $emailSyncService->syncLinkedApplications($user, $user->email);
        }

        if ($previousPhotoPath !== $user->profile_photo_path) {
            $this->deleteProfilePhoto($previousPhotoPath);
        }

        CommunityPresence::forgetMemberDirectory();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'remove_profile_photo' => ['nullable', 'boolean'],
            'profile_photo' => [
                $request->boolean('remove_profile_photo') ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $user = $request->user();
        $previousPhotoPath = $user->profile_photo_path;

        if ($request->boolean('remove_profile_photo')) {
            $user->forceFill(['profile_photo_path' => null])->save();
            $this->deleteProfilePhoto($previousPhotoPath);
            CommunityPresence::forgetMemberDirectory();

            return Redirect::route('profile.edit')->with('status', 'profile-photo-removed');
        }

        $newPhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');

        if (! is_string($newPhotoPath) || ! Storage::disk('public')->exists($newPhotoPath)) {
            throw ValidationException::withMessages([
                'profile_photo' => __('The photo could not be saved. Please try again.'),
            ]);
        }

        try {
            $user->forceFill(['profile_photo_path' => $newPhotoPath])->save();
        } catch (Throwable $exception) {
            $this->deleteProfilePhoto($newPhotoPath);

            throw $exception;
        }

        $this->deleteProfilePhoto($previousPhotoPath);
        CommunityPresence::forgetMemberDirectory();

        return Redirect::route('profile.edit')->with('status', 'profile-photo-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_if(
            $request->user()->isAdmin() || $request->user()->isChatter(),
            403,
            'Administrator and chatter accounts must be deactivated by an administrator.'
        );

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $this->deleteProfilePhoto($user->profile_photo_path);
        $user->delete();
        CommunityPresence::forgetMemberDirectory();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function deleteProfilePhoto(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
