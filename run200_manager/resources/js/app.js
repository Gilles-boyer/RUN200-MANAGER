import './qr-scanner.js';
import './charts.js';

/**
 * Unified Theme Management
 * Handles dark/light mode with persistence across Livewire navigation
 *
 * Note: RUN200 Racing design system is primarily designed for dark mode.
 * Light mode support is limited - many components use hardcoded white text.
 * For now, we force dark mode to ensure readability.
 */
(function initTheme() {
    // TEMPORARY: Force dark mode until light mode design is fully implemented
    // The racing design system uses text-white extensively which is unreadable in light mode
    const FORCE_DARK_MODE = true;

    function getThemePreference() {
        if (FORCE_DARK_MODE) {
            return 'dark';
        }
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        if (FORCE_DARK_MODE) {
            theme = 'dark';
        }
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        // Update meta theme-color for mobile browsers
        const metaTheme = document.querySelector('meta[name="theme-color"]');
        if (metaTheme) {
            metaTheme.content = theme === 'dark' ? '#1a1a1a' : '#E53935';
        }
    }

    // Apply theme immediately on script load
    const theme = getThemePreference();
    applyTheme(theme);

    // Store preference if not set
    if (!localStorage.getItem('theme')) {
        localStorage.setItem('theme', theme);
    }

    // Re-apply theme after Livewire navigation (wire:navigate)
    document.addEventListener('livewire:navigating', () => {
        // Theme will be reapplied after navigation
    });

    document.addEventListener('livewire:navigated', () => {
        // Re-apply theme after Livewire SPA navigation
        applyTheme(getThemePreference());
    });

    // Also handle turbo/regular navigation and page show (back/forward)
    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(getThemePreference());
    });

    window.addEventListener('pageshow', () => {
        applyTheme(getThemePreference());
    });

    // Listen for system preference changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!FORCE_DARK_MODE && localStorage.getItem('themeManual') !== 'true') {
            const newTheme = e.matches ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: newTheme } }));
        }
    });

    // Global function to toggle theme (disabled when FORCE_DARK_MODE is true)
    window.toggleDarkMode = function() {
        if (FORCE_DARK_MODE) {
            console.info('Theme toggle is disabled. Dark mode is forced for optimal readability.');
            return true;
        }
        const currentTheme = localStorage.getItem('theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme', newTheme);
        localStorage.setItem('themeManual', 'true');
        applyTheme(newTheme);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: newTheme } }));
        return newTheme === 'dark';
    };

    // Global function to check if dark mode
    window.isDarkMode = function() {
        return document.documentElement.classList.contains('dark');
    };

    // Global function to set specific theme
    window.setTheme = function(theme) {
        if (FORCE_DARK_MODE) {
            console.info('Theme setting is disabled. Dark mode is forced for optimal readability.');
            return;
        }
        if (theme === 'dark' || theme === 'light') {
            localStorage.setItem('theme', theme);
            localStorage.setItem('themeManual', 'true');
            applyTheme(theme);
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
        }
    };

    // Global function to check if theme toggle is available
    window.isThemeToggleEnabled = function() {
        return !FORCE_DARK_MODE;
    };
})();
