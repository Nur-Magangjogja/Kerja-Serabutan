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

            // Redirect mitra to mitra dashboard
            if ($user->role === 'mitra' && !$request->routeIs(['mitra.*', 'logout'])) {
                return redirect()->route('mitra.dashboard');
            }

            // Redirect admin/super_admin based on their role
            if (in_array($user->role, ['admin', 'super_admin']) && !$request->routeIs(['admin.*', 'superadmin.*', 'logout'])) {
                $dashboardRoute = $user->role === 'super_admin' ? 'superadmin.dashboard' : 'admin.dashboard';
                return redirect()->route($dashboardRoute);
            }

            // Allow customer to access dashboard and other authenticated routes
            if ($user->role === 'customer') {
                // Customer can access most routes normally
                return $next($request);
            }
        }

        return $next($request);
    }
}
