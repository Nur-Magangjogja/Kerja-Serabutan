<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If not authenticated, redirect to login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Allow admin and super_admin
        if (!in_array(auth()->user()->role, ['admin', 'super_admin', 'superadmin'])) {
            abort(403, 'Unauthorized. Admin access only.');
        }

        return $next($request);
    }
}
