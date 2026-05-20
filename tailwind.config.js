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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
                display: ['"Playfair Display"', 'Georgia', 'serif'],
            },
            colors: {
                boss: {
                    // Legacy aliases — updated to blush-pink brand palette
                    gold: '#EEB4C3',
                    'gold-light': '#F3C3CF',
                    'gold-hover': '#DFA1B4',
                    pink: '#F7D6E0',
                    rose: '#C4687A',
                    dark: '#09070A',
                    ink: '#09070A',
                    panel: '#171016',
                    'panel-strong': '#21161D',
                    ivory: '#FFF8F6',
                    cream: '#F5EDE6',
                    muted: '#FAFAF8',
                },
            },
            boxShadow: {
                luxe: '0 22px 60px rgba(9, 7, 10, 0.22)',
                glow: '0 0 38px rgba(238, 180, 195, 0.18)',
            },
        },
    },

    plugins: [forms],
};
