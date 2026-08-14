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
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
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

        if (!Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            // Log failed login attempt for partner reporting (if user exists)
            try {
                $found = User::where('email', $this->email)->first();
                PartnerActivity::create([
                    'user_id' => $found?->id,
                    'activity_type' => 'login_failed',
                    'description' => 'Gagal login',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                ]);
            } catch (\Throwable $e) {
                // Don't let logging failures affect authentication flow
                \Log::warning('Failed to record login_failed PartnerActivity: ' . $e->getMessage());
            }

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
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

        // Only block non-privileged users who are pending or inactive
        if (!$isPrivileged && ($user->status === 'pending' || $user->status === 'inactive')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda masih menunggu verifikasi dari admin. Silakan tunggu hingga akun Anda disetujui.',
            ]);
        }

        if ($user->status === 'blocked') {
            Auth::logout();
            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda telah diblokir. Silakan hubungi administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Record successful login activity for mitra users
        try {
            if ($user && $user->role === 'mitra') {
                PartnerActivity::create([
                    'user_id' => $user->id,
                    'activity_type' => 'login',
                    'description' => 'Login berhasil',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                ]);
            }
        } catch (\Throwable $e) {
            // allow login to proceed even if activity logging fails
            \Log::warning('Failed to record PartnerActivity on login: ' . $e->getMessage());
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
