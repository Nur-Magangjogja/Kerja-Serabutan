<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
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

<div class="space-y-6">
    <div class="text-center">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-indigo-600 text-white shadow-lg shadow-primary-500/25 p-3.5 mb-3 items-center justify-center">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Verifikasi Alamat Email</h2>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto leading-relaxed">
            Terima kasih telah mendaftar! Silakan klik tautan verifikasi yang baru saja kami kirimkan ke email Anda.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-300 font-medium leading-relaxed">
                Tautan verifikasi baru telah berhasil dikirim ke alamat email Anda.
            </span>
        </div>
    @endif

    <div class="space-y-3 pt-2">
        <button wire:click="sendVerification" type="button"
            class="w-full bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Kirim Ulang Email Verifikasi
        </button>

        <div class="text-center pt-2">
            <button wire:click="logout" type="button"
                class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline cursor-pointer">
                Keluar dari Sesi
            </button>
        </div>
    </div>
</div>
