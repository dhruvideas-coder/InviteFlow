import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const loading = ref(true);
    const error = ref(null);

    const isAuthenticated = computed(() => !!user.value);
    const isSuperAdmin = computed(() => user.value?.role === 'super_admin');
    const isAdmin = computed(() => user.value?.role === 'admin');
    const isMember = computed(() => user.value?.role === 'member');

    const avatarInitials = computed(() => {
        if (!user.value?.name) return '?';
        return user.value.name
            .split(' ')
            .slice(0, 2)
            .map(w => w[0])
            .join('')
            .toUpperCase();
    });

    async function fetchUser() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await axios.get('/api/user');
            user.value = data.authenticated ? data.user : null;
        } catch {
            user.value = null;
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        try {
            await axios.post('/auth/logout', {}, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            });
        } finally {
            user.value = null;
            window.location.href = '/login';
        }
    }

    return { user, loading, error, isAuthenticated, isSuperAdmin, isAdmin, isMember, avatarInitials, fetchUser, logout };
});
