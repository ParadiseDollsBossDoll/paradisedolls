<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsModel;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
            'model' => EnsureUserIsModel::class,
        ]);

        $middleware->appendToGroup('web', [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
