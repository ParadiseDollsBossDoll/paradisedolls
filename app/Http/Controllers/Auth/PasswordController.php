<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnforceAuthenticatedSessionPolicy;
use App\Models\User;
use App\Services\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request, UserSessionService $sessions): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $currentSessionId = $request->session()->getId();
        $user = DB::transaction(function () use ($request, $validated): User {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);

            if (! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => __('The password is incorrect.'),
                ])->errorBag('updatePassword');
            }

            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
                'auth_session_version' => (int) $user->auth_session_version + 1,
            ])->save();

            return $user;
        });

        $sessions->revokeOthers($user, $currentSessionId);
        $request->session()->regenerate(true);
        $request->session()->put([
            EnforceAuthenticatedSessionPolicy::SESSION_VERSION_KEY => (int) $user->auth_session_version,
            EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY => now()->timestamp,
        ]);

        return back()->with('status', 'password-updated');
    }
}
