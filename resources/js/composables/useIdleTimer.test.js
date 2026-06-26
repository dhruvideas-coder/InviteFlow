import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';
import { useIdleTimer } from './useIdleTimer';

// Heartbeat hits axios.get('/api/user'); stub it so no real request is made.
vi.mock('axios', () => ({
    default: { get: vi.fn(() => Promise.resolve({ data: {} })) },
}));

const FIVE_MIN = 5 * 60 * 1000;

// Mount a tiny host component that runs the composable, so onMounted/onUnmounted fire.
function mountTimer(onTimeout) {
    let api;
    const Host = defineComponent({
        setup() {
            api = useIdleTimer({ timeout: FIVE_MIN, onTimeout });
            return () => null;
        },
    });
    const wrapper = mount(Host);
    return { wrapper, api: () => api };
}

describe('useIdleTimer (5 minute idle auto-logout)', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        localStorage.clear();
    });
    afterEach(() => {
        vi.restoreAllMocks();
        vi.useRealTimers();
    });

    it('does NOT log out before 5 minutes of inactivity', () => {
        const onTimeout = vi.fn();
        const { wrapper, api } = mountTimer(onTimeout);

        vi.advanceTimersByTime(FIVE_MIN - 1000); // 4:59 idle

        expect(onTimeout).not.toHaveBeenCalled();
        expect(api().showWarning.value).toBe(true); // warning is up in the last 30s
        wrapper.unmount();
    });

    it('shows the warning 30s before, and logs out exactly at 5 minutes', () => {
        const onTimeout = vi.fn();
        const { wrapper, api } = mountTimer(onTimeout);

        vi.advanceTimersByTime(FIVE_MIN - 31 * 1000); // 4:29 — before warning window
        expect(api().showWarning.value).toBe(false);
        expect(onTimeout).not.toHaveBeenCalled();

        vi.advanceTimersByTime(2000); // 4:31 — inside 30s warning window
        expect(api().showWarning.value).toBe(true);
        expect(onTimeout).not.toHaveBeenCalled();

        vi.advanceTimersByTime(FIVE_MIN); // well past 5:00
        expect(onTimeout).toHaveBeenCalledTimes(1);
        wrapper.unmount();
    });

    it('resets the timer on user activity (no logout while active)', () => {
        const onTimeout = vi.fn();
        const { wrapper, api } = mountTimer(onTimeout);

        // Stay idle almost the whole window, then move the mouse.
        vi.advanceTimersByTime(FIVE_MIN - 5000); // 4:55
        window.dispatchEvent(new Event('mousemove'));

        // Another ~4:55 passes since that activity — still under 5 min from reset.
        vi.advanceTimersByTime(FIVE_MIN - 5000);
        expect(onTimeout).not.toHaveBeenCalled();

        // Now go fully idle past the window from the last activity.
        vi.advanceTimersByTime(6000);
        expect(onTimeout).toHaveBeenCalledTimes(1);
        wrapper.unmount();
    });

    it('logs out only once even if time keeps advancing', () => {
        const onTimeout = vi.fn();
        const { wrapper } = mountTimer(onTimeout);

        vi.advanceTimersByTime(FIVE_MIN * 3);
        expect(onTimeout).toHaveBeenCalledTimes(1);
        wrapper.unmount();
    });
});
