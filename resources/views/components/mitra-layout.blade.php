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
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900|space-grotesk:400,500,600,700|dm-sans:400,500,700,800,900|syne:400,500,600,700,800|nunito:400,600,700,800,900|playfair-display:400,500,600,700,800,900|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

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
                <!-- Main Content -->
                <div class="flex-1 pb-24">
                    {{ $slot }}
                </div>

                <!-- Floating Glassmorphism Bottom Navigation Bar -->
                <x-mitra.bottom-nav />
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
                        setTimeout(() => {
                            container.innerHTML = '';
                        }, effectiveTimeout);
                    } catch (err) {
                        console.error('showMitraNotification error', err);
                    }
                };
            }

            const mitraHelpDetailTemplate = "{{ route('mitra.helps.detail', ['id' => 'REPLACE_ID']) }}";
            const mitraChatRoute = "{{ route('mitra.chat') }}";

            if (!window._mitraComponentListenersAttached) {
                window._mitraComponentListenersAttached = true;

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

                window.triggerMitraNotification = function (payload) {
                    window.showMitraNotification(payload || {});
                };
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
</body>

</html>
