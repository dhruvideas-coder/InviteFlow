<template>
    <div class="space-y-6 max-w-2xl">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Settings</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage your account and preferences</p>
        </div>

        <!-- Profile -->
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-900">Profile</h3>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xl font-bold">
                    AD
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Admin User</p>
                    <p class="text-sm text-gray-500">admin@example.com</p>
                    <button class="btn btn-ghost btn-sm text-xs mt-1 -ml-2 text-primary-600">Change Photo</button>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="label">Full Name</label>
                    <input v-model="settings.name" type="text" class="input" />
                </div>
                <div class="form-group">
                    <label class="label">Email</label>
                    <input v-model="settings.email" type="email" class="input" disabled />
                </div>
                <div class="form-group">
                    <label class="label">Organization Name</label>
                    <input v-model="settings.org" type="text" class="input" />
                </div>
                <div class="form-group">
                    <label class="label">Language</label>
                    <select v-model="settings.lang" class="select">
                        <option value="en">English</option>
                        <option value="gu">Gujarati</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button class="btn btn-primary">Save Changes</button>
            </div>
        </div>

        <!-- WhatsApp settings -->
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-900">WhatsApp Message Template</h3>
            <div class="form-group">
                <label class="label">Default Message</label>
                <textarea v-model="settings.waTemplate" rows="4" class="input resize-none" placeholder="Hello {name}, you are invited..."></textarea>
            </div>
            <p class="text-xs text-gray-500">Available variables: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">{name}</code> <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">{village}</code> <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">{link}</code></p>
            <div class="flex justify-end">
                <button class="btn btn-primary">Update Template</button>
            </div>
        </div>

        <!-- Notifications -->
        <div v-if="!auth.isMember" class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-900">Notifications</h3>
            <div class="space-y-3">
                <label v-for="notif in notifications" :key="notif.key" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ notif.label }}</p>
                        <p class="text-xs text-gray-500">{{ notif.desc }}</p>
                    </div>
                    <button
                        @click="notif.enabled = !notif.enabled"
                        :class="[
                            'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none',
                            notif.enabled ? 'bg-primary-600' : 'bg-gray-200',
                        ]"
                    >
                        <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition duration-200', notif.enabled ? 'translate-x-4' : 'translate-x-0']"></span>
                    </button>
                </label>
            </div>
        </div>

        <!-- Danger zone -->
        <div v-if="!auth.isMember" class="card p-5 border-red-100">
            <h3 class="font-semibold text-red-600 mb-3">Danger Zone</h3>
            <div class="flex items-center justify-between p-3 rounded-xl bg-red-50">
                <div>
                    <p class="text-sm font-medium text-gray-900">Delete Account</p>
                    <p class="text-xs text-gray-500">Permanently delete your account and all data</p>
                </div>
                <button class="btn btn-danger btn-sm">Delete Account</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const settings = ref({
    name: 'Admin User',
    email: 'admin@example.com',
    org: 'My Organization',
    lang: 'en',
    waTemplate: 'Hello {name},\n\nYou are invited to our event.\nView your personalized invitation: {link}\n\nBest regards,\nThe Team',
});

const notifications = ref([
    { key: 'link_click', label: 'Link Opened', desc: 'Notify when a recipient opens their invitation link', enabled: true },
    { key: 'expiry', label: 'Expiry Reminders', desc: 'Get reminded 3 days before links expire', enabled: true },
    { key: 'quota', label: 'Quota Alerts', desc: 'Alert when 80% of subscription quota is used', enabled: false },
    { key: 'weekly', label: 'Weekly Report', desc: 'Receive a weekly analytics summary via email', enabled: true },
]);
</script>
