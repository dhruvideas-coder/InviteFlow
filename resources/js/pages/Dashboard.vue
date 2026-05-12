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
            <div v-for="stat in stats" :key="stat.label" class="stat-card">
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
        </div>

        <!-- Middle row -->
        <div class="grid md:grid-cols-3 gap-4 sm:gap-6">
            <!-- Recent documents -->
            <div class="md:col-span-2 card overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-gray-50">
                    <h3 class="font-semibold text-gray-900">{{ lang.t('recent_documents') }}</h3>
                    <RouterLink to="/documents" class="text-sm text-primary-600 hover:text-primary-700 font-medium">{{ lang.t('view_all') }}</RouterLink>
                </div>
                <div class="divide-y divide-gray-50">
                    <div v-for="doc in recentDocs" :key="doc.id" class="flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50/60 transition-colors">
                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center shrink-0', doc.color]">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ doc.name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ doc.recipients }} recipients · {{ doc.date }}</p>
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

            <!-- Subscription card -->
            <div class="card p-5 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Subscription</h3>
                    <span class="badge badge-primary">Pro Plan</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">WhatsApp Sends</span>
                            <span class="font-semibold text-gray-900">420 / 1000</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-full w-[42%] rounded-full bg-gradient-to-r from-primary-500 to-primary-600"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Email Sends</span>
                            <span class="font-semibold text-gray-900">215 / 1000</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-full w-[21.5%] rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Documents</span>
                            <span class="font-semibold text-gray-900">12 / 50</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-full w-[24%] rounded-full bg-gradient-to-r from-amber-400 to-amber-500"></div>
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

        <!-- Quick actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <RouterLink
                v-for="action in quickActions"
                :key="action.label"
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
import { RouterLink } from 'vue-router';
import { FileText, Users, Link2, BarChart2, Upload, MessageCircle, Download, Settings } from 'lucide-vue-next';
import { useLanguageStore } from '@/stores/language';

const lang = useLanguageStore();

const stats = [
    { labelKey: 'documents', value: '12', trend: '+2 this week', trendUp: true, icon: FileText, iconBg: 'bg-primary-50', iconColor: 'text-primary-600' },
    { labelKey: 'recipients', value: '1,248', trend: '+84 today', trendUp: true, icon: Users, iconBg: 'bg-emerald-50', iconColor: 'text-emerald-600' },
    { labelKey: 'links_generated', value: '3,412', trend: '+312 today', trendUp: true, icon: Link2, iconBg: 'bg-amber-50', iconColor: 'text-amber-600' },
    { labelKey: 'whatsapp_sent', value: '420', trend: '42% of quota', trendUp: true, icon: MessageCircle, iconBg: 'bg-rose-50', iconColor: 'text-rose-500' },
];

const recentDocs = [
    { id: 1, name: 'Wedding Invitation 2026', recipients: 250, date: '2 hours ago', status: 'Active', color: 'bg-gradient-to-br from-pink-400 to-rose-500' },
    { id: 2, name: 'Corporate Annual Meet', recipients: 120, date: 'Yesterday', status: 'Active', color: 'bg-gradient-to-br from-primary-400 to-primary-600' },
    { id: 3, name: 'Navratri Celebration Card', recipients: 380, date: '3 days ago', status: 'Active', color: 'bg-gradient-to-br from-amber-400 to-orange-500' },
    { id: 4, name: 'Birthday Party Invite', recipients: 45, date: '1 week ago', status: 'Expired', color: 'bg-gradient-to-br from-emerald-400 to-teal-500' },
];

const quickActions = [
    { labelKey: 'upload_template', descKey: 'add_new_document', to: '/documents/create', icon: Upload, bg: 'bg-primary-50', color: 'text-primary-600' },
    { labelKey: 'add_recipients', descKey: 'manage_your_list', to: '/recipients', icon: Users, bg: 'bg-emerald-50', color: 'text-emerald-600' },
    { labelKey: 'send_via_whatsapp', descKey: 'share_invitations', to: '/links', icon: MessageCircle, bg: 'bg-green-50', color: 'text-green-600' },
    { labelKey: 'view_analytics', descKey: 'track_performance', to: '/analytics', icon: BarChart2, bg: 'bg-amber-50', color: 'text-amber-600' },
];
</script>
