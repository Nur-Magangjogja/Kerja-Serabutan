import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    clearScreen: false,

    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
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