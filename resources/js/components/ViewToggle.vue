<template>
    <!-- Hidden on mobile: phones always use the card view -->
    <div class="hidden sm:inline-flex items-center bg-gray-100 rounded-xl p-0.5 border border-gray-200 shrink-0">
        <button
            type="button"
            @click="$emit('update:modelValue', 'table')"
            :class="[
                'inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all',
                modelValue === 'table' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'
            ]"
            :title="lang.t('table_view')"
            :aria-label="lang.t('table_view')"
            :aria-pressed="modelValue === 'table'"
        >
            <Table2 class="w-4 h-4" />
        </button>
        <button
            type="button"
            @click="$emit('update:modelValue', 'card')"
            :class="[
                'inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all',
                modelValue === 'card' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'
            ]"
            :title="lang.t('card_view')"
            :aria-label="lang.t('card_view')"
            :aria-pressed="modelValue === 'card'"
        >
            <LayoutGrid class="w-4 h-4" />
        </button>
    </div>
</template>

<script setup>
import { Table2, LayoutGrid } from 'lucide-vue-next';
import { useLanguageStore } from '@/stores/language';

const lang = useLanguageStore();

defineProps({
    modelValue: {
        type: String,
        default: 'table',
    },
});

defineEmits(['update:modelValue']);
</script>
