<div x-data="{ 
    currentTheme: (typeof window.getTheme === 'function' ? window.getTheme() : (localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system')),
    changeTheme(mode) {
        this.currentTheme = mode;
        if (typeof window.setTheme === 'function') {
            window.setTheme(mode);
        } else {
            localStorage.setItem('theme', mode);
            localStorage.setItem('color-theme', mode);
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = (mode === 'dark') || (mode === 'system' && prefersDark);
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.colorScheme = 'dark';
                document.documentElement.style.backgroundColor = '#111827';
                if (document.body) document.body.style.backgroundColor = '#111827';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.style.colorScheme = 'light';
                document.documentElement.style.backgroundColor = '#f3f4f6';
                if (document.body) document.body.style.backgroundColor = '#f3f4f6';
            }
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
        }
    }
}" 
@theme-changed.window="currentTheme = $event.detail.theme"
class="w-full sm:w-auto grid grid-cols-3 sm:inline-flex p-1 sm:p-1.5 bg-gray-200/50 dark:bg-gray-800/50 backdrop-blur-md rounded-2xl gap-1 sm:gap-1.5 border border-black/[0.06] dark:border-white/[0.08] shadow-inner max-w-full">

    <!-- Light Mode Button (Putih) -->
    <button type="button"
        @click="changeTheme('light')"
        class="flex items-center justify-center gap-1.5 sm:gap-2 px-2.5 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm cursor-pointer min-w-0 transition-colors duration-150"
        :class="currentTheme === 'light' 
            ? 'bg-white text-gray-900 shadow-sm border border-black/[0.06] dark:border-transparent font-semibold z-10' 
            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] border border-transparent font-medium'">
        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span class="truncate">
            <span class="sm:hidden">Light</span>
            <span class="hidden sm:inline">Terang (Light)</span>
        </span>
    </button>

    <!-- Dark Mode Button (Hitam) -->
    <button type="button"
        @click="changeTheme('dark')"
        class="flex items-center justify-center gap-1.5 sm:gap-2 px-2.5 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm cursor-pointer min-w-0 transition-colors duration-150"
        :class="currentTheme === 'dark' 
            ? 'bg-gray-900 text-white shadow-sm border border-transparent dark:border-white/[0.12] font-semibold z-10' 
            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] border border-transparent font-medium'">
        <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        <span class="truncate">
            <span class="sm:hidden">Dark</span>
            <span class="hidden sm:inline">Gelap (Dark)</span>
        </span>
    </button>

    <!-- System Button (Tetap Biru) -->
    <button type="button"
        @click="changeTheme('system')"
        class="flex items-center justify-center gap-1.5 sm:gap-2 px-2.5 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm cursor-pointer min-w-0 transition-colors duration-150"
        :class="currentTheme === 'system' 
            ? 'bg-primary-600 text-white shadow-sm border border-transparent font-semibold z-10' 
            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] border border-transparent font-medium'">
        <svg class="w-4 h-4 flex-shrink-0" :class="currentTheme === 'system' ? 'text-white' : 'text-primary-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        <span class="truncate">
            <span class="sm:hidden">Auto</span>
            <span class="hidden sm:inline">Sistem (Auto)</span>
        </span>
    </button>
</div>
