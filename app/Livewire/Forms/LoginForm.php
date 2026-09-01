<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use App\Models\PartnerActivity;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Registration;

class LoginForm extends Form
{
    #[Validate('required|string|min:3|max:100')]
    public string $email = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->email);
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        
        $credentials = [
            $fieldType => $loginInput,
            'password' => $this->password,
        ];

        if (!Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($this->throttleKey(), 60);

            // Log failed login attempt for system activity log
            try {
                $found = User::where('email', $loginInput)->orWhere('name', $loginInput)->first();
                \App\Models\ActivityLog::record(
                    $found,
                    'login_failed',
                    "Percobaan login gagal untuk akun: {$loginInput} (kredensial salah)"
                );
            } catch (\Throwable $e) {
                // ignore
            }

            throw ValidationException::withMessages([
                'form.email' => 'Email/Username atau password yang Anda masukkan tidak sesuai.',
            ]);
        }

        // Check user status after successful authentication
        $user = Auth::user();

        // Allow admin and super_admin to login regardless of registration pending flag
        $isPrivileged = in_array($user->role, ['admin', 'super_admin']);

        // If user's registration was rejected, redirect to a page showing rejection reason
        try {
            $reg = Registration::where('email', $user->email)->latest()->first();
            if ($reg && ($reg->status === 'rejected')) {
                Auth::logout();
                // redirect to rejected page showing reason
                redirect()->route('auth.rejected', ['registration' => $reg->id])->send();
                return;
            }
        } catch (\Throwable $e) {
            // ignore lookup errors
        }

        // Jika belum verifikasi email
        if (!$user->hasVerifiedEmail()) {
            if ($user->created_at && $user->created_at->diffInSeconds(now()) >= 600) {
                User::purgeExpiredUnverified($user->email);
                Auth::logout();
                throw ValidationException::withMessages([
                    'form.email' => 'Batas waktu verifikasi email (10 menit) telah kedaluwarsa. Akun otomatis dihapus, silakan lakukan pendaftaran baru.',
                ]);
            }

            Auth::logout();
            throw ValidationException::withMessages([
                'form.email' => 'Alamat email Anda belum diverifikasi. Silakan periksa inbox/spam email Anda dalam batas waktu 10 menit.',
            ]);
        }

        // Cek jika akun inactive yang belum menyelesaikan form data diri & KTP
        if (!$isPrivileged && $user->status === 'inactive' && (empty($user->nik) || empty($user->ktp_photo))) {
            // Cek jika sudah lewat 1x24 jam
            if ($user->created_at && $user->created_at->diffInHours(now()) >= 24) {
                User::purgeExpiredInactive($user->email);
                Auth::logout();
                throw ValidationException::withMessages([
                    'form.email' => 'Batas waktu penyelesaian formulir pendaftaran (1x24 jam) telah kedaluwarsa. Akun otomatis dihapus, silakan lakukan pendaftaran baru.',
                ]);
            }

            // Jika masih dalam batas 1x24 jam, izinkan login untuk melanjutkan pengisian form di Step 1
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Blokir user non-privileged yang statusnya pending atau inactive (yang sudah mengirimkan data KTP dan menunggu persetujuan admin)
        if (!$isPrivileged && ($user->status === 'pending' || $user->status === 'inactive')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda masih menunggu verifikasi KTP dari admin. Silakan tunggu hingga akun Anda disetujui.',
            ]);
        }

        if ($user->status === 'blocked') {
            Auth::logout();
            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda telah diblokir. Silakan hubungi administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Record successful login activity to system ActivityLog (all roles)
        try {
            \App\Models\ActivityLog::record(
                $user,
                'login',
                "User {$user->name} ({$user->role}) berhasil login ke sistem"
            );
        } catch (\Throwable $e) {
            // allow login to proceed even if activity logging fails
        }
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}
