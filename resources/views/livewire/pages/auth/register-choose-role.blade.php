<?php
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function choose($role): void
    {
        // Normalize accepted roles
        $allowed = ['customer', 'mitra'];
        $role = in_array($role, $allowed) ? $role : 'customer';

        Session::put('registration_role', $role);
        Cookie::queue('registration_role', $role, 60 * 24 * 7);

        // Clear any previous registration UUID to start fresh
        Session::forget('registration_uuid');
        Cookie::queue(Cookie::forget('registration_uuid'));
        Cookie::queue(Cookie::forget('registration_step1_draft'));

        $this->redirect(route('register.step1'), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <!-- Header Title -->
    <div class="text-center">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-600/20 p-3.5 mb-3 items-center justify-center">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pilih Peran Akun Anda</h2>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto leading-relaxed">
            Tentukan bagaimana Anda ingin menggunakan platform kami.
        </p>
    </div>

    <!-- Role Selection Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Customer Card -->
        <button wire:click="choose('customer')" type="button"
            class="group p-5 bg-gray-50/70 hover:bg-primary-50/50 dark:bg-gray-750/50 dark:hover:bg-primary-950/40 rounded-2xl border-2 border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-all text-left cursor-pointer shadow-xs hover:shadow-md active:scale-[0.98]">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950/70 text-primary-600 dark:text-sky-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-sky-400 transition-colors">Customer</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                Meminta bantuan & memesan layanan untuk kebutuhan sehari-hari.
            </p>
            <div class="mt-4 flex items-center text-xs font-bold text-primary-600 dark:text-sky-400 gap-1">
                <span>Daftar sebagai Customer</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </button>

        <!-- Mitra Card -->
        <button wire:click="choose('mitra')" type="button"
            class="group p-5 bg-gray-50/70 hover:bg-emerald-50/50 dark:bg-gray-750/50 dark:hover:bg-emerald-950/40 rounded-2xl border-2 border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 transition-all text-left cursor-pointer shadow-xs hover:shadow-md active:scale-[0.98]">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Mitra</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                Menjadi penyedia layanan, memberi bantuan & mendapatkan penghasilan.
            </p>
            <div class="mt-4 flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400 gap-1">
                <span>Daftar sebagai Mitra</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </button>
    </div>

    <!-- Footer Note -->
    <div class="text-center pt-2">
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline font-semibold ml-0.5">
                Masuk ke Akun
            </a>
        </p>
    </div>
</div>