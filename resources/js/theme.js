/**
 * Flowbite & Tailwind Dark Mode Theme Manager
 */

export function getTheme() {
    const saved = localStorage.getItem('color-theme') || localStorage.getItem('theme');
    if (saved === 'dark' || saved === 'light') {
        return saved;
    }
    return 'system';
}

export function applyTheme(mode, { skipIfSame = false } = {}) {
    mode = mode || getTheme();
    if (mode !== 'dark' && mode !== 'light') {
        mode = 'system';
    }

    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
    const d = document.documentElement;
    const currentlyDark = d.classList.contains('dark');

    // Skip redundant DOM mutation to prevent unnecessary repaints / flash
    if (skipIfSame && isDark === currentlyDark) {
        return;
    }

    if (isDark) {
        d.classList.add('dark');
        d.style.colorScheme = 'dark';
        d.style.backgroundColor = '#111827';
        if (document.body) document.body.style.backgroundColor = '#111827';
    } else {
        d.classList.remove('dark');
        d.style.colorScheme = 'light';
        d.style.backgroundColor = '#f3f4f6';
        if (document.body) document.body.style.backgroundColor = '#f3f4f6';
    }

    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
}

export function setTheme(mode) {
    if (mode !== 'dark' && mode !== 'light') {
        mode = 'system';
    }
    localStorage.setItem('theme', mode);
    localStorage.setItem('color-theme', mode);
    document.cookie = "theme=" + mode + "; path=/; max-age=31536000; SameSite=Lax";
    applyTheme(mode);
}

export function updateChartDefaults() {
    try {
        if (window.Chart && window.Chart.defaults) {
            const isDark = document.documentElement.classList.contains('dark');
            window.Chart.defaults.color = isDark ? '#9ca3af' : '#64748b';
            window.Chart.defaults.borderColor = isDark ? 'rgba(75, 85, 99, 0.4)' : 'rgba(203, 213, 225, 0.4)';
            
            if (window.Chart.defaults.plugins && window.Chart.defaults.plugins.tooltip) {
                window.Chart.defaults.plugins.tooltip.backgroundColor = isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(15, 23, 42, 0.95)';
                window.Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
                window.Chart.defaults.plugins.tooltip.bodyColor = isDark ? '#e2e8f0' : '#ffffff';
                window.Chart.defaults.plugins.tooltip.borderColor = isDark ? 'rgba(75, 85, 99, 0.4)' : 'rgba(203, 213, 225, 0.4)';
            }
        }
    } catch (e) {
        // Silently ignore if chart is not yet initialized
    }
}

// Expose theme functions globally for Blade views and Alpine components
window.getTheme = getTheme;
window.setTheme = setTheme;
window.applyTheme = applyTheme;
window.updateChartDefaults = updateChartDefaults;

// Initial application — skip redundant repaint if server already set correct class
applyTheme(null, { skipIfSame: true });
updateChartDefaults();

// Listeners
window.addEventListener('theme-changed', () => {
    updateChartDefaults();
});

document.addEventListener('DOMContentLoaded', () => {
    // Skip if state already matches (server rendered correctly via cookie)
    applyTheme(null, { skipIfSame: true });
    updateChartDefaults();
});

/**
 * Livewire SPA Navigation: Prevent dark mode flash
 * During page morphing we disable ALL CSS transitions so elements
 * cannot flicker between light/dark states before the theme class settles.
 */
let _noTransStyle = null;

function disableTransitions() {
    if (_noTransStyle) return;
    _noTransStyle = document.createElement('style');
    _noTransStyle.id = '__no-trans-during-nav';
    _noTransStyle.textContent = '*,*::before,*::after{transition:none!important;animation-duration:0.01ms!important;}';
    document.head.appendChild(_noTransStyle);
}

function enableTransitions() {
    // Wait two frames so the browser has fully painted the new DOM
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (_noTransStyle && _noTransStyle.parentNode) {
                _noTransStyle.parentNode.removeChild(_noTransStyle);
            }
            _noTransStyle = null;
        });
    });
}

document.addEventListener('livewire:navigating', () => {
    disableTransitions();
    // Ensure theme class is set BEFORE the new page morphs in
    applyTheme(null, { skipIfSame: true });
});

document.addEventListener('livewire:navigated', () => {
    // Re-apply in case Livewire morphed the <html> element's class attribute
    applyTheme(null, { skipIfSame: false });
    updateChartDefaults();
    enableTransitions();
});

if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getTheme() === 'system') {
            applyTheme('system');
        }
    });
}
