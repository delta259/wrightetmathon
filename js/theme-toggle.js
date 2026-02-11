/**
 * Theme Toggle - Light/Dark Mode Switcher
 * Wright et Mathon POS
 * Version: 1.0 - January 2026
 */

(function() {
    'use strict';

    // Configuration
    const STORAGE_KEY = 'wm-theme';
    const LIGHT_THEME = 'light';
    const DARK_THEME = 'dark';

    // SVG Icons
    const SUN_ICON = `<svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="5"></circle>
        <line x1="12" y1="1" x2="12" y2="3"></line>
        <line x1="12" y1="21" x2="12" y2="23"></line>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
        <line x1="1" y1="12" x2="3" y2="12"></line>
        <line x1="21" y1="12" x2="23" y2="12"></line>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
    </svg>`;

    const MOON_ICON = `<svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>`;

    /**
     * Get saved theme from localStorage or system preference
     */
    function getSavedTheme() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            return saved;
        }
        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return DARK_THEME;
        }
        return LIGHT_THEME;
    }

    /**
     * Apply theme to document
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);
        updateToggleButton(theme);
    }

    /**
     * Toggle between light and dark themes
     */
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || LIGHT_THEME;
        const newTheme = currentTheme === LIGHT_THEME ? DARK_THEME : LIGHT_THEME;
        applyTheme(newTheme);
    }

    /**
     * Update toggle button appearance
     */
    function updateToggleButton(theme) {
        const btn = document.querySelector('.theme-toggle');
        if (!btn) return;

        const sunIcon = btn.querySelector('.icon-sun');
        const moonIcon = btn.querySelector('.icon-moon');

        if (sunIcon && moonIcon) {
            if (theme === DARK_THEME) {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
                btn.setAttribute('title', 'Passer en mode clair');
                btn.setAttribute('aria-label', 'Passer en mode clair');
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
                btn.setAttribute('title', 'Passer en mode sombre');
                btn.setAttribute('aria-label', 'Passer en mode sombre');
            }
        }
    }

    /**
     * Create and inject the toggle button
     */
    function createToggleButton() {
        // Don't create if already exists
        if (document.querySelector('.theme-toggle')) {
            return;
        }

        const btn = document.createElement('button');
        btn.className = 'theme-toggle';
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Changer de thème');
        btn.innerHTML = SUN_ICON + MOON_ICON;

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTheme();
        });

        document.body.appendChild(btn);
    }

    /**
     * Listen for system theme changes
     */
    function listenForSystemThemeChanges() {
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                // Only auto-switch if user hasn't manually set a preference
                if (!localStorage.getItem(STORAGE_KEY)) {
                    applyTheme(e.matches ? DARK_THEME : LIGHT_THEME);
                }
            });
        }
    }

    /**
     * Initialize theme system
     */
    function init() {
        // Apply saved theme immediately (before DOM ready to prevent flash)
        const savedTheme = getSavedTheme();
        document.documentElement.setAttribute('data-theme', savedTheme);

        // When DOM is ready, create toggle button
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                createToggleButton();
                updateToggleButton(savedTheme);
                listenForSystemThemeChanges();
            });
        } else {
            createToggleButton();
            updateToggleButton(savedTheme);
            listenForSystemThemeChanges();
        }
    }

    // Expose API globally
    window.ThemeToggle = {
        toggle: toggleTheme,
        setTheme: applyTheme,
        getTheme: function() {
            return document.documentElement.getAttribute('data-theme') || LIGHT_THEME;
        },
        LIGHT: LIGHT_THEME,
        DARK: DARK_THEME
    };

    // Initialize
    init();

})();
