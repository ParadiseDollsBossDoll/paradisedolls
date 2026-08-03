<?php

namespace App\Http\Middleware;

use App\Models\ChatterProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceAuthenticatedSessionPolicy
{
    public const SESSION_VERSION_KEY = 'auth.session_version';

    public const LAST_ACTIVITY_KEY = 'auth.last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->isChatter() && Auth::guard('web')->viaRemember()) {
            return $this->terminateSession($request, __('Please log in again to access the chatter workspace.'));
        }

        $sessionVersion = $request->session()->get(self::SESSION_VERSION_KEY);
        if ($sessionVersion === null && (int) $user->auth_session_version > 0 && ! Auth::guard('web')->viaRemember()) {
            return $this->terminateSession($request, __('Your session has expired. Please log in again.'));
        }

        if ($sessionVersion !== null && (int) $sessionVersion !== (int) $user->auth_session_version) {
            return $this->terminateSession($request, __('Your session has expired. Please log in again.'));
        }

        $lastActivityAt = (int) $request->session()->get(self::LAST_ACTIVITY_KEY, 0);
        $lifetimeMinutes = max(1, (int) ($user->isChatter()
            ? config('session.chatter_lifetime', 720)
            : config('session.authenticated_lifetime', 120)));

        if ($lastActivityAt > 0 && now()->timestamp - $lastActivityAt > $lifetimeMinutes * 60) {
            return $this->terminateSession($request, __('Your session has expired due to inactivity. Please log in again.'));
        }

        if ($user->isChatter()) {
            $profile = ChatterProfile::query()
                ->where('user_id', $user->id)
                ->first();

            if (! $profile || $profile->employment_status !== ChatterProfile::STATUS_ACTIVE) {
                return $this->terminateSession(
                    $request,
                    __('This chatter account is not active. Please contact an administrator.'),
                    403,
                );
            }

            $user->setRelation('chatterProfile', $profile);
        }

        $request->session()->put(self::SESSION_VERSION_KEY, (int) $user->auth_session_version);

        if (! $this->isPassiveRequest($request)) {
            $request->session()->put(self::LAST_ACTIVITY_KEY, now()->timestamp);
        }

        return $next($request);
    }

    private function terminateSession(Request $request, string $message, int $status = 401): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }

    private function isPassiveRequest(Request $request): bool
    {
        return $request->routeIs(
            'chatter.state',
            'community.channels.index',
            'community.channels.messages.index',
            'community.channels.read',
            'community.presence.index',
            'community.presence.ping',
            'community.presence.typing',
        ) || $request->is('broadcasting/auth', 'broadcasting/user-auth');
    }
}
