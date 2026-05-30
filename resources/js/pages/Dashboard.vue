<template>
    <div class="space-y-6">
        <!-- Welcome banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 to-primary-800 p-6 text-white">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 right-20 w-32 h-32 bg-white/5 rounded-full translate-y-1/2"></div>
            <div class="relative">
                <p class="text-primary-200 text-sm font-medium mb-1">{{ lang.t('welcome_back') }} 👋</p>
                <h2 class="text-2xl font-bold">{{ lang.t('admin_greeting') }}</h2>
                <p class="text-primary-200 text-sm mt-2">{{ lang.t('dashboard_subtitle') }}</p>
                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <RouterLink to="/documents/create" class="btn btn-sm bg-white text-primary-700 hover:bg-primary-50 focus:ring-white shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ lang.t('new_document') }}
                    </RouterLink>
                    <RouterLink to="/recipients" class="btn btn-sm border border-white/30 text-white hover:bg-white/10 focus:ring-white">
                        {{ lang.t('add_recipient') }}
                    </RouterLink>
                </div>
            </div>
        </div>

        <!-- Stats grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <!-- Skeleton -->
            <template v-if="loading">
                <div v-for="i in 4" :key="i" class="stat-card animate-pulse">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-200"></div>
                        <div class="w-16 h-5 rounded-full bg-gray-200"></div>
                    </div>
                    <div class="h-7 w-14 bg-gray-200 rounded mb-2"></div>
                    <div class="h-4 w-24 bg-gray-100 rounded"></div>
                </div>
            </template>
            <!-- Data -->
            <template v-else>
                <div v-for="stat in stats" :key="stat.labelKey" class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', stat.iconBg]">
                            <component :is="stat.icon" :class="['w-5 h-5', stat.iconColor]" />
                        </div>
                        <span :class="['badge text-xs', stat.trendUp ? 'badge-green' : 'badge-red']">
                            {{ stat.trend }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ lang.t(stat.labelKey) }}</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Middle row -->
        <div class="grid md:grid-cols-3 gap-4 sm:gap-6">
            <!-- Recent documents -->
            <div class="md:col-span-2 card overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-gray-50">
                    <h3 class="font-semibold text-gray-900">{{ lang.t('recent_documents') }}</h3>
                    <RouterLink to="/documents" class="text-sm text-primary-600 hover:text-primary-700 font-medium">{{ lang.t('view_all') }}</RouterLink>
                </div>

                <!-- Skeleton rows -->
                <template v-if="loading">
                    <div v-for="i in 4" :key="i" class="flex items-center gap-3 px-4 py-3.5 animate-pulse">
                        <div class="w-9 h-9 rounded-xl bg-gray-200 shrink-0"></div>
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="h-4 w-40 bg-gray-200 rounded"></div>
                            <div class="h-3 w-28 bg-gray-100 rounded"></div>
                        </div>
                        <div class="w-14 h-5 rounded-full bg-gray-200 shrink-0"></div>
                    </div>
                </template>

                <!-- Data rows -->
                <div v-else class="divide-y divide-gray-50">
                    <div v-if="recentDocs.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
                        {{ lang.t('no_documents_yet') }}
                    </div>
                    <div
                        v-for="(doc, idx) in recentDocs"
                        :key="doc.id"
                        class="flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50/60 transition-colors"
                    >
                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center shrink-0', DOC_COLORS[idx % DOC_COLORS.length]]">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ doc.name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ doc.recipients_count }} recipients · {{ timeAgo(doc.created_at) }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span :class="['badge text-xs', doc.status === 'Active' ? 'badge-green' : 'badge-amber']">
                                {{ doc.status }}
                            </span>
                            <RouterLink :to="`/documents/${doc.id}/edit`" class="hidden sm:inline-flex btn btn-ghost btn-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription card (static) -->
            <div class="card p-5 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Subscription</h3>
                    <span class="badge badge-primary">Pro Plan</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">WhatsApp Sends</span>
                            <span v-if="loading" class="w-16 h-4 bg-gray-200 rounded animate-pulse inline-block"></span>
                            <span v-else class="font-semibold text-gray-900">{{ fmt(statsData.whatsapp_sent) }} / 1000</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-700"
                                :style="{ width: loading ? '0%' : Math.min(statsData.whatsapp_sent / 1000 * 100, 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Documents</span>
                            <span v-if="loading" class="w-12 h-4 bg-gray-200 rounded animate-pulse inline-block"></span>
                            <span v-else class="font-semibold text-gray-900">{{ fmt(statsData.documents) }} / 50</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500 transition-all duration-700"
                                :style="{ width: loading ? '0%' : Math.min(statsData.documents / 50 * 100, 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Links Generated</span>
                            <span v-if="loading" class="w-16 h-4 bg-gray-200 rounded animate-pulse inline-block"></span>
                            <span v-else class="font-semibold text-gray-900">{{ fmt(statsData.links_generated) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-full w-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 opacity-30"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-500">
                    Renews on <span class="font-semibold text-gray-700">June 15, 2026</span>
                </div>

                <RouterLink to="/subscription" class="btn btn-secondary w-full">
                    {{ lang.t('manage_subscription') }}
                </RouterLink>
            </div>
        </div>

        <!-- Quick actions (static — no API needed) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <RouterLink
                v-for="action in quickActions"
                :key="action.labelKey"
                :to="action.to"
                class="card card-hover p-4 flex items-center gap-4 group"
            >
                <div :class="['w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105', action.bg]">
                    <component :is="action.icon" :class="['w-5 h-5', action.color]" />
                </div>
                <div>
                    <p class="font-medium text-gray-900 text-sm">{{ lang.t(action.labelKey) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ lang.t(action.descKey) }}</p>
                </div>
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { FileText, Users, Link2, BarChart2, Upload, MessageCircle, Download, Settings } from 'lucide-vue-next';
import { useLanguageStore } from '@/stores/language';

const lang = useLanguageStore();

const loading    = ref(true);
const statsData  = ref({ documents: 0, recipients: 0, links_generated: 0, whatsapp_sent: 0 });
const trendsData = ref({ docs_this_week: 0, links_today: 0, wa_pct: 0 });
const recentDocs = ref([]);

const DOC_COLORS = [
    'bg-gradient-to-br from-pink-400 to-rose-500',
    'bg-gradient-to-br from-primary-400 to-primary-600',
    'bg-gradient-to-br from-amber-400 to-orange-500',
    'bg-gradient-to-br from-emerald-400 to-teal-500',
    'bg-gradient-to-br from-violet-400 to-purple-500',
    'bg-gradient-to-br from-sky-400 to-blue-500',
];

const fmt = (n) => new Intl.NumberFormat().format(n ?? 0);

function timeAgo(dateStr) {
    const seconds = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (seconds < 60)   return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
    const hours = Math.floor(seconds / 3600);
    if (hours < 24)     return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    const days = Math.floor(hours / 24);
    if (days === 1)     return 'Yesterday';
    if (days < 7)       return `${days} days ago`;
    const weeks = Math.floor(days / 7);
    return weeks === 1 ? '1 week ago' : `${weeks} weeks ago`;
}

const stats = computed(() => [
    {
        labelKey: 'documents',
        value: fmt(statsData.value.documents),
        trend: `+${trendsData.value.docs_this_week} this week`,
        trendUp: trendsData.value.docs_this_week >= 0,
        icon: FileText, iconBg: 'bg-primary-50', iconColor: 'text-primary-600',
    },
    {
        labelKey: 'recipients',
        value: fmt(statsData.value.recipients),
        trend: `${fmt(statsData.value.recipients)} total`,
        trendUp: true,
        icon: Users, iconBg: 'bg-emerald-50', iconColor: 'text-emerald-600',
    },
    {
        labelKey: 'links_generated',
        value: fmt(statsData.value.links_generated),
        trend: `+${trendsData.value.links_today} today`,
        trendUp: trendsData.value.links_today >= 0,
        icon: Link2, iconBg: 'bg-amber-50', iconColor: 'text-amber-600',
    },
    {
        labelKey: 'whatsapp_sent',
        value: fmt(statsData.value.whatsapp_sent),
        trend: `${trendsData.value.wa_pct}% of links`,
        trendUp: true,
        icon: MessageCircle, iconBg: 'bg-rose-50', iconColor: 'text-rose-500',
    },
]);

const quickActions = [
    { labelKey: 'upload_template',  descKey: 'add_new_document',  to: '/documents/create', icon: Upload,        bg: 'bg-primary-50', color: 'text-primary-600' },
    { labelKey: 'add_recipients',   descKey: 'manage_your_list',  to: '/recipients',       icon: Users,         bg: 'bg-emerald-50', color: 'text-emerald-600' },
    { labelKey: 'send_via_whatsapp',descKey: 'share_invitations', to: '/links',            icon: MessageCircle, bg: 'bg-green-50',   color: 'text-green-600'   },
    { labelKey: 'view_analytics',   descKey: 'track_performance', to: '/analytics',        icon: BarChart2,     bg: 'bg-amber-50',   color: 'text-amber-600'   },
];

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/dashboard');
        statsData.value  = data.stats;
        trendsData.value = data.trends;
        recentDocs.value = data.recent_docs;
    } catch {
        // keep zeros — page still renders
    } finally {
        loading.value = false;
    }
});
</script>
