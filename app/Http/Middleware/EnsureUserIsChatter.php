<?php

namespace App\Http\Middleware;

use App\Models\ChatterProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsChatter
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isChatter()) {
            abort(403);
        }

        $profile = $user->chatterProfile;

        if (! $profile || $profile->employment_status !== ChatterProfile::STATUS_ACTIVE) {
            abort(403, 'This chatter account is not active. Please contact an administrator.');
        }

        return $next($request);
    }
}
