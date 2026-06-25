<template>
    <div class="relative" ref="dropdownRef">
        <button 
            @click="isOpen = !isOpen"
            class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200 border border-transparent hover:border-gray-200 group"
            :class="{ 'bg-gray-100 border-gray-200': isOpen }"
        >
            <span class="text-sm font-semibold text-gray-700">{{ currentLanguage.name }}</span>
            <ChevronDown 
                class="w-4 h-4 text-gray-400 transition-transform duration-200" 
                :class="{ 'rotate-180': isOpen }"
            />
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0 translate-y-1"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 translate-y-1"
        >
            <div 
                v-if="isOpen"
                class="absolute right-0 mt-2 w-40 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 overflow-hidden"
            >
                <div class="px-4 py-2 mb-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Select Language</p>
                </div>
                
                <button
                    v-for="(lang, code) in langStore.locales"
                    :key="code"
                    @click="selectLanguage(code)"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-primary-50 group"
                    :class="langStore.currentLocale === code ? 'text-primary-700 bg-primary-50/50 font-bold' : 'text-gray-600 hover:text-primary-600'"
                >
                    <span class="flex-1 text-left">{{ lang.name }}</span>
                    <Check v-if="langStore.currentLocale === code" class="w-4 h-4 text-primary-500" />
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useLanguageStore } from '@/stores/language';
import { ChevronDown, Check } from 'lucide-vue-next';

const langStore = useLanguageStore();
const isOpen = ref(false);
const dropdownRef = ref(null);

const currentLanguage = computed(() => {
    return langStore.locales[langStore.currentLocale] || langStore.locales['en'];
});

const selectLanguage = (code) => {
    langStore.setLocale(code);
    isOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
