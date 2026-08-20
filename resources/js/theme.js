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
        document.documentElement.style.colorScheme = 'dark';
        document.documentElement.style.backgroundColor = '#111827';
        if (document.body) {
            document.body.style.backgroundColor = '#111827';
        }
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
        document.documentElement.style.backgroundColor = '#f3f4f6';
        if (document.body) {
            document.body.style.backgroundColor = '#f3f4f6';
        }
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

// Initial application
applyTheme();
updateChartDefaults();

// Listeners
window.addEventListener('theme-changed', () => {
    updateChartDefaults();
});

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    updateChartDefaults();
});

// Livewire Navigation lifecycle: keep theme persistent across SPA swaps
document.addEventListener('livewire:navigating', () => {
    applyTheme();
});

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
