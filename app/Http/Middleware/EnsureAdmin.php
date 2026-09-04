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

        // Strict: ONLY admin role allowed. Super Admin must use /superadmin/*
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk Admin Wilayah.');
        }

        return $next($request);
    }
}
