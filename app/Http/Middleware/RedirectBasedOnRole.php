<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow Livewire internal endpoints to pass through without role redirects
        if ($request->is('livewire*')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            // Redirect super_admin
            if ($user->role === 'super_admin' && !$request->routeIs(['superadmin.*', 'logout', 'profile', 'profile.*', 'notifications.*', 'password.*', 'verification.*'])) {
                return redirect()->route('superadmin.dashboard');
            }

            // Redirect admin
            if ($user->role === 'admin' && !$request->routeIs(['admin.*', 'logout', 'profile', 'profile.*', 'notifications.*', 'password.*', 'verification.*'])) {
                return redirect()->route('admin.dashboard');
            }

            // Redirect mitra to mitra dashboard
            if ($user->role === 'mitra' && !$request->routeIs(['mitra.*', 'logout', 'profile', 'profile.*', 'notifications.*', 'password.*', 'verification.*'])) {
                return redirect()->route('mitra.dashboard');
            }

            // Allow customer to access dashboard and other authenticated routes
            if ($user->role === 'customer' && !$request->routeIs(['customer.*', 'dashboard', 'chat.*', 'ajax.*', 'logout', 'profile', 'profile.*', 'notifications.*', 'password.*', 'verification.*'])) {
                return redirect()->route('customer.dashboard');
            }
        }

        return $next($request);
    }
}
