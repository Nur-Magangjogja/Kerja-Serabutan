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
 * Global Navigation Progress & Loading Feedback
 */
(function() {
    let progressBar = null;
    let progressTimer = null;
    let currentActiveNav = null;

    function getProgressBar() {
        if (!progressBar) {
            progressBar = document.getElementById('global-nav-progress');
            if (!progressBar) {
                progressBar = document.createElement('div');
                progressBar.id = 'global-nav-progress';
                document.body.appendChild(progressBar);
            }
        }
        return progressBar;
    }

    function startProgress() {
        const bar = getProgressBar();
        if (progressTimer) clearInterval(progressTimer);
        bar.style.transition = 'width 0.2s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.15s ease';
        bar.style.opacity = '1';
        bar.style.width = '20%';

        let currentWidth = 20;
        progressTimer = setInterval(() => {
            if (currentWidth < 85) {
                currentWidth += (85 - currentWidth) * 0.18;
                bar.style.width = currentWidth + '%';
            }
        }, 120);
    }

    function completeProgress() {
        const bar = getProgressBar();
        if (progressTimer) clearInterval(progressTimer);
        bar.style.transition = 'width 0.15s ease, opacity 0.25s ease';
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => {
                bar.style.width = '0%';
                if (currentActiveNav) {
                    currentActiveNav.classList.remove('nav-loading');
                    currentActiveNav = null;
                }
                document.querySelectorAll('.nav-loading').forEach(el => el.classList.remove('nav-loading'));
            }, 250);
        }, 120);
    }

    // Attach click listener for immediate button tactile feedback
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[wire\\:navigate], .nav-item, [data-nav-item]');
        if (link && link.getAttribute('href') && !link.getAttribute('href').startsWith('#') && !link.getAttribute('href').startsWith('javascript:')) {
            try {
                const url = new URL(link.href, window.location.origin);
                if (url.pathname !== window.location.pathname || url.search !== window.location.search) {
                    document.querySelectorAll('.nav-loading').forEach(el => el.classList.remove('nav-loading'));
                    link.classList.add('nav-loading');
                    currentActiveNav = link;
                    startProgress();
                }
            } catch (err) {}
        }
    }, true);

    document.addEventListener('livewire:navigating', () => {
        document.body.classList.add('navigating');
        startProgress();
    });

    document.addEventListener('livewire:navigated', () => {
        completeProgress();
        // Remove after two animation frames (same cadence as enableTransitions in theme.js)
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                document.body.classList.remove('navigating');
            });
        });
    });
})();


