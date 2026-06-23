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

        <!-- Host / Organizer (admin / super admin only) -->
        <div v-if="!auth.isMember" class="card p-5 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-900">{{ lang.t('host_profile_title') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ lang.t('host_profile_subtitle') }}</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="relative w-20 h-20 rounded-2xl overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                    <img v-if="hostPreview" :src="hostPreview" alt="" class="w-full h-full object-cover" />
                    <svg v-else class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0v.75H4.5v-.75z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <input ref="hostFileEl" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onHostFile" />
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="hostFileEl?.click()" class="btn btn-ghost btn-sm bg-gray-50 hover:bg-primary-50 hover:text-primary-600 text-xs">
                            {{ lang.t('upload_host_image') }}
                        </button>
                        <button v-if="hostPreview" type="button" @click="removeHostImage" class="btn btn-ghost btn-sm text-xs text-red-500 hover:bg-red-50">
                            {{ lang.t('remove') }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-400">{{ lang.t('host_image_hint') }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center justify-between gap-2">
                        <label class="label">{{ lang.t('host_name_en_label') }}</label>
                        <button type="button" @click="convertHostName" :disabled="hostConverting || !hostNameEn.trim()" class="btn btn-secondary btn-sm shrink-0 text-xs flex items-center gap-1 disabled:opacity-50">
                            <svg v-if="hostConverting" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            {{ hostConverting ? lang.t('converting') : lang.t('auto_convert') }}
                        </button>
                    </div>
                    <input v-model="hostNameEn" type="text" class="input" :placeholder="lang.t('host_name_placeholder')" />
                </div>
                <div class="form-group">
                    <label class="label">{{ lang.t('host_name_gu_label') }}</label>
                    <input v-model="hostNameGu" type="text" class="input" placeholder="પટેલ પરિવાર" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <span v-if="hostError" class="text-xs text-red-600">{{ hostError }}</span>
                <span v-else-if="hostSaved" class="text-xs text-green-600">{{ lang.t('saved') }}</span>
                <button @click="saveHostProfile" :disabled="hostSaving" class="btn btn-primary disabled:opacity-50">
                    {{ hostSaving ? lang.t('converting') : lang.t('save_changes') }}
                </button>
            </div>
        </div>

        <!-- WhatsApp daily sending limit -->
        <div class="card p-5 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600 shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ lang.t('whatsapp_limit_title') }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ lang.t('whatsapp_limit_desc', { limit: quota.limit }) }}</p>
                    </div>
                </div>
                <span
                    class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl border"
                    :class="quota.reached ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-100'"
                >
                    <span class="w-1.5 h-1.5 rounded-full" :class="quota.reached ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                    {{ quota.reached ? lang.t('whatsapp_limit_paused') : lang.t('whatsapp_limit_active') }}
                </span>
            </div>

            <!-- Usage bar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-gray-700">{{ lang.t('whatsapp_limit_used', { used: quota.used, limit: quota.limit }) }}</span>
                    <span class="text-gray-500">{{ lang.t('whatsapp_limit_remaining', { remaining: quota.remaining }) }}</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="quota.reached ? 'bg-amber-500' : 'bg-green-500'"
                        :style="{ width: Math.min(100, Math.round((quota.used / quota.limit) * 100)) + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Reached message -->
            <div v-if="quota.reached" class="flex items-start gap-3 p-3 rounded-2xl bg-amber-50 border border-amber-200">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-amber-900">{{ lang.t('whatsapp_limit_reached_title') }}</p>
                    <p class="text-xs text-amber-700 mt-0.5">{{ lang.t('whatsapp_limit_reached_msg', { limit: quota.limit }) }}</p>
                    <p v-if="quota.resets_at" class="text-xs font-medium text-amber-800 mt-1">{{ lang.t('whatsapp_limit_resets', { countdown: countdownText(lang.t), time: formatResetTime(quota.resets_at) }) }}</p>
                </div>
            </div>
        </div>

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
                <button @click="showDeleteModal = true" class="btn btn-danger btn-sm">{{ lang.t('delete_account') }}</button>
            </div>
        </div>

        <!-- Delete account confirmation modal -->
        <Transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeDeleteModal"></div>

                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold text-gray-900">{{ lang.t('delete_account_confirm_title') }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ lang.t('delete_account_confirm_msg') }}</p>
                            </div>
                        </div>

                        <div v-if="deleteError" class="p-3 rounded-xl bg-red-50 text-red-600 text-sm border border-red-100">
                            {{ deleteError }}
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeDeleteModal" :disabled="deleting" class="btn btn-ghost btn-sm disabled:opacity-50">{{ lang.t('cancel') }}</button>
                            <button type="button" @click="deleteAccount" :disabled="deleting" class="btn btn-danger btn-sm flex items-center gap-2 disabled:opacity-70">
                                <svg v-if="deleting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ deleting ? lang.t('deleting') : lang.t('delete_account_confirm_btn') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, watch, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import { useLanguageStore } from '@/stores/language';
import { useMessageTemplate, renderTemplate } from '@/composables/useMessageTemplate';
import { useWhatsappQuota } from '@/composables/useWhatsappQuota';

const auth = useAuthStore();
const lang = useLanguageStore();
const { loadTemplates, saveTemplates } = useMessageTemplate();
const { quota, fetchQuota, countdownText } = useWhatsappQuota();

function formatResetTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

const settings = ref({
    name: auth.user?.name || '',
    email: auth.user?.email || '',
    org: 'My Organization',
});

// ── Host / Organizer profile ───────────────────────────────────────────
const hostFileEl = ref(null);
const hostNameEn = ref('');
const hostNameGu = ref('');
const hostConverting = ref(false);
let hostConvertTimer = null;
let suppressHostConvert = false;
const hostImageUrl = ref(null); // currently saved image (server URL)
const hostFile = ref(null);     // newly picked File, not yet saved
const hostRemove = ref(false);  // user cleared the existing image
const hostLocalPreview = ref(''); // object URL for a freshly picked file
const hostSaving = ref(false);
const hostSaved = ref(false);
const hostError = ref('');

const hostPreview = computed(() =>
    hostLocalPreview.value || (hostRemove.value ? '' : hostImageUrl.value) || ''
);

function onHostFile(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    hostFile.value = file;
    hostRemove.value = false;
    if (hostLocalPreview.value) URL.revokeObjectURL(hostLocalPreview.value);
    hostLocalPreview.value = URL.createObjectURL(file);
}

function removeHostImage() {
    hostFile.value = null;
    if (hostLocalPreview.value) URL.revokeObjectURL(hostLocalPreview.value);
    hostLocalPreview.value = '';
    hostRemove.value = true;
    if (hostFileEl.value) hostFileEl.value.value = '';
}

/** Transliterate Latin text to the Gujarati script (same source as recipients). */
async function translateToGujarati(text) {
    if (!text) return '';
    try {
        const res = await fetch(
            `https://inputtools.google.com/request?text=${encodeURIComponent(text)}&itc=gu-t-i0-und&num=1&cp=0&cs=1&ie=utf-8&oe=utf-8`
        );
        const data = await res.json();
        if (data[0] === 'SUCCESS' && data[1]?.[0]?.[1]?.[0]) return data[1][0][1][0];
    } catch {}
    return '';
}

async function convertHostName() {
    const text = hostNameEn.value.trim();
    if (!text) return;
    hostConverting.value = true;
    try {
        const result = await translateToGujarati(text);
        if (result) hostNameGu.value = result;
    } finally {
        hostConverting.value = false;
    }
}

// Auto-fill the Gujarati name as the admin types English (debounced), mirroring
// the recipient form. Manual edits to the Gujarati field are preserved.
watch(hostNameEn, (val) => {
    clearTimeout(hostConvertTimer);
    if (suppressHostConvert) return;
    if (!val || !val.trim()) { hostNameGu.value = ''; return; }
    hostConvertTimer = setTimeout(convertHostName, 700);
});

async function loadHostProfile() {
    const { data } = await axios.get('/api/host-profile');
    // Don't let loading saved values retrigger the auto-converter.
    suppressHostConvert = true;
    hostNameEn.value = data.host_name_en || '';
    hostNameGu.value = data.host_name_gu || '';
    hostImageUrl.value = data.image_url || null;
    nextTick(() => { suppressHostConvert = false; });
}

async function saveHostProfile() {
    hostError.value = '';
    hostSaving.value = true;
    try {
        const fd = new FormData();
        fd.append('host_name_en', hostNameEn.value || '');
        fd.append('host_name_gu', hostNameGu.value || '');
        if (hostFile.value) fd.append('image', hostFile.value);
        if (hostRemove.value) fd.append('remove', '1');
        const { data } = await axios.post('/api/host-profile', fd);
        hostImageUrl.value = data.image_url || null;
        if (hostLocalPreview.value) URL.revokeObjectURL(hostLocalPreview.value);
        hostLocalPreview.value = '';
        hostFile.value = null;
        hostRemove.value = false;
        hostSaved.value = true;
        setTimeout(() => { hostSaved.value = false; }, 2500);
    } catch (e) {
        hostError.value = e.response?.data?.message || lang.t('error_saving');
    } finally {
        hostSaving.value = false;
    }
}

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
    fetchQuota(true);
    if (auth.isMember) return;
    await loadHostProfile();
    const t = await loadTemplates();
    draft.value = { en: t.en, gu: t.gu };
});

// ── Delete account ─────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deleting = ref(false);
const deleteError = ref('');

function closeDeleteModal() {
    if (deleting.value) return;
    showDeleteModal.value = false;
    deleteError.value = '';
}

async function deleteAccount() {
    deleting.value = true;
    deleteError.value = '';
    try {
        await axios.delete('/api/account', {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        });
        // Account (and members) soft-deleted and session invalidated server-side.
        auth.user = null;
        window.location.href = '/login';
    } catch (e) {
        deleteError.value = e.response?.data?.message || lang.t('delete_account_failed');
        deleting.value = false;
    }
}

const notifications = ref([
    { key: 'link_click', labelKey: 'link_opened', descKey: 'link_opened_desc', enabled: true },
    { key: 'expiry', labelKey: 'expiry_reminders', descKey: 'expiry_reminders_desc', enabled: true },
    { key: 'quota', labelKey: 'quota_alerts', descKey: 'quota_alerts_desc', enabled: false },
    { key: 'weekly', labelKey: 'weekly_report', descKey: 'weekly_report_desc', enabled: true },
]);
</script>
