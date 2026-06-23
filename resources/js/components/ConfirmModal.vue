<template>
    <Transition name="fade">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="onCancel"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0" :class="iconWrap">
                            <!-- danger -->
                            <svg v-if="variant === 'danger'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                            <!-- success -->
                            <svg v-else-if="variant === 'success'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <!-- primary / default -->
                            <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ message }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="onCancel" :disabled="loading" class="btn btn-ghost btn-sm disabled:opacity-50">
                            {{ cancelLabel || lang.t('cancel') }}
                        </button>
                        <button type="button" @click="$emit('confirm')" :disabled="loading" class="inline-flex items-center justify-center gap-2 px-5 py-2 text-sm font-medium text-white rounded-xl shadow-sm transition-all disabled:opacity-70" :class="confirmBtn">
                            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ loadingLabel && loading ? loadingLabel : confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { useLanguageStore } from '@/stores/language';

const lang = useLanguageStore();

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: '' },
    cancelLabel: { type: String, default: '' },
    loadingLabel: { type: String, default: '' },
    variant: { type: String, default: 'danger' }, // danger | success | primary
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel']);

function onCancel() {
    if (props.loading) return;
    emit('cancel');
}

const iconWrap = computed(() => ({
    danger: 'bg-red-100 text-red-600',
    success: 'bg-emerald-100 text-emerald-600',
    primary: 'bg-primary-100 text-primary-600',
}[props.variant] || 'bg-red-100 text-red-600'));

const confirmBtn = computed(() => ({
    danger: 'bg-red-600 hover:bg-red-700',
    success: 'bg-emerald-600 hover:bg-emerald-700',
    primary: 'bg-primary-600 hover:bg-primary-700',
}[props.variant] || 'bg-red-600 hover:bg-red-700'));
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
