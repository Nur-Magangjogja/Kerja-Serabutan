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

export function applyTheme(mode) {
    mode = mode || getTheme();
    if (mode !== 'dark' && mode !== 'light') {
        mode = 'system';
    }

    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = mode === 'dark' || (mode === 'system' && prefersDark);

    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
}

export function setTheme(mode) {
    if (mode !== 'dark' && mode !== 'light') {
        mode = 'system';
    }
    localStorage.setItem('theme', mode);
    localStorage.setItem('color-theme', mode);
    applyTheme(mode);
}

export function updateChartDefaults() {
    if (window.Chart) {
        const isDark = document.documentElement.classList.contains('dark');
        window.Chart.defaults.color = isDark ? '#9ca3af' : '#64748b';
        window.Chart.defaults.borderColor = isDark ? '#374151' : 'rgba(15, 23, 42, 0.06)';
    }
}

// Expose theme functions globally for Blade views and Alpine components
window.getTheme = getTheme;
window.setTheme = setTheme;
window.applyTheme = applyTheme;
window.updateChartDefaults = updateChartDefaults;

// Initial application
applyTheme();

// Listeners
window.addEventListener('theme-changed', () => {
    updateChartDefaults();
});

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    updateChartDefaults();
});

// Re-apply on Livewire page navigation
document.addEventListener('livewire:navigated', () => {
    applyTheme();
    updateChartDefaults();
});

if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getTheme() === 'system') {
            applyTheme('system');
        }
    });
}
