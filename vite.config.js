import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/create-piggy.js',
                    'resources/js/pick-date-strategy-frequency-options.js',
                    'resources/js/ziggy.js',
                    'resources/js/scheduled-savings.js',
                    'resources/js/piggy-bank-highlight.js',
                    'resources/js/help-popup.js',
                    'resources/js/register-policy-check.js'],
            refresh: true,
        }),
    ],
});
