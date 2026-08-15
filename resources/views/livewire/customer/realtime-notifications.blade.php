<div wire:poll.15s.visible="poll"></div>

<script>
    // Listen for Livewire dispatched events and re-dispatch as browser events
    document.addEventListener('livewire:init', () => {
        Livewire.on('help-taken', (event) => {
            console.log('Livewire help-taken received:', event);
            window.dispatchEvent(new CustomEvent('help-taken', { detail: event }));
        });

        Livewire.on('help-new-message', (event) => {
            console.log('Livewire help-new-message received:', event);
            window.dispatchEvent(new CustomEvent('help-new-message', { detail: event }));
        });

        // Bridge status changes (from Mitra GPS tracker) to browser events
        Livewire.on('status-changed', (event) => {
            try {
                console.log('Livewire status-changed received:', event);

                // Normalize payload: Livewire may send named params as an object
                const payload = (event && event.detail) ? event.detail : event;

                // Re-dispatch a generic status update event
                window.dispatchEvent(new CustomEvent('help-status-update', { detail: payload }));

                // If the newStatus indicates partner is on the way, also dispatch specific event
                const newStatus = payload && (payload.newStatus || payload.status || '');
                if (newStatus && String(newStatus).includes('partner_on_the_way')) {
                    console.log('Bridging to help-on-the-way event', payload);
                    window.dispatchEvent(new CustomEvent('help-on-the-way', { detail: { helpId: payload.helpId ?? payload.help_id, mitraName: payload.mitraName ?? payload.mitra_name ?? 'Mitra' } }));
                }
            } catch (err) { console.error('Error handling status-changed bridge', err); }
        });
    });
</script>
