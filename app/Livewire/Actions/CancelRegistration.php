<?php

namespace App\Livewire\Actions;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CancelRegistration
{
    /**
     * Cancel and purge in-progress registration for the current user/session.
     *
     * @param User|null $user
     * @return void
     */
    public function __invoke(?User $user = null): void
    {
        $user = $user ?? Auth::user();
        $email = $user ? strtolower(trim($user->email)) : null;
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');

        // 1. Cari & Bersihkan berkas foto pendaftaran di storage
        $registrations = collect();
        if ($email) {
            $registrations = Registration::where('email', $email)
                ->where('status', '!=', 'approved')
                ->get();
        }
        if ($registrations->isEmpty() && $uuid) {
            $found = Registration::where('uuid', $uuid)
                ->where('status', '!=', 'approved')
                ->get();
            $registrations = $found;
        }

        foreach ($registrations as $reg) {
            try {
                if (!empty($reg->ktp_photo_path)) {
                    Storage::disk('public')->delete($reg->ktp_photo_path);
                }
                if (!empty($reg->selfie_photo_path)) {
                    Storage::disk('public')->delete($reg->selfie_photo_path);
                }
                $reg->delete();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 2. Hapus draft file pada user jika ada
        if ($user) {
            try {
                if (!empty($user->ktp_photo)) {
                    Storage::disk('public')->delete($user->ktp_photo);
                }
                if (!empty($user->selfie_photo)) {
                    Storage::disk('public')->delete($user->selfie_photo);
                }

                // Hapus akun jika masih dalam tahap registrasi (status inactive atau unverified)
                // dan BUKAN admin / super_admin / akun approved
                if (
                    !in_array($user->role, ['admin', 'super_admin'])
                    && ($user->status === 'inactive' || !$user->hasVerifiedEmail() || !$user->verified)
                ) {
                    $user->delete();
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 3. Logout dan bersihkan session auth
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        // 4. Bersihkan semua cookie registrasi
        Cookie::queue(Cookie::forget('registration_uuid'));
        Cookie::queue(Cookie::forget('registration_role'));
        Cookie::queue(Cookie::forget('registration_step1_draft'));
        Cookie::queue(Cookie::forget('sb_register_draft'));
        Cookie::queue(Cookie::forget('sb_register_leave_time'));

        Session::flash('status', 'Pembuatan akun telah berhasil dibatalkan. Silakan masuk dengan akun Anda.');
    }
}
