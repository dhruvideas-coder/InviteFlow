<template>
    <div class="h-full">
        <!-- Global loading spinner while checking session -->
        <div v-if="auth.loading" class="min-h-screen flex items-center justify-center bg-slate-50">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-200 animate-pulse">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex gap-1">
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 300ms"></div>
                </div>
            </div>
        </div>

        <RouterView v-else />
    </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
</script>
