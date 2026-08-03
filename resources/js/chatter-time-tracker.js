window.chatterTimeTracker = ({
    stateUrl,
    initialWorkedSeconds = 0,
    initialTimerRunning = false,
    initialOnBreak = false,
    hasOpenShift = false,
}) => ({
    baseWorkedSeconds: Math.max(0, Number(initialWorkedSeconds) || 0),
    timerRunning: Boolean(initialTimerRunning),
    onBreak: Boolean(initialOnBreak),
    hasOpenShift: Boolean(hasOpenShift),
    clientStartedAt: performance.now(),
    displaySeconds: Math.max(0, Number(initialWorkedSeconds) || 0),
    timer: null,
    stateSyncController: null,
    stateSyncRetryTimer: null,
    stateSyncFailures: 0,
    nextSyncAt: 0,
    visibilityHandler: null,
    pageShowHandler: null,
    submitHandler: null,

    init() {
        this.startTimer();

        this.visibilityHandler = () => {
            if (document.visibilityState === 'visible') {
                // Keep the last trusted duration moving while the foreground
                // reconciliation request is in flight or temporarily fails.
                this.startTimer();
                this.syncFromServer();
            } else {
                this.displaySeconds = this.calculateWorkedSeconds();
                this.stopTimer();
                this.clearStateSyncRetry();
            }
        };
        this.pageShowHandler = () => {
            this.startTimer();
            this.syncFromServer();
        };
        this.submitHandler = () => this.abortStateSync();

        document.addEventListener('visibilitychange', this.visibilityHandler);
        document.addEventListener('submit', this.submitHandler, true);
        window.addEventListener('pageshow', this.pageShowHandler);
    },

    destroy() {
        this.stopTimer();
        this.abortStateSync();
        this.clearStateSyncRetry();

        document.removeEventListener('visibilitychange', this.visibilityHandler);
        document.removeEventListener('submit', this.submitHandler, true);
        window.removeEventListener('pageshow', this.pageShowHandler);
    },

    startTimer() {
        this.stopTimer();
        this.displaySeconds = this.calculateWorkedSeconds();

        if (!this.hasOpenShift || !this.timerRunning || document.visibilityState !== 'visible') {
            return;
        }

        this.timer = window.setInterval(() => {
            this.displaySeconds = this.calculateWorkedSeconds();
        }, 1000);
    },

    stopTimer() {
        if (this.timer !== null) {
            window.clearInterval(this.timer);
            this.timer = null;
        }
    },

    abortStateSync() {
        this.stateSyncController?.abort();
        this.stateSyncController = null;
    },

    clearStateSyncRetry() {
        if (this.stateSyncRetryTimer !== null) {
            window.clearTimeout(this.stateSyncRetryTimer);
            this.stateSyncRetryTimer = null;
        }
    },

    scheduleStateSyncRetry(delayMilliseconds) {
        this.clearStateSyncRetry();
        this.stateSyncFailures += 1;

        if (this.stateSyncFailures > 3 || document.visibilityState !== 'visible') {
            return;
        }

        const delay = Math.max(3000, Math.min(30000, delayMilliseconds));
        this.nextSyncAt = Date.now() + delay;
        this.stateSyncRetryTimer = window.setTimeout(() => {
            this.stateSyncRetryTimer = null;
            this.syncFromServer();
        }, delay + 50);
    },

    calculateWorkedSeconds() {
        const secondsSinceSync = this.timerRunning
            ? Math.max(0, Math.floor((performance.now() - this.clientStartedAt) / 1000))
            : 0;

        return this.baseWorkedSeconds + secondsSinceSync;
    },

    format(value) {
        const seconds = Math.max(0, Number(value) || 0);

        return String(Math.floor(seconds / 3600)).padStart(2, '0')
            + ':' + String(Math.floor((seconds % 3600) / 60)).padStart(2, '0')
            + ':' + String(Math.floor(seconds % 60)).padStart(2, '0');
    },

    async syncFromServer() {
        if (!stateUrl || document.visibilityState !== 'visible' || Date.now() < this.nextSyncAt) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            return;
        }

        this.abortStateSync();
        this.clearStateSyncRetry();
        const controller = new AbortController();
        const requestStartedAt = performance.now();
        const timeout = window.setTimeout(() => controller.abort(), 8000);
        this.stateSyncController = controller;
        this.nextSyncAt = Date.now() + 3000;

        try {
            const response = await fetch(stateUrl, {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if ([401, 403, 419].includes(response.status) || response.redirected) {
                window.location.reload();
                return;
            }

            if (response.status === 429) {
                const retryAfterSeconds = Math.max(5, Number(response.headers.get('Retry-After')) || 10);
                this.scheduleStateSyncRetry(retryAfterSeconds * 1000);
                this.startTimer();
                return;
            }

            if (!response.ok) {
                throw new Error(`State sync failed with status ${response.status}`);
            }

            const state = await response.json();
            this.stateSyncFailures = 0;
            const serverHasOpenShift = Boolean(state.has_open_shift);
            const serverOnBreak = Boolean(state.on_break);

            // Another tab may have started, paused, resumed, or ended the shift.
            // A reload gives Alpine and all action controls the same server state.
            if (serverHasOpenShift !== this.hasOpenShift || serverOnBreak !== this.onBreak) {
                window.location.reload();
                return;
            }

            const transitSeconds = state.timer_running
                ? Math.max(0, Math.floor((performance.now() - requestStartedAt) / 2000))
                : 0;
            this.baseWorkedSeconds = Math.max(0, Number(state.worked_seconds) || 0) + transitSeconds;
            this.timerRunning = Boolean(state.timer_running);
            this.onBreak = serverOnBreak;
            this.hasOpenShift = serverHasOpenShift;
            this.clientStartedAt = performance.now();
            this.displaySeconds = this.baseWorkedSeconds;
            this.startTimer();
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.scheduleStateSyncRetry(3000 * (2 ** this.stateSyncFailures));
                this.startTimer();
            }
        } finally {
            window.clearTimeout(timeout);
            if (this.stateSyncController === controller) {
                this.stateSyncController = null;
            }
        }
    },
});
