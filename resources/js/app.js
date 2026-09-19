import './bootstrap';
import './theme';
import './flowbite';

/**
 * Global Utility: Copy text to clipboard with optional visual indicator
 */
window.copyToClipboard = function(text, targetButton = null) {
    if (!navigator.clipboard) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
        } catch (err) {
            console.error('Copy fallback failed', err);
        }
        document.body.removeChild(textArea);
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        if (targetButton) {
            const originalHtml = targetButton.innerHTML;
            targetButton.dataset.original = originalHtml;
            targetButton.innerHTML = `<span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Tersalin!</span>`;
            setTimeout(() => {
                targetButton.innerHTML = originalHtml;
            }, 1800);
        }
    }).catch(err => {
        console.error('Gagal menyalin:', err);
    });
};

/**
 * Global Utility: Live Image Preview for file inputs
 */
window.previewImageFile = function(inputElement, previewImgElementId) {
    const file = inputElement?.files?.[0];
    const preview = document.getElementById(previewImgElementId);
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
};

/**
 * Prevent mouse wheel scrolling from incrementing/decrementing number inputs
 */
document.addEventListener('wheel', function(e) {
    if (document.activeElement && document.activeElement.tagName === 'INPUT' && document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
    if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'number') {
        e.preventDefault();
    }
}, { passive: false });

/**
 * Livewire 3 Navigation Event Integration
 */
(function() {
    document.addEventListener('livewire:navigating', () => {
        document.body.classList.add('navigating');
    });

    document.addEventListener('livewire:navigated', () => {
        // Remove after two animation frames (same cadence as enableTransitions in theme.js)
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                document.body.classList.remove('navigating');
            });
        });
    });
})();


/**
 * Global Utility: Play Notification Sound
 * Plays notification sound from /sfx/ when notification events arrive
 */
window.playNotificationSound = function(options = {}) {
    try {
        const force = options && options.force === true;
        const soundEnabled = (typeof window.getNotificationSoundEnabled === 'function')
            ? window.getNotificationSoundEnabled()
            : (window.USER_SOUND_ENABLED !== false);

        if (!force && soundEnabled === false) {
            return;
        }

        const defaultUrl = window.DEFAULT_NOTIFICATION_SOUND || '/sfx/mixkit-software-interface-start-2574.mp3';
        const soundUrl = options.url || defaultUrl;
        const audio = new Audio(soundUrl);
        audio.volume = typeof options.volume === 'number' ? Math.max(0, Math.min(1, options.volume)) : 0.85;

        const playPromise = audio.play();
        if (playPromise !== undefined) {
            playPromise.catch(err => {
                // Autoplay restrictions or missing audio file handled silently
                console.debug('Notification audio playback prevented:', err);
            });
        }
    } catch (err) {
        console.debug('playNotificationSound error:', err);
    }
};

// Global event listener for Livewire or Alpine dispatches
window.addEventListener('play-notification-sound', function(e) {
    const detail = e && e.detail ? e.detail : {};
    window.playNotificationSound(detail);
});



