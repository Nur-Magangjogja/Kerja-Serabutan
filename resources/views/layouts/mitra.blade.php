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
                    var currentlyDark = d.classList.contains('dark');

                    if (isDark !== currentlyDark) {
                        var css = document.createElement('style');
                        css.type = 'text/css';
                        css.appendChild(document.createTextNode('*,*::before,*::after{-webkit-transition:none!important;-moz-transition:none!important;-o-transition:none!important;-ms-transition:none!important;transition:none!important}'));
                        document.head.appendChild(css);

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

                        setTimeout(function() {
                            if (css.parentNode) css.parentNode.removeChild(css);
                        }, 50);
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

    <title>{{ $title ?? \App\Models\AppSetting::get('app_name', 'SayaBantu') }} - Mitra</title>
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Fonts (Loaded asynchronously / non-blocking) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

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
            <!-- Global notification (toast) for mitra actions -->
            <div id="mitra-global-notification" class="fixed top-4 inset-x-0 mx-auto w-full max-w-md px-4 pointer-events-none z-[99999]">
                <div id="mitra-global-notification-inner" class="mx-auto max-w-md"></div>
            </div>
            <!-- Content -->
            <div class="flex flex-col min-h-screen">
                <!-- Livewire -->
                <div class="flex-1 pb-24">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>

                <!-- Floating Glassmorphism Bottom Navigation Bar -->
                <div class="fixed bottom-4 inset-x-0 mx-auto w-full max-w-md px-3 sm:px-4 z-50 pointer-events-none">
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

                            <a href="{{ route('mitra.helps.processing') }}" wire:navigate
                                class="nav-item flex flex-col items-center py-1.5 px-3 rounded-2xl transition {{ request()->routeIs('mitra.helps.processing') ? 'text-primary-600 dark:text-primary-400 font-bold active' : 'text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400' }}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z" />
                                </svg>
                                <span class="nav-label text-[11px] font-semibold mt-0.5">Diproses</span>
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
                </div>
            </div>
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
    @include('partials.help-modal')

    {{-- Realtime notifications poll component (invisible) --}}
    @livewire('mitra.notifications.realtime')

    <script>
        (function () {
            if (!window.showMitraNotification) {
                window.showMitraNotification = function({ title = 'Notifikasi', message = '', url = '#' , timeout = 4000, type = 'success' }) {
                    try {
                        const container = document.getElementById('mitra-global-notification-inner');
                        if (!container) return;
                        container.innerHTML = '';

                        const wrap = document.createElement('div');
                        wrap.className = 'bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 p-3 max-w-md mx-3 pointer-events-auto transition transform duration-300';
                        wrap.style.boxShadow = '0 10px 30px rgba(2,6,23,0.08)';

                        // Text-only body (matching customer notification style)
                        const body = document.createElement('div');
                        body.className = 'min-w-0';
                        const titleEl = document.createElement('div');
                        titleEl.className = 'text-sm font-semibold text-gray-900 dark:text-white';
                        titleEl.innerText = String(title || 'Notifikasi');

                        const msgEl = document.createElement('div');
                        msgEl.className = 'text-xs text-gray-600 dark:text-gray-300 mt-0.5';
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
                    } catch (err) { console.error('showMitraNotification error', err); }
                };
            }

            const mitraHelpDetailTemplate = "{{ route('mitra.helps.detail', ['id' => 'REPLACE_ID']) }}";
            const mitraChatRoute = "{{ route('mitra.chat') }}";

            if (!window._mitraListenersAttached) {
                window._mitraListenersAttached = true;

                window.addEventListener('help-taken', function (e) {
                    const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
                    const url = helpId ? mitraHelpDetailTemplate.replace('REPLACE_ID', helpId) : mitraHelpDetailTemplate.replace('REPLACE_ID', '');
                    window.showMitraNotification({ title: 'Bantuan Diambil', message: 'Anda berhasil mengambil bantuan. Ketuk untuk melihat detail.', url });
                });

                window.addEventListener('message-sent', function (e) {
                    const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
                    const url = helpId ? mitraChatRoute + '?help=' + encodeURIComponent(helpId) : mitraChatRoute;
                    window.showMitraNotification({ title: 'Pesan Terkirim', message: 'Pesan berhasil dikirim. Ketuk untuk membuka chat.', url });
                });

                window.addEventListener('help-new-message', function (e) {
                    const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
                    const from = e && e.detail && e.detail.from ? e.detail.from : 'Customer';
                    const message = e && e.detail && e.detail.message ? e.detail.message : '';
                    const url = helpId ? mitraChatRoute + '?help=' + encodeURIComponent(helpId) : mitraChatRoute;
                    window.showMitraNotification({ title: 'Pesan Baru dari ' + from, message: message || 'Ketuk untuk membuka chat.', url, timeout: 6000 });
                });

                window.triggerMitraNotification = function (payload) { window.showMitraNotification(payload || {}); };
            }

            if (!window.togglePassword) {
                window.togglePassword = function(fieldId) {
                    const field = document.getElementById(fieldId);
                    if (!field) return;
                    field.type = (field.type === 'password') ? 'text' : 'password';
                };
            }
        })();
    </script>
    @stack('scripts')
</body>

</html>