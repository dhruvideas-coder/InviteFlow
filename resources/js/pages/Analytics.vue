<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <p class="text-sm text-gray-500">Track your invitation performance</p>
            <div class="flex gap-2">
                <select v-model="period" class="select sm:w-36">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
                <button class="btn btn-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden sm:inline">Export</span>
                </button>
            </div>
        </div>


        <!-- KPI cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="kpi in kpis" :key="kpi.label" class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', kpi.bg]">
                        <component :is="kpi.icon" :class="['w-5 h-5', kpi.color]" />
                    </div>
                    <div :class="['flex items-center gap-1 text-xs font-semibold', kpi.up ? 'text-emerald-600' : 'text-red-500']">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="kpi.up ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'" />
                        </svg>
                        {{ kpi.change }}
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ kpi.value }}</p>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ kpi.label }}</p>
            </div>
        </div>

        <!-- Chart placeholder + breakdown -->
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Chart area -->
            <div class="lg:col-span-2 card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h3 class="font-semibold text-gray-900">Links Generated Over Time</h3>
                    <div class="flex gap-3 text-xs">
                        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-primary-500 inline-block"></span>Links</div>
                        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>WhatsApp</div>
                    </div>
                </div>
                <!-- Bar chart simulation -->
                <div class="flex items-end gap-2 h-40">
                    <div
                        v-for="(bar, i) in chartBars"
                        :key="i"
                        class="flex-1 flex flex-col items-center gap-1"
                    >
                        <div class="w-full flex gap-0.5 items-end" :style="{ height: '128px' }">
                            <div
                                class="flex-1 rounded-t-md bg-primary-200 hover:bg-primary-400 transition-colors cursor-pointer"
                                :style="{ height: bar.links + '%' }"
                                :title="`${bar.links} links`"
                            ></div>
                            <div
                                class="flex-1 rounded-t-md bg-emerald-200 hover:bg-emerald-400 transition-colors cursor-pointer"
                                :style="{ height: bar.wa + '%' }"
                                :title="`${bar.wa} WA`"
                            ></div>
                        </div>
                        <span class="text-xs text-gray-400">{{ bar.label }}</span>
                    </div>
                </div>
            </div>

            <!-- Top documents -->
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Top Documents</h3>
                <div class="space-y-3">
                    <div v-for="(doc, i) in topDocs" :key="doc.name" class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-400 w-4">{{ i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ doc.name }}</p>
                            <div class="h-1.5 rounded-full bg-gray-100 mt-1">
                                <div class="h-full rounded-full bg-gradient-to-r from-primary-400 to-primary-600" :style="{ width: doc.pct + '%' }"></div>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 shrink-0">{{ doc.count }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery breakdown -->
        <div class="grid sm:grid-cols-2 gap-6">
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Delivery Channels</h3>
                <div class="space-y-4">
                    <div v-for="ch in channels" :key="ch.name">
                        <div class="flex justify-between text-sm mb-2">
                            <div class="flex items-center gap-2">
                                <div :class="['w-3 h-3 rounded-full', ch.dot]"></div>
                                <span class="text-gray-700">{{ ch.name }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ ch.count }} <span class="text-gray-400 font-normal">({{ ch.pct }}%)</span></span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div :class="['h-full rounded-full', ch.bar]" :style="{ width: ch.pct + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Village Breakdown</h3>
                <div class="space-y-2.5">
                    <div v-for="v in villageStats" :key="v.name" class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 w-20 truncate">{{ v.name }}</span>
                        <div class="flex-1 h-2 rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-amber-400" :style="{ width: v.pct + '%' }"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 w-8 text-right">{{ v.count }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { FileText, Users, Link2, MessageCircle } from 'lucide-vue-next';

const period = ref('30');

const kpis = [
    { label: 'Documents Created', value: '12', change: '+2', up: true, icon: FileText, bg: 'bg-primary-50', color: 'text-primary-600' },
    { label: 'Links Generated', value: '3,412', change: '+18%', up: true, icon: Link2, bg: 'bg-amber-50', color: 'text-amber-600' },
    { label: 'WhatsApp Sent', value: '420', change: '+5%', up: true, icon: MessageCircle, bg: 'bg-green-50', color: 'text-green-600' },
    { label: 'Email Sent', value: '215', change: '-2%', up: false, icon: Users, bg: 'bg-rose-50', color: 'text-rose-500' },
];

const chartBars = [
    { label: 'Apr 10', links: 60, wa: 40 },
    { label: 'Apr 14', links: 75, wa: 55 },
    { label: 'Apr 18', links: 45, wa: 30 },
    { label: 'Apr 22', links: 85, wa: 70 },
    { label: 'Apr 26', links: 90, wa: 65 },
    { label: 'Apr 30', links: 70, wa: 50 },
    { label: 'May 4', links: 95, wa: 80 },
];

const topDocs = [
    { name: 'Navratri Celebration Card', count: 1200, pct: 100 },
    { name: 'Wedding Invitation 2026', count: 980, pct: 82 },
    { name: 'Corporate Annual Meet', count: 720, pct: 60 },
    { name: 'Birthday Party Invite', count: 310, pct: 26 },
    { name: 'New Year Greeting', count: 202, pct: 17 },
];

const channels = [
    { name: 'WhatsApp', count: 420, pct: 63, dot: 'bg-green-400', bar: 'bg-green-400' },
    { name: 'Email', count: 215, pct: 32, dot: 'bg-primary-400', bar: 'bg-primary-400' },
    { name: 'Direct Link', count: 34, pct: 5, dot: 'bg-gray-400', bar: 'bg-gray-400' },
];

const villageStats = [
    { name: 'Anand', count: 380, pct: 100 },
    { name: 'Vadodara', count: 290, pct: 76 },
    { name: 'Surat', count: 210, pct: 55 },
    { name: 'Ahmedabad', count: 185, pct: 49 },
    { name: 'Nadiad', count: 120, pct: 32 },
    { name: 'Kheda', count: 63, pct: 17 },
];
</script>
