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
        $badges = app(AdminSidebarBadges::class);
        $snapshot = $section !== null ? $badges->snapshot() : null;
        $response = $next($request);

        if ($section !== null && $response->isSuccessful()) {
            $badges->markViewed($request->user(), $section, $startedAt, $snapshot);
        }

        return $response;
    }
}
