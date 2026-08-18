<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? \App\Models\AppSetting::get('app_name', 'SayaBantu') }} - Mitra</title>
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Flowbite & Global Theme Initialization Script (Anti-FOUC) -->
    <script>
        window.getTheme = function() {
            return localStorage.getItem('color-theme') || localStorage.getItem('theme') || 'system';
        };

        window.applyTheme = function(mode) {
            mode = mode || window.getTheme();
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
        };

        window.setTheme = function(mode) {
            localStorage.setItem('theme', mode);
            localStorage.setItem('color-theme', mode);
            window.applyTheme(mode);
        };

        // Execute immediately before DOM render
        window.applyTheme();

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (window.getTheme() === 'system') {
                    window.applyTheme('system');
                }
            });
        }
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    <!-- Centered Container -->
    <div class="min-h-screen flex items-start justify-center bg-gray-100 dark:bg-gray-950 transition-colors duration-200">
        <!-- Mobile Width Container -->
        <div class="w-full max-w-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 relative shadow-2xl transition-colors duration-200">
            <!-- Global notification (toast) for mitra actions -->
            <div id="mitra-global-notification" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-60 pointer-events-none" style="max-width:448px; width:100vw;">
                <div id="mitra-global-notification-inner" class="mx-auto max-w-md"></div>
            </div>
            <!-- Content -->
            <div class="flex flex-col min-h-screen">
                <!-- Main Content -->
                <div class="flex-1">
                    {{ $slot }}
                </div>

                <!-- Bottom Navigation Bar -->
                <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-2xl z-50 transition-colors duration-200"
                    style="max-width: 448px; width: 100vw;">
                    <div class="max-w-md mx-auto flex items-center justify-around px-4 py-2.5">
                        <a href="{{ route('mitra.dashboard') }}"
                            class="flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.dashboard') && !request()->has('tab') ? 'text-primary-600' : 'text-gray-400 hover:text-primary-600' }} transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="text-xs font-bold mt-0.5">Beranda</span>
                        </a>
                        <a href="{{ route('mitra.helps.all') }}"
                            class="flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.helps.all') ? 'text-primary-600' : 'text-gray-400 hover:text-primary-600' }} transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span class="text-xs font-bold mt-0.5">Bantuan</span>
                        </a>
                        <a href="{{ route('mitra.helps.processing') }}"
                            class="flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.helps.processing') ? 'text-primary-600' : 'text-gray-400 hover:text-primary-600' }} transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 5h18v2H3V5zm0 6h12v2H3v-2zm0 6h8v2H3v-2z" />
                            </svg>
                            <span class="text-xs font-bold mt-0.5">Diproses</span>
                        </a>
                        <a href="{{ route('mitra.helps.completed') }}"
                            class="flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.helps.completed') ? 'text-primary-600' : 'text-gray-400 hover:text-primary-600' }} transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-xs font-bold mt-0.5">Riwayat</span>
                        </a>
                        <a href="{{ route('mitra.profile') }}"
                            class="flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.profile') ? 'text-primary-600' : 'text-gray-400 hover:text-primary-600' }} transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs font-bold mt-0.5">Profil</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
    @include('partials.help-modal')

    {{-- Realtime notifications poll component (invisible) --}}
    @livewire('mitra.realtime-notifications')

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
            } else {
                field.type = 'password';
            }
        }
    </script>

    <script>
        // Utility to show a temporary clickable toast in the mitra header area.
        function showMitraNotification({ title = 'Notifikasi', message = '', url = '#' , timeout = 4000 }) {
            try {
                const container = document.getElementById('mitra-global-notification-inner');
                if (!container) return;

                // Clear any existing notification (single toast at a time)
                container.innerHTML = '';

                const wrap = document.createElement('div');
                wrap.className = 'bg-white rounded-xl shadow-lg border border-gray-100 p-3 flex items-start gap-3 max-w-md mx-3 pointer-events-auto transition transform duration-300';
                wrap.style.boxShadow = '0 6px 20px rgba(0,0,0,0.08)';

                const icon = document.createElement('div');
                icon.className = 'w-10 h-10 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 flex-shrink-0';
                icon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341"/></svg>';

                const body = document.createElement('div');
                body.className = 'flex-1 min-w-0';
                body.innerHTML = `<div class="text-sm font-semibold text-gray-900">${escapeHtml(title)}</div><div class="text-xs text-gray-600 mt-0.5">${escapeHtml(message)}</div>`;

                const link = document.createElement('a');
                link.href = url || '#';
                link.className = 'contents';
                // Wrap clickable area
                wrap.appendChild(icon);
                wrap.appendChild(body);

                // When clicked, navigate to target and clear toast
                wrap.addEventListener('click', function (ev) {
                    ev.preventDefault();
                    if (url && url !== '#') {
                        window.location.href = url;
                    }
                    container.innerHTML = '';
                });

                container.appendChild(wrap);

                // auto-hide
                setTimeout(() => {
                    container.innerHTML = '';
                }, timeout);
            } catch (err) {
                console.error('showMitraNotification error', err);
            }
        }

        function escapeHtml(unsafe) {
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Build route templates (replace placeholder REPLACE_ID with actual id when available)
        const mitraHelpDetailTemplate = "{{ route('mitra.helps.detail', ['id' => 'REPLACE_ID']) }}";
        const mitraChatRoute = "{{ route('mitra.chat') }}";

        // Listen for Livewire/browser events used elsewhere in the app
        window.addEventListener('help-taken', function (e) {
            const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
            const url = helpId ? mitraHelpDetailTemplate.replace('REPLACE_ID', helpId) : mitraHelpDetailTemplate.replace('REPLACE_ID', '');
            showMitraNotification({ title: 'Bantuan Diambil', message: 'Anda berhasil mengambil bantuan. Ketuk untuk melihat detail.', url });
        });

        window.addEventListener('message-sent', function (e) {
            // If event includes helpId, navigate to chat for that help
            const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
            const url = helpId ? mitraChatRoute + '?help=' + encodeURIComponent(helpId) : mitraChatRoute;
            showMitraNotification({ title: 'Pesan Terkirim', message: 'Pesan berhasil dikirim. Ketuk untuk membuka chat.', url });
        });

        // Event dispatched by server-side polling component when a new chat arrives for mitra
        window.addEventListener('help-new-message', function (e) {
            const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
            const from = e && e.detail && e.detail.from ? e.detail.from : 'Customer';
            const message = e && e.detail && e.detail.message ? e.detail.message : '';
            const url = helpId ? mitraChatRoute + '?help=' + encodeURIComponent(helpId) : mitraChatRoute;
            showMitraNotification({ title: 'Pesan Baru dari ' + from, message: message || 'Ketuk untuk membuka chat.', url, timeout: 6000 });
        });

        // Also expose a global helper for other components to trigger notifications
        window.triggerMitraNotification = function (payload) {
            showMitraNotification(payload || {});
        }
    </script>
</body>

</html>
