<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function continueToDashboard(): void
    {
        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="space-y-6 text-center py-2">
    <!-- Success Icon Animation -->
    <div class="relative inline-flex items-center justify-center">
        <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 rounded-3xl flex items-center justify-center shadow-lg shadow-emerald-500/20 border-2 border-emerald-300 dark:border-emerald-700">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <!-- Celebration badges -->
        <span class="absolute -top-2 -right-2 text-2xl animate-bounce">🎉</span>
    </div>

    <!-- Title & Description -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pendaftaran Berhasil!</h2>
        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-2 max-w-sm mx-auto leading-relaxed">
            Akun Anda telah berhasil dibuat dan saat ini sedang dalam proses verifikasi oleh tim Admin.
        </p>
    </div>

    <!-- Status Cards Checklist -->
    <div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 text-left space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Data Diri & KTP Tersimpan</h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Informasi identitas Anda tersimpan dengan aman.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Foto Berkas Terunggah</h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Foto KTP dan selfie siap diverifikasi.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Menunggu Persetujuan Admin</h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Proses review verifikasi berkas 1x24 jam.</p>
            </div>
        </div>
    </div>

    <!-- Info Next Step -->
    <div class="bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-2xl p-4 text-left flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <div class="text-xs text-blue-800 dark:text-blue-200 leading-relaxed">
            <span class="font-bold block mb-0.5">Langkah Berikutnya</span>
            Setelah disetujui, Anda dapat langsung masuk dengan email dan kata sandi yang telah Anda buat.
        </div>
    </div>

    <!-- Action Button -->
    <div class="pt-2">
        <a href="{{ route('login') }}" wire:navigate
            class="w-full inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
            Masuk ke Halaman Login
        </a>
    </div>
</div>