<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommunityAccessIsAssigned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->canModerateCommunity()) {
            return $next($request);
        }

        if ($user->hasCommunityChatAccess()) {
            return $next($request);
        }

        if ($request->isMethod('GET') && ! $request->expectsJson()) {
            return redirect()
                ->route($user->isModel() ? 'member.dashboard' : 'dashboard')
                ->with('status', __('Community chat unlocks after Kayla assigns your community access.'));
        }

        abort(403, __('Community chat unlocks after Kayla assigns your community access.'));
    }
}
