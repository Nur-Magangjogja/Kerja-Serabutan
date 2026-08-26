@props(['active' => null])

@php
    if ($active) {
        $isIdentity = ($active === 'identity');
        $isAppearance = ($active === 'appearance');
        $isHelp = ($active === 'help');
        $isWithdraw = ($active === 'withdraw');
        $isBanners = ($active === 'banners');
    } else {
        $isIdentity = request()->routeIs('superadmin.settings.identity*');
        $isAppearance = request()->routeIs('superadmin.settings.appearance*');
        $isHelp = request()->routeIs('superadmin.settings.help*');
        $isWithdraw = request()->routeIs('superadmin.settings.withdraw*');
        $isBanners = request()->routeIs('superadmin.settings.banners*');
    }
@endphp

<div class="mb-6">
    <!-- Header Title -->
    <div class="mb-4">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Pengaturan</h1>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola seluruh konfigurasi sistem, tema tampilan, biaya platform, tarif withdraw, dan banner dari satu tempat.</p>
    </div>

    <!-- Sub-navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 p-1.5 bg-gray-200/70 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 max-w-full shadow-sm">
        <!-- Tab: Identitas Aplikasi -->
        <a href="{{ route('superadmin.settings.identity') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold {{ $isIdentity ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-300/50 dark:hover:bg-gray-700/50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span>Identitas Aplikasi</span>
        </a>

        <!-- Tab: Tema Tampilan -->
        <a href="{{ route('superadmin.settings.appearance') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold {{ $isAppearance ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-300/50 dark:hover:bg-gray-700/50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <span>Tema Tampilan</span>
        </a>

        <!-- Tab: Bantuan & Biaya Admin -->
        <a href="{{ route('superadmin.settings.help') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold {{ $isHelp ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-300/50 dark:hover:bg-gray-700/50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c.79 0 1.5.3 2.04.78L20 14v6a1 1 0 01-1 1h-6l-5.22-5.22A4 4 0 1112 8z" />
            </svg>
            <span>Biaya Layanan Platform</span>
        </a>

        <!-- Tab: Biaya & Rekening Withdraw -->
        <a href="{{ route('superadmin.settings.withdraw') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold {{ $isWithdraw ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-300/50 dark:hover:bg-gray-700/50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Tarif & Bank Withdraw</span>
        </a>

        <!-- Tab: Manajemen Banner -->
        <a href="{{ route('superadmin.settings.banners') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold {{ $isBanners ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-300/50 dark:hover:bg-gray-700/50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Manajemen Banner</span>
        </a>
    </div>
</div>
