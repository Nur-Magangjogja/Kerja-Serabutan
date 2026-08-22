<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function startRegistration(): void
    {
        // Redirect ke step 1 untuk registrasi multi-step
        $this->redirect(route('register.choose-role'), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <!-- Header Icon & Title -->
    <div class="text-center">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-600/20 p-3.5 mb-3 items-center justify-center">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Akun Baru</h2>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto leading-relaxed">
            Bergabung sekarang untuk mulai memberi atau menerima bantuan.
        </p>
    </div>

    <!-- Preparation Checklist Card -->
    <div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80">
        <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-primary-600"></span>
            Dokumen yang Perlu Disiapkan:
        </h3>
        <div class="space-y-3 text-xs sm:text-sm">
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-emerald-200/60 dark:border-emerald-800/60">
                    1
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white">Data Identitas (KTP)</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">NIK 16 digit, nama lengkap, dan domisili sesuai KTP.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-blue-200/60 dark:border-blue-800/60">
                    2
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white">Foto KTP</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Foto e-KTP jelas, terang, dan tidak buram.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-purple-200/60 dark:border-purple-800/60">
                    3
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white">Foto Selfie + KTP</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Foto wajah memegang KTP untuk verifikasi identitas.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-amber-200/60 dark:border-amber-800/60">
                    4
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white">Email & Kata Sandi</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Email aktif untuk masuk dan menerima notifikasi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Warning Alert -->
    <div class="bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <div class="text-xs text-blue-800 dark:text-blue-200 leading-relaxed">
            <span class="font-bold block mb-0.5">Verifikasi Terlindungi</span>
            Data Anda dienkripsi dan hanya digunakan untuk keperluan keamanan transaksi komunitas.
        </div>
    </div>

    <!-- Actions -->
    <div class="space-y-3 pt-2">
        <button wire:click="startRegistration" type="button" 
            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
            <span>Mulai Pendaftaran</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>

        <div class="text-center">
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline font-semibold ml-0.5">
                    Masuk Sekarang
                </a>
            </p>
        </div>
    </div>
</div>