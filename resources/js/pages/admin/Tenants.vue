<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Tenant Management</h2>
                <p class="text-sm text-gray-500 mt-0.5">Manage all business admin accounts on the platform</p>
            </div>
            <button class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Admin
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-4">
            <div v-for="s in stats" :key="s.label" class="stat-card text-center">
                <p class="text-2xl font-bold text-gray-900">{{ s.value }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ s.label }}</p>
            </div>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <div class="px-4 py-3 border-b border-gray-100 flex gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input v-model="search" type="text" class="input pl-10" placeholder="Search tenants..." />
                </div>
                <select v-model="planFilter" class="select sm:w-36">
                    <option value="">All Plans</option>
                    <option value="basic">Basic</option>
                    <option value="pro">Pro</option>
                    <option value="enterprise">Enterprise</option>
                </select>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Plan</th>
                        <th>Usage</th>
                        <th>Documents</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in filteredTenants" :key="t.id">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                    {{ t.name[0] }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ t.name }}</p>
                                    <p class="text-xs text-gray-400">{{ t.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span :class="['badge text-xs', planClass(t.plan)]">{{ t.plan }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 rounded-full bg-gray-100 min-w-16">
                                    <div class="h-full rounded-full bg-primary-500" :style="{ width: t.usagePct + '%' }"></div>
                                </div>
                                <span class="text-xs text-gray-600 whitespace-nowrap">{{ t.usagePct }}%</span>
                            </div>
                        </td>
                        <td class="text-gray-600">{{ t.docs }}</td>
                        <td class="text-gray-500 text-xs">{{ t.joined }}</td>
                        <td>
                            <span :class="['badge text-xs', t.active ? 'badge-green' : 'badge-red']">
                                {{ t.active ? 'Active' : 'Suspended' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-1 justify-end">
                                <button class="btn btn-ghost btn-sm text-xs">View</button>
                                <button
                                    @click="t.active = !t.active"
                                    :class="['btn btn-sm text-xs', t.active ? 'btn-ghost text-amber-600 hover:bg-amber-50' : 'btn-ghost text-emerald-600 hover:bg-emerald-50']"
                                >
                                    {{ t.active ? 'Suspend' : 'Activate' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const search = ref('');
const planFilter = ref('');

const stats = [
    { label: 'Total Admins', value: 67 },
    { label: 'Active', value: 59 },
    { label: 'Pro / Enterprise', value: 55 },
    { label: 'MRR', value: '₹38,500' },
];

const tenants = ref([
    { id: 1, name: 'Rajesh Shah Events', email: 'rajesh@events.com', plan: 'Pro', usagePct: 72, docs: 23, joined: 'Jan 2026', active: true },
    { id: 2, name: 'Mehta Celebrations', email: 'info@mehta.com', plan: 'Enterprise', usagePct: 45, docs: 67, joined: 'Dec 2025', active: true },
    { id: 3, name: 'Patel Mandal', email: 'patel@mandal.org', plan: 'Basic', usagePct: 88, docs: 4, joined: 'Feb 2026', active: true },
    { id: 4, name: 'Kumar Weddings', email: 'kumar@weddings.in', plan: 'Pro', usagePct: 31, docs: 12, joined: 'Mar 2026', active: false },
    { id: 5, name: 'Desai Corp Events', email: 'events@desaicorp.com', plan: 'Enterprise', usagePct: 22, docs: 89, joined: 'Nov 2025', active: true },
]);

const filteredTenants = computed(() => {
    return tenants.value.filter(t => {
        const q = search.value.toLowerCase();
        const matchSearch = !q || t.name.toLowerCase().includes(q) || t.email.toLowerCase().includes(q);
        const matchPlan = !planFilter.value || t.plan.toLowerCase() === planFilter.value;
        return matchSearch && matchPlan;
    });
});

function planClass(plan) {
    return { Basic: 'badge-gray', Pro: 'badge-primary', Enterprise: 'badge-amber' }[plan] || 'badge-gray';
}
</script>
