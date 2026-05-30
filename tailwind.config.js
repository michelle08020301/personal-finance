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
                body:    ['DM Sans', ...defaultTheme.fontFamily.sans],
                display: ['Syne', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                lime:   '#c8ff80',
                teal:   '#80ffea',
                forest: '#071a04',
                amber:  '#ffb840',
                danger: '#ff8080',
                info:   '#80b8ff',
            },
            backgroundImage: {
                'gradient-lime': 'linear-gradient(135deg, #c8ff80, #80ffea)',
            },
        },
    },
    plugins: [forms],
};