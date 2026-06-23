<template>
    <div class="space-y-6 max-w-5xl mx-auto">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ lang.t('user_management') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ lang.t('user_management_desc') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <ViewToggle v-model="viewMode" />
                <button
                    v-if="auth.isSuperAdmin || auth.isAdmin"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ lang.t('add_user') }}
                </button>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 rounded-xl bg-red-50 text-red-600 text-sm border border-red-100 flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ error }}
        </div>

        <!-- Success State -->
        <div v-if="success" class="p-4 rounded-xl bg-emerald-50 text-emerald-600 text-sm border border-emerald-100 flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ success }}
        </div>

        <!-- Table View (desktop only when table mode is active) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hidden" :class="{ 'sm:block': viewMode === 'table' }">
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ lang.t('user') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ lang.t('role') }}</th>
                            <th v-if="auth.isSuperAdmin" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ lang.t('admin_parent') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ lang.t('joined_date') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">{{ lang.t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">{{ lang.t('loading_users') }}</td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">{{ lang.t('no_users_found') }}</td>
                        </tr>
                        <tr v-else v-for="user in users" :key="user.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img v-if="user.avatar" :src="user.avatar" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm">
                                    <div v-else class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold ring-2 ring-white shadow-sm">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="user.role === 'super_admin'" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">{{ lang.t('super_admin') }}</span>
                                <span v-else-if="user.role === 'admin'" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-primary-100 text-primary-700">{{ lang.t('admin') }}</span>
                                <span v-else class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600">{{ lang.t('member') }}</span>
                            </td>
                            <td v-if="auth.isSuperAdmin" class="px-6 py-4 text-sm text-gray-600">
                                <span v-if="user.parent" class="text-primary-600 font-medium">{{ user.parent.name }}</span>
                                <span v-else class="text-gray-400 italic">{{ lang.t('none') }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div v-if="auth.isSuperAdmin || (auth.isAdmin && user.parent_id === auth.user.id)" class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(user)" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button v-if="user.id !== auth.user.id" @click="confirmDelete(user)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                                <div v-else>
                                    <span class="text-xs text-gray-400 italic">{{ lang.t('no_access') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card View (mobile always; desktop when card mode is active) -->
        <div :class="{ 'sm:hidden': viewMode === 'table' }">
            <!-- Loading -->
            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="i in 6" :key="i" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 animate-pulse space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-3 bg-gray-100 rounded w-2/3"></div>
                            <div class="h-2.5 bg-gray-100 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Empty -->
            <div v-else-if="users.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center text-gray-400">
                {{ lang.t('no_users_found') }}
            </div>
            <!-- Cards -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="user in users" :key="user.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col gap-4 hover:shadow-md hover:border-gray-200 transition-all">
                    <div class="flex items-center gap-3">
                        <img v-if="user.avatar" :src="user.avatar" class="w-12 h-12 rounded-full object-cover ring-2 ring-white shadow-sm">
                        <div v-else class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-lg ring-2 ring-white shadow-sm">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-gray-900 truncate">{{ user.name }}</div>
                            <div class="text-sm text-gray-500 truncate">{{ user.email }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap text-sm">
                        <span v-if="user.role === 'super_admin'" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">{{ lang.t('super_admin') }}</span>
                        <span v-else-if="user.role === 'admin'" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-primary-100 text-primary-700">{{ lang.t('admin') }}</span>
                        <span v-else class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600">{{ lang.t('member') }}</span>
                        <span class="text-xs text-gray-400">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                    </div>

                    <div v-if="auth.isSuperAdmin" class="text-xs text-gray-500">
                        {{ lang.t('admin_parent') }}:
                        <span v-if="user.parent" class="text-primary-600 font-medium">{{ user.parent.name }}</span>
                        <span v-else class="text-gray-400 italic">{{ lang.t('none') }}</span>
                    </div>

                    <div class="flex items-center gap-2 pt-3 mt-auto border-t border-gray-50">
                        <template v-if="auth.isSuperAdmin || (auth.isAdmin && user.parent_id === auth.user.id)">
                            <button @click="openEditModal(user)" class="inline-flex items-center justify-center gap-1.5 flex-1 py-2 text-sm font-medium text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                {{ lang.t('edit') }}
                            </button>
                            <button v-if="user.id !== auth.user.id" @click="confirmDelete(user)" class="inline-flex items-center justify-center gap-1.5 py-2 px-3 text-sm font-medium text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </template>
                        <span v-else class="text-xs text-gray-400 italic py-2">{{ lang.t('no_access') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deleted accounts (recovery) — super admin only -->
        <div v-if="auth.isSuperAdmin" class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-red-50/40">
                <h3 class="font-semibold text-red-600">{{ lang.t('deleted_accounts') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ lang.t('deleted_accounts_desc') }}</p>
            </div>

            <div v-if="trashedLoading" class="px-6 py-8 text-center text-gray-400 text-sm">{{ lang.t('loading_users') }}</div>
            <div v-else-if="trashedUsers.length === 0" class="px-6 py-10 text-center text-gray-400 text-sm">{{ lang.t('no_deleted_accounts') }}</div>

            <ul v-else class="divide-y divide-gray-100">
                <li v-for="user in trashedUsers" :key="user.id" class="flex flex-col sm:flex-row sm:items-center gap-3 px-6 py-4">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold shrink-0">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 truncate">{{ user.name }}</div>
                            <div class="text-sm text-gray-500 truncate">{{ user.email }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap text-xs">
                        <span v-if="user.role === 'super_admin'" class="px-2.5 py-1 rounded-md font-semibold bg-amber-100 text-amber-700">{{ lang.t('super_admin') }}</span>
                        <span v-else-if="user.role === 'admin'" class="px-2.5 py-1 rounded-md font-semibold bg-primary-100 text-primary-700">{{ lang.t('admin') }}</span>
                        <span v-else class="px-2.5 py-1 rounded-md font-semibold bg-gray-100 text-gray-600">{{ lang.t('member') }}</span>
                        <span class="text-gray-400">{{ lang.t('deleted_on') }}: {{ new Date(user.deleted_at).toLocaleDateString() }}</span>
                    </div>

                    <div class="flex items-center gap-2 sm:ml-auto">
                        <button @click="restoreUser(user)" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            {{ lang.t('restore') }}
                        </button>
                        <button @click="forceDeleteUser(user)" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            {{ lang.t('delete_permanently') }}
                        </button>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Modal (Create / Edit) -->
        <Transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>

                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">{{ editMode ? lang.t('edit_user') : lang.t('add_new_user') }}</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveUser" class="p-6 overflow-y-auto space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ lang.t('full_name') }}</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none" placeholder="e.g. John Doe">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ lang.t('email_address') }}</label>
                            <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none" placeholder="e.g. john@example.com">
                        </div>

                        <div v-if="auth.isSuperAdmin">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ lang.t('system_role') }}</label>
                            <select v-model="form.role" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none">
                                <option value="super_admin">{{ lang.t('super_admin_full') }}</option>
                                <option value="admin">{{ lang.t('admin_manage') }}</option>
                                <option value="member">{{ lang.t('member_frontend') }}</option>
                            </select>
                        </div>

                        <div v-if="auth.isSuperAdmin && form.role === 'member'">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ lang.t('assign_to_admin') }}</label>
                            <select v-model="form.parent_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none">
                                <option :value="null">{{ lang.t('none_direct_member') }}</option>
                                <option v-for="admin in adminsOnly" :key="admin.id" :value="admin.id">
                                    {{ admin.name }} ({{ admin.email }})
                                </option>
                            </select>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 mt-4">
                            <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">{{ lang.t('cancel') }}</button>
                            <button type="submit" :disabled="saving" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-sm transition-all flex items-center gap-2 disabled:opacity-70">
                                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ editMode ? lang.t('save_changes') : lang.t('create_user') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Shared confirmation dialog (delete / restore / permanent delete) -->
        <ConfirmModal
            :show="confirmDialog.show"
            :title="confirmDialog.title"
            :message="confirmDialog.message"
            :confirm-label="confirmDialog.confirmLabel"
            :loading-label="confirmDialog.loadingLabel"
            :variant="confirmDialog.variant"
            :loading="confirmDialog.loading"
            @confirm="onConfirmDialog"
            @cancel="closeConfirmDialog"
        />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import { useLanguageStore } from '@/stores/language';
import { useViewMode } from '@/composables/useViewMode';
import ViewToggle from '@/components/ViewToggle.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const auth = useAuthStore();
const lang = useLanguageStore();
const { viewMode } = useViewMode('users');
const users = ref([]);
const loading = ref(true);
const error = ref('');
const success = ref('');

const showModal = ref(false);
const editMode = ref(false);
const saving = ref(false);
const currentUserId = ref(null);

const trashedUsers = ref([]);
const trashedLoading = ref(false);

// Single shared confirmation dialog. `action` holds the async callback to run
// when the user confirms; it should return normally on success or throw to
// keep the dialog open.
const confirmDialog = reactive({
    show: false,
    title: '',
    message: '',
    confirmLabel: '',
    loadingLabel: '',
    variant: 'danger',
    loading: false,
    action: null,
});

function openConfirmDialog(opts) {
    error.value = '';
    success.value = '';
    Object.assign(confirmDialog, { show: true, loading: false, loadingLabel: '', ...opts });
}

function closeConfirmDialog() {
    if (confirmDialog.loading) return;
    confirmDialog.show = false;
    confirmDialog.action = null;
}

async function onConfirmDialog() {
    if (!confirmDialog.action) return;
    confirmDialog.loading = true;
    try {
        await confirmDialog.action();
        confirmDialog.show = false;
        confirmDialog.action = null;
    } catch {
        // Worker already surfaced the error banner; just close the dialog.
        confirmDialog.show = false;
        confirmDialog.action = null;
    } finally {
        confirmDialog.loading = false;
    }
}

const form = ref({
    name: '',
    email: '',
    role: 'member',
    parent_id: null,
});

const adminsOnly = computed(() => {
    return users.value.filter(u => u.role === 'admin');
});

onMounted(() => {
    fetchUsers();
    if (auth.isSuperAdmin) fetchTrashed();
});

const fetchTrashed = async () => {
    if (!auth.isSuperAdmin) return;
    trashedLoading.value = true;
    try {
        const response = await axios.get('/api/users/trashed');
        trashedUsers.value = response.data;
    } catch (err) {
        console.error(err);
    } finally {
        trashedLoading.value = false;
    }
};

const restoreUser = (user) => {
    openConfirmDialog({
        title: lang.t('restore'),
        message: lang.t('confirm_restore_user', { name: user.name }),
        confirmLabel: lang.t('restore'),
        variant: 'success',
        action: async () => {
            try {
                await axios.post(`/api/users/${user.id}/restore`);
                success.value = lang.t('account_restored');
                await Promise.all([fetchUsers(), fetchTrashed()]);
            } catch (err) {
                error.value = err.response?.data?.message || lang.t('failed_restore_user');
                throw err;
            }
        },
    });
};

const forceDeleteUser = (user) => {
    openConfirmDialog({
        title: lang.t('delete_permanently'),
        message: lang.t('confirm_force_delete_user', { name: user.name }),
        confirmLabel: lang.t('delete_permanently'),
        loadingLabel: lang.t('deleting'),
        variant: 'danger',
        action: async () => {
            try {
                await axios.delete(`/api/users/${user.id}/force`);
                success.value = lang.t('account_purged');
                await fetchTrashed();
            } catch (err) {
                error.value = err.response?.data?.message || lang.t('failed_purge_user');
                throw err;
            }
        },
    });
};

const fetchUsers = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await axios.get('/api/users');
        users.value = response.data;
    } catch (err) {
        error.value = lang.t('failed_load_users');
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    editMode.value = false;
    currentUserId.value = null;
    form.value = { name: '', email: '', role: 'member', parent_id: null };
    showModal.value = true;
    success.value = '';
    error.value = '';
};

const openEditModal = (user) => {
    editMode.value = true;
    currentUserId.value = user.id;
    form.value = {
        name: user.name,
        email: user.email,
        role: user.role,
        parent_id: user.parent_id
    };
    showModal.value = true;
    success.value = '';
    error.value = '';
};

const closeModal = () => {
    showModal.value = false;
};

const saveUser = async () => {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        if (editMode.value) {
            await axios.put(`/api/users/${currentUserId.value}`, form.value);
            success.value = lang.t('user_updated');
        } else {
            await axios.post('/api/users', form.value);
            success.value = lang.t('user_created');
        }
        await fetchUsers();
        closeModal();
    } catch (err) {
        error.value = err.response?.data?.message || lang.t('error_saving');
    } finally {
        saving.value = false;
    }
};

const confirmDelete = (user) => {
    openConfirmDialog({
        title: lang.t('delete_account'),
        message: lang.t('confirm_delete_user', { name: user.name }),
        confirmLabel: lang.t('delete'),
        loadingLabel: lang.t('deleting'),
        variant: 'danger',
        action: async () => {
            try {
                await axios.delete(`/api/users/${user.id}`);
                success.value = lang.t('user_deleted');
                await fetchUsers();
                if (auth.isSuperAdmin) await fetchTrashed();
            } catch (err) {
                error.value = err.response?.data?.message || lang.t('failed_delete_user');
                throw err;
            }
        },
    });
};
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
