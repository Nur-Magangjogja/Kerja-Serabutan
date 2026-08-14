<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Add global middleware for role-based redirects
        $middleware->web(append: [
            \App\Http\Middleware\RedirectBasedOnRole::class,
        ]);

        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'kustomer' => \App\Http\Middleware\EnsureKustomer::class,
            // New alias: use 'customer' everywhere going forward
            'customer' => \App\Http\Middleware\EnsureCustomer::class,
            'mitra' => \App\Http\Middleware\EnsureMitra::class,
        ]);

        // Exclude Midtrans webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'topup/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
