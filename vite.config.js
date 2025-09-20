import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
            server: {
                proxy: {
                    '/': 'http://127.0.0.1:8000/',
                }
            }
        }),
    ],
});
