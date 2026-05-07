import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
                display: ['"Playfair Display"', 'Georgia', 'serif'],
            },
            colors: {
                boss: {
                    gold: '#C9A96E',
                    'gold-light': '#E8C88A',
                    'gold-hover': '#B89058',
                    pink: '#F7D6E0',
                    rose: '#C4687A',
                    dark: '#140E12',
                    ink: '#080808',
                    panel: '#130F12',
                    'panel-strong': '#1A1218',
                    ivory: '#F0EDE8',
                    cream: '#F5EDE6',
                    muted: '#FAFAF8',
                },
            },
            boxShadow: {
                luxe: '0 22px 60px rgba(20, 14, 18, 0.16)',
                glow: '0 0 38px rgba(201, 169, 110, 0.18)',
            },
        },
    },

    plugins: [forms],
};
