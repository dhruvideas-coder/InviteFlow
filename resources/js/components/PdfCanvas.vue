<template>
    <div class="relative overflow-hidden bg-gray-50 rounded-lg border border-gray-100" :style="wrapperStyle">
        <!-- Loading State -->
        <div v-if="loading && !error" class="absolute inset-0 flex items-center justify-center bg-white/60 z-10">
            <div class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center bg-gray-50">
            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-[10px] text-gray-400 mb-2">Preview failed</p>
            <a :href="src" target="_blank" class="text-[10px] font-bold text-primary-600 hover:underline">
                Open Original PDF
            </a>
        </div>

        <!-- The Canvas (PDF.js) -->
        <canvas ref="canvasEl" v-show="!error" class="max-w-full h-auto mx-auto block shadow-sm" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount, nextTick } from 'vue';
import pdfjsWorkerUrl from 'pdfjs-dist/legacy/build/pdf.worker.min.mjs?url';

const props = defineProps({
    src: { type: String, required: true },
    width: { type: Number, default: 400 },
    page: { type: Number, default: 1 },
});

const emit = defineEmits(['rendered', 'total-pages']);

const canvasEl = ref(null);
const loading = ref(true);
const error = ref(false);
const cssW = ref(props.width);
const cssH = ref(Math.round(props.width * 1.414));

const wrapperStyle = computed(() => ({
    width: cssW.value + 'px',
    height: cssH.value + 'px',
}));

let _pdfjsLib = null;
let currentTask = null;
let pdfDoc = null;
let renderSeq = 0; // each call claims a slot; stale calls bail out silently

async function loadPdfJs() {
    if (_pdfjsLib) return _pdfjsLib;
    _pdfjsLib = await import('pdfjs-dist/legacy/build/pdf.mjs');
    _pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorkerUrl;
    return _pdfjsLib;
}

async function render() {
    if (!props.src) return;

    const seq = ++renderSeq;

    loading.value = true;
    error.value = false;

    try {
        const lib = await loadPdfJs();
        if (seq !== renderSeq) return;

        if (!pdfDoc) {
            const loadingTask = lib.getDocument({ url: props.src });
            pdfDoc = await loadingTask.promise;
            if (seq !== renderSeq) return;
            emit('total-pages', pdfDoc.numPages);
        }

        const page = await pdfDoc.getPage(props.page);
        if (seq !== renderSeq) return;

        const viewport = page.getViewport({ scale: 1 });
        const scale = props.width / viewport.width;
        const scaledViewport = page.getViewport({ scale });

        const canvas = canvasEl.value;
        if (!canvas) return;

        const context = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;

        canvas.width = Math.floor(scaledViewport.width * dpr);
        canvas.height = Math.floor(scaledViewport.height * dpr);
        canvas.style.width = Math.floor(scaledViewport.width) + 'px';
        canvas.style.height = Math.floor(scaledViewport.height) + 'px';

        cssW.value = Math.floor(scaledViewport.width);
        cssH.value = Math.floor(scaledViewport.height);

        context.scale(dpr, dpr);

        if (currentTask) currentTask.cancel();
        if (seq !== renderSeq) return;

        currentTask = page.render({ canvasContext: context, viewport: scaledViewport });
        await currentTask.promise;

        if (seq !== renderSeq) return;

        loading.value = false;
        emit('rendered', { width: cssW.value, height: cssH.value });
    } catch (err) {
        // Cancelled because a newer render started — not an error
        if (err?.name === 'RenderingCancelledException' || seq !== renderSeq) return;
        console.error('[PdfCanvas] Render failed:', err);
        loading.value = false;
        error.value = true;
    }
}

onMounted(async () => {
    await nextTick();
    render();
});

watch(() => props.src, () => {
    pdfDoc = null;
    render();
});
watch(() => props.page, render);
watch(() => props.width, render);

onBeforeUnmount(() => {
    if (currentTask) currentTask.cancel();
});
</script>
