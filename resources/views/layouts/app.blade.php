@php
    $isDarkActive = ($isDark ?? (request()->cookie('theme') === 'dark'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $isDarkActive ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark light">

    <!-- Instant Theme Anti-FOUC & Scrollbar Hidden (Executed synchronously before any network requests/fonts) -->
    <style>
        html { color-scheme: light dark; -ms-overflow-style: none !important; scrollbar-width: none !important; }
        html.dark { background-color: #111827 !important; color-scheme: dark; }
        html.dark body { background-color: #111827 !important; color: #f9fafb; }
        html.dark main { background-color: #111827 !important; }
        html:not(.dark) { background-color: #f9fafb !important; color-scheme: light; }
        html:not(.dark) body { background-color: #f9fafb !important; }
        .no-transition, .no-transition * { -webkit-transition: none !important; transition: none !important; }
        html, body, *, *::before, *::after { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        *::-webkit-scrollbar, html::-webkit-scrollbar, body::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    </style>
    <script>
        (function() {
            window.applyTheme = function(mode) {
                try {
                    mode = mode || localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system';
                    if (mode !== 'dark' && mode !== 'light') mode = 'system';
                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var isDark = (mode === 'dark') || (mode === 'system' && prefersDark);
                    var d = document.documentElement;
                    if (isDark) {
                        d.classList.add('dark');
                        d.style.colorScheme = 'dark';
                        d.style.backgroundColor = '#111827';
                        if (document.body) document.body.style.backgroundColor = '#111827';
                    } else {
                        d.classList.remove('dark');
                        d.style.colorScheme = 'light';
                        d.style.backgroundColor = '#f9fafb';
                        if (document.body) document.body.style.backgroundColor = '#f9fafb';
                    }
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
                } catch(e) {}
            };

            window.setTheme = function(mode) {
                if (mode !== 'dark' && mode !== 'light') mode = 'system';
                localStorage.setItem('theme', mode);
                localStorage.setItem('color-theme', mode);
                document.cookie = "theme=" + mode + "; path=/; max-age=31536000; SameSite=Lax";
                window.applyTheme(mode);
            };

            window.getTheme = function() {
                var saved = localStorage.getItem('theme') || localStorage.getItem('color-theme');
                if (saved === 'dark' || saved === 'light') return saved;
                return 'system';
            };

            // Execute immediately on page load
            window.applyTheme();

            document.addEventListener('livewire:navigating', function() { if (window.applyTheme) window.applyTheme(); });
            document.addEventListener('livewire:navigated', function() { if (window.applyTheme) window.applyTheme(); });
        })();
    </script>

    <title>{{ \App\Models\AppSetting::get('app_name', config('app.name', 'SayaBantu')) }}</title>
    <meta name="description" content="{{ \App\Models\AppSetting::get('app_description', 'Solusi bantuan cepat, aman, dan terpercaya.') }}">
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Fonts (Loaded asynchronously / non-blocking) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900|space-grotesk:400,500,600,700|dm-sans:400,500,700,800,900|syne:400,500,600,700,800|nunito:400,600,700,800,900|playfair-display:400,500,600,700,800,900|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>


<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 overflow-x-hidden">
    <!-- Centered Container -->
    <div class="min-h-screen flex items-start justify-center bg-gray-100 dark:bg-gray-950">
        <!-- Mobile Width Container -->
        <div class="w-full max-w-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 relative shadow-2xl">
            <!-- Global notification (toast) for customer actions -->
            <div id="customer-global-notification" class="fixed top-4 inset-x-0 mx-auto w-full max-w-md px-4 pointer-events-none z-[99999]">
                <div id="customer-global-notification-inner" class="mx-auto max-w-md"></div>
            </div>
            <!-- Content -->
            <main class="pb-24">
                @if($__env->hasSection('content'))
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>

            <!-- Floating Glassmorphism Bottom Navigation -->
            @auth
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
                
                @if(auth()->user()->role === 'mitra')
                    <!-- Bottom Navigation Mitra -->
                    <nav id="bottom-nav" class="pointer-events-auto bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl sm:rounded-3xl border border-white/60 dark:border-gray-700/60 shadow-xl shadow-gray-900/10 dark:shadow-black/50 px-2 py-1.5 transition-all">
                        <div class="flex items-center justify-around">
                            <a href="{{ route('mitra.dashboard') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('mitra.dashboard') && !request()->has('tab') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Beranda</span>
                            </a>

                            <a href="{{ route('mitra.helps.all') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('mitra.helps.all') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Bantuan</span>
                            </a>

                            <a href="{{ route('mitra.chat') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('mitra.chat*') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Chat</span>
                            </a>

                            <a href="{{ route('mitra.helps.completed') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('mitra.helps.completed') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h18v14H3V7zm5 3v8h2v-8H8zm4 0v8h2v-8h-2z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Riwayat</span>
                            </a>

                            <a href="{{ route('mitra.profile') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('mitra.profile*') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Profil</span>
                            </a>
                        </div>
                    </nav>
                @else
                    <!-- Bottom Navigation Customer -->
                    <nav id="bottom-nav" class="pointer-events-auto bg-white/20 dark:bg-gray-800/20 backdrop-blur-xl rounded-2xl sm:rounded-3xl border border-white/60 dark:border-gray-700/60 shadow-xl shadow-gray-900/10 dark:shadow-black/50 px-2 py-1.5 transition-all">
                        <div class="flex items-center justify-around">
                            <a href="{{ route('customer.dashboard') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('customer.dashboard') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Beranda</span>
                            </a>

                            <a href="{{ route('customer.helps.index') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('customer.helps.*') && !request()->routeIs('customer.helps.create') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Bantuan</span>
                            </a>

                            <a href="{{ route('customer.helps.create') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('customer.helps.create') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center -mt-1 {{ request()->routeIs('customer.helps.create') ? 'bg-[#0098e7] text-white shadow-md shadow-sky-500/30' : 'bg-gray-100 dark:bg-gray-700/80 text-gray-500 dark:text-gray-300' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <span class="nav-label text-[10px] font-bold mt-0.5 {{ request()->routeIs('customer.helps.create') ? 'text-[#0098e7]' : '' }}">Bantu</span>
                            </a>

                            <a href="{{ route('customer.transactions.index') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('customer.transactions.*') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h18v14H3V7zm5 3v8h2v-8H8zm4 0v8h2v-8h-2z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Transaksi</span>
                            </a>

                            <a href="{{ route('profile') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('profile.*') || request()->routeIs('profile') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Profil</span>
                            </a>
                        </div>
                    </nav>
                @endif
                </div>
            @endauth
        </div>
    </div>

    <script>
        // Safe fallback for Laravel Echo when WebSocket is not active
        if (typeof window !== 'undefined' && typeof window.Echo === 'undefined') {
            window.Echo = {
                socketId: () => undefined,
                private: () => ({ listen: () => ({}), stopListening: () => ({}) }),
                channel: () => ({ listen: () => ({}), stopListening: () => ({}) }),
                join: () => ({ here: () => ({ joining: () => ({ leaving: () => ({ listen: () => ({}) }) }) }) }),
                leave: () => {},
                leaveChannel: () => {},
                connector: {
                    socketId: () => undefined,
                    channels: {},
                }
            };
        }
    </script>

    @livewireScripts
    @stack('scripts')
    {{-- Realtime notifications for customer (invisible) --}}
    @livewire('customer.notifications.realtime')

    <script>
        (function () {
            if (!window.showCustomerNotification) {
                window.showCustomerNotification = function({ title = 'Notifikasi', message = '', url = '#' , timeout = 4000, type = 'success' }) {
                    try {
                        const container = document.getElementById('customer-global-notification-inner');
                        if (!container) return;
                        container.innerHTML = '';

                        const wrap = document.createElement('div');
                        wrap.className = 'bg-white rounded-xl shadow-xl border border-gray-100 p-3 max-w-md mx-3 pointer-events-auto transition transform duration-300';
                        wrap.style.boxShadow = '0 10px 30px rgba(2,6,23,0.08)';

                        // Text-only body (no icons)
                        const body = document.createElement('div');
                        body.className = 'min-w-0';
                        const titleEl = document.createElement('div');
                        titleEl.className = 'text-sm font-semibold text-gray-900';
                        titleEl.innerText = String(title || 'Notifikasi');

                        const msgEl = document.createElement('div');
                        msgEl.className = 'text-xs text-gray-600 mt-0.5';
                        msgEl.innerText = String(message || '');

                        body.appendChild(titleEl);
                        if ((message || '').toString().trim() !== '') body.appendChild(msgEl);

                        wrap.appendChild(body);

                        wrap.addEventListener('click', function (ev) {
                            ev.preventDefault();
                            if (url && url !== '#') {
                                window.location.href = url;
                            }
                            container.innerHTML = '';
                        });

                        container.appendChild(wrap);
                        const effectiveTimeout = (type === 'error' || type === 'warning' || type === 'danger') ? Math.max(timeout, 8000) : timeout;
                        setTimeout(() => { container.innerHTML = ''; }, effectiveTimeout);
                    } catch (err) { console.error('showCustomerNotification error', err); }
                };
            }

            const customerHelpDetailTemplate = "{{ route('customer.helps.detail', ['id' => 'REPLACE_ID']) }}";
            const customerChatRoute = "{{ route('customer.chat') ?? route('mitra.chat') }}";

            if (!window._customerListenersAttached) {
                window._customerListenersAttached = true;

                // Listen for various help status updates
                window.addEventListener('help-new-message', function (e) {
                    const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
                    const from = e && e.detail && e.detail.from ? e.detail.from : 'Mitra';
                    const message = e && e.detail && e.detail.message ? e.detail.message : '';
                    const url = helpId ? customerChatRoute + '?help=' + encodeURIComponent(helpId) : customerChatRoute;
                    window.showCustomerNotification({ title: 'Pesan Baru dari ' + from, message: message || 'Ketuk untuk membuka chat.', url, timeout: 6000, type: 'message' });
                });

                window.addEventListener('help-taken', function (e) {
                    const helpId = e && e.detail && (e.detail.helpId ?? e.detail.help_id) ? (e.detail.helpId ?? e.detail.help_id) : null;
                    const helpTitle = e && e.detail && (e.detail.helpTitle ?? e.detail.help_title) ? (e.detail.helpTitle ?? e.detail.help_title) : null;
                    const mitraName = e && e.detail && (e.detail.mitraName ?? e.detail.mitra_name) ? (e.detail.mitraName ?? e.detail.mitra_name) : 'Mitra';
                    const url = helpId ? customerHelpDetailTemplate.replace('REPLACE_ID', helpId) : '#';
                    const message = e && e.detail && e.detail.message ? e.detail.message : (helpTitle ? `${mitraName} telah mengambil bantuan Anda: ${helpTitle}` : `${mitraName} telah mengambil bantuan Anda. Ketuk untuk melihat detail.`);
                    const title = helpTitle ? `Bantuan: ${helpTitle}` : '\u2705 Bantuan Diambil!';
                    window.showCustomerNotification({ 
                        title, 
                        message, 
                        url, 
                        type: 'taken', 
                        timeout: 6000 
                    });
                });

                window.addEventListener('help-on-the-way', function (e) {
                    const helpId = e && e.detail && (e.detail.helpId ?? e.detail.help_id) ? (e.detail.helpId ?? e.detail.help_id) : null;
                    const helpTitle = e && e.detail && (e.detail.helpTitle ?? e.detail.help_title) ? (e.detail.helpTitle ?? e.detail.help_title) : null;
                    const mitraName = e && e.detail && (e.detail.mitraName ?? e.detail.mitra_name) ? (e.detail.mitraName ?? e.detail.mitra_name) : 'Mitra';
                    const url = helpId ? customerHelpDetailTemplate.replace('REPLACE_ID', helpId) : '#';
                    const message = e && e.detail && e.detail.message ? e.detail.message : (helpTitle ? `${mitraName} sedang menuju lokasi bantuan '${helpTitle}'. Ketuk untuk tracking.` : `${mitraName} sedang menuju lokasi Anda. Ketuk untuk tracking.`);
                    const title = helpTitle ? `Dalam Perjalanan: ${helpTitle}` : '\ud83d\ude80 Mitra Dalam Perjalanan';
                    window.showCustomerNotification({ 
                        title, 
                        message, 
                        url, 
                        type: 'on_the_way', 
                        timeout: 7000 
                    });
                });

                window.addEventListener('help-arrived', function (e) {
                    const helpId = e && e.detail && (e.detail.helpId ?? e.detail.help_id) ? (e.detail.helpId ?? e.detail.help_id) : null;
                    const helpTitle = e && e.detail && (e.detail.helpTitle ?? e.detail.help_title) ? (e.detail.helpTitle ?? e.detail.help_title) : null;
                    const mitraName = e && e.detail && (e.detail.mitraName ?? e.detail.mitra_name) ? (e.detail.mitraName ?? e.detail.mitra_name) : 'Mitra';
                    const url = helpId ? customerHelpDetailTemplate.replace('REPLACE_ID', helpId) : '#';
                    const message = e && e.detail && e.detail.message ? e.detail.message : (helpTitle ? `${mitraName} telah tiba untuk bantuan '${helpTitle}'. Silakan konfirmasi.` : `${mitraName} telah tiba di lokasi Anda. Silakan konfirmasi.`);
                    const title = helpTitle ? `Tiba: ${helpTitle}` : '\ud83d\udccd Mitra Sudah Sampai!';
                    window.showCustomerNotification({ 
                        title, 
                        message, 
                        url, 
                        type: 'arrived', 
                        timeout: 8000 
                    });
                });

                window.addEventListener('help-completed', function (e) {
                    const helpId = e && e.detail && (e.detail.helpId ?? e.detail.help_id) ? (e.detail.helpId ?? e.detail.help_id) : null;
                    const helpTitle = e && e.detail && (e.detail.helpTitle ?? e.detail.help_title) ? (e.detail.helpTitle ?? e.detail.help_title) : null;
                    const mitraName = e && e.detail && (e.detail.mitraName ?? e.detail.mitra_name) ? (e.detail.mitraName ?? e.detail.mitra_name) : 'Mitra';
                    const url = helpId ? customerHelpDetailTemplate.replace('REPLACE_ID', helpId) : '#';
                    const message = e && e.detail && e.detail.message ? e.detail.message : (helpTitle ? `Bantuan '${helpTitle}' telah diselesaikan oleh ${mitraName}. Beri rating mitra Anda.` : `Bantuan telah diselesaikan oleh ${mitraName}. Beri rating mitra Anda.`);
                    const title = helpTitle ? `Selesai: ${helpTitle}` : '\ud83c\udf89 Bantuan Selesai!';
                    window.showCustomerNotification({ 
                        title, 
                        message, 
                        url, 
                        type: 'completed', 
                        timeout: 8000 
                    });
                });

                window.addEventListener('help-status-update', function (e) {
                    try {
                        const detail = e && e.detail ? e.detail : {};
                        const normalized = (detail.data) ? detail.data : (Array.isArray(detail) && detail.length ? detail[0] : detail);

                        const read = (obj, keys) => {
                            for (let k of keys) {
                                if (!obj) continue;
                                if (Object.prototype.hasOwnProperty.call(obj, k) && obj[k] !== null && obj[k] !== undefined && String(obj[k]) !== '') return obj[k];
                            }
                            return null;
                        };

                        const helpId = read(normalized, ['helpId','help_id','id']);
                        const helpTitle = read(normalized, ['helpTitle','help_title','title']);
                        const mitraName = read(normalized, ['mitraName','mitra_name','mitra']) || 'Mitra';

                        const status = read(normalized, ['newStatus','new_status','status','state']) || '';
                        const payloadMessage = read(normalized, ['message','msg','text']) || null;

                        const url = helpId ? customerHelpDetailTemplate.replace('REPLACE_ID', helpId) : '#';

                        let fallbackMessage = 'Status bantuan diperbarui';
                        if (status) {
                            const s = String(status).toLowerCase();
                            if (s.includes('partner_on_the_way') || s.includes('on_the_way') || s.includes('perjalanan')) {
                                fallbackMessage = helpTitle ? `${mitraName} sedang menuju lokasi untuk bantuan '${helpTitle}'.` : `${mitraName} sedang menuju lokasi bantuan Anda.`;
                            } else if (s.includes('partner_arrived') || s.includes('arrived') || s.includes('sampai')) {
                                fallbackMessage = helpTitle ? `${mitraName} telah tiba untuk bantuan '${helpTitle}'.` : `${mitraName} telah tiba di lokasi Anda.`;
                            } else if (s.includes('selesai') || s.includes('completed')) {
                                fallbackMessage = helpTitle ? `Bantuan '${helpTitle}' telah selesai.` : 'Bantuan telah selesai.';
                            } else if (s.includes('diambil') || s.includes('taken')) {
                                fallbackMessage = helpTitle ? `${mitraName} telah mengambil bantuan '${helpTitle}'.` : `${mitraName} telah mengambil bantuan Anda.`;
                            }
                        }

                        const message = payloadMessage || fallbackMessage;

                        let type = 'info';
                        let title = '🔔 Update Status';
                        const sLower = String(status).toLowerCase();
                        if (sLower.includes('selesai') || sLower.includes('completed')) {
                            type = 'completed';
                            title = helpTitle ? `Selesai: ${helpTitle}` : 'Bantuan Selesai!';
                        } else if (sLower.includes('sampai') || sLower.includes('arrived')) {
                            type = 'arrived';
                            title = helpTitle ? `Tiba: ${helpTitle}` : 'Mitra Sudah Sampai!';
                        } else if (sLower.includes('perjalanan') || sLower.includes('on_the_way') || sLower.includes('partner_on_the_way')) {
                            type = 'on_the_way';
                            title = helpTitle ? `Dalam Perjalanan: ${helpTitle}` : 'Mitra Dalam Perjalanan';
                        } else if (sLower.includes('diambil') || sLower.includes('taken')) {
                            type = 'taken';
                            title = helpTitle ? `Diambil: ${helpTitle}` : 'Bantuan Diambil!';
                        }

                        window.showCustomerNotification({ title, message, url, type, timeout: 7000 });
                    } catch (err) { console.error('help-status-update handler error', err); }
                });

                window.addEventListener('customer-toast', function (e) {
                    try {
                        const d = e && e.detail ? e.detail : {};
                        window.showCustomerNotification({
                            title: d.title || 'Notifikasi',
                            message: d.message || '',
                            url: d.url || '#',
                            timeout: d.timeout || 4000,
                            type: d.type || 'info'
                        });
                    } catch (err) { console.error('customer-toast handler error', err); }
                });

                // Listen for Livewire dispatch to open Midtrans Snap (Nonaktif / Disabled)
                // window.addEventListener('openMidtransSnap', function (e) {
                //     ...
                // });
            }
        })();
    </script>
    <script>
        // Toggle blur on bottom nav and any elements with `.blur-on-modal` when a modal is present in DOM.
        function checkConfirmModalAndToggleBlur() {
            try {
                var modal = document.querySelector('[data-confirm-modal], [data-transaction-modal], [data-tracking-modal]');
                var nav = document.querySelector('#bottom-nav');
                var extras = document.querySelectorAll('.blur-on-modal');

                if (nav) {
                    if (modal) {
                        nav.classList.add('filter', 'blur-sm');
                    } else {
                        nav.classList.remove('filter', 'blur-sm');
                    }
                }

                if (extras && extras.length) {
                    extras.forEach(function (el) {
                        if (modal) {
                            el.classList.add('filter', 'blur-sm');
                        } else {
                            el.classList.remove('filter', 'blur-sm');
                        }
                    });
                }
            } catch (e) {
                console.warn('checkConfirmModalAndToggleBlur error', e);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            checkConfirmModalAndToggleBlur();
        });

        // Livewire fires these events after DOM updates
        window.addEventListener('livewire:load', function () {
            checkConfirmModalAndToggleBlur();
        });

        window.addEventListener('livewire:update', function () {
            checkConfirmModalAndToggleBlur();
        });

        // Also observe mutations to catch cases where Livewire doesn't trigger events
        try {
            var observer = new MutationObserver(function () { checkConfirmModalAndToggleBlur(); });
            observer.observe(document.body, { childList: true, subtree: true });
        } catch (e) {
            // ignore
        }
    </script>
</body>

</html>