<?php

use App\Livewire\Actions\Logout;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public int $remainingSeconds = 600;
    public int $resendCooldown = 0;

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(route('register'), navigate: true);
            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        // Hitung sisa detik dari batas 10 menit (600 detik) untuk kedaluwarsa akun
        if ($user->created_at) {
            $elapsedSeconds = (int) $user->created_at->diffInSeconds(now());
            $this->remainingSeconds = max(0, 600 - $elapsedSeconds);
            if ($this->remainingSeconds <= 0) {
                $this->expireAccount();
                return;
            }
        }

        // Hitung sisa cooldown 2 menit (120 detik) untuk kirim ulang email
        $lastSent = session('last_verification_sent_at');
        $now = now()->timestamp;
        if ($lastSent) {
            $secondsSinceLastSent = max(0, $now - (int)$lastSent);
            $this->resendCooldown = max(0, 120 - $secondsSinceLastSent);
        } elseif ($user->created_at) {
            $secondsSinceCreated = (int) $user->created_at->diffInSeconds(now());
            $this->resendCooldown = max(0, 120 - $secondsSinceCreated);
        } else {
            $this->resendCooldown = 120;
        }
    }

    public function expireAccount(): void
    {
        $user = Auth::user();
        if ($user && !$user->hasVerifiedEmail()) {
            try {
                Registration::where('email', $user->email)->where('status', '!=', 'approved')->delete();
                $user->delete();
                Auth::logout();
                request()->session()->invalidate();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        Session::flash('error', 'Batas waktu verifikasi email (10 menit) telah kedaluwarsa. Akun otomatis dihapus, silakan lakukan pendaftaran ulang.');
        $this->redirect(route('register'), navigate: true);
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(route('register'), navigate: true);
            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->redirect(route('register.step1'), navigate: true);
            return;
        }

        // Cek jika akun sudah lewat 10 menit
        if ($user->created_at && $user->created_at->diffInSeconds(now()) >= 600) {
            $this->expireAccount();
            return;
        }

        // Proteksi Cooldown 2 Menit (120 Detik) di sisi Server
        $lastSent = session('last_verification_sent_at');
        $now = now()->timestamp;
        if ($lastSent && ($now - (int)$lastSent) < 120) {
            $remaining = 120 - ($now - (int)$lastSent);
            $this->resendCooldown = $remaining;
            $this->dispatch('verification-cooldown-updated', cooldown: $remaining);
            Session::flash('error', "Harap tunggu {$remaining} detik sebelum meminta pengiriman ulang email verifikasi.");
            return;
        }

        try {
            $user->sendEmailVerificationNotification();
            session(['last_verification_sent_at' => $now]);
            $this->resendCooldown = 120;
            $this->dispatch('verification-sent', cooldown: 120);
            Session::flash('status', 'verification-link-sent');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[VerifyEmail] Gagal kirim email verifikasi: ' . $e->getMessage());
            Session::flash('error', 'Gagal mengirim email verifikasi. Silakan coba lagi beberapa saat.');
        }
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="space-y-6"
    x-data="{
        seconds: {{ $remainingSeconds }},
        resendSeconds: {{ $resendCooldown }},
        timer: null,
        resendTimer: null,
        formatTime(sec) {
            const m = Math.floor(Math.max(0, sec) / 60).toString().padStart(2, '0');
            const s = (Math.max(0, sec) % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },
        startTimers() {
            if (this.timer) clearInterval(this.timer);
            if (this.resendTimer) clearInterval(this.resendTimer);

            this.timer = setInterval(() => {
                if (this.seconds > 0) {
                    this.seconds--;
                } else {
                    clearInterval(this.timer);
                    $wire.expireAccount();
                }
            }, 1000);

            this.resendTimer = setInterval(() => {
                if (this.resendSeconds > 0) {
                    this.resendSeconds--;
                }
            }, 1000);
        }
    }"
    x-init="startTimers()"
    @verification-sent.window="resendSeconds = $event.detail.cooldown || 120"
    @verification-cooldown-updated.window="resendSeconds = $event.detail.cooldown">

    <!-- Header & Icon -->
    <div class="text-center">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-indigo-600 text-white shadow-lg shadow-primary-500/25 p-3.5 mb-3 items-center justify-center">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Verifikasi Alamat Email</h2>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto leading-relaxed">
            Terima kasih telah mendaftar! Tautan verifikasi telah kami kirimkan ke:
        </p>
        @if(auth()->user())
            <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 font-bold text-xs border border-primary-200 dark:border-primary-800">
                <span>📧</span>
                <span>{{ auth()->user()->email }}</span>
            </div>
        @endif
    </div>

    <!-- ⏱️ Countdown Timer Box (10 Menit Kedaluwarsa) -->
    <div class="bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/80 rounded-2xl p-4 text-center space-y-1 shadow-xs">
        <div class="flex items-center justify-center gap-2 text-amber-800 dark:text-amber-300">
            <svg class="w-4 h-4 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-bold uppercase tracking-wider">Sisa Waktu Verifikasi:</span>
        </div>
        <div class="text-2xl font-black font-mono tracking-widest text-amber-700 dark:text-amber-400" x-text="formatTime(seconds)">
            10:00
        </div>
        <p class="text-[11px] text-amber-700/80 dark:text-amber-400/80 leading-relaxed">
            Jika melewati batas <strong>10 menit</strong>, akun akan otomatis dibatalkan & dihapus dari sistem.
        </p>
    </div>

    <!-- Notifikasi Sukses Kirim Ulang -->
    @if (session('status') == 'verification-link-sent')
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-start gap-3 shadow-xs animate-fade-in">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-300 font-medium leading-relaxed">
                Tautan verifikasi baru telah berhasil dikirim ke alamat email Anda.
            </span>
        </div>
    @endif

    <!-- Notifikasi Pesan Error / Cooldown Warning -->
    @if (session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 rounded-2xl flex items-start gap-3 shadow-xs animate-fade-in">
            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs sm:text-sm text-rose-800 dark:text-rose-300 font-medium leading-relaxed">
                {{ session('error') }}
            </span>
        </div>
    @endif

    <div class="space-y-3 pt-1">
        <!-- Tombol Kirim Ulang dengan Cooldown 2 Menit & Loading Protection -->
        <button wire:click="sendVerification"
            type="button"
            :disabled="resendSeconds > 0"
            wire:loading.attr="disabled"
            wire:target="sendVerification"
            :class="resendSeconds > 0 
                ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 cursor-not-allowed shadow-none' 
                : 'bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white shadow-md hover:shadow-lg active:scale-[0.98] cursor-pointer'"
            class="w-full font-bold text-xs sm:text-sm py-3.5 rounded-xl transition-all flex items-center justify-center gap-2">
            
            <!-- Spinner saat Livewire loading -->
            <svg wire:loading wire:target="sendVerification" class="w-4 h-4 animate-spin text-current" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>

            <!-- Ikon Biasa / Cooldown (disembunyikan saat loading) -->
            <svg wire:loading.remove wire:target="sendVerification" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>

            <!-- Teks Status Loading -->
            <span wire:loading wire:target="sendVerification">Mengirim Email...</span>

            <!-- Teks saat Cooldown Selesai -->
            <span wire:loading.remove wire:target="sendVerification" x-show="resendSeconds <= 0">
                Kirim Ulang Email Verifikasi
            </span>

            <!-- Teks saat Cooldown Masih Berjalan (2 Menit) -->
            <span wire:loading.remove wire:target="sendVerification" x-show="resendSeconds > 0" x-cloak>
                <span>Kirim Ulang Tersedia (<span x-text="formatTime(resendSeconds)">02:00</span>)</span>
            </span>
        </button>

        <div class="text-center pt-2">
            <button wire:click="logout" type="button"
                class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline cursor-pointer">
                Batal & Keluar dari Sesi
            </button>
        </div>
    </div>
</div>
