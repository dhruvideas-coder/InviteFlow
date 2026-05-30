<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <p class="text-sm text-gray-500">{{ lang.t('analytics_desc') }}</p>
            <div class="flex gap-2">
                <select v-model="period" class="select sm:w-36" :disabled="loading">
                    <option value="7">{{ lang.t('last_7_days') }}</option>
                    <option value="30">{{ lang.t('last_30_days') }}</option>
                    <option value="90">{{ lang.t('last_90_days') }}</option>
                </select>
                <button class="btn btn-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden sm:inline">{{ lang.t('export') }}</span>
                </button>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Skeleton -->
            <template v-if="loading">
                <div v-for="i in 4" :key="i" class="stat-card animate-pulse">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-200"></div>
                        <div class="w-10 h-4 rounded bg-gray-200"></div>
                    </div>
                    <div class="h-7 w-14 bg-gray-200 rounded mb-2"></div>
                    <div class="h-4 w-28 bg-gray-100 rounded"></div>
                </div>
            </template>
            <!-- Data -->
            <template v-else>
                <div v-for="kpi in kpis" :key="kpi.labelKey" class="stat-card">
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
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ lang.t(kpi.labelKey) }}</p>
                </div>
            </template>
        </div>

        <!-- Chart + top documents -->
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Bar chart -->
            <div class="lg:col-span-2 card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h3 class="font-semibold text-gray-900">{{ lang.t('links_generated_over_time') }}</h3>
                    <div class="flex gap-3 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-primary-500 inline-block"></span>{{ lang.t('links') }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>{{ lang.t('whatsapp') }}
                        </div>
                    </div>
                </div>

                <!-- Chart skeleton -->
                <div v-if="loading" class="flex items-end gap-2 h-40 animate-pulse">
                    <div v-for="i in 7" :key="i" class="flex-1 flex gap-0.5 items-end" style="height: 128px">
                        <div class="flex-1 rounded-t-md bg-gray-200" :style="{ height: (30 + i * 10) + '%' }"></div>
                        <div class="flex-1 rounded-t-md bg-gray-100" :style="{ height: (20 + i * 8) + '%' }"></div>
                    </div>
                </div>

                <!-- Chart bars -->
                <div v-else class="flex items-end gap-2 h-40">
                    <div
                        v-for="(bar, i) in thinChart"
                        :key="i"
                        class="flex-1 flex flex-col items-center gap-1"
                    >
                        <div class="w-full flex gap-0.5 items-end" style="height: 128px">
                            <div
                                class="flex-1 rounded-t-md bg-primary-200 hover:bg-primary-400 transition-colors cursor-pointer"
                                :style="{ height: barHeight(bar.links) + '%' }"
                                :title="`${bar.links} links`"
                            ></div>
                            <div
                                class="flex-1 rounded-t-md bg-emerald-200 hover:bg-emerald-400 transition-colors cursor-pointer"
                                :style="{ height: barHeight(bar.wa) + '%' }"
                                :title="`${bar.wa} WA`"
                            ></div>
                        </div>
                        <span class="text-xs text-gray-400">{{ fmtChartDate(bar.date) }}</span>
                    </div>
                    <!-- Empty state -->
                    <div v-if="thinChart.length === 0" class="flex-1 flex items-center justify-center text-sm text-gray-400" style="height: 128px">
                        No data for this period
                    </div>
                </div>
            </div>

            <!-- Top documents -->
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ lang.t('top_documents') }}</h3>

                <!-- Skeleton -->
                <div v-if="loading" class="space-y-3 animate-pulse">
                    <div v-for="i in 5" :key="i" class="flex items-center gap-3">
                        <div class="w-4 h-3 bg-gray-200 rounded"></div>
                        <div class="flex-1 space-y-1.5">
                            <div class="h-3.5 bg-gray-200 rounded" :style="{ width: (80 - i * 10) + '%' }"></div>
                            <div class="h-1.5 rounded-full bg-gray-100"></div>
                        </div>
                        <div class="w-8 h-3 bg-gray-200 rounded"></div>
                    </div>
                </div>

                <!-- Data -->
                <div v-else class="space-y-3">
                    <div v-if="topDocs.length === 0" class="text-sm text-gray-400 text-center py-4">No data</div>
                    <div v-for="(doc, i) in topDocs" :key="doc.name" class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-400 w-4">{{ i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ doc.name }}</p>
                            <div class="h-1.5 rounded-full bg-gray-100 mt-1">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-primary-400 to-primary-600 transition-all duration-700"
                                    :style="{ width: doc.pct + '%' }"
                                ></div>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 shrink-0">{{ fmt(doc.count) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery breakdown -->
        <div class="grid sm:grid-cols-2 gap-6">
            <!-- Channels -->
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ lang.t('delivery_channels') }}</h3>

                <!-- Skeleton -->
                <div v-if="loading" class="space-y-4 animate-pulse">
                    <div v-for="i in 3" :key="i">
                        <div class="flex justify-between mb-2">
                            <div class="h-4 w-24 bg-gray-200 rounded"></div>
                            <div class="h-4 w-16 bg-gray-200 rounded"></div>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100"></div>
                    </div>
                </div>

                <!-- Data -->
                <div v-else class="space-y-4">
                    <div v-if="channels.length === 0" class="text-sm text-gray-400 text-center py-4">No data</div>
                    <div v-for="ch in channelsDisplay" :key="ch.name">
                        <div class="flex justify-between text-sm mb-2">
                            <div class="flex items-center gap-2">
                                <div :class="['w-3 h-3 rounded-full', ch.dot]"></div>
                                <span class="text-gray-700">{{ ch.name }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">
                                {{ fmt(ch.count) }} <span class="text-gray-400 font-normal">({{ ch.pct }}%)</span>
                            </span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div :class="['h-full rounded-full transition-all duration-700', ch.bar]" :style="{ width: ch.pct + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Village breakdown -->
            <div class="card p-5">
                <h3 class="font-semibold text-gray-900 mb-4">{{ lang.t('village_breakdown') }}</h3>

                <!-- Skeleton -->
                <div v-if="loading" class="space-y-2.5 animate-pulse">
                    <div v-for="i in 6" :key="i" class="flex items-center gap-3">
                        <div class="h-3 w-16 bg-gray-200 rounded"></div>
                        <div class="flex-1 h-2 rounded-full bg-gray-100"></div>
                        <div class="h-3 w-6 bg-gray-200 rounded"></div>
                    </div>
                </div>

                <!-- Data -->
                <div v-else class="space-y-2.5">
                    <div v-if="villages.length === 0" class="text-sm text-gray-400 text-center py-4">No data</div>
                    <div v-for="v in villages" :key="v.name" class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 w-20 truncate">{{ v.name }}</span>
                        <div class="flex-1 h-2 rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-amber-400 transition-all duration-700"
                                :style="{ width: v.pct + '%' }"
                            ></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 w-8 text-right">{{ fmt(v.count) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { FileText, Users, Link2, MessageCircle } from 'lucide-vue-next';
import { useLanguageStore } from '@/stores/language';

const lang = useLanguageStore();

const loading  = ref(true);
const period   = ref('30');

const kpisRaw  = ref({ documents: { value: 0, prev: 0 }, links: { value: 0, prev: 0 }, whatsapp: { value: 0, prev: 0 }, email: { value: 0, prev: 0 } });
const chartRaw = ref([]);
const topDocs  = ref([]);
const channels = ref([]);
const villages = ref([]);

const fmt = (n) => new Intl.NumberFormat().format(n ?? 0);

function calcChange(curr, prev) {
    if (!prev) return curr > 0 ? { text: '+100%', up: true } : { text: '–', up: true };
    const pct = Math.round((curr - prev) / prev * 100);
    return { text: (pct >= 0 ? '+' : '') + pct + '%', up: pct >= 0 };
}

const kpis = computed(() => {
    const d  = kpisRaw.value;
    const ch = calcChange(d.documents.value, d.documents.prev);
    const lh = calcChange(d.links.value,     d.links.prev);
    const wh = calcChange(d.whatsapp.value,  d.whatsapp.prev);
    const eh = calcChange(d.email.value,     d.email.prev);

    return [
        { labelKey: 'documents_created', value: fmt(d.documents.value), change: ch.text, up: ch.up, icon: FileText,      bg: 'bg-primary-50', color: 'text-primary-600' },
        { labelKey: 'links_generated',   value: fmt(d.links.value),     change: lh.text, up: lh.up, icon: Link2,         bg: 'bg-amber-50',   color: 'text-amber-600'   },
        { labelKey: 'whatsapp_sent',     value: fmt(d.whatsapp.value),  change: wh.text, up: wh.up, icon: MessageCircle, bg: 'bg-green-50',   color: 'text-green-600'   },
        { labelKey: 'email_sent',        value: fmt(d.email.value),     change: eh.text, up: eh.up, icon: Users,         bg: 'bg-rose-50',    color: 'text-rose-500'    },
    ];
});

// Sample chart to ~7 bars for readability
const thinChart = computed(() => {
    const data = chartRaw.value;
    if (data.length <= 7) return data;
    const count = 7;
    const indices = Array.from({ length: count }, (_, i) =>
        Math.round(i * (data.length - 1) / (count - 1))
    );
    return [...new Set(indices)].map(i => data[i]);
});

const maxBarVal = computed(() => Math.max(...thinChart.value.map(b => b.links), 1));

function barHeight(val) {
    return Math.max(Math.round(val / maxBarVal.value * 100), val > 0 ? 4 : 0);
}

function fmtChartDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    if (+period.value === 7) return d.toLocaleDateString('en', { weekday: 'short' });
    return d.toLocaleDateString('en', { month: 'short', day: 'numeric' });
}

const CHANNEL_STYLE = {
    'WhatsApp':    { dot: 'bg-green-400',   bar: 'bg-green-400'   },
    'Email':       { dot: 'bg-primary-400', bar: 'bg-primary-400' },
    'Direct Link': { dot: 'bg-gray-400',    bar: 'bg-gray-400'    },
};

const channelsDisplay = computed(() =>
    channels.value.map(ch => ({
        ...ch,
        ...(CHANNEL_STYLE[ch.name] ?? { dot: 'bg-gray-400', bar: 'bg-gray-400' }),
    }))
);

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/analytics', { params: { period: period.value } });
        kpisRaw.value  = data.kpis;
        chartRaw.value = data.chart;
        topDocs.value  = data.top_docs;
        channels.value = data.channels;
        villages.value = data.villages;
    } catch {
        // keep zeros
    } finally {
        loading.value = false;
    }
}

watch(period, fetchData);
onMounted(fetchData);
</script>
