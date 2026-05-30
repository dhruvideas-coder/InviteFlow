<template>
    <div class="space-y-6 max-w-2xl">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ lang.t('settings') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ lang.t('settings_subtitle') }}</p>
        </div>

        <!-- Profile -->
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-900">{{ lang.t('profile') }}</h3>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xl font-bold">
                    {{ auth.avatarInitials }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ auth.user?.name }}</p>
                    <p class="text-sm text-gray-500">{{ auth.user?.email }}</p>
                    <button class="btn btn-ghost btn-sm text-xs mt-1 -ml-2 text-primary-600">{{ lang.t('change_photo') }}</button>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="label">{{ lang.t('full_name') }}</label>
                    <input v-model="settings.name" type="text" class="input" />
                </div>
                <div class="form-group">
                    <label class="label">{{ lang.t('email') }}</label>
                    <input v-model="settings.email" type="email" class="input" disabled />
                </div>
                <div class="form-group">
                    <label class="label">{{ lang.t('org_name') }}</label>
                    <input v-model="settings.org" type="text" class="input" />
                </div>
                <div class="form-group">
                    <label class="label">{{ lang.t('language') }}</label>
                    <select
                        :value="lang.currentLocale"
                        @change="lang.setLocale($event.target.value)"
                        class="select"
                    >
                        <option v-for="(l, code) in lang.locales" :key="code" :value="code">
                            {{ l.name }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button class="btn btn-primary">{{ lang.t('save_changes') }}</button>
            </div>
        </div>

        <!-- WhatsApp settings -->
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-900">{{ lang.t('whatsapp_template_title') }}</h3>
            <div class="form-group">
                <label class="label">{{ lang.t('default_message') }}</label>
                <textarea v-model="settings.waTemplate" rows="4" class="input resize-none" placeholder="Hello {name}, you are invited..."></textarea>
            </div>
            <p class="text-xs text-gray-500">{{ lang.t('available_variables') }}: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">{name}</code> <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">{village}</code> <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">{link}</code></p>
            <div class="flex justify-end">
                <button class="btn btn-primary">{{ lang.t('update_template') }}</button>
            </div>
        </div>

        <!-- Notifications -->
        <div v-if="!auth.isMember" class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-900">{{ lang.t('notifications') }}</h3>
            <div class="space-y-3">
                <label v-for="notif in notifications" :key="notif.key" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ lang.t(notif.labelKey) }}</p>
                        <p class="text-xs text-gray-500">{{ lang.t(notif.descKey) }}</p>
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
            <h3 class="font-semibold text-red-600 mb-3">{{ lang.t('danger_zone') }}</h3>
            <div class="flex items-center justify-between p-3 rounded-xl bg-red-50">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ lang.t('delete_account') }}</p>
                    <p class="text-xs text-gray-500">{{ lang.t('delete_account_desc') }}</p>
                </div>
                <button class="btn btn-danger btn-sm">{{ lang.t('delete_account') }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useLanguageStore } from '@/stores/language';

const auth = useAuthStore();
const lang = useLanguageStore();

const settings = ref({
    name: auth.user?.name || '',
    email: auth.user?.email || '',
    org: 'My Organization',
    waTemplate: 'Hello {name},\n\nYou are invited to our event.\nView your personalized invitation: {link}\n\nBest regards,\nThe Team',
});

const notifications = ref([
    { key: 'link_click', labelKey: 'link_opened', descKey: 'link_opened_desc', enabled: true },
    { key: 'expiry', labelKey: 'expiry_reminders', descKey: 'expiry_reminders_desc', enabled: true },
    { key: 'quota', labelKey: 'quota_alerts', descKey: 'quota_alerts_desc', enabled: false },
    { key: 'weekly', labelKey: 'weekly_report', descKey: 'weekly_report_desc', enabled: true },
]);
</script>
