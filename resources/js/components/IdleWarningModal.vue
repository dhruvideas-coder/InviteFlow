<template>
    <Transition name="fade">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-amber-100 text-amber-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-gray-900">{{ lang.t('session_expiring') }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ lang.t('session_expiring_msg', { seconds }) }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="$emit('logout')" class="btn btn-ghost btn-sm">
                            {{ lang.t('sign_out_now') }}
                        </button>
                        <button type="button" @click="$emit('stay')" class="inline-flex items-center justify-center gap-2 px-5 py-2 text-sm font-medium text-white rounded-xl shadow-sm transition-all bg-primary-600 hover:bg-primary-700">
                            {{ lang.t('stay_logged_in') }}
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
    remaining: { type: Number, default: 0 }, // ms left before logout
});

defineEmits(['stay', 'logout']);

const seconds = computed(() => Math.ceil(props.remaining / 1000));
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
