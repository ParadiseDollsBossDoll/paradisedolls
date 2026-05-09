<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogCommunityRequestPerformance
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);

        if (! config('community.performance.enabled')) {
            return $response;
        }

        $durationMs = (microtime(true) - $startedAt) * 1000;
        $thresholdMs = max(1, (int) config('community.performance.slow_request_ms', 800));

        if ($durationMs < $thresholdMs) {
            return $response;
        }

        Log::channel(config('community.performance.log_channel'))->warning('Slow community request detected.', [
            'method' => $request->method(),
            'path' => $request->path(),
            'route' => $request->route()?->getName(),
            'status' => $response->getStatusCode(),
            'duration_ms' => (int) round($durationMs),
            'user_id' => $request->user()?->id,
            'channel_slug' => $request->route('channel')?->slug,
            'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);

        return $response;
    }
}
