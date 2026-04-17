import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const port = parseInt(env.VITE_PORT || '5173', 10);

    return {
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
                        'resources/js/register-policy-check.js',
                        'resources/js/enter-saving-amount-strategy.js',
                        'resources/js/classic-piggy-bank.js'],
                refresh: true,
            }),
        ],
        server: {
            port,
            strictPort: true,
        },
    };
});
