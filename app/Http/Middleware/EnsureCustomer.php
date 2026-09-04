<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== 'customer') {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk Customer.');
        }

        // 1. Pastikan email terverifikasi
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // 2. Pastikan form data diri/KTP sudah selesai diisi
        if (empty($user->nik) || (empty($user->ktp_photo) && empty($user->ktp_path))) {
            return redirect()->route('register.step1');
        }

        // 3. Pastikan akun sudah disetujui & aktif oleh admin
        if ($user->status !== 'active' || !$user->verified) {
            return redirect()->route('registration.success');
        }

        return $next($request);
    }
}
