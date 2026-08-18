<div x-data="{ 
    currentTheme: (window.getTheme ? window.getTheme() : (localStorage.getItem('theme') || 'system')),
    changeTheme(mode) {
        this.currentTheme = mode;
        if (typeof window.setTheme === 'function') {
            window.setTheme(mode);
        } else {
            localStorage.setItem('theme', mode);
            localStorage.setItem('color-theme', mode);
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
        }
    }
}" 
@theme-changed.window="currentTheme = $event.detail.theme"
class="inline-flex p-1.5 bg-gray-200 dark:bg-gray-800 rounded-2xl gap-1.5 border border-gray-300 dark:border-gray-700 transition-colors duration-200 shadow-inner">

    <!-- Light Mode Button (Putih) -->
    <button type="button"
        @click="changeTheme('light')"
        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 cursor-pointer"
        :class="currentTheme === 'light' 
            ? 'bg-white text-gray-900 shadow-md ring-2 ring-gray-300/80 dark:ring-gray-600 scale-105' 
            : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-700/50'">
        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span>Terang (Light)</span>
    </button>

    <!-- Dark Mode Button (Hitam) -->
    <button type="button"
        @click="changeTheme('dark')"
        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 cursor-pointer"
        :class="currentTheme === 'dark' 
            ? 'bg-gray-950 text-white shadow-md ring-2 ring-gray-700/80 scale-105' 
            : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-700/50'">
        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        <span>Gelap (Dark)</span>
    </button>

    <!-- System Button (Tetap Biru) -->
    <button type="button"
        @click="changeTheme('system')"
        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 cursor-pointer"
        :class="currentTheme === 'system' 
            ? 'bg-primary-600 text-white shadow-md ring-2 ring-primary-400/50 scale-105' 
            : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/80 dark:hover:bg-gray-700/50'">
        <svg class="w-4 h-4" :class="currentTheme === 'system' ? 'text-white' : 'text-primary-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        <span>Sistem (Auto)</span>
    </button>
</div>
