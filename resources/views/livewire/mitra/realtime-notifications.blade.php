<div wire:poll.15s.visible="poll"></div>

<script>
	document.addEventListener('livewire:init', () => {
		// Re-dispatch server-sent mitra-help-status events to browser
		Livewire.on('mitra-help-status', (event) => {
			console.log('Livewire mitra-help-status received:', event);
			window.dispatchEvent(new CustomEvent('mitra-help-status', { detail: event }));
		});

		// Also listen for generic mitra-help-status browser events (fallback)
		window.addEventListener('mitra-help-status', function (e) {
			try {
				const d = e && e.detail ? e.detail : {};
				console.log('Browser mitra-help-status event:', d);
				// If the status is cancel_rejected, redirect the mitra to the help detail page
				try {
					const newStatus = (d.newStatus || d.new_status || '').toString().toLowerCase();
					const helpId = d.helpId || d.help_id || d.helpId || 0;
					if (newStatus === 'cancel_rejected' && helpId) {
						const redirectUrl = `${window.location.origin}/mitra/helps/${helpId}/detail`;
						console.log('Redirecting mitra to', redirectUrl);
						window.location.href = redirectUrl;
					}
				} catch (err) {
					console.error('Error handling mitra-help-status redirect', err);
				}
			} catch (err) { console.error('mitra-help-status handler', err); }
		});
	});
</script>
