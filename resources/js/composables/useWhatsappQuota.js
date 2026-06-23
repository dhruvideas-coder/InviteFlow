import { ref, computed, watch } from 'vue';
import axios from 'axios';

/**
 * Per-user WhatsApp daily sending quota (rolling 24-hour window).
 *
 * Each user may send a fixed number of WhatsApp invitations per day
 * (enforced server-side in InvitationLinkController). The snapshot is fetched
 * once and refreshed after every send — the send endpoint returns an updated
 * `whatsapp_quota` payload, and a 429 response carries the same shape.
 *
 * Shape: { limit, used, remaining, reached, resets_at }
 */
const quota = ref({ limit: 50, used: 0, remaining: 50, reached: false, resets_at: null });
const loaded = ref(false);
let loadPromise = null;

/** Whether sending is currently blocked because the daily cap is reached. */
const blocked = computed(() => quota.value.reached);

/** Apply a quota snapshot returned by the API (send response or 429 body). */
function setQuota(q) {
    if (q && typeof q === 'object') {
        quota.value = q;
        loaded.value = true;
    }
}

/** Fetch the current quota; cached for the session unless `force` is set. */
function fetchQuota(force = false) {
    if (loaded.value && !force) return Promise.resolve(quota.value);
    if (!loadPromise) {
        loadPromise = axios.get('/api/whatsapp-quota')
            .then(({ data }) => { setQuota(data); return data; })
            .finally(() => { loadPromise = null; });
    }
    return loadPromise;
}

// ── Live countdown to when the next slot frees up ──────────────────────
const nowTs = ref(Date.now());
let timer = null;
function startTicking() {
    if (timer) return;
    timer = setInterval(() => { nowTs.value = Date.now(); }, 1000);
}

/** Time left until `resets_at`, broken into parts and re-computed every tick. */
const resetsInParts = computed(() => {
    const iso = quota.value.resets_at;
    if (!iso) return { total: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
    const diff = new Date(iso).getTime() - nowTs.value;
    if (diff <= 0) return { total: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
    const totalSec = Math.floor(diff / 1000);
    return {
        total:   diff,
        hours:   Math.floor(totalSec / 3600),
        minutes: Math.floor((totalSec % 3600) / 60),
        seconds: totalSec % 60,
        expired: false,
    };
});

/**
 * Human countdown string ("3h 20m" / "20m" / "45s"), localized via the
 * passed translator (e.g. `lang.t`). Reactive — updates every second.
 */
function countdownText(t) {
    const p = resetsInParts.value;
    if (p.expired) return t('whatsapp_limit_resets_soon');
    if (p.hours > 0) return t('countdown_hm', { h: p.hours, m: p.minutes });
    if (p.minutes > 0) return t('countdown_m', { m: p.minutes });
    return t('countdown_s', { s: p.seconds });
}

// When the countdown runs out, a slot has freed up — refresh once so the UI
// flips back to "Active" and the send buttons reappear without a manual reload.
let refetching = false;
watch(() => resetsInParts.value.expired, (expired) => {
    if (expired && quota.value.reached && !refetching) {
        refetching = true;
        fetchQuota(true).finally(() => { refetching = false; });
    }
});

export function useWhatsappQuota() {
    startTicking();
    return { quota, blocked, loaded, fetchQuota, setQuota, resetsInParts, countdownText };
}
