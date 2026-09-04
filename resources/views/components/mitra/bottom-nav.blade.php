@auth
    @if(auth()->user()->role === 'mitra')
        <div x-data="{
            isInputFocused: false,
            init() {
                const handleFocusIn = (e) => {
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) && e.target.type !== 'checkbox' && e.target.type !== 'radio') {
                        this.isInputFocused = true;
                    }
                };
                const handleFocusOut = () => {
                    setTimeout(() => {
                        const active = document.activeElement;
                        if (!active || !['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName)) {
                            this.isInputFocused = false;
                        }
                    }, 120);
                };
                window.addEventListener('focusin', handleFocusIn);
                window.addEventListener('focusout', handleFocusOut);
            }
        }"
        x-show="!isInputFocused"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-4 inset-x-0 mx-auto w-full max-w-md px-3 sm:px-4 z-50 pointer-events-none">
            <nav id="bottom-nav" class="pointer-events-auto h-16 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl border border-white/60 dark:border-gray-700/60 shadow-xl shadow-gray-900/10 dark:shadow-black/50 px-2 py-1.5 transition-all">
                <div class="flex items-center justify-around h-full">
                    <a href="{{ route('mitra.dashboard') }}" wire:navigate
                        class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-2 rounded-2xl transition {{ request()->routeIs('mitra.dashboard') && !request()->has('tab') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                        </svg>
                        <span class="nav-label text-[11px] font-semibold mt-0.5">Beranda</span>
                    </a>

                    <a href="{{ route('mitra.helps.all') }}" wire:navigate
                        class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-2 rounded-2xl transition {{ request()->routeIs('mitra.helps.all') || request()->routeIs('mitra.helps.processing') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" />
                        </svg>
                        <span class="nav-label text-[11px] font-semibold mt-0.5">Bantuan</span>
                    </a>

                    <a href="{{ route('mitra.helps.completed') }}" wire:navigate
                        class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-2 rounded-2xl transition {{ request()->routeIs('mitra.helps.completed') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 3h18v2H3V3zm0 4h18v14H3V7zm5 3v8h2v-8H8zm4 0v8h2v-8h-2z" />
                        </svg>
                        <span class="nav-label text-[11px] font-semibold mt-0.5">Riwayat</span>
                    </a>

                    <a href="{{ route('mitra.profile') }}" wire:navigate
                        class="nav-item flex-1 flex flex-col items-center justify-center py-1 px-2 rounded-2xl transition {{ request()->routeIs('mitra.profile*') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                        </svg>
                        <span class="nav-label text-[11px] font-semibold mt-0.5">Profil</span>
                    </a>
                </div>
            </nav>
        </div>
    @endif
@endauth
