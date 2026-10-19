<?php

namespace App\Http\Middleware;

use App\Services\AdminSidebarBadges;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $section = $request->isMethod('GET')
            ? (AdminSidebarBadges::ROUTES[$request->route()?->getName()] ?? null)
            : null;
        $startedAt = now();
        $response = $next($request);

        if ($section !== null && $response->isSuccessful()) {
            app(AdminSidebarBadges::class)->markViewed($request->user(), $section, $startedAt);
        }

        return $response;
    }
}
