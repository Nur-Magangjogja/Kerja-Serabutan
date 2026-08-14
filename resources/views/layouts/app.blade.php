<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'sayabantu') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
        
        /* Bottom Navigation Animations */
        @keyframes slideUp {
            from {
                transform: translate(-50%, 100%);
                opacity: 0;
            }
            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        @keyframes ripple {
            0% {
                transform: scale(0);
                opacity: 0.6;
            }
            100% {
                transform: scale(4);
                opacity: 0;
            }
        }

        @keyframes bounce-in {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        #bottom-nav {
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item {
            position: relative;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(0, 152, 231, 0.15);
            transform: translate(-50%, -50%);
            transition: width 0.4s, height 0.4s;
        }

        .nav-item:active::before {
            width: 60px;
            height: 60px;
        }

        .nav-item:hover {
            transform: translateY(-3px);
        }

        .nav-item:active {
            transform: translateY(-1px) scale(0.95);
        }

        .nav-item svg {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .nav-item:hover svg {
            transform: scale(1.15);
        }

        .nav-item:active svg {
            transform: scale(0.9);
        }

        .nav-item.active svg {
            animation: bounce-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .nav-item .nav-label {
            transition: all 0.2s ease;
        }

        .nav-item:hover .nav-label {
            transform: scale(1.05);
        }

        .nav-fab {
            position: relative;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .nav-fab::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #0098e7 0%, #0077cc 100%);
            transform: translate(-50%, -50%);
            z-index: -1;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .nav-fab:hover {
            transform: translateY(-4px) rotate(90deg);
        }

        .nav-fab:hover::after {
            transform: translate(-50%, -50%) scale(1.2);
            box-shadow: 0 8px 20px rgba(0, 152, 231, 0.4);
        }

        .nav-fab:active {
            transform: translateY(-2px) rotate(90deg) scale(0.9);
        }

        .nav-fab svg {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .nav-fab:hover svg {
            transform: rotate(-90deg);
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-2px);
            }
        }

        .nav-item.active {
            animation: float 2s ease-in-out infinite;
        }

        /* Indicator dot for active state */
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: currentColor;
            animation: bounce-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50">
    <!-- Centered Container -->
    <div class="min-h-screen flex items-start justify-center bg-gray-100">
        <!-- Mobile Width Container -->
        <div class="w-full max-w-md bg-gray-50 relative shadow-2xl">
            <!-- Global notification (toast) for customer actions -->
            <div id="customer-global-notification" class="fixed top-4 left-1/2 transform -translate-x-1/2 pointer-events-none" style="max-width:448px; width:100vw; z-index:99999;">
                <div id="customer-global-notification-inner" class="mx-auto max-w-md"></div>
            </div>
            <!-- Content -->
            <main class="pb-20">
                @if($__env->hasSection('content'))
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>

            <!-- Bottom Navigation (styled like mitra layout) -->
            @auth
                <nav id="bottom-nav" class="fixed bottom-0 left-1/2 transform -translate-x-1/2 bg-white border-t border-gray-200 shadow-2xl z-50"
                    style="max-width: 448px; width: 100vw;">
                    <div class="max-w-md mx-auto flex items-center justify-around px-4 py-2.5">
                        <a href="{{ route('customer.dashboard') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('customer.dashboard') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Beranda</span>
                        </a>

                        <a href="{{ route('customer.helps.index') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('customer.helps.*') && !request()->routeIs('customer.helps.create') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Bantuan</span>
                        </a>

                        <a href="{{ route('customer.helps.create') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('customer.helps.create') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 4a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H5a1 1 0 110-2h6V5a1 1 0 011-1z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Bantu</span>
                        </a>

                        <a href="{{ route('customer.transactions.index') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('customer.transactions.*') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 3h18v2H3V3zm0 4h18v14H3V7zm5 3v8h2v-8H8zm4 0v8h2v-8h-2z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Transaksi</span>
                        </a>

                        <a href="{{ route('profile') }}"
                            class="nav-item flex flex-col items-center py-1.5 {{ request()->routeIs('profile.*') || request()->routeIs('profile') ? 'text-primary-600 active' : 'text-gray-400 hover:text-primary-600' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                            </svg>
                            <span class="nav-label text-xs font-bold mt-0.5">Profil</span>
                        </a>
                    </div>
                </nav>
            @endauth
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
    {{-- Realtime notifications for customer (invisible) --}}
    @livewire('customer.realtime-notifications')

    <script>
        function showCustomerNotification({ title = 'Notifikasi', message = '', url = '#' , timeout = 4000, type = 'success' }) {
            try {
                console.log('showCustomerNotification (text-only) called', { title, message, url, timeout, type });
                const container = document.getElementById('customer-global-notification-inner');
                if (!container) { console.warn('customer-global-notification-inner not found'); return; }
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
                // If it's an error or warning, keep slightly longer by default
                const effectiveTimeout = (type === 'error' || type === 'warning' || type === 'danger') ? Math.max(timeout, 8000) : timeout;
                setTimeout(() => { container.innerHTML = ''; }, effectiveTimeout);
            } catch (err) { console.error('showCustomerNotification error', err); }
        }

        function escapeHtml(unsafe) {
            return String(unsafe).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        const customerHelpDetailTemplate = "{{ route('customer.helps.detail', ['id' => 'REPLACE_ID']) }}";
        const customerChatRoute = "{{ route('customer.chat') ?? route('mitra.chat') }}";

        // Listen for various help status updates
        window.addEventListener('help-new-message', function (e) {
            console.log('customer help-new-message received', e && e.detail ? e.detail : e);
            const helpId = e && e.detail && e.detail.helpId ? e.detail.helpId : null;
            const from = e && e.detail && e.detail.from ? e.detail.from : 'Mitra';
            const message = e && e.detail && e.detail.message ? e.detail.message : '';
            const url = helpId ? customerChatRoute + '?help=' + encodeURIComponent(helpId) : customerChatRoute;
            showCustomerNotification({ title: 'Pesan Baru dari ' + from, message: message || 'Ketuk untuk membuka chat.', url, timeout: 6000, type: 'message' });
        });

        window.addEventListener('help-taken', function (e) {
            console.log('🎯 help-taken event received!', e.detail);
            const helpId = e && e.detail && (e.detail.helpId ?? e.detail.help_id) ? (e.detail.helpId ?? e.detail.help_id) : null;
            const helpTitle = e && e.detail && (e.detail.helpTitle ?? e.detail.help_title) ? (e.detail.helpTitle ?? e.detail.help_title) : null;
            const mitraName = e && e.detail && (e.detail.mitraName ?? e.detail.mitra_name) ? (e.detail.mitraName ?? e.detail.mitra_name) : 'Mitra';
            const url = helpId ? customerHelpDetailTemplate.replace('REPLACE_ID', helpId) : '#';
            const message = e && e.detail && e.detail.message ? e.detail.message : (helpTitle ? `${mitraName} telah mengambil bantuan Anda: ${helpTitle}` : `${mitraName} telah mengambil bantuan Anda. Ketuk untuk melihat detail.`);
            const title = helpTitle ? `Bantuan: ${helpTitle}` : '\u2705 Bantuan Diambil!';
            console.log('🔔 Showing toast notification for help taken', { title, message });
            showCustomerNotification({ 
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
            showCustomerNotification({ 
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
            showCustomerNotification({ 
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
            showCustomerNotification({ 
                title, 
                message, 
                url, 
                type: 'completed',
                timeout: 8000 
            });
        });

        window.addEventListener('help-status-update', function (e) {
            try {
                console.log('help-status-update raw event:', e);

                const detail = e && e.detail ? e.detail : {};

                // If payload is nested under `data` or first array element, normalize it
                const normalized = (detail.data) ? detail.data : (Array.isArray(detail) && detail.length ? detail[0] : detail);

                // Helper to read many possible keys
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

                // Build fallback based on status
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

                // Determine type and title
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

                console.log('help-status-update parsed:', { helpId, helpTitle, mitraName, status, message, type, title });

                showCustomerNotification({ title, message, url, type, timeout: 7000 });
            } catch (err) { console.error('help-status-update handler error', err); }
        });

        // Generic text-only toast trigger for other components
        // Usage: window.dispatchEvent(new CustomEvent('customer-toast', { detail: { title: 'Hi', message: 'Hello', type: 'info', timeout: 4000, url: '#' } }));
        window.addEventListener('customer-toast', function (e) {
            try {
                const d = e && e.detail ? e.detail : {};
                showCustomerNotification({
                    title: d.title || 'Notifikasi',
                    message: d.message || '',
                    url: d.url || '#',
                    timeout: d.timeout || 4000,
                    type: d.type || 'info'
                });
            } catch (err) { console.error('customer-toast handler error', err); }
        });
    </script>
    <script>
        // Listen for Livewire dispatch to open Midtrans Snap
        window.addEventListener('openMidtransSnap', function (e) {
            try {
                var token = e.detail?.snapToken || e.detail?.detail?.snapToken || e.detail;
                if (!token && e?.detail?.snapToken) token = e.detail.snapToken;

                if (!token) {
                    console.warn('openMidtransSnap called without snap token', e);
                    return;
                }

                if (typeof snap === 'undefined' || !snap.pay) {
                    console.warn('Midtrans snap.js not loaded. Cannot open snap.pay.');
                    return;
                }

                snap.pay(token, {
                    onSuccess: function (result) {
                        console.info('Snap success callback', result);
                        // send a quick client callback to server so we can update status immediately
                        fetch("{{ route('topup.client-callback') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ order_id: result.order_id, result: result })
                        }).then(function (resp) {
                            return resp.json();
                        }).then(function (json) {
                            console.info('Client callback response', json);
                            // Optionally reload or notify Livewire to refresh
                            if (window.Livewire) {
                                try { window.Livewire.emit('balance-updated'); } catch (e) { }
                            }

                            // Redirect user to success page (include order_id)
                            try {
                                var successUrl = "{{ route('topup.success') }}" + '?order_id=' + encodeURIComponent(result.order_id);
                                // small delay to give server a moment to process queued job
                                setTimeout(function () {
                                    window.location.href = successUrl;
                                }, 800);
                            } catch (e) {
                                console.warn('Failed to redirect to success page', e);
                            }
                        }).catch(function (err) {
                            console.warn('Client callback failed', err);
                            // still redirect to success page as fallback
                            try {
                                window.location.href = "{{ route('topup.success') }}" + '?order_id=' + encodeURIComponent(result.order_id);
                            } catch (e) { }
                        });
                    },
                    onPending: function (result) {
                        console.info('Snap pending callback', result);
                    },
                    onError: function (result) {
                        console.error('Snap error callback', result);
                    },
                    onClose: function () {
                        console.info('Snap widget closed by user');
                    }
                });
            } catch (err) {
                console.error('Failed to open Midtrans snap', err);
            }
        });
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