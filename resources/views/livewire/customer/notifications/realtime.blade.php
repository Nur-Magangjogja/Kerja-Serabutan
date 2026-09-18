<div wire:poll.3s="poll"></div>

<script>
    (function() {
        function initRealtimeListeners() {
            if (window.Livewire && typeof window.Livewire.on === 'function') {
                if (!window._customerRealtimeLivewireListenersAttached) {
                    window._customerRealtimeLivewireListenersAttached = true;

                    Livewire.on('help-taken', (event) => {
                        const data = Array.isArray(event) ? (event[0] || {}) : event;
                        window.dispatchEvent(new CustomEvent('help-taken', { detail: data }));
                    });

                    Livewire.on('help-new-message', (event) => {
                        const data = Array.isArray(event) ? (event[0] || {}) : event;
                        window.dispatchEvent(new CustomEvent('help-new-message', { detail: data }));
                        if (typeof window.playNotificationSound === 'function') {
                            window.playNotificationSound({ force: true });
                        }
                    });

                    Livewire.on('play-notification-sound', () => {
                        if (typeof window.playNotificationSound === 'function') {
                            window.playNotificationSound({ force: true });
                        }
                    });

                    // Bridge status changes (from Mitra GPS tracker) to browser events
                    Livewire.on('status-changed', (event) => {
                        try {
                            const payload = (event && event.detail) ? event.detail : (Array.isArray(event) ? (event[0] || {}) : event);
                            window.dispatchEvent(new CustomEvent('help-status-update', { detail: payload }));

                            const newStatus = payload && (payload.newStatus || payload.status || '');
                            if (newStatus && String(newStatus).includes('partner_on_the_way')) {
                                window.dispatchEvent(new CustomEvent('help-on-the-way', { detail: { helpId: payload.helpId ?? payload.help_id, mitraName: payload.mitraName ?? payload.mitra_name ?? 'Mitra' } }));
                            }
                        } catch (err) { console.error('Error handling status-changed bridge', err); }
                    });
                }
            }
        }

        if (window.Livewire) {
            initRealtimeListeners();
        } else {
            document.addEventListener('livewire:init', initRealtimeListeners, { once: true });
        }
        document.addEventListener('livewire:navigated', initRealtimeListeners);
    })();
</script>

