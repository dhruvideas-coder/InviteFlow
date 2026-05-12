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
            name: 'bundle-pdfjs-worker',
            generateBundle() {
                // Emit the worker with a .js extension and a stable (un-hashed) filename
                // so it is always served at /build/pdf.worker.js regardless of content hash.
                // .js avoids Apache/Hostinger MIME-type issues with .mjs files.
                this.emitFile({
                    type: 'asset',
                    fileName: 'pdf.worker.js',
                    source: fs.readFileSync(
                        resolve(__dirname, 'node_modules/pdfjs-dist/legacy/build/pdf.worker.min.mjs'),
                        'utf-8'
                    ),
                });
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
