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

    <!-- Theme Anti-FOUC -->
    <script>
        (function() {
            const saved = localStorage.getItem('color-theme') || localStorage.getItem('theme') || 'system';
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (saved === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>


    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>


<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200 overflow-x-hidden">
    <!-- Centered Container -->
    <div class="min-h-screen flex items-start justify-center bg-gray-100 dark:bg-gray-950 transition-colors duration-200">
        <!-- Mobile Width Container -->
        <div class="w-full max-w-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 relative shadow-2xl transition-colors duration-200">
            <!-- Global notification (toast) for mitra actions -->
            <div id="mitra-global-notification" class="fixed top-4 inset-x-0 mx-auto w-full max-w-md px-4 pointer-events-none z-[99999]">
                <div id="mitra-global-notification-inner" class="mx-auto max-w-md"></div>
            </div>
            <!-- Content -->
            <div class="flex flex-col min-h-screen">
                <!-- Main Content -->
                <div class="flex-1 pb-20">
                    {{ $slot }}
                </div>

                <!-- Bottom Navigation Bar -->
                <nav id="bottom-nav" class="fixed bottom-0 inset-x-0 mx-auto w-full max-w-md bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-2xl z-50 transition-colors duration-200">
                    <div class="flex items-center justify-around px-2 py-2">
                        <a href="{{ route('mitra.dashboard') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.dashboard') && !request()->has('tab') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Beranda</span>
                        </a>

                        <a href="{{ route('mitra.helps.all') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.helps.all') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Bantuan</span>
                        </a>

                        <a href="{{ route('mitra.helps.processing') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.helps.processing') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Diproses</span>
                        </a>

                        <a href="{{ route('mitra.helps.completed') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.helps.completed') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 3h18v2H3V3zm0 4h18v14H3V7zm5 3v8h2v-8H8zm4 0v8h2v-8h-2z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Riwayat</span>
                        </a>

                        <a href="{{ route('mitra.profile') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('mitra.profile*') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Profil</span>
                        </a>
                    </div>
                </nav>
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
