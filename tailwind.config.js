import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"DejaVu Sans"', 'Arial', ...defaultTheme.fontFamily.sans],
                display: ['"DejaVu Sans"', 'Arial', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                boss: {
                    // Dynamic theme colors — resolved via CSS variables at runtime
                    gold:         'rgb(var(--pd-gold-rgb) / <alpha-value>)',
                    'gold-light': 'rgb(var(--pd-gold-light-rgb) / <alpha-value>)',
                    'gold-hover': 'rgb(var(--pd-gold-hover-rgb) / <alpha-value>)',
                    // Static brand colors — not affected by theme picker
                    pink: '#FFE6F0',
                    rose: '#B00058',
                    dark: '#29212A',
                    ink:  '#29212A',
                    panel: '#342A35',
                    'panel-strong': '#403341',
                    ivory: '#FFFFFF',
                    cream: '#FFF7EF',
                    muted: '#FFF9FC',
                },
            },
            boxShadow: {
                luxe: '0 22px 60px rgba(9, 7, 10, 0.22)',
                glow: '0 0 38px rgb(var(--pd-gold-rgb) / 0.18)',
            },
        },
    },

    plugins: [forms],
};
