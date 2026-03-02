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

        // Redirect authenticated users based on their role
        $this->configureRedirectsForAuthentication();
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

