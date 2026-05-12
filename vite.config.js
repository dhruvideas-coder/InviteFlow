import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
        {
            name: 'copy-pdfjs-worker',
            buildStart() {
                const src = resolve(__dirname, 'node_modules/pdfjs-dist/legacy/build/pdf.worker.min.mjs');
                const dest = resolve(__dirname, 'public/pdf.worker.min.mjs');
                fs.copyFileSync(src, dest);
            },
        },
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    optimizeDeps: {
        // pdfjs-dist uses browser DOM APIs (DOMMatrix etc.) — exclude from
        // esbuild pre-bundling so Vite serves it directly to the browser
        exclude: ['pdfjs-dist'],
    },
});
