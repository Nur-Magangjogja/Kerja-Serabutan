<div wire:poll.3s="poll"></div>

<script>
	(function() {
		function initRealtimeMitraListeners() {
			if (window.Livewire && typeof window.Livewire.on === 'function') {
				if (!window._mitraRealtimeLivewireListenersAttached) {
					window._mitraRealtimeLivewireListenersAttached = true;

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

					// Re-dispatch server-sent mitra-help-status events to browser
					Livewire.on('mitra-help-status', (event) => {
						const data = Array.isArray(event) ? (event[0] || {}) : event;
						window.dispatchEvent(new CustomEvent('mitra-help-status', { detail: data }));
					});
				}
			}
		}

		if (window.Livewire) {
			initRealtimeMitraListeners();
		} else {
			document.addEventListener('livewire:init', initRealtimeMitraListeners, { once: true });
		}
		document.addEventListener('livewire:navigated', initRealtimeMitraListeners);
	})();
</script>

