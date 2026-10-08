import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    clearScreen: false,

    server: {
        host: '127.0.0.1',
        hmr: {
            host: '127.0.0.1',
        },
        cors: true,
    },

    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});