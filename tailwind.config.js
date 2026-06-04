import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // ============================================
                // PRIMARY COLOR SCHEME
                // Change these to update the entire app theme
                // ============================================
                primary: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',  // Main primary color
                    600: '#059669',  // Primary hover/active
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                },
                // ============================================
                // SECONDARY/ACCENT COLOR
                // For highlights and accents
                // ============================================
                accent: {
                    50: '#f0fdfa',
                    100: '#ccfbf1',
                    200: '#99f6e4',
                    300: '#5eead4',
                    400: '#2dd4bf',
                    500: '#14b8a6',  // Main accent color (teal)
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
                    950: '#042f2e',
                },
                // ============================================
                // SIDEBAR SPECIFIC COLORS
                // For consistent sidebar styling
                // ============================================
                sidebar: {
                    bg: {
                        light: '#ffffff',
                        dark: '#0B1120',
                    },
                    border: {
                        light: '#e5e7eb',
                        dark: 'rgba(31, 41, 55, 0.5)',
                    },
                    active: {
                        light: '#ecfdf5',  // primary-50
                        dark: 'rgba(16, 185, 129, 0.1)',  // primary with opacity
                    },
                    'active-border': {
                        light: '#d1fae5',  // primary-100
                        dark: 'rgba(16, 185, 129, 0.2)',
                    },
                    text: {
                        light: '#6b7280',  // gray-500
                        dark: '#9ca3af',   // gray-400
                    },
                    'text-active': {
                        light: '#059669',  // primary-600
                        dark: '#ffffff',
                    },
                },
            },
            // Box shadow with primary color
            boxShadow: {
                'primary': '0 10px 15px -3px rgba(16, 185, 129, 0.1), 0 4px 6px -2px rgba(16, 185, 129, 0.05)',
                'primary-lg': '0 25px 50px -12px rgba(16, 185, 129, 0.25)',
            },
        },
    },

    plugins: [forms],
};
