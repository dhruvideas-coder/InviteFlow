<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ lang.t('settings') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ lang.t('settings_subtitle') }}</p>
        </div>

        <!-- Profile + Notifications — half/half on laptop -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        <!-- Profile -->
        <div class="card p-5 space-y-4" :class="{ 'lg:col-span-2': auth.isMember }">
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

        </div><!-- end Profile/Notifications grid -->

        <!-- WhatsApp message templates (admin / super admin only) -->
        <div v-if="!auth.isMember" class="card p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ lang.t('whatsapp_template_title') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ lang.t('whatsapp_template_subtitle') }}</p>
                </div>
                <!-- Language tabs -->
                <div class="inline-flex rounded-xl bg-gray-100 p-1 self-start">
                    <button
                        v-for="(l, code) in lang.locales"
                        :key="code"
                        @click="activeLang = code"
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                            activeLang === code ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500 hover:text-gray-700',
                        ]"
                    >{{ l.flag }} {{ l.name }}</button>
                </div>
            </div>

            <!-- Token insert buttons -->
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tk in tokens"
                    :key="tk.token"
                    type="button"
                    @click="insertToken(tk.token)"
                    class="btn btn-ghost btn-sm bg-gray-50 hover:bg-primary-50 hover:text-primary-600 text-xs font-mono"
                    :title="lang.t('insert_token')"
                >+ {{ lang.t(tk.labelKey) }}</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Editor -->
                <div class="form-group">
                    <label class="label">{{ lang.t('default_message') }} — {{ lang.locales[activeLang].name }}</label>
                    <textarea
                        ref="taEl"
                        v-model="draft[activeLang]"
                        rows="9"
                        class="input resize-none font-mono text-sm"
                        :placeholder="lang.t('template_placeholder')"
                    ></textarea>
                </div>
                <!-- Live preview -->
                <div class="form-group">
                    <label class="label">{{ lang.t('live_preview') }}</label>
                    <div class="rounded-xl bg-[#e5ddd5] p-3 h-[232px] overflow-y-auto">
                        <div class="bg-white rounded-lg rounded-tl-none shadow-sm p-3 max-w-[90%] text-sm text-gray-800 whitespace-pre-wrap break-words">{{ previewText }}</div>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500">{{ lang.t('link_token_hint') }}</p>

            <div class="flex items-center justify-end gap-3">
                <span v-if="saveError" class="text-xs text-red-600">{{ saveError }}</span>
                <span v-else-if="saved" class="text-xs text-green-600">{{ lang.t('saved') }}</span>
                <button @click="saveWaTemplates" :disabled="saving" class="btn btn-primary disabled:opacity-50">
                    {{ saving ? lang.t('converting') : lang.t('update_template') }}
                </button>
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
import { ref, computed, nextTick, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useLanguageStore } from '@/stores/language';
import { useMessageTemplate, renderTemplate } from '@/composables/useMessageTemplate';

const auth = useAuthStore();
const lang = useLanguageStore();
const { loadTemplates, saveTemplates } = useMessageTemplate();

const settings = ref({
    name: auth.user?.name || '',
    email: auth.user?.email || '',
    org: 'My Organization',
});

// ── WhatsApp message templates ─────────────────────────────────────────
const activeLang = ref('en');
const draft = ref({ en: '', gu: '' });
const taEl = ref(null);
const saving = ref(false);
const saved = ref(false);
const saveError = ref('');

const tokens = [
    { token: '{name}', labelKey: 'token_name' },
    { token: '{village}', labelKey: 'token_village' },
    { token: '{document}', labelKey: 'token_document' },
    { token: '{link}', labelKey: 'token_link' },
];

const sampleRecipient = {
    name_en: 'Ramesh Patel', name_gu: 'રમેશ પટેલ',
    village_en: 'Anand', village_gu: 'આણંદ',
};
const sampleDoc = computed(() => ({
    name: activeLang.value === 'gu' ? 'લગ્ન આમંત્રણ' : 'Wedding Invitation',
    language: activeLang.value,
}));
const previewText = computed(() => renderTemplate(draft.value[activeLang.value], {
    recipient: sampleRecipient,
    document: sampleDoc.value,
    link: `${window.location.origin}/doc/view/sample123`,
}));

function insertToken(token) {
    const el = taEl.value;
    const current = draft.value[activeLang.value] || '';
    if (!el) {
        draft.value[activeLang.value] = current + token;
        return;
    }
    const start = el.selectionStart ?? current.length;
    const end = el.selectionEnd ?? current.length;
    draft.value[activeLang.value] = current.slice(0, start) + token + current.slice(end);
    nextTick(() => {
        el.focus();
        const pos = start + token.length;
        el.setSelectionRange(pos, pos);
    });
}

async function saveWaTemplates() {
    saveError.value = '';
    if (!draft.value.en.includes('{link}') || !draft.value.gu.includes('{link}')) {
        saveError.value = lang.t('link_token_required');
        return;
    }
    saving.value = true;
    try {
        await saveTemplates({ en: draft.value.en, gu: draft.value.gu });
        saved.value = true;
        setTimeout(() => { saved.value = false; }, 2500);
    } catch (e) {
        saveError.value = e.response?.data?.message || lang.t('error_saving');
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    if (auth.isMember) return;
    const t = await loadTemplates();
    draft.value = { en: t.en, gu: t.gu };
});

const notifications = ref([
    { key: 'link_click', labelKey: 'link_opened', descKey: 'link_opened_desc', enabled: true },
    { key: 'expiry', labelKey: 'expiry_reminders', descKey: 'expiry_reminders_desc', enabled: true },
    { key: 'quota', labelKey: 'quota_alerts', descKey: 'quota_alerts_desc', enabled: false },
    { key: 'weekly', labelKey: 'weekly_report', descKey: 'weekly_report_desc', enabled: true },
]);
</script>
