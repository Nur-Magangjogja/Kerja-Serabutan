import './bootstrap';

// Do NOT import Alpine here to avoid loading two Alpine instances.
// Livewire's bundled script already includes Alpine; loading Alpine twice
// causes the "Detected multiple instances of Alpine running" warning
// and can interfere with Livewire event handling.

// Provide a safe runtime fallback that augments the Alpine object produced
// by Livewire's bundled Alpine if needed (for example, `navigate`).
document.addEventListener('DOMContentLoaded', function () {
	try {
		if (window.Alpine && !window.Alpine.navigate) {
			window.Alpine.navigate = function (url) {
				try {
					if (url && typeof url === 'object' && url.detail && url.detail.url) {
						url = url.detail.url;
					}
				} catch (e) {}
				if (typeof url === 'string') {
					window.location.href = url;
					return true;
				}
				return false;
			};
			window.Alpine.navigate.disableProgressBar = function () {};
		}
	} catch (e) {
		// ignore
	}
});
