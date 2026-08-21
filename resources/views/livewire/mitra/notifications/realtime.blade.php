<div></div>

<script>
	document.addEventListener('livewire:init', () => {
		// Re-dispatch server-sent mitra-help-status events to browser
		Livewire.on('mitra-help-status', (event) => {
			console.log('Livewire mitra-help-status received:', event);
			window.dispatchEvent(new CustomEvent('mitra-help-status', { detail: event }));
		});
	});
</script>

