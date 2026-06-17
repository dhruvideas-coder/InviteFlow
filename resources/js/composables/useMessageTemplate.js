import { ref } from 'vue';
import axios from 'axios';

/**
 * Admin-configured WhatsApp message templates (EN/GU).
 *
 * Templates are owned per tenant (admin) on the backend; members inherit their
 * parent admin's templates. The set is fetched once and cached for the session.
 *
 * Supported tokens (all optional except {link}, enforced server-side):
 *   {name}     recipient name
 *   {village}  recipient village
 *   {document} document title
 *   {link}     personalized invitation URL
 *
 * The language of a sent message follows the DOCUMENT's language, so the
 * recipient's name/village are inserted in the same script as the message.
 */
const templates = ref(null); // { en: '...', gu: '...' }
const canEdit = ref(false);
let loadPromise = null;

function ensureLoaded() {
    if (templates.value) return Promise.resolve(templates.value);
    if (!loadPromise) {
        loadPromise = axios.get('/api/message-templates')
            .then(({ data }) => {
                templates.value = data.templates;
                canEdit.value = data.can_edit;
                return templates.value;
            })
            .finally(() => { loadPromise = null; });
    }
    return loadPromise;
}

/** Replace tokens in `body` using the given recipient/document/link. */
export function renderTemplate(body, { recipient = {}, document = {}, link = '' } = {}) {
    const gu = document.language === 'gu';
    const name = (gu ? recipient.name_gu : recipient.name_en) || recipient.name_en || recipient.name_gu || '';
    const village = (gu ? recipient.village_gu : recipient.village_en) || recipient.village_en || recipient.village_gu || '';
    return (body || '')
        .replaceAll('{name}', name)
        .replaceAll('{village}', village)
        .replaceAll('{document}', document.name || '')
        .replaceAll('{link}', link);
}

export function useMessageTemplate() {
    const loadTemplates = () => ensureLoaded();

    async function saveTemplates(next) {
        const { data } = await axios.put('/api/message-templates', { templates: next });
        templates.value = data.templates;
        canEdit.value = data.can_edit;
        return data;
    }

    /** Resolve the rendered WhatsApp message for a recipient + document. */
    async function buildMessage(recipient, document, link) {
        const set = await ensureLoaded();
        const lang = document?.language === 'gu' ? 'gu' : 'en';
        return renderTemplate(set[lang], { recipient, document, link });
    }

    return { templates, canEdit, loadTemplates, saveTemplates, buildMessage };
}
