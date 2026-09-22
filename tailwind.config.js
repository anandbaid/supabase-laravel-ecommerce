import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    safelist: [
        // These pill colors are built from PHP string literals in app/Models/Order.php
        // (statusColor()/paymentStatusColor()), not written out in any Blade file, so
        // the scan above should already catch them — safelisted too as a guarantee
        // against future refactors moving that logic somewhere Tailwind doesn't scan.
        'bg-yellow-100', 'text-yellow-700',
        'bg-green-100', 'text-green-700',
        'bg-blue-100', 'text-blue-700',
        'bg-indigo-100', 'text-indigo-700',
        'bg-red-100', 'text-red-700',
        'bg-gray-100', 'text-gray-700',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};