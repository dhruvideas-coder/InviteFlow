<template>
    <div class="flex h-full min-h-screen bg-slate-50">

        <!-- Mobile backdrop -->
        <Transition name="fade">
            <div
                v-if="mobileMenuOpen"
                @click="mobileMenuOpen = false"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 md:hidden"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex flex-col bg-white border-r border-gray-100 transition-all duration-300 ease-in-out',
                // Desktop: collapse/expand by width
                isDesktop
                    ? (sidebarOpen ? 'w-64' : 'w-[68px]')
                    : (mobileMenuOpen ? 'translate-x-0 w-72 shadow-2xl' : '-translate-x-full w-72'),
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-3 overflow-hidden flex-1 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shrink-0 shadow-sm shadow-primary-200">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div v-show="showLabels" class="overflow-hidden flex-1 min-w-0">
                        <span class="text-base font-bold text-gray-900 block whitespace-nowrap">InviteFlow</span>
                        <span class="text-xs text-gray-400 block whitespace-nowrap">{{ roleBadge }}</span>
                    </div>
                </div>
                <!-- Close button — mobile only -->
                <button
                    v-if="!isDesktop"
                    @click="mobileMenuOpen = false"
                    class="ml-2 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors shrink-0"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto overflow-x-hidden">
                <template v-for="item in visibleNavItems" :key="item.name || item.divider">
                    <div v-if="item.divider" class="border-t border-gray-100 my-2 mx-1"></div>
                    <RouterLink
                        v-else
                        :to="item.to"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150',
                            isActive(item.to)
                                ? 'bg-primary-50 text-primary-700'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
                        ]"
                        :title="(isDesktop && !sidebarOpen) ? item.label : undefined"
                    >
                        <component :is="item.icon" class="w-5 h-5 shrink-0" />
                        <span v-show="showLabels" class="whitespace-nowrap">{{ item.label }}</span>
                        <span
                            v-if="item.badge && showLabels"
                            class="ml-auto text-xs px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 font-semibold"
                        >{{ item.badge }}</span>
                    </RouterLink>
                </template>
            </nav>

            <!-- User section -->
            <div class="px-3 pb-4 border-t border-gray-100 pt-3 shrink-0 space-y-1">
                <div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="shrink-0 relative">
                        <img
                            v-if="auth.user?.avatar"
                            :src="auth.user.avatar"
                            class="w-8 h-8 rounded-full object-cover ring-2 ring-primary-100"
                            :alt="auth.user.name"
                        />
                        <div
                            v-else
                            class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold"
                        >
                            {{ auth.avatarInitials }}
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-400 border-2 border-white"></span>
                    </div>
                    <div v-show="showLabels" class="overflow-hidden min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth.user?.name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth.user?.email }}</p>
                    </div>
                </div>

                <button
                    @click="auth.logout()"
                    class="flex items-center gap-3 w-full px-3 py-2 text-sm font-medium rounded-xl transition-all text-gray-500 hover:bg-red-50 hover:text-red-600"
                    :title="(isDesktop && !sidebarOpen) ? 'Sign out' : undefined"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span v-show="showLabels" class="whitespace-nowrap">{{ lang.t('sign_out') }}</span>
                </button>
            </div>
        </aside>

        <!-- Main content — no left margin on mobile, dynamic on desktop -->
        <div
            :class="[
                'flex-1 flex flex-col min-w-0 transition-all duration-300',
                isDesktop ? (sidebarOpen ? 'ml-64' : 'ml-[68px]') : 'ml-0',
            ]"
        >
            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex items-center h-14 sm:h-16 p-4 sm:p-6 bg-white border-b border-gray-100 shadow-sm">
                <button
                    @click="isDesktop ? (sidebarOpen = !sidebarOpen) : (mobileMenuOpen = !mobileMenuOpen)"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-900 mr-3 -ml-1 transition-all shrink-0"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1 min-w-0">
                    <h1 class="text-base sm:text-lg font-bold text-gray-900 truncate">{{ translatedPageTitle }}</h1>
                </div>

                <div class="flex items-center gap-3 ml-3">
                    <LanguageSwitcher />

                    <div class="hidden sm:flex items-center gap-2">
                        <span v-if="auth.isSuperAdmin" class="badge badge-amber">{{ lang.t('super_admin') }}</span>
                        <span v-else-if="auth.isAdmin" class="badge badge-primary">{{ lang.t('admin') }}</span>
                        <span v-else class="badge badge-green">{{ lang.t('member') }}</span>
                    </div>

                    <!-- Desktop: full text button (admin/super_admin only) -->
                    <RouterLink
                        v-if="auth.isAdmin || auth.isSuperAdmin"
                        to="/documents/create"
                        class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ lang.t('new_document') }}
                    </RouterLink>

                    <!-- Mobile: icon-only button (admin/super_admin only) -->
                    <RouterLink
                        v-if="auth.isAdmin || auth.isSuperAdmin"
                        to="/documents/create"
                        class="sm:hidden inline-flex items-center justify-center w-9 h-9 rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </RouterLink>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { RouterView, RouterLink, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useLanguageStore } from '@/stores/language';
import { useBreakpoints } from '@vueuse/core';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import {
    LayoutDashboard, FileText, Users, Link2, BarChart2,
    CreditCard, Settings, Shield, Building2, UserCog, Mail
} from 'lucide-vue-next';

const auth = useAuthStore();
const lang = useLanguageStore();
const route = useRoute();

const bp = useBreakpoints({ md: 768 });
const isDesktop = bp.greaterOrEqual('md');

const sidebarOpen = ref(true);       // desktop collapse state
const mobileMenuOpen = ref(false);   // mobile drawer state

// Close mobile drawer on navigation
watch(() => route.path, () => {
    mobileMenuOpen.value = false;
});

// Whether nav labels should be visible
const showLabels = computed(() => {
    return isDesktop.value ? sidebarOpen.value : mobileMenuOpen.value;
});

const roleBadge = computed(() => {
    if (auth.isSuperAdmin) return lang.t('super_admin');
    if (auth.isAdmin) return lang.t('admin');
    return lang.t('member');
});

const allNavItems = computed(() => [
    { label: lang.t('dashboard'), to: '/dashboard', icon: LayoutDashboard },
    { label: lang.t('documents'), to: '/documents', icon: FileText },
    { label: lang.t('recipients'), to: '/recipients', icon: Users },
    { label: lang.t('analytics'), to: '/analytics', icon: BarChart2 },
    { divider: true },
    { label: lang.t('subscription'), to: '/subscription', icon: CreditCard, memberHidden: true },
    { label: lang.t('settings'), to: '/settings', icon: Settings },
    { label: lang.t('users'), to: '/users', icon: UserCog, adminOnly: true },
    { divider: true, superAdminOnly: true },
    { label: lang.t('subscription_plans'), to: '/admin/plans', icon: Shield, badge: 'Admin', superAdminOnly: true },
    { label: lang.t('tenant_management'), to: '/admin/tenants', icon: Building2, superAdminOnly: true },
]);

const visibleNavItems = computed(() => {
    return allNavItems.value.filter(item => {
        if (item.superAdminOnly && !auth.isSuperAdmin) return false;
        if (item.adminOnly && !(auth.isAdmin || auth.isSuperAdmin)) return false;
        if (item.memberHidden && auth.isMember) return false;
        return true;
    });
});

const pageTitles = {
    '/dashboard': 'dashboard',
    '/documents': 'documents',
    '/documents/create': 'create_document',
    '/recipients': 'recipients',
    '/analytics': 'analytics',
    '/subscription': 'subscription',
    '/settings': 'settings',
    '/users': 'users',
    '/admin/plans': 'subscription_plans',
    '/admin/tenants': 'tenant_management',
};

const translatedPageTitle = computed(() => {
    const key = pageTitles[route.path];
    return key ? lang.t(key) : 'InviteFlow';
});

function isActive(to) {
    if (to === '/dashboard') return route.path === '/dashboard';
    return route.path.startsWith(to);
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
