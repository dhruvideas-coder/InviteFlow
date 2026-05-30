import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('@/layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', redirect: '/dashboard' },
            { path: 'dashboard', name: 'dashboard', component: () => import('@/pages/Dashboard.vue') },
            { path: 'documents', name: 'documents', component: () => import('@/pages/Documents.vue') },
            { path: 'documents/create', name: 'documents.create', component: () => import('@/pages/DocumentBuilder.vue'), meta: { roles: ['admin', 'super_admin'] } },
            { path: 'documents/:id/edit', name: 'documents.edit', component: () => import('@/pages/DocumentBuilder.vue'), meta: { roles: ['admin', 'super_admin'] } },


            { path: 'recipients', name: 'recipients', component: () => import('@/pages/Recipients.vue') },
            { path: 'analytics', name: 'analytics', component: () => import('@/pages/Analytics.vue') },
            { path: 'subscription', name: 'subscription', component: () => import('@/pages/Subscription.vue'), meta: { roles: ['admin', 'super_admin'] } },
            { path: 'settings', name: 'settings', component: () => import('@/pages/Settings.vue') },
            { 
                path: 'users', 
                name: 'users', 
                component: () => import('@/pages/Users.vue'),
                meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } 
            },
            {
                path: 'admin/plans',
                name: 'admin.plans',
                component: () => import('@/pages/admin/Plans.vue'),
                meta: { requiresAuth: true, role: 'super_admin' },
            },
            {
                path: 'admin/tenants',
                name: 'admin.tenants',
                component: () => import('@/pages/admin/Tenants.vue'),
                meta: { requiresAuth: true, role: 'super_admin' },
            },
        ],
    },
    {
        path: '/doc/view/:token',
        name: 'doc.view',
        component: () => import('@/pages/DocumentView.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/NotFound.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    // Fetch user once on first navigation
    if (auth.loading) {
        await auth.fetchUser();
    }

    // Guest-only routes (login page)
    if (to.meta.guest && auth.isAuthenticated) {
        return '/dashboard';
    }

    // Protected routes
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return '/login';
    }

    // Role-restricted routes
    if (to.meta.role && auth.user?.role !== to.meta.role) {
        return '/dashboard';
    }
    
    // Multiple Roles allowed
    if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
        return '/dashboard';
    }
});

export default router;
