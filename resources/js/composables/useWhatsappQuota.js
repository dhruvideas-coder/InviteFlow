import { ref, computed } from 'vue';
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

export function useWhatsappQuota() {
    return { quota, blocked, loaded, fetchQuota, setQuota };
}
