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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                heading: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                'sky-shift': {
                    from: { backgroundPosition: '0 0, 0 0, 0 0, center' },
                    to: { backgroundPosition: '60px 30px, -35px 20px, 45px -22px, center' },
                },
                'cloud-drift': {
                    from: { transform: 'translateX(0)' },
                    to: { transform: 'translateX(80px)' },
                },
                'plane-float': {
                    '0%, 100%': { transform: 'translateY(0) rotate(-7deg)' },
                    '50%': { transform: 'translateY(-8px) rotate(-6deg)' },
                },
                'fade-up': {
                    from: { opacity: '0', transform: 'translateY(18px) scale(.985)' },
                    to: { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
            },
            animation: {
                'sky-shift': 'sky-shift 18s linear infinite alternate',
                'cloud-drift': 'cloud-drift 10s linear infinite alternate',
                'plane-float': 'plane-float 5s ease-in-out infinite',
                'fade-up': 'fade-up .7s ease-out both',
            },
        },
    },

    plugins: [forms],
};
