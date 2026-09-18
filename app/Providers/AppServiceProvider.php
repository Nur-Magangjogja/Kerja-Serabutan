<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Help;
use App\Observers\HelpObserver;
use App\Models\User;
use App\Models\Rating;
use App\Observers\UserObserver;
use App\Observers\RatingObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Redirector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Redirector $redirect): void
    {
        // Register Help Observer
        Help::observe(HelpObserver::class);

        // Register User & Rating observers for activity logging
        User::observe(UserObserver::class);
        Rating::observe(RatingObserver::class);

        // Register BalanceTransaction observer to update user balances when transactions complete
        \App\Models\BalanceTransaction::observe(\App\Observers\BalanceTransactionObserver::class);

        // Reset in-memory memoized AppSetting state per-job / per-request cycle in long-running workers (Octane / Queue)
        \Illuminate\Support\Facades\Queue::looping(function () {
            \App\Models\AppSetting::clearRuntimeCache();
        });
        $this->app->terminating(function () {
            \App\Models\AppSetting::clearRuntimeCache();
        });

        // Set global default pagination view to unified 5-number design
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.superadmin');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.superadmin');

        // Cross-database compatibility: Register mathematical functions for SQLite in testing/local environments
        $registerSqliteFunctions = function ($connection) {
            if ($connection->getDriverName() === 'sqlite') {
                try {
                    $pdo = $connection->getPdo();
                    if ($pdo instanceof \PDO && method_exists($pdo, 'sqliteCreateFunction')) {
                        $pdo->sqliteCreateFunction('radians', 'deg2rad', 1);
                        $pdo->sqliteCreateFunction('least', fn(...$args) => min($args));
                        $pdo->sqliteCreateFunction('greatest', fn(...$args) => max($args));
                        $pdo->sqliteCreateFunction('acos', 'acos', 1);
                        $pdo->sqliteCreateFunction('cos', 'cos', 1);
                        $pdo->sqliteCreateFunction('sin', 'sin', 1);
                    }
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        };

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Database\Events\ConnectionEstablished::class,
            function ($event) use ($registerSqliteFunctions) {
                $registerSqliteFunctions($event->connection);
            }
        );

        // Redirect authenticated users based on their role
        $this->configureRedirectsForAuthentication();

        // Auto-start Laravel Reverb in local development if not already running
        if ($this->app->environment('local') && !$this->app->runningInConsole()) {
            $this->ensureReverbServerRunning();
        }
    }

    /**
     * Ensure Laravel Reverb server is running in local environment without manual intervention.
     */
    private function ensureReverbServerRunning(): void
    {
        $host = config('reverb.servers.reverb.host', '127.0.0.1');
        $port = (int) config('reverb.servers.reverb.port', 8080);
        if ($host === '0.0.0.0') {
            $host = '127.0.0.1';
        }

        // Check throttled to once every 15s to maintain 0ms overhead on requests
        $lockKey = 'auto_reverb_check_lock';
        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 15)) {
            return;
        }

        $connection = @fsockopen($host, $port, $errno, $errstr, 0.05);
        if (is_resource($connection)) {
            fclose($connection);
            return;
        }

        try {
            $artisanPath = escapeshellarg(base_path('artisan'));
            $phpBinary = PHP_BINARY ? escapeshellarg(PHP_BINARY) : 'php';

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B {$phpBinary} {$artisanPath} reverb:start > NUL 2>&1", "r"));
            } else {
                exec("{$phpBinary} {$artisanPath} reverb:start > /dev/null 2>&1 &");
            }
        } catch (\Throwable $e) {
            // Silently fallback if execution is disabled in PHP ini
        }
    }

    /**
     * Configure redirects after authentication
     */
    private function configureRedirectsForAuthentication(): void
    {
        $this->app['redirect']->macro('intended', function ($default = '/', $status = 302, $headers = [], $secure = null) {
            $intended = session()->pull('url.intended');

            if ($intended) {
                return redirect()->to($intended, $status, $headers, $secure);
            }

            // Check user role and redirect accordingly
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->role === 'mitra') {
                    return redirect()->to(route('mitra.dashboard'), $status, $headers, $secure);
                } elseif (in_array($user->role, ['admin', 'super_admin'])) {
                    return redirect()->to(
                        $user->role === 'super_admin' ? route('superadmin.dashboard') : route('admin.dashboard'),
                        $status,
                        $headers,
                        $secure
                    );
                }
            }

            return redirect()->to($default, $status, $headers, $secure);
        });
    }
}

