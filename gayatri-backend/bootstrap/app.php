<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: class_exists('Pusher\Pusher') ? __DIR__.'/../routes/channels.php' : null,
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Must run inside the 'web' group, after StartSession — the bare
        // global append() runs before sessions boot, which made every
        // request spin up a second, disconnected session (CSRF 419 loop).
        $middleware->web(append: [\App\Http\Middleware\SessionTimeout::class]);
        $middleware->validateCsrfTokens(except: [
            '/webhooks/razorpay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
