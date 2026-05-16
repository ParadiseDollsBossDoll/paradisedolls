<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsEnrolledInCourse;
use App\Http\Middleware\EnsureUserIsModel;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trusted = env('TRUSTED_PROXIES');
        if (filled($trusted)) {
            $middleware->trustProxies(at: $trusted === '*' ? '*' : array_values(array_filter(array_map(trim(...), explode(',', $trusted)))));
        }

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'course.enrolled' => EnsureUserIsEnrolledInCourse::class,
            'community.perf' => \App\Http\Middleware\LogCommunityRequestPerformance::class,
            'model' => EnsureUserIsModel::class,
        ]);

        $middleware->appendToGroup('web', [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if (! $request->is('logout')) {
                return null;
            }

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        });
    })->create();
