<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Subscription</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage your plan and billing</p>
        </div>

        <!-- Current plan -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 to-primary-800 p-6 text-white">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge bg-white/20 text-white border-0">Current Plan</span>
                    </div>
                    <h3 class="text-2xl font-bold">Pro Plan</h3>
                    <p class="text-primary-200 text-sm mt-1">₹500 / month · Renews June 15, 2026</p>
                </div>
                <div class="flex flex-col items-start sm:items-end gap-2">
                    <p class="text-primary-200 text-sm">420 / 1000 sends used</p>
                    <button class="btn bg-white text-primary-700 hover:bg-primary-50 btn-sm">Upgrade to Enterprise</button>
                </div>
            </div>
            <div class="mt-4 h-2 rounded-full bg-white/20">
                <div class="h-full w-[42%] rounded-full bg-white/70"></div>
            </div>
        </div>

        <!-- Plans grid -->
        <div>
            <h3 class="font-semibold text-gray-900 mb-4">Available Plans</h3>
            <div class="grid sm:grid-cols-3 gap-4">
                <div
                    v-for="plan in plans"
                    :key="plan.name"
                    :class="[
                        'card p-5 flex flex-col gap-4 relative',
                        plan.popular ? 'border-primary-300 ring-2 ring-primary-100' : '',
                    ]"
                >
                    <div v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="badge badge-primary bg-primary-600 text-white px-3 py-1 text-xs font-semibold rounded-full">Most Popular</span>
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-900">{{ plan.name }}</h4>
                        <div class="flex items-end gap-1 mt-1">
                            <span class="text-3xl font-bold text-gray-900">{{ plan.price }}</span>
                            <span class="text-gray-500 text-sm mb-0.5">/month</span>
                        </div>
                    </div>

                    <ul class="space-y-2 flex-1">
                        <li v-for="feat in plan.features" :key="feat" class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ feat }}
                        </li>
                    </ul>

                    <button
                        :class="[
                            'btn w-full',
                            plan.current ? 'btn-secondary opacity-60 pointer-events-none' : 'btn-primary',
                        ]"
                    >
                        {{ plan.current ? 'Current Plan' : 'Upgrade' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Usage history -->
        <div class="card">
            <div class="flex items-center justify-between p-5 border-b border-gray-50">
                <h3 class="font-semibold text-gray-900">Usage History</h3>
                <button class="btn btn-ghost btn-sm text-xs">View all</button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>WhatsApp</th>
                        <th>Email</th>
                        <th>Links</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in usageHistory" :key="u.period">
                        <td class="font-medium text-gray-900">{{ u.period }}</td>
                        <td class="text-gray-600">{{ u.wa }}</td>
                        <td class="text-gray-600">{{ u.email }}</td>
                        <td class="text-gray-600">{{ u.links }}</td>
                        <td><span class="badge badge-green">Paid</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
const plans = [
    {
        name: 'Basic',
        price: '₹100',
        popular: false,
        current: false,
        features: ['100 WhatsApp sends', '100 Email sends', '5 Documents', 'Basic analytics', 'Link expiry control'],
    },
    {
        name: 'Pro',
        price: '₹500',
        popular: true,
        current: true,
        features: ['1,000 WhatsApp sends', '1,000 Email sends', '50 Documents', 'Advanced analytics', 'Gujarati auto-conversion', 'Bulk CSV upload', 'White-label links'],
    },
    {
        name: 'Enterprise',
        price: '₹2,000',
        popular: false,
        current: false,
        features: ['Unlimited sends', 'Unlimited documents', 'Priority support', 'Custom domain', 'API access', 'Dedicated account manager', 'Custom branding'],
    },
];

const usageHistory = [
    { period: 'May 2026', wa: 420, email: 215, links: 3412 },
    { period: 'Apr 2026', wa: 380, email: 190, links: 2980 },
    { period: 'Mar 2026', wa: 445, email: 220, links: 3100 },
    { period: 'Feb 2026', wa: 310, email: 145, links: 2200 },
];
</script>
