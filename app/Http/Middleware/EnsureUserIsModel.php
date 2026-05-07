<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsModel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isModel()) {
            return $next($request);
        }

        if ($user?->isAdmin()) {
            return redirect()->route(
                $request->routeIs('member.courses.*', 'member.lessons.*')
                    ? 'admin.courses.index'
                    : 'admin.models.progress'
            );
        }

        abort(403);
    }
}
