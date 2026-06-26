<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Subscription</h2>
            <p class="text-sm text-gray-500 mt-0.5">One-time payment · Lifetime access — no monthly renewals</p>
        </div>

        <!-- Current plan banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 to-primary-800 p-6 text-white">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge bg-white/20 text-white border-0">Current Plan</span>
                        <span class="badge bg-emerald-400/20 text-emerald-50 border-0 inline-flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Lifetime
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold">{{ currentPlan.name }} Plan</h3>
                    <p class="text-primary-200 text-sm mt-1">{{ currentPlan.price }} one-time · Lifetime access, no renewals</p>
                </div>
                <div class="flex flex-col items-start sm:items-end gap-2">
                    <p class="text-primary-200 text-sm">{{ currentPlan.tagline }}</p>
                    <button v-if="currentPlan.key !== 'enterprise'" class="btn bg-white text-primary-700 hover:bg-primary-50 btn-sm">Upgrade plan</button>
                </div>
            </div>
        </div>

        <!-- Plan cards -->
        <div>
            <h3 class="font-semibold text-gray-900 mb-4">Available Lifetime Plans</h3>
            <div class="grid sm:grid-cols-3 gap-4">
                <div
                    v-for="plan in plans"
                    :key="plan.key"
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
                        <p class="text-xs text-gray-500 mt-0.5">{{ plan.tagline }}</p>
                        <div class="flex items-end gap-1.5 mt-3">
                            <span class="text-3xl font-bold text-gray-900">{{ plan.price }}</span>
                            <span class="text-gray-500 text-sm mb-1">one-time</span>
                        </div>
                    </div>

                    <ul class="space-y-2 flex-1">
                        <li v-for="feat in plan.highlights" :key="feat" class="flex items-start gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ feat }}
                        </li>
                    </ul>

                    <button
                        :class="[
                            'btn w-full',
                            plan.key === currentPlan.key ? 'btn-secondary opacity-60 pointer-events-none' : 'btn-primary',
                        ]"
                    >
                        {{ plan.key === currentPlan.key ? 'Current Plan' : 'Choose ' + plan.name }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Full comparison -->
        <div class="card overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <h3 class="font-semibold text-gray-900">Compare Plans</h3>
                <p class="text-xs text-gray-500 mt-0.5">Modules, permissions and limits included in each lifetime plan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left font-semibold text-gray-700 px-5 py-3 w-2/5">Feature</th>
                            <th v-for="plan in plans" :key="plan.key"
                                class="text-center font-semibold px-4 py-3"
                                :class="plan.popular ? 'text-primary-700 bg-primary-50/60' : 'text-gray-700'">
                                {{ plan.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="group in comparison" :key="group.section">
                            <tr class="bg-gray-50/70">
                                <td colspan="4" class="px-5 py-2 text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ group.section }}</td>
                            </tr>
                            <tr v-for="row in group.rows" :key="row.label" class="border-b border-gray-50 last:border-0">
                                <td class="px-5 py-3 text-gray-700">
                                    {{ row.label }}
                                    <span v-if="row.soon" class="ml-1.5 badge badge-gray text-[9px] align-middle">Soon</span>
                                </td>
                                <td v-for="plan in plans" :key="plan.key"
                                    class="px-4 py-3 text-center"
                                    :class="plan.popular ? 'bg-primary-50/40' : ''">
                                    <!-- boolean → check / dash -->
                                    <template v-if="typeof row[plan.key] === 'boolean'">
                                        <svg v-if="row[plan.key]" class="w-4 h-4 text-emerald-500 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span v-else class="text-gray-300">—</span>
                                    </template>
                                    <!-- value → text -->
                                    <span v-else class="font-medium text-gray-800">{{ row[plan.key] }}</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-400 text-center">All plans are a one-time lifetime purchase. ∞ means unlimited. Prices to be finalized.</p>
    </div>
</template>

<script setup>
// ── Static plan data (testing UI only) ─────────────────────────────────
// TODO: replace these placeholders with the real lifetime prices, then wire
// this whole screen to GET /api/usage once the backend plan system lands.
const PRICE = { basic: '₹___', pro: '₹___', enterprise: '₹___' };

const plans = [
    {
        key: 'basic', name: 'Basic', tagline: 'For getting started', price: PRICE.basic, popular: false,
        highlights: ['5 Documents', '2 Members', '500 Recipients', '50 WhatsApp / day', 'Basic analytics', 'Gujarati auto-convert'],
    },
    {
        key: 'pro', name: 'Pro', tagline: 'For growing teams', price: PRICE.pro, popular: true,
        highlights: ['50 Documents', '10 Members', '5,000 Recipients', 'Advanced analytics', 'Bulk CSV import', 'WhatsApp templates', 'Host images'],
    },
    {
        key: 'enterprise', name: 'Enterprise', tagline: 'For unlimited scale', price: PRICE.enterprise, popular: false,
        highlights: ['Unlimited Documents', 'Unlimited Members', 'Unlimited Recipients', '100 WhatsApp / day', 'White-label & API', 'Everything in Pro'],
    },
];

// Statically mark which plan the account is on (swap for real data later).
const currentPlan = plans.find(p => p.key === 'pro');

// Full feature/permission/limit matrix. Booleans render as ✓ / —; strings render as text.
const comparison = [
    {
        section: 'Limits',
        rows: [
            { label: 'Documents',                     basic: '5',   pro: '50',    enterprise: '∞' },
            { label: 'Members (sub-users)',           basic: '2',   pro: '10',    enterprise: '∞' },
            { label: 'Recipients',                    basic: '500', pro: '5,000', enterprise: '∞' },
            { label: 'WhatsApp sends / day per user', basic: '50',  pro: '50',    enterprise: '100' },
        ],
    },
    {
        section: 'Modules',
        rows: [
            { label: 'Dashboard, Documents, Recipients, Settings', basic: true,      pro: true,        enterprise: true },
            { label: 'Analytics',                                  basic: 'Basic',   pro: 'Advanced',  enterprise: 'Advanced' },
            { label: 'Users (member management)',                  basic: 'Up to 2', pro: 'Up to 10',  enterprise: 'Unlimited' },
            { label: 'Subscription page',                          basic: true,      pro: true,        enterprise: true },
        ],
    },
    {
        section: 'Permissions & Features',
        rows: [
            { label: 'Gujarati auto-convert',         basic: true,  pro: true,  enterprise: true },
            { label: 'Bulk CSV import',               basic: false, pro: true,  enterprise: true },
            { label: 'WhatsApp message templates',    basic: false, pro: true,  enterprise: true },
            { label: 'Host / main-person images',     basic: false, pro: true,  enterprise: true },
            { label: 'White-label links / API',       basic: false, pro: false, enterprise: true, soon: true },
        ],
    },
];
</script>
