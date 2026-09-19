@php
    $isDarkActive = ($isDark ?? (request()->cookie('theme') === 'dark'));
    $isChatPage = request()->routeIs('mitra.chat') || request()->routeIs('*.chat*');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $isDarkActive ? 'dark' : '' }} {{ $isChatPage ? 'h-[100dvh] max-h-[100dvh] overflow-hidden' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
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
        html, body, .hide-scrollbar, .scrollbar-none, .no-scrollbar, .scrollbar-hide { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        html::-webkit-scrollbar, body::-webkit-scrollbar, .hide-scrollbar::-webkit-scrollbar, .scrollbar-none::-webkit-scrollbar, .no-scrollbar::-webkit-scrollbar, .scrollbar-hide::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
        .dropdown-scrollbar, .custom-scrollbar, .scrollbar-thin { scrollbar-width: thin !important; scrollbar-color: rgba(156, 163, 175, 0.5) transparent !important; -ms-overflow-style: auto !important; }
        .dropdown-scrollbar::-webkit-scrollbar, .custom-scrollbar::-webkit-scrollbar, .scrollbar-thin::-webkit-scrollbar { display: block !important; width: 6px !important; height: 6px !important; }
        .dropdown-scrollbar::-webkit-scrollbar-track, .custom-scrollbar::-webkit-scrollbar-track, .scrollbar-thin::-webkit-scrollbar-track { background: transparent !important; }
        .dropdown-scrollbar::-webkit-scrollbar-thumb, .custom-scrollbar::-webkit-scrollbar-thumb, .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5) !important; border-radius: 9999px !important; }
        .dark .dropdown-scrollbar, .dark .custom-scrollbar, .dark .scrollbar-thin { scrollbar-color: rgba(107, 114, 128, 0.6) transparent !important; }
        .dark .dropdown-scrollbar::-webkit-scrollbar-thumb, .dark .custom-scrollbar::-webkit-scrollbar-thumb, .dark .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(107, 114, 128, 0.5) !important; }
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

    <!-- Fonts (Google Fonts & Bunny Fonts Fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Space+Grotesk:wght@300..700&family=Syne:wght@400..800&family=Outfit:wght@400..800&family=Poppins:wght@400..800&family=Lexend:wght@400..800&family=Montserrat:wght@400..800&family=Inter:wght@400..800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900|space-grotesk:400,500,600,700|dm-sans:400,500,700,800,900|syne:400,500,600,700,800|nunito:400,600,700,800,900|playfair-display:400,500,600,700,800,900|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>


<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 overflow-x-hidden {{ $isChatPage ? 'h-[100dvh] max-h-[100dvh] overflow-hidden overscroll-none' : '' }}" style="{{ $isChatPage ? 'overscroll-behavior: none; overscroll-behavior-y: none;' : '' }}">
    <!-- Centered Container -->
    <div class="{{ $isChatPage ? 'h-[100dvh] max-h-[100dvh] overflow-hidden' : 'min-h-screen' }} flex items-start justify-center bg-gray-100 dark:bg-gray-950">
        <!-- Mobile Width Container -->
        <div class="w-full max-w-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 relative shadow-2xl {{ $isChatPage ? 'h-[100dvh] max-h-[100dvh] flex flex-col overflow-hidden' : '' }}">
            <!-- Global notification (toast) for mitra actions -->
            <div id="mitra-global-notification" class="fixed top-4 inset-x-0 mx-auto w-full max-w-md px-4 pointer-events-none z-[99999]">
                <div id="mitra-global-notification-inner" class="mx-auto max-w-md"></div>
            </div>
            <!-- Content -->
            <div class="{{ $isChatPage ? 'flex-1 min-h-0 flex flex-col overflow-hidden h-full max-h-full' : 'flex flex-col min-h-screen' }}">
                <!-- Livewire -->
                <div class="{{ $isChatPage ? 'flex-1 min-h-0 flex flex-col overflow-hidden h-full max-h-full' : 'flex-1 pb-24' }}">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
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
        window.USER_SOUND_ENABLED = {{ (auth()->check() && (auth()->user()->notification_settings['sound_enabled'] ?? true)) ? 'true' : 'false' }};
        window.DEFAULT_NOTIFICATION_SOUND = "{{ asset('sfx/mixkit-software-interface-start-2574.mp3') }}";
        window.getNotificationSoundEnabled = function() {
            if (typeof window.USER_SOUND_ENABLED !== 'undefined') {
                return window.USER_SOUND_ENABLED;
            }
            return true;
        };

        function playWebAudioChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                if (!window._notifAudioCtx || window._notifAudioCtx.state === 'closed') {
                    window._notifAudioCtx = new AudioCtx();
                }
                const ctx = window._notifAudioCtx;
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const now = ctx.currentTime;
                
                // Tone 1: 587.33 Hz (D5)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                gain1.gain.setValueAtTime(0.25, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.3);

                // Tone 2: 880 Hz (A5)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.08);
                gain2.gain.setValueAtTime(0.25, now + 0.08);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.08);
                osc2.stop(now + 0.5);
            } catch(e) {}
        }

        window.playNotificationSound = function(options = {}) {
            try {
                const force = options && options.force === true;
                const soundEnabled = (typeof window.getNotificationSoundEnabled === 'function')
                    ? window.getNotificationSoundEnabled()
                    : (window.USER_SOUND_ENABLED !== false);

                if (!force && soundEnabled === false) return;

                const defaultUrl = window.DEFAULT_NOTIFICATION_SOUND || "{{ asset('sfx/mixkit-software-interface-start-2574.mp3') }}";
                const soundUrl = options.url || defaultUrl;
                const audio = new Audio(soundUrl);
                audio.volume = typeof options.volume === 'number' ? Math.max(0, Math.min(1, options.volume)) : 0.9;
                const p = audio.play();
                if (p !== undefined) {
                    p.catch(() => {
                        playWebAudioChime();
                    });
                }
            } catch (e) {
                playWebAudioChime();
            }
        };

        // Pre-unlock audio on user interaction
        (function() {
            let unlocked = false;
            const unlock = () => {
                if (unlocked) return;
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (AudioCtx) {
                        if (!window._notifAudioCtx) {
                            window._notifAudioCtx = new AudioCtx();
                        }
                        if (window._notifAudioCtx.state === 'suspended') {
                            window._notifAudioCtx.resume();
                        }
                    }
                    const a = new Audio("data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA");
                    a.volume = 0;
                    const p = a.play();
                    if (p !== undefined) {
                        p.then(() => { unlocked = true; }).catch(() => {});
                    }
                } catch(e) {}
            };
            ['click', 'touchstart', 'touchend', 'pointerdown', 'keydown', 'scroll'].forEach(evt => {
                window.addEventListener(evt, unlock, { once: true, passive: true });
            });
        })();

        (function () {
            if (!window.showMitraNotification) {
                window.showMitraNotification = function({ title = 'Notifikasi', message = '', url = '#' , timeout = 6000, type = 'success' }) {
                    try {
                        // Mainkan audio notifikasi jika aktif
                        if (typeof window.playNotificationSound === 'function') {
                            window.playNotificationSound({ force: true });
                        }

                        const container = document.getElementById('mitra-global-notification-inner');
                        if (!container) return;
                        container.innerHTML = '';

                        const wrap = document.createElement('div');
                        wrap.className = 'bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-700/80 p-3.5 max-w-md mx-auto pointer-events-auto transition-all duration-300 transform translate-y-0 opacity-100 flex items-center gap-3.5 cursor-pointer select-none';
                        wrap.style.boxShadow = '0 16px 40px -8px rgba(0, 0, 0, 0.25)';

                        // Icon badge
                        const iconWrap = document.createElement('div');
                        iconWrap.className = 'w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-sky-500/20';
                        iconWrap.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>';

                        // Body
                        const body = document.createElement('div');
                        body.className = 'flex-1 min-w-0';

                        const headerRow = document.createElement('div');
                        headerRow.className = 'flex items-center justify-between gap-1';

                        const titleEl = document.createElement('div');
                        titleEl.className = 'text-xs font-bold text-gray-900 dark:text-white truncate';
                        titleEl.innerText = String(title || 'Pesan Baru');

                        const badgeEl = document.createElement('span');
                        badgeEl.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 shrink-0';
                        badgeEl.innerText = 'Pesan Baru';

                        headerRow.appendChild(titleEl);
                        headerRow.appendChild(badgeEl);

                        const msgEl = document.createElement('div');
                        msgEl.className = 'text-xs text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2 leading-snug';
                        msgEl.innerText = String(message || 'Ketuk untuk membuka obrolan.');

                        body.appendChild(headerRow);
                        body.appendChild(msgEl);

                        wrap.appendChild(iconWrap);
                        wrap.appendChild(body);

                        wrap.addEventListener('click', function (ev) {
                            ev.preventDefault();
                            if (url && url !== '#') {
                                if (window.Livewire && typeof window.Livewire.navigate === 'function' && !url.startsWith('http://') && !url.startsWith('https://')) {
                                    window.Livewire.navigate(url);
                                } else {
                                    window.location.href = url;
                                }
                            }
                            container.innerHTML = '';
                        });

                        container.appendChild(wrap);
                        const effectiveTimeout = (type === 'error' || type === 'warning' || type === 'danger') ? Math.max(timeout, 8000) : timeout;
                        setTimeout(() => { 
                            wrap.classList.add('opacity-0', '-translate-y-2');
                            setTimeout(() => { container.innerHTML = ''; }, 300);
                        }, effectiveTimeout);
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

                window.addEventListener('help-new-message', function (e) {
                    const d = (e && e.detail && Array.isArray(e.detail)) ? (e.detail[0] || {}) : (e && e.detail ? e.detail : {});
                    const helpId = d.helpId || d.help_id || null;
                    const fromId = d.fromId || d.from_id || null;
                    const cancelId = d.cancelId || d.cancel_id || null;
                    const reportId = d.reportId || d.report_id || null;

                    // Selalu bunyikan nada notifikasi setiap ada pesan baru
                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound({ force: true });
                    }

                    const chatWrapper = document.getElementById('messagesWrapper');
                    if (chatWrapper) {
                        const activeHelpId = chatWrapper.dataset.activeHelpId;
                        const activePartnerId = chatWrapper.dataset.activePartnerId;
                        const activeCancelId = chatWrapper.dataset.activeCancelId;
                        const activeReportId = chatWrapper.dataset.activeReportId;

                        if ((helpId && activeHelpId && String(helpId) === String(activeHelpId)) ||
                            (fromId && activePartnerId && String(fromId) === String(activePartnerId)) ||
                            (cancelId && activeCancelId && String(cancelId) === String(activeCancelId)) ||
                            (reportId && activeReportId && String(reportId) === String(activeReportId))) {
                            return;
                        }
                    }

                    const from = d.from || d.from_name || 'Customer';
                    const message = d.message || '';
                    const defaultUrl = helpId ? mitraChatRoute + '/' + encodeURIComponent(helpId) : mitraChatRoute;
                    const url = d.url || defaultUrl;
                    window.showMitraNotification({ title: 'Pesan Baru dari ' + from, message: message || 'Ketuk untuk membuka chat.', url: url, timeout: 6000, type: 'message' });
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