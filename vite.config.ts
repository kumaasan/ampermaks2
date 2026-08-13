import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import vueDevTools from 'vite-plugin-vue-devtools';
import path from 'node:path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        // 1. Vue DevTools musi być przed laravel()
        vueDevTools({
            // w Laravel + Inertia często trzeba wskazać plik wejściowy
            appendTo: 'resources/js/app.ts',
        }),

        // 2. Reszta pluginów Laravela/Vue
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
});