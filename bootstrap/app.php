<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserThemeAndSessionState::class,
        ]);

        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'kustomer' => \App\Http\Middleware\EnsureKustomer::class,
            // New alias: use 'customer' everywhere going forward
            'customer' => \App\Http\Middleware\EnsureCustomer::class,
            'mitra' => \App\Http\Middleware\EnsureMitra::class,
            'approved' => \App\Http\Middleware\EnsureAccountApproved::class,
        ]);

        // Exclude Midtrans webhook from CSRF verification (Nonaktif / Disabled)
        // $middleware->validateCsrfTokens(except: [
        //     'topup/notification',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
