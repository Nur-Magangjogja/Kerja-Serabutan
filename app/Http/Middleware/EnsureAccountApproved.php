<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountApproved
{
    /**
     * Handle an incoming request.
     *
     * Pastikan pengguna tidak dapat mengakses halaman aplikasi sebelum:
     * 1. Memverifikasi alamat email
     * 2. Menyelesaikan seluruh formulir data diri dan berkas KTP (Step 1 - 4)
     * 3. Disetujui dan divalidasi oleh Admin (status = active dan verified = true)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Superadmin and Admin bypass verification and onboarding checks
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // 1. Cek Verifikasi Email
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // 2. Cek Pengisian Data Diri & Dokumen KTP (Step 1 s/d Step 4)
        if (empty($user->nik) || (empty($user->ktp_photo) && empty($user->ktp_path))) {
            return redirect()->route('register.step1');
        }

        // 3. Cek Status Akun yang Diblokir
        if ($user->status === 'blocked') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah diblokir. Silakan hubungi administrator.',
            ]);
        }

        // 4. Cek Status Pendaftaran yang Ditolak Admin
        $reg = \App\Models\Registration::where('email', $user->email)->latest()->first();
        if ($reg && $reg->status === 'rejected') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('auth.rejected', ['registration' => $reg->id]);
        }

        // 5. Cek Validasi dan Persetujuan Admin (status aktif & verified = true)
        if ($user->status !== 'active' || !$user->verified) {
            // Jika rute saat ini adalah halaman info pendaftaran berhasil, izinkan tampil
            if ($request->routeIs('registration.success')) {
                return $next($request);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('registration.success');
        }

        return $next($request);
    }
}
