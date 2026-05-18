import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { resolve, join } from 'path';
import { existsSync, readFileSync, writeFileSync } from 'fs';

const PDFJS_WORKER_POLYFILL = `if(!Uint8Array.prototype.toHex){Uint8Array.prototype.toHex=function(){return Array.from(this,b=>b.toString(16).padStart(2,'0')).join('');}}
if(!Uint8Array.fromHex){Uint8Array.fromHex=function(h){const a=new Uint8Array(h.length>>1);for(let i=0;i<a.length;i++)a[i]=parseInt(h.slice(i*2,i*2+2),16);return a;}}
`;

function copyPdfjsWorker() {
    return {
        name: 'copy-pdfjs-worker',
        buildStart() {
            const src = join(__dirname, 'node_modules/pdfjs-dist/legacy/build/pdf.worker.mjs');
            const dest = join(__dirname, 'public/pdf.worker.mjs');
            if (existsSync(src)) {
                writeFileSync(dest, PDFJS_WORKER_POLYFILL + readFileSync(src, 'utf8'));
            }
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
        copyPdfjsWorker(),
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
        exclude: ['pdfjs-dist'],
    },
});
