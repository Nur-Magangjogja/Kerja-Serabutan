<?php

use App\Livewire\Actions\CancelRegistration;
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

        if (in_array($user->role ?? '', ['admin', 'super_admin', 'superadmin'])) {
            $route = in_array($user->role, ['super_admin', 'superadmin']) ? 'superadmin.dashboard' : 'admin.dashboard';
            $this->redirect(route($route), navigate: true);
            return;
        }

        if ($user->verified && $user->status === 'active') {
            $route = $user->role === 'mitra' ? 'mitra.dashboard' : 'customer.dashboard';
            $this->redirect(route($route), navigate: true);
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

    public function cancelRegistration(CancelRegistration $cancelRegistration): void
    {
        $cancelRegistration();
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="space-y-6"
    x-data="{
        confirmCancelModal: false,
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
            <button @click="confirmCancelModal = true" type="button"
                class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition-colors py-1.5 px-3 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Batalkan Pembuatan Akun & Masuk</span>
            </button>
        </div>
    </div>

    <!-- Modal Konfirmasi Pembatalan Pendaftaran (Alpine.js) -->
    <div x-show="confirmCancelModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop Blur Overlay -->
        <div x-show="confirmCancelModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             @click="confirmCancelModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="confirmCancelModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl border border-gray-100 dark:border-gray-700 transition-all sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7">
                
                <div class="flex items-start gap-4">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 sm:mx-0 shadow-xs">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="text-left flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white" id="modal-title">
                            Batalkan Pembuatan Akun?
                        </h3>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan pendaftaran ini? Akun belum terverifikasi Anda akan dihapus dan Anda akan dialihkan kembali ke halaman <strong>Masuk (Login)</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    <button type="button" 
                        @click="confirmCancelModal = false"
                        class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 transition cursor-pointer">
                        Lanjutkan Verifikasi
                    </button>
                    <button type="button" 
                        wire:click="cancelRegistration"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 text-xs font-bold shadow-sm transition active:scale-[0.98] disabled:opacity-50 cursor-pointer">
                        <svg wire:loading wire:target="cancelRegistration" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="cancelRegistration">Ya, Batalkan & Keluar</span>
                        <span wire:loading wire:target="cancelRegistration">Membatalkan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
