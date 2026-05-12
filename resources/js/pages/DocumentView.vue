<template>
    <div class="min-h-screen bg-[#F8FAFC] flex flex-col font-sans selection:bg-primary-100 selection:text-primary-900">
        <!-- Minimal Top bar -->
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md px-4 sm:px-8 py-4 flex items-center justify-between border-b border-slate-200/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <span class="block font-black text-slate-900 tracking-tight leading-none">InviteFlow</span>
                    <span class="text-[10px] font-bold text-primary-600 uppercase tracking-wider">Premium Preview</span>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <button
                    v-if="!expired && doc"
                    class="group relative inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-bold transition-all hover:bg-slate-800 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
                    :disabled="downloading"
                    @click="downloadInvitation"
                >
                    <template v-if="downloading">
                        <Loader2 class="w-4 h-4 animate-spin" />
                        <span>Preparing...</span>
                    </template>
                    <template v-else>
                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="hidden xs:inline">Download</span>
                    </template>
                </button>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 flex flex-col items-center py-6 sm:py-12 px-4 sm:px-6 max-w-7xl mx-auto w-full">
            <!-- Expired state -->
            <div v-if="expired" class="text-center max-w-sm my-auto animate-in fade-in zoom-in duration-700">
                <div class="relative w-24 h-24 mx-auto mb-8">
                    <div class="absolute inset-0 bg-red-100 rounded-3xl rotate-6 animate-pulse"></div>
                    <div class="absolute inset-0 bg-white rounded-3xl border-2 border-red-50 flex items-center justify-center shadow-xl shadow-red-100/50">
                        <svg class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-3 tracking-tight">Access Denied</h2>
                <p class="text-slate-500 text-base leading-relaxed mb-8">This invitation link has expired or the security token is invalid. Please contact the sender for a new link.</p>
                <a href="/" class="inline-flex items-center font-bold text-primary-600 hover:text-primary-700">
                    Go to Homepage
                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Document preview -->
            <div v-else class="w-full max-w-4xl space-y-8 animate-in slide-in-from-bottom-12 duration-1000">
                <!-- Info Card -->
                <div v-if="doc" class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-slate-200/60 text-center space-y-2">
                    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-50 text-[10px] font-bold text-primary-700 uppercase tracking-wider mb-2">
                        Personal Invitation
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                        Namaste, {{ recipientName }}!
                    </h1>
                    <p class="text-slate-500 font-medium text-lg italic">
                        You have been invited to join us from <span class="text-slate-900 not-italic font-bold">{{ recipientVillage }}</span>.
                    </p>
                </div>

                <!-- The Document -->
                <div 
                    ref="canvasContainerRef" 
                    class="group relative bg-white rounded-[2.5rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.15)] border border-slate-200/50 overflow-hidden flex items-center justify-center min-h-[500px] transition-transform duration-500"
                >
                    <template v-if="loading">
                        <div class="flex flex-col items-center gap-6 py-20">
                            <div class="relative">
                                <div class="w-16 h-16 border-4 border-primary-100 rounded-full"></div>
                                <div class="absolute top-0 left-0 w-16 h-16 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest animate-pulse">Loading your invitation...</p>
                        </div>
                    </template>

                    <template v-else-if="doc">
                        <div 
                            id="capture-area"
                            class="relative mx-auto"
                            :style="{ width: canvasW + 'px', height: canvasH + 'px' }"
                        >
                            <img
                                v-if="isImage"
                                :src="doc.file_url"
                                @load="onImageLoad"
                                class="w-full h-full object-contain pointer-events-none select-none rounded-sm"
                            />
                            <PdfCanvas
                                v-else
                                :src="doc.file_url"
                                :width="canvasW"
                                :page="currentPage"
                                @rendered="onPdfRendered"
                                @total-pages="onTotalPages"
                                class="pointer-events-none select-none rounded-sm"
                            />

                            <!-- Personalization Overlay -->
                            <div
                                v-for="field in (doc.fields || []).filter(f => (f.page_number ?? 1) === currentPage)" :key="field.id"
                                class="absolute flex flex-col items-center justify-center text-center pointer-events-none select-none whitespace-nowrap"
                                :style="{
                                    left: (field.x_percent / 100 * canvasW) + 'px',
                                    top: (field.y_percent / 100 * canvasH) + 'px',
                                    width: (field.width_percent / 100 * canvasW) + 'px',
                                    height: (field.height_percent / 100 * canvasH) + 'px',
                                    transform: 'translate(0, 0)'
                                }"
                            >
                                <span
                                    class="font-bold drop-shadow-sm leading-tight"
                                    :style="{ 
                                        fontSize: Math.max(10, Math.round((field.height_percent / 100 * canvasH) * 0.7)) + 'px', 
                                        fontFamily: doc.language === 'en' ? 'serif' : 'sans-serif',
                                        color: field.color || '#000000'
                                    }"
                                >
                                    {{ getFieldValue(field) }}
                                </span>
                            </div>

                            <!-- Page navigation overlay -->
                            <div v-if="pdfTotalPages > 1" class="absolute bottom-6 left-0 right-0 flex justify-center pointer-events-none opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300">
                                <div class="flex items-center gap-2 bg-slate-900/90 backdrop-blur-md rounded-2xl px-4 py-2 pointer-events-auto shadow-2xl">
                                    <button @click="prevPage" :disabled="currentPage === 1"
                                        class="p-1.5 text-white/70 hover:text-white disabled:opacity-30 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <div class="h-4 w-[1px] bg-white/20 mx-1"></div>
                                    <span class="text-xs text-white tabular-nums px-2 font-bold">{{ currentPage }} / {{ pdfTotalPages }}</span>
                                    <div class="h-4 w-[1px] bg-white/20 mx-1"></div>
                                    <button @click="nextPage" :disabled="currentPage === pdfTotalPages"
                                        class="p-1.5 text-white/70 hover:text-white disabled:opacity-30 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div v-else class="text-center p-20">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-slate-400 font-medium">Document unavailable.</p>
                    </div>
                </div>

            </div>
        </main>

        <footer class="py-8 border-t border-slate-200/50 bg-white">
            <p class="text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.4em]">
                InviteFlow • Secure Personalized Preview System
            </p>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watchEffect } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { Loader2 } from 'lucide-vue-next';
import PdfCanvas from '@/components/PdfCanvas.vue';

import { PDFDocument, rgb } from 'pdf-lib';

const route = useRoute();
const token = route.params.token;

const expired = ref(false);

const loading = ref(true);
const doc = ref(null);
const recipient = ref({
    name_en: 'Sureshbhai Patel',
    name_gu: 'સુરેશભાઈ પટેલ',
    village: 'Anand',
    mobile: '+91 98765 43210',
});

const isImage = computed(() => {
    const type = doc.value?.file_type ?? '';
    return ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(type.toLowerCase());
});

const getFieldValue = (field) => {
    if (!doc.value) return '';
    const lang = doc.value.language || 'gu';
    
    let value = '';
    if (recipient.value) {
        if (field.field_type === 'village') {
            value = lang === 'gu' ? recipient.value.village_gu : recipient.value.village_en;
        } else {
            value = lang === 'gu' ? recipient.value.name_gu : recipient.value.name_en;
        }
    }
    
    return value || getTranslatedLabel(field, lang);
};

const getTranslatedLabel = (field, lang) => {
    const isGu = lang === 'gu';
    if (field.field_type === 'name') {
        return isGu ? 'નામ' : 'Recipient Name';
    }
    if (field.field_type === 'village') {
        return isGu ? 'ગામનું નામ' : 'Village Name';
    }
    return field.label;
};

const recipientName = computed(() => {
    if (!doc.value || !recipient.value) return '';
    return doc.value.language === 'gu' ? recipient.value.name_gu : recipient.value.name_en;
});

const recipientVillage = computed(() => {
    if (!doc.value || !recipient.value) return '';
    return doc.value.language === 'gu' ? recipient.value.village_gu : recipient.value.village_en;
});

// ── Page Navigation ─────────────────────────────────────────
const pdfTotalPages = ref(1);
const currentPage = ref(1);

function onTotalPages(n) { if (n > 1) pdfTotalPages.value = n; }
function prevPage() { if (currentPage.value > 1) currentPage.value--; }
function nextPage() { if (currentPage.value < pdfTotalPages.value) currentPage.value++; }

// ── Canvas Scaling ──────────────────────────────────────────
const canvasContainerRef = ref(null);
const canvasW = ref(Math.min(700, window.innerWidth - 32));
const pdfAspectRatio = ref(1.414);
const canvasH = computed(() => Math.round(canvasW.value * pdfAspectRatio.value));

function onPdfRendered({ width, height }) {
    pdfAspectRatio.value = height / width;
}

function onImageLoad(e) {
    const { naturalWidth, naturalHeight } = e.target;
    if (naturalWidth && naturalHeight) {
        pdfAspectRatio.value = naturalHeight / naturalWidth;
    }
}

watchEffect((onCleanup) => {
    const el = canvasContainerRef.value;
    if (!el) return;
    const ro = new ResizeObserver(([entry]) => {
        // Leave some padding for the card container
        canvasW.value = Math.max(280, entry.contentRect.width - 2); 
    });
    ro.observe(el);
    onCleanup(() => ro.disconnect());
});


async function fetchInvitationData() {
    loading.value = true;
    try {
        const { data: link } = await axios.get(`/api/invitation-links/${token}`);
        
        doc.value = link.document;
        recipient.value = {
            name_en: link.recipient.name_en,
            name_gu: link.recipient.name_gu,
            village_en: link.recipient.village_en,
            village_gu: link.recipient.village_gu,
            village: link.recipient.village_en, // For header fallback
            mobile: link.recipient.mobile,
        };
        
        if (link.expires_at && new Date(link.expires_at) < new Date()) {
            expired.value = true;
        }
    } catch (err) {
        console.error('Failed to fetch invitation:', err);
        expired.value = true;
    } finally {
        loading.value = false;
    }
}

const downloading = ref(false);

/**
 * Downloads the current invitation page with personalization
 */
async function downloadInvitation() {
    if (!doc.value || downloading.value) return;
    downloading.value = true;

    try {
        if (isImage.value) {
            await downloadAsImage();
        } else {
            await downloadAsPdf();
        }
    } catch (err) {
        console.error('Download failed:', err);
        alert('Failed to generate invitation. Please try again.');
    } finally {
        downloading.value = false;
    }
}

async function downloadAsImage() {
    const container = document.getElementById('capture-area');
    if (!container) return;

    const baseElement = container.querySelector('canvas') || container.querySelector('img');
    if (!baseElement) return;

    const exportCanvas = document.createElement('canvas');
    const ctx = exportCanvas.getContext('2d');
    
    const exportWidth = baseElement.width || baseElement.naturalWidth || canvasW.value;
    const exportHeight = baseElement.height || baseElement.naturalHeight || canvasH.value;
    
    exportCanvas.width = exportWidth;
    exportCanvas.height = exportHeight;

    ctx.drawImage(baseElement, 0, 0, exportWidth, exportHeight);

    const fields = (doc.value.fields || []).filter(f => (f.page_number ?? 1) === currentPage.value);
    fields.forEach(field => {
        const x = (field.x_percent / 100) * exportWidth;
        const y = (field.y_percent / 100) * exportHeight;
        const h = (field.height_percent / 100) * exportHeight;
        const w = (field.width_percent / 100) * exportWidth;
        
        const fontSize = Math.max(12, Math.round(h * 0.7));
        const fontFamily = doc.value.language === 'en' ? 'serif' : 'sans-serif';
        
        ctx.font = `bold ${fontSize}px ${fontFamily}`;
        ctx.fillStyle = field.color || '#000000';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(getFieldValue(field), x + (w / 2), y + (h / 2));
    });

    const dataUrl = exportCanvas.toDataURL('image/png', 1.0);
    triggerDownload(dataUrl, `Invitation_${recipient.value.name_en.replace(/\s+/g, '_')}.png`);
}

async function downloadAsPdf() {
    // 1. Fetch original PDF
    const response = await fetch(doc.value.file_url);
    const pdfBytes = await response.arrayBuffer();

    // 2. Load with pdf-lib
    const pdfDoc = await PDFDocument.load(pdfBytes);
    const pages = pdfDoc.getPages();

    // 3. Process fields
    const fields = doc.value.fields || [];
    
    // We'll use the standard Helvetica-Bold font for now
    // In a real app, we'd embed a custom font for Gujarati support
    const font = await pdfDoc.embedFont('Helvetica-Bold');

    for (const field of fields) {
        const pageIdx = (field.page_number ?? 1) - 1;
        if (pageIdx >= pages.length) continue;

        const page = pages[pageIdx];
        const { width, height } = page.getSize();

        const x = (field.x_percent / 100) * width;
        const topY = (field.y_percent / 100) * height;
        const w = (field.width_percent / 100) * width;
        const h = (field.height_percent / 100) * height;

        // pdf-lib uses bottom-up coordinates
        const y = height - topY - (h / 2); // Center of the field vertically

        const fontSize = Math.max(8, Math.round(h * 0.75));
        const text = getFieldValue(field);
        const textWidth = font.widthOfTextAtSize(text, fontSize);

        page.drawText(text, {
            x: x + (w / 2) - (textWidth / 2), // Center horizontally in the field area
            y: y - (fontSize / 4), // Small adjustment for baseline
            size: fontSize,
            font: font,
            color: hexToRgb(field.color || '#000000'),
        });
    }

    // 4. Save and download
    const modifiedPdfBytes = await pdfDoc.save();
    const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);
    triggerDownload(url, `Invitation_${recipient.value.name_en.replace(/\s+/g, '_')}.pdf`);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function triggerDownload(url, filename) {
    const link = document.createElement('a');
    link.download = filename;
    link.href = url;
    link.click();
}

function hexToRgb(hex) {
    const r = parseInt(hex.slice(1, 3), 16) / 255;
    const g = parseInt(hex.slice(3, 5), 16) / 255;
    const b = parseInt(hex.slice(5, 7), 16) / 255;
    return rgb(r, g, b);
}

onMounted(fetchInvitationData);
</script>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes zoom-in {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
@keyframes slide-in-from-bottom {
    from { transform: translateY(2rem); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.animate-in {
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fade-in;
}
.zoom-in {
    animation-name: zoom-in;
}
.slide-in-from-bottom-8 {
    animation-name: slide-in-from-bottom;
}
.duration-500 {
    animation-duration: 500ms;
}
.duration-700 {
    animation-duration: 700ms;
}
</style>
