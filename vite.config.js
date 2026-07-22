import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            // Hanya refresh full-page saat view/route berubah — bukan setiap file PHP/Livewire
            refresh: [
                'resources/views/**',
                'routes/**',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/**', '**/vendor/**', '**/bootstrap/cache/**'],
        },
    },
});
