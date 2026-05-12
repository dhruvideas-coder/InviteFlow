<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ lang.t('documents') }}</h1>
                <p class="text-sm text-gray-500 mt-1">Manage bulk document requests and tracking.</p>
            </div>
            <RouterLink to="/documents/create" class="btn btn-primary px-6 py-2.5 shadow-lg shadow-primary-200 flex items-center gap-2">
                <Plus class="w-4 h-4 stroke-[3px]" />
                <span>{{ lang.t('new_document') }}</span>
            </RouterLink>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-5 border-l-4 border-primary-500">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ lang.t('total_documents') }}</p>
                <h3 class="text-2xl font-black text-gray-900">{{ documents.length }}</h3>
            </div>
            <div class="card p-5 border-l-4 border-amber-400">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ lang.t('in_progress') }}</p>
                <h3 class="text-2xl font-black text-gray-900">{{ documents.filter(d => d.status === 'active').length }}</h3>
            </div>
            <div class="card p-5 border-l-4 border-emerald-500">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ lang.t('completed') }}</p>
                <h3 class="text-2xl font-black text-gray-900">{{ documents.filter(d => d.status === 'completed').length }}</h3>
            </div>
            <div class="card p-5 border-l-4 border-slate-300">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ lang.t('drafts') }}</p>
                <h3 class="text-2xl font-black text-gray-900">{{ documents.filter(d => d.status === 'draft').length }}</h3>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input v-model="search" type="text" class="input pl-10" :placeholder="lang.t('search_documents')" @input="fetchDocuments" />
            </div>
            <select v-model="statusFilter" class="select sm:w-48" @change="fetchDocuments">
                <option value="">{{ lang.t('all_statuses') }}</option>
                <option value="draft">{{ lang.t('draft') }}</option>
                <option value="active">{{ lang.t('active') }}</option>
                <option value="completed">{{ lang.t('completed') }}</option>
                <option value="expired">{{ lang.t('expired') }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ lang.t('title') }}</th>
                        <th>{{ lang.t('status') }}</th>
                        <th>{{ lang.t('progress') }}</th>
                        <th class="hidden md:table-cell">{{ lang.t('date') }}</th>
                        <th class="text-right">{{ lang.t('actions') }}</th>
                    </tr>
                </thead>
                <tbody v-if="loading">
                    <tr v-for="i in 5" :key="i">
                        <td colspan="5" class="py-6 text-center">
                            <div class="animate-pulse flex items-center justify-center gap-4">
                                <div class="h-4 bg-gray-100 rounded w-1/3"></div>
                                <div class="h-4 bg-gray-100 rounded w-1/4"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="doc in documents" :key="doc.id">
                        <td>
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-900 line-clamp-1">{{ doc.name }}</span>
                                <span class="text-[10px] text-gray-500 mt-0.5 line-clamp-1">{{ doc.description || lang.t('no_description') }}</span>
                            </div>
                        </td>
                        <td>
                            <span :class="['badge', statusClass(doc.status)]">{{ lang.t(doc.status.toLowerCase()) }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2 text-gray-600">
                                <div :class="['w-1.5 h-1.5 rounded-full shrink-0', doc.completed_signers > 0 ? 'bg-emerald-400' : 'bg-gray-300']"></div>
                                <span class="text-xs font-medium whitespace-nowrap">{{ doc.completed_signers }}/{{ doc.total_signers }}</span>
                            </div>
                        </td>
                        <td class="hidden md:table-cell text-gray-600 text-xs whitespace-nowrap">
                            {{ formatDate(doc.created_at) }}
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                <RouterLink v-if="doc.status === 'draft'" :to="`/documents/${doc.id}/edit`" class="text-primary-600 hover:text-primary-700 font-bold text-[10px] uppercase tracking-wider">{{ lang.t('edit') }}</RouterLink>
                                <span v-if="doc.status === 'draft'" class="text-gray-100">|</span>
                                <button @click="openPreview(doc)" class="text-primary-600 hover:text-primary-700 font-bold text-[10px] uppercase tracking-wider">{{ lang.t('view') }}</button>
                                <button @click="confirmDelete(doc)" class="p-1 text-gray-300 hover:text-red-500 transition-colors ml-1">
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!documents.length">
                        <td colspan="5" class="text-center py-20">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mb-2">
                                    <Mail class="w-8 h-8 text-gray-300" />
                                </div>
                                <h3 class="font-semibold text-gray-800">{{ lang.t('no_documents_found') }}</h3>
                                <p class="text-sm text-gray-400">{{ lang.t('create_first_document') }}</p>
                                <RouterLink to="/documents/create" class="btn btn-primary mt-4">
                                    {{ lang.t('create_document') }}
                                </RouterLink>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete Modal -->
        <Transition name="modal">
            <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
                <div class="modal max-w-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                            <Trash2 class="w-6 h-6 text-red-500" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ lang.t('delete_document') }}</h3>
                            <p class="text-xs text-gray-500">{{ lang.t('action_cannot_undone') }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                        Are you sure you want to delete <span class="font-semibold text-gray-900">{{ deleteTarget.name }}</span>? All associated data will be removed.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="deleteTarget = null" class="btn btn-secondary">{{ lang.t('cancel') }}</button>
                        <button @click="deleteDocument" class="btn btn-danger" :disabled="deleting">
                            <Loader2 v-if="deleting" class="w-4 h-4 animate-spin" />
                            <span v-else>{{ lang.t('delete_document') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Preview Modal -->
        <Transition name="modal">
            <div v-if="previewTarget" class="modal-overlay" @click.self="previewTarget = null">
                <div class="modal max-w-4xl w-full p-0 overflow-hidden bg-[#F8FAFC]">
                    <!-- Modal Header -->
                    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-white border-b border-slate-200 flex items-center justify-between sticky top-0 z-10">
                        <div class="min-w-0 mr-3">
                            <h3 class="font-bold text-gray-900 leading-tight truncate text-sm sm:text-base">{{ previewTarget.name }}</h3>
                            <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider mt-0.5">Template Preview • {{ previewTarget.file_type.toUpperCase() }}</p>
                        </div>
                        <button @click="previewTarget = null" class="p-2 hover:bg-gray-100 rounded-xl transition-colors shrink-0">
                            <X class="w-5 h-5 text-gray-400" />
                        </button>
                    </div>

                    <!-- Modal Body (Canvas) -->
                    <div class="p-3 sm:p-6 overflow-y-auto max-h-[calc(100dvh-120px)] sm:max-h-[calc(90vh-70px)]">
                        <div
                            ref="canvasContainerRef"
                            class="relative bg-white rounded-2xl shadow-xl border border-slate-200/50 overflow-hidden mx-auto flex items-center justify-center min-h-[250px] sm:min-h-[400px]"
                        >
                            <div 
                                id="preview-area"
                                class="relative mx-auto"
                                :style="{ width: canvasW + 'px', height: canvasH + 'px' }"
                            >
                                <img
                                    v-if="isImage(previewTarget)"
                                    :src="previewTarget.file_url"
                                    @load="onImageLoad"
                                    class="w-full h-full object-contain pointer-events-none select-none"
                                />
                                <PdfCanvas
                                    v-else
                                    :src="previewTarget.file_url"
                                    :width="canvasW"
                                    :page="currentPage"
                                    @rendered="onPdfRendered"
                                    @total-pages="onTotalPages"
                                    class="pointer-events-none select-none"
                                />

                                <!-- Fields Overlay -->
                                <div
                                    v-for="field in (previewTarget.fields || []).filter(f => (f.page_number ?? 1) === currentPage)" :key="field.id"
                                    class="absolute border-2 flex items-center justify-center text-center pointer-events-none select-none overflow-hidden"
                                    :style="{
                                        left: (field.x_percent / 100 * canvasW) + 'px',
                                        top: (field.y_percent / 100 * canvasH) + 'px',
                                        width: (field.width_percent / 100 * canvasW) + 'px',
                                        height: (field.height_percent / 100 * canvasH) + 'px',
                                        borderColor: field.color || '#3b82f6',
                                        backgroundColor: (field.color || '#3b82f6') + '20'
                                    }"
                                >
                                    <span
                                        class="font-bold px-1 truncate"
                                        :style="{ 
                                            fontSize: Math.max(8, Math.round((field.height_percent / 100 * canvasH) * 0.4)) + 'px',
                                            color: field.color || '#1e40af'
                                        }"
                                    >
                                        {{ getTranslatedLabel(field, previewTarget.language) }}
                                    </span>
                                </div>

                                <!-- Page navigation overlay -->
                                <div v-if="pdfTotalPages > 1" class="absolute bottom-4 left-0 right-0 flex justify-center pointer-events-none opacity-100">
                                    <div class="flex items-center gap-2 bg-slate-900/90 backdrop-blur-md rounded-2xl px-4 py-1.5 pointer-events-auto shadow-2xl">
                                        <button @click="prevPage" :disabled="currentPage === 1"
                                            class="p-1 text-white/70 hover:text-white disabled:opacity-30 transition-colors">
                                            <ChevronLeft class="w-4 h-4" />
                                        </button>
                                        <span class="text-[10px] text-white tabular-nums px-2 font-bold">{{ currentPage }} / {{ pdfTotalPages }}</span>
                                        <button @click="nextPage" :disabled="currentPage === pdfTotalPages"
                                            class="p-1 text-white/70 hover:text-white disabled:opacity-30 transition-colors">
                                            <ChevronRight class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watchEffect } from 'vue';
import { RouterLink } from 'vue-router';
import { Plus, Search, Mail, Trash2, Loader2, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import axios from 'axios';
import PdfCanvas from '@/components/PdfCanvas.vue';
import { useLanguageStore } from '@/stores/language';

const lang = useLanguageStore();

const documents = ref([]);
const loading = ref(true);
const search = ref('');
const statusFilter = ref('');
const deleteTarget = ref(null);
const deleting = ref(false);
const previewTarget = ref(null);

// Preview Logic
const canvasContainerRef = ref(null);
const canvasW = ref(Math.min(600, window.innerWidth - 80));
const pdfAspectRatio = ref(1.414);
const canvasH = computed(() => Math.round(canvasW.value * pdfAspectRatio.value));
const pdfTotalPages = ref(1);
const currentPage = ref(1);

const isImage = (doc) => {
    const type = doc?.file_type ?? '';
    return ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(type.toLowerCase());
};

const openPreview = (doc) => {
    previewTarget.value = doc;
    currentPage.value = 1;
    pdfTotalPages.value = 1;
};

const onPdfRendered = ({ width, height }) => {
    pdfAspectRatio.value = height / width;
};

const onImageLoad = (e) => {
    const { naturalWidth, naturalHeight } = e.target;
    if (naturalWidth && naturalHeight) {
        pdfAspectRatio.value = naturalHeight / naturalWidth;
    }
};

const onTotalPages = (n) => { pdfTotalPages.value = n; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < pdfTotalPages.value) currentPage.value++; };

const getTranslatedLabel = (field, docLanguage) => {
    if (field.field_type === 'name') {
        return lang.t('recipient_name');
    }
    if (field.field_type === 'village') {
        return lang.t('village_name');
    }
    return field.label;
};

watchEffect((onCleanup) => {
    const el = canvasContainerRef.value;
    if (!el) return;
    const ro = new ResizeObserver(([entry]) => {
        canvasW.value = Math.max(280, entry.contentRect.width - 4);
    });
    ro.observe(el);
    onCleanup(() => ro.disconnect());
});

const fetchDocuments = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/documents', {
            params: {
                type: 'document',
                search: search.value || undefined,
                status: statusFilter.value || undefined
            }
        });
        documents.value = data;
    } catch (err) {
        console.error('Failed to fetch documents:', err);
    } finally {
        loading.value = false;
    }
};

const statusClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'active': return 'badge-primary';
        case 'completed': return 'badge-green';
        case 'draft': return 'badge-gray';
        case 'expired': return 'badge-red';
        default: return 'badge-gray';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const confirmDelete = (doc) => {
    deleteTarget.value = doc;
};

const deleteDocument = async () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    try {
        await axios.delete(`/api/documents/${deleteTarget.value.id}`);
        documents.value = documents.value.filter(d => d.id !== deleteTarget.value.id);
        deleteTarget.value = null;
    } catch (err) {
        console.error('Delete failed:', err);
    } finally {
        deleting.value = false;
    }
};

onMounted(fetchDocuments);
</script>

<style scoped>
@reference "../../css/app.css";
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }

.badge {
    @apply px-2.5 py-1 rounded-lg font-bold text-[10px] tracking-wider;
}
</style>
