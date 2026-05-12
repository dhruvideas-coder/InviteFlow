<template>
    <div class="space-y-6 max-w-5xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-bold text-gray-900">User Management</h2>
                <p class="text-sm text-gray-500 mt-1">Manage system administrators and frontend members.</p>
            </div>
            <button 
                v-if="auth.isSuperAdmin || auth.isAdmin"
                @click="openCreateModal"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add User
            </button>
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

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th v-if="auth.isSuperAdmin" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin/Parent</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined Date</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">Loading users...</td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">No users found.</td>
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
                                <span v-if="user.role === 'super_admin'" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">Super Admin</span>
                                <span v-else-if="user.role === 'admin'" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-primary-100 text-primary-700">Admin</span>
                                <span v-else class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600">Member</span>
                            </td>
                            <td v-if="auth.isSuperAdmin" class="px-6 py-4 text-sm text-gray-600">
                                <span v-if="user.parent" class="text-primary-600 font-medium">{{ user.parent.name }}</span>
                                <span v-else class="text-gray-400 italic">None</span>
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
                                    <span class="text-xs text-gray-400 italic">No access</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal (Create / Edit) -->
        <Transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">{{ editMode ? 'Edit User' : 'Add New User' }}</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="saveUser" class="p-6 overflow-y-auto space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none" placeholder="e.g. John Doe">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                            <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none" placeholder="e.g. john@example.com">
                        </div>
                        
                        <div v-if="auth.isSuperAdmin">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">System Role</label>
                            <select v-model="form.role" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none">
                                <option value="super_admin">Super Admin (Full Access)</option>
                                <option value="admin">Admin (Can manage their members)</option>
                                <option value="member">Member (Frontend Access)</option>
                            </select>
                        </div>

                        <div v-if="auth.isSuperAdmin && form.role === 'member'">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Assign to Admin (Optional)</label>
                            <select v-model="form.parent_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none">
                                <option :value="null">None (Direct Member)</option>
                                <option v-for="admin in adminsOnly" :key="admin.id" :value="admin.id">
                                    {{ admin.name }} ({{ admin.email }})
                                </option>
                            </select>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 mt-4">
                            <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                            <button type="submit" :disabled="saving" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-sm transition-all flex items-center gap-2 disabled:opacity-70">
                                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ editMode ? 'Save Changes' : 'Create User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const users = ref([]);
const loading = ref(true);
const error = ref('');
const success = ref('');

// Modal state
const showModal = ref(false);
const editMode = ref(false);
const saving = ref(false);
const currentUserId = ref(null);
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
});

const fetchUsers = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await axios.get('/api/users');
        users.value = response.data;
    } catch (err) {
        error.value = 'Failed to load users. Please try again.';
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
            success.value = 'User updated successfully.';
        } else {
            await axios.post('/api/users', form.value);
            success.value = 'User created successfully.';
        }
        await fetchUsers();
        closeModal();
    } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while saving.';
    } finally {
        saving.value = false;
    }
};

const confirmDelete = async (user) => {
    if (confirm(`Are you sure you want to delete ${user.name}? This cannot be undone.`)) {
        try {
            await axios.delete(`/api/users/${user.id}`);
            success.value = 'User deleted successfully.';
            await fetchUsers();
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete user.';
        }
    }
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
