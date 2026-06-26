import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

/**
 * Idle (inactivity) auto-logout timer.
 *
 * Tracks user activity (mouse, keyboard, touch, scroll). After `timeout` ms of
 * no activity it fires `onTimeout`. `warning` ms before that it flips
 * `showWarning` to true and starts counting down via `remaining`.
 *
 * The timeout is driven by the server session lifetime (the `session-lifetime`
 * meta tag, set from config/session.php). Change SESSION_LIFETIME in .env and
 * both the popup and the real session expiry move together.
 *
 * Activity is shared across browser tabs through localStorage, so working in one
 * tab keeps every tab alive, and logging out in one tab logs out the rest.
 * While the user is active we ping the server periodically so its (sliding)
 * session does not expire underneath an active user.
 */
const ACTIVITY_KEY = 'idle:lastActivity';
const LOGOUT_KEY = 'idle:loggedOut';
const ACTIVITY_EVENTS = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click', 'wheel'];

// Server session lifetime in minutes (from <meta name="session-lifetime">),
// falling back to 5 if the tag is missing.
function sessionLifetimeMs() {
    const meta = document.querySelector('meta[name="session-lifetime"]')?.content;
    const minutes = parseInt(meta || '', 10);
    return (Number.isFinite(minutes) && minutes > 0 ? minutes : 5) * 60 * 1000;
}

export function useIdleTimer({
    timeout = sessionLifetimeMs(),
    warning,
    onTimeout = () => {},
} = {}) {
    // Warn 30s before logout, but never more than 40% of the total window
    // (so a 1-minute lifetime still warns at ~24s rather than immediately).
    const warnMs = warning ?? Math.min(30 * 1000, Math.floor(timeout * 0.4));

    const showWarning = ref(false);
    const remaining = ref(timeout); // ms left before logout

    let pollId = null;
    let throttleUntil = 0;
    let lastPing = Date.now();
    let pinging = false;
    let finished = false;

    const now = () => Date.now();

    const getLastActivity = () => {
        const v = parseInt(localStorage.getItem(ACTIVITY_KEY) || '0', 10);
        return v || now();
    };

    // Refresh the server's sliding session so an active user is not logged out
    // server-side. Used by the heartbeat and by "Stay logged in".
    async function pingServer() {
        if (pinging) return;
        pinging = true;
        try {
            await axios.get('/api/user');
            lastPing = now();
        } catch {
            // ignore — the next real request / poll will surface auth failure
        } finally {
            pinging = false;
        }
    }

    // Record activity, throttled to at most once per second to avoid hammering
    // localStorage on every mousemove.
    function recordActivity(force = false) {
        if (finished) return;
        const t = now();
        if (!force && t < throttleUntil) return;
        throttleUntil = t + 1000;
        localStorage.setItem(ACTIVITY_KEY, String(t));
        if (showWarning.value) showWarning.value = false;
        // Heartbeat: keep the server session alive for an active user.
        if (t - lastPing > timeout / 2) pingServer();
    }

    function finish() {
        if (finished) return;
        finished = true;
        stop();
        onTimeout();
    }

    function tick() {
        const left = timeout - (now() - getLastActivity());
        remaining.value = Math.max(0, left);
        if (left <= 0) {
            // Tell other tabs to log out too, then log out here.
            localStorage.setItem(LOGOUT_KEY, String(now()));
            finish();
        } else if (left <= warnMs) {
            showWarning.value = true;
        } else if (showWarning.value) {
            showWarning.value = false;
        }
    }

    // "Stay logged in" — refresh the server session and reset the timer everywhere.
    function stayActive() {
        showWarning.value = false;
        recordActivity(true);
        lastPing = 0; // force the heartbeat
        pingServer();
    }

    const activityHandler = () => recordActivity();

    function onStorage(e) {
        if (e.key === LOGOUT_KEY && e.newValue) {
            finish(); // another tab hit the idle limit
        }
    }

    function start() {
        recordActivity(true);
        lastPing = now();
        ACTIVITY_EVENTS.forEach(ev =>
            window.addEventListener(ev, activityHandler, { passive: true })
        );
        window.addEventListener('storage', onStorage);
        pollId = setInterval(tick, 1000);
    }

    function stop() {
        if (pollId) clearInterval(pollId);
        pollId = null;
        ACTIVITY_EVENTS.forEach(ev => window.removeEventListener(ev, activityHandler));
        window.removeEventListener('storage', onStorage);
    }

    onMounted(start);
    onBeforeUnmount(stop);

    return { showWarning, remaining, stayActive };
}
