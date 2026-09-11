<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventAdminRegistrationAccess
{
    /**
     * Handle an incoming request.
     *
     * Block superadmin, admin, and already verified/approved users from accessing registration & onboarding pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. Superadmin & Admin are strictly blocked from registration
            if (in_array($user->role ?? '', ['super_admin', 'superadmin'])) {
                return redirect()->route('superadmin.dashboard');
            }
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            // 2. Already verified & active users (Mitra & Customer) are strictly blocked from re-registering
            if ($user->verified && $user->status === 'active') {
                if ($user->role === 'mitra') {
                    return redirect()->route('mitra.dashboard');
                }
                if ($user->role === 'customer') {
                    return redirect()->route('customer.dashboard');
                }
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
