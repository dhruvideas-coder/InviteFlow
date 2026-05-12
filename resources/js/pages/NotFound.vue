<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="text-center max-w-md">
            <div class="w-24 h-24 rounded-3xl bg-primary-50 flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-6xl font-bold text-gray-200 mb-4">404</h1>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Page not found</h2>
            <p class="text-gray-500 text-sm mb-6">The page you're looking for doesn't exist or has been moved.</p>
            <RouterLink to="/dashboard" class="btn btn-primary">
                Go to Dashboard
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>
