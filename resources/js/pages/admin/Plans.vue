<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Subscription Plans</h2>
                <p class="text-sm text-gray-500 mt-0.5">Define and manage platform subscription tiers</p>
            </div>
            <button @click="showModal = true" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                New Plan
            </button>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div v-for="plan in plans" :key="plan.id" class="card p-5 space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900">{{ plan.name }}</h3>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ plan.price }} <span class="text-sm font-normal text-gray-500">/mo</span></p>
                    </div>
                    <div class="flex gap-1">
                        <button class="btn btn-ghost btn-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <ul class="space-y-2">
                    <li class="flex justify-between text-sm">
                        <span class="text-gray-600">WhatsApp sends</span>
                        <span class="font-semibold text-gray-900">{{ plan.wa }}</span>
                    </li>
                    <li class="flex justify-between text-sm">
                        <span class="text-gray-600">Email sends</span>
                        <span class="font-semibold text-gray-900">{{ plan.email }}</span>
                    </li>
                    <li class="flex justify-between text-sm">
                        <span class="text-gray-600">Documents</span>
                        <span class="font-semibold text-gray-900">{{ plan.docs }}</span>
                    </li>
                    <li class="flex justify-between text-sm">
                        <span class="text-gray-600">Active clients</span>
                        <span class="font-semibold text-gray-900">{{ plan.clients }}</span>
                    </li>
                </ul>

                <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                    <span class="badge badge-green">{{ plan.clients }} subscribers</span>
                    <button class="btn btn-danger btn-sm text-xs">Remove</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const showModal = ref(false);

const plans = ref([
    { id: 1, name: 'Basic', price: '₹100', wa: 100, email: 100, docs: 5, clients: 12 },
    { id: 2, name: 'Pro', price: '₹500', wa: 1000, email: 1000, docs: 50, clients: 47 },
    { id: 3, name: 'Enterprise', price: '₹2,000', wa: 'Unlimited', email: 'Unlimited', docs: 'Unlimited', clients: 8 },
]);
</script>
