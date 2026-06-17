import { ref, watch } from 'vue';

/**
 * Shared, persisted table/card view preference.
 *
 * Each module passes its own `key` so its choice is remembered independently
 * (e.g. "documents", "recipients"). The preference is stored in localStorage
 * and shared across components that use the same key within a session.
 *
 * NOTE: On mobile the UI always renders the card view regardless of this
 * value — that is handled in the templates with responsive classes, so the
 * toggle (and this value) only affects the sm+ breakpoint.
 */
const stores = {};

export function useViewMode(key = 'global', defaultMode = 'table') {
    if (!stores[key]) {
        const saved = localStorage.getItem(`viewMode:${key}`);
        const viewMode = ref(saved === 'card' || saved === 'table' ? saved : defaultMode);
        watch(viewMode, (val) => localStorage.setItem(`viewMode:${key}`, val));
        stores[key] = { viewMode };
    }

    const { viewMode } = stores[key];

    const setViewMode = (mode) => {
        if (mode === 'card' || mode === 'table') viewMode.value = mode;
    };

    const toggle = () => {
        viewMode.value = viewMode.value === 'table' ? 'card' : 'table';
    };

    return { viewMode, setViewMode, toggle };
}
