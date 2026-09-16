<div wire:poll.8s.visible="poll"></div>

<script>
	document.addEventListener('livewire:init', () => {
		Livewire.on('help-new-message', (event) => {
			const data = Array.isArray(event) ? (event[0] || {}) : event;
			window.dispatchEvent(new CustomEvent('help-new-message', { detail: data }));
		});

		// Re-dispatch server-sent mitra-help-status events to browser
		Livewire.on('mitra-help-status', (event) => {
			const data = Array.isArray(event) ? (event[0] || {}) : event;
			window.dispatchEvent(new CustomEvent('mitra-help-status', { detail: data }));
		});
	});
</script>

