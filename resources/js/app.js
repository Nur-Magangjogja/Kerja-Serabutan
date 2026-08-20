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
 * Prevent redundant page morphing / twitching when clicking the currently active page link
 */
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[wire\\:navigate], a[href]');
    if (!link) return;
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
    
    try {
        const url = new URL(link.href, window.location.origin);
        if (url.origin === window.location.origin && url.pathname === window.location.pathname && url.search === window.location.search && !url.hash) {
            // Already on this exact route! Prevent redundant re-fetch & judder
            e.preventDefault();
            e.stopPropagation();
        }
    } catch (err) {}
}, true);

