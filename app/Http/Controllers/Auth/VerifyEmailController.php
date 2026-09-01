<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified with a 10-minute expiry check.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('register')->with('error', 'Akun tidak ditemukan atau batas waktu verifikasi (10 menit) telah kedaluwarsa. Silakan lakukan pendaftaran ulang.');
        }

        // Cek apakah akun belum diverifikasi dan sudah lewat batas waktu 10 menit sejak pendaftaran
        if (!$user->hasVerifiedEmail() && $user->created_at && $user->created_at->diffInMinutes(now()) >= 10) {
            try {
                Registration::where('email', $user->email)->where('status', '!=', 'approved')->delete();
                $user->delete();
                Auth::logout();
                $request->session()->invalidate();
            } catch (\Throwable $e) {
                // ignore
            }

            return redirect()->route('register')->with('error', 'Batas waktu verifikasi email (10 menit) telah habis. Akun otomatis dibatalkan dan dihapus. Silakan lakukan pendaftaran baru.');
        }

        // Cek validitas signed URL (otomatis mengecek expires)
        if (! $request->hasValidSignature()) {
            if (!$user->hasVerifiedEmail()) {
                try {
                    Registration::where('email', $user->email)->where('status', '!=', 'approved')->delete();
                    $user->delete();
                    Auth::logout();
                    $request->session()->invalidate();
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            return redirect()->route('register')->with('error', 'Tautan verifikasi email telah kedaluwarsa (melebihi 10 menit). Akun otomatis dihapus, silakan lakukan pendaftaran akun baru.');
        }

        // Cek kesesuaian hash email
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('register')->with('error', 'Tautan verifikasi tidak valid.');
        }

        // Login otomatis jika belum login di sesi peramban ini
        if (!Auth::check() || Auth::id() !== $user->id) {
            Auth::login($user);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Setelah email terverifikasi, langsung arahkan ke Step 1 (Pengisian Data Diri & Verifikasi KTP)
        if (empty($user->nik) || (empty($user->ktp_photo) && empty($user->ktp_path))) {
            return redirect()->route('register.step1')->with('verified', 1);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
