<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserSessionService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, UserSessionService $sessions): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $lockKey = 'password-reset:'.hash('sha256', Str::lower(trim((string) $request->email)));

        try {
            $status = Cache::lock($lockKey, 15)->block(5, function () use ($request, $sessions): string {
                return Password::reset(
                    $request->only('email', 'password', 'password_confirmation', 'token'),
                    function (User $user) use ($request, $sessions) {
                        $user = DB::transaction(function () use ($request, $user): User {
                            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
                            $lockedUser->forceFill([
                                'password' => Hash::make($request->password),
                                'remember_token' => Str::random(60),
                                'auth_session_version' => (int) $lockedUser->auth_session_version + 1,
                            ])->save();

                            return $lockedUser;
                        });

                        $sessions->revokeAll($user);
                        event(new PasswordReset($user));
                    }
                );
            });
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'email' => __('Please wait before trying to reset this password again.'),
            ]);
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
