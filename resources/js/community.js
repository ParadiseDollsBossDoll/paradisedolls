const CHANNEL_PLACEHOLDER = '__slug__';
const MESSAGE_PLACEHOLDER = '__id__';
const TYPING_HEARTBEAT_MS = 2200;
const TYPING_WHISPER_TTL_MS = 4200;
const MAX_MESSAGES_IN_DOM = 150;
const MESSAGE_TIME_FORMATTER = new Intl.DateTimeFormat(undefined, {
    hour: 'numeric',
    minute: '2-digit',
});
const MESSAGE_DATE_FORMATTER = new Intl.DateTimeFormat(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
});
const MESSAGE_TIMESTAMP_FORMATTER = new Intl.DateTimeFormat(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
});

const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

const escapeRegExp = (value) => String(value ?? '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

const firstErrorMessage = (errors) => {
    if (!errors || typeof errors !== 'object') {
        return '';
    }

    const firstEntry = Object.values(errors).find((value) => Array.isArray(value) ? value.length > 0 : Boolean(value));

    if (Array.isArray(firstEntry)) {
        return String(firstEntry[0] ?? '');
    }

    return firstEntry ? String(firstEntry) : '';
};

const deriveErrorMessage = (response, payload, rawText = '') => {
    if (typeof payload?.message === 'string' && payload.message.trim() !== '') {
        return payload.message;
    }

    const validationMessage = firstErrorMessage(payload?.errors);

    if (validationMessage) {
        return validationMessage;
    }

    if (response.status === 419) {
        return 'Your session expired. Refresh the page and try again.';
    }

    if (response.status === 401) {
        return 'Your session is no longer active. Sign in again and retry.';
    }

    if (response.status === 403) {
        return 'You no longer have permission to do that.';
    }

    if (response.status >= 500) {
        return 'The server hit an error while processing that request.';
    }

    if (rawText.trim().startsWith('<!DOCTYPE html>')) {
        return 'The server returned an unexpected response. Refresh the page and try again.';
    }

    return 'Something went wrong.';
};

const fetchJson = async (url, { headers: extraHeaders, ...options } = {}) => {
    const socketId = typeof window !== 'undefined' && window.Echo && typeof window.Echo.socketId === 'function'
        ? window.Echo.socketId()
        : null;

    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            ...(socketId ? { 'X-Socket-Id': socketId } : {}),
            ...(extraHeaders ?? {}),
        },
        ...options,
    });

    let payload = {};
    let rawText = '';

    try {
        rawText = await response.text();
        payload = rawText ? JSON.parse(rawText) : {};
    } catch (error) {
        payload = {};
    }

    if (!response.ok) {
        const err = new Error(deriveErrorMessage(response, payload, rawText));
        err.status = response.status;
        err.payload = payload;
        throw err;
    }

    return payload;
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('communityChat', (initialState) => ({
        user: initialState.user,
        server: initialState.server,
        progress: initialState.progress,
        channels: initialState.channels ?? [],
        selectedChannel: initialState.selected_channel,
        messages: initialState.messages ?? [],
        members: initialState.members ?? { online: [], offline: [], offline_count: 0, total: 0, typing: [] },
        archivedChannels: initialState.archived_channels ?? [],
        moderationLogs: initialState.moderation_logs ?? [],
        memberDirectory: initialState.member_directory ?? [],
        channelAccessOptions: initialState.channel_access_options ?? { access_modes: [], denied_behaviors: [], roles: [] },
        routes: initialState.routes,
        features: initialState.features,
        draft: '',
        replyTo: null,
        attachmentFile: null,
        attachmentName: '',
        attachmentPreview: null,
        shellDrawerOpen: false,
        channelDrawerOpen: false,
        membersDrawerOpen: false,
        channelModalOpen: false,
        channelSyncTimer: null,
        loadingMessages: false,
        loadingOlder: false,
        searchingMessages: false,
        sendingMessage: false,
        hasMoreMessages: initialState.has_more ?? false,
        searchQuery: '',
        activeSearchQuery: '',
        searchResults: [],
        hasSearched: false,
        searchDebounceTimer: null,
        firstUnreadMessageId: initialState.first_unread_message_id ?? null,
        pollTimer: null,
        presenceTimer: null,
        typingTimer: null,
        typingStopTimer: null,
        currentEchoChannel: null,
        privateSubscriptions: {},
        currentPresenceChannel: null,
        releaseConnectionListener: null,
        connectionState: 'connecting',
        connectionFailures: 0,
        typingHeartbeatAt: 0,
        typingSent: false,
        typingWhisperTimers: {},
        dragActive: false,
        lastPresenceRefreshAt: 0,
        channelNotice: initialState.channel_notice,
        composerNotice: null,
        searchNotice: null,
        courseChannelSearch: '',
        messageLoadError: null,
        scrollThrottleFrame: null,
        channelForm: {
            id: null,
            slug: null,
            name: '',
            description: '',
            category: 'Community',
            is_private: false,
            is_locked: false,
            slowmode_seconds: 0,
            access_mode: 'members',
            denied_behavior: 'hidden',
            allowed_roles: [],
            invited_user_ids: [],
        },

        init() {
            this.messages = this.hydrateMessages(this.messages);
            this.syncSearchResults();
            this.$nextTick(() => this.scrollToRelevantAnchor(true));
            this.bindEchoConnection();
            this.startChannelSync();
            this.syncPollingMode();
            this.subscribeToPresence();
            this.subscribeToRealtime();

            window.addEventListener('beforeunload', () => {
                this.sendTypingState(false);
                this.stopPolling();
            }, { once: true });

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.refreshChannelRoster(true);
                    this.refreshPresence();

                    if (this.selectedChannel && this.isNearBottom()) {
                        this.markCurrentChannelRead();
                    }
                }
            });
        },

        stopPolling() {
            if (this.scrollThrottleFrame) {
                cancelAnimationFrame(this.scrollThrottleFrame);
                this.scrollThrottleFrame = null;
            }

            if (this.pollTimer) {
                window.clearInterval(this.pollTimer);
            }

            if (this.channelSyncTimer) {
                window.clearInterval(this.channelSyncTimer);
                this.channelSyncTimer = null;
            }

            if (this.presenceTimer) {
                window.clearInterval(this.presenceTimer);
            }

            if (this.typingTimer) {
                window.clearInterval(this.typingTimer);
            }

            if (this.typingStopTimer) {
                window.clearTimeout(this.typingStopTimer);
            }

            Object.keys(this.typingWhisperTimers).forEach((memberId) => {
                window.clearTimeout(this.typingWhisperTimers[memberId]);
            });
            this.typingWhisperTimers = {};

            if (window.Echo) {
                Object.keys(this.privateSubscriptions).forEach((channelName) => {
                    window.Echo.leave(channelName);
                });
            }

            this.privateSubscriptions = {};
            this.currentEchoChannel = null;

            if (window.Echo && this.currentPresenceChannel) {
                window.Echo.leave(this.currentPresenceChannel);
            }

            if (this.releaseConnectionListener) {
                this.releaseConnectionListener();
                this.releaseConnectionListener = null;
            }
        },

        startChannelSync() {
            if (this.channelSyncTimer) {
                return;
            }
            // Only poll when Echo is disconnected; 30s is plenty even for fallback mode
            this.channelSyncTimer = window.setInterval(() => {
                if (this.shouldUsePollingFallback()) {
                    this.refreshChannelRoster(true);
                }
            }, 30000);
        },

        startPolling() {
            if (!this.shouldUsePollingFallback()) {
                this.stopFallbackPolling();
                return;
            }

            if (!this.pollTimer) {
                this.pollTimer = window.setInterval(() => this.syncLatestMessages(), 12000);
            }

            if (!this.presenceTimer) {
                this.presenceTimer = window.setInterval(
                    () => this.refreshPresence(),
                    Math.max(10000, this.communityPerformance().presence_refresh_interval_ms)
                );
            }
        },

        stopFallbackPolling() {
            if (this.pollTimer) {
                window.clearInterval(this.pollTimer);
                this.pollTimer = null;
            }

            if (this.presenceTimer) {
                window.clearInterval(this.presenceTimer);
                this.presenceTimer = null;
            }

            if (this.typingTimer) {
                window.clearInterval(this.typingTimer);
                this.typingTimer = null;
            }
        },

        syncPollingMode() {
            if (this.shouldUsePollingFallback()) {
                this.startPolling();
                return;
            }

            this.stopFallbackPolling();
        },

        shouldUsePollingFallback() {
            return !window.Echo || this.connectionState !== 'connected';
        },

        selectedRealtimeChannelName() {
            return this.selectedChannel ? `community.channel.${this.selectedChannel.id}` : null;
        },

        bindEchoConnection() {
            if (!window.Echo) {
                this.connectionState = 'disconnected';
                return;
            }

            if (this.releaseConnectionListener) {
                return;
            }

            this.updateConnectionState(window.Echo.connectionStatus?.() ?? 'connecting');

            this.releaseConnectionListener = window.Echo.connector.onConnectionChange((status) => {
                this.updateConnectionState(status);
            });
        },

        updateConnectionState(status) {
            if (status === 'connected') {
                this.connectionState = 'connected';
                this.connectionFailures = 0;
                this.syncPollingMode();
                this.subscribeToRealtime();
                this.subscribeToPresence();
                return;
            }

            if (status === 'connecting') {
                this.connectionFailures += 1;
                this.connectionState = this.connectionState === 'connected' || this.connectionFailures > 1
                    ? 'reconnecting'
                    : 'connecting';
                this.syncPollingMode();
                return;
            }

            this.connectionState = 'disconnected';
            this.syncPollingMode();
        },

        markAuthFailure(message) {
            this.connectionState = 'failed_auth';
            this.syncPollingMode();
            this.channelNotice = { tone: 'error', message };
        },

        subscribeToRealtime() {
            if (!window.Echo) {
                return;
            }

            const activeChannelNames = new Set(
                (this.channels ?? [])
                    .filter((channel) => channel.can_access)
                    .map((channel) => `community.channel.${channel.id}`)
            );

            Object.keys(this.privateSubscriptions).forEach((channelName) => {
                if (activeChannelNames.has(channelName)) {
                    return;
                }

                window.Echo.leave(channelName);
                delete this.privateSubscriptions[channelName];
            });

            this.currentEchoChannel = this.selectedRealtimeChannelName();

            this.channels
                .filter((channel) => channel.can_access)
                .forEach((channel) => {
                const channelName = `community.channel.${channel.id}`;

                if (this.privateSubscriptions[channelName]) {
                    return;
                }

                const subscription = window.Echo.private(channelName)
                    .subscribed(() => {
                        if (this.selectedChannel?.id === channel.id) {
                            this.connectionState = 'connected';
                            this.connectionFailures = 0;
                        }
                    })
                    .error((error) => {
                        if (error?.status === 403 && this.selectedChannel?.id === channel.id) {
                            this.markAuthFailure(`Live access to #${this.selectedChannel?.name ?? 'this channel'} could not be verified.`);
                            return;
                        }

                        if (this.selectedChannel?.id === channel.id) {
                            this.connectionState = 'disconnected';
                        }
                    })
                    .listen('.community.message.created', ({ message }) => {
                        this.connectionState = 'connected';
                        this.connectionFailures = 0;
                        this.upsertMessage(message, true);
                    })
                    .listen('.community.message.deleted', ({ message_id: messageId, channel_id: channelId }) => {
                        this.handleDeletedMessage(channelId ?? channel.id, messageId);
                    })
                    .listen('.community.message.reactions', ({ message }) => {
                        this.upsertMessage(message, false);
                    })
                    .listenForWhisper('typing', (payload) => {
                        this.handleTypingWhisper(channel.id, payload);
                    });

                this.privateSubscriptions[channelName] = subscription;
                });
        },

        subscribeToPresence() {
            if (!window.Echo) {
                return;
            }

            const presenceChannelName = 'community.presence';

            if (this.currentPresenceChannel === presenceChannelName) {
                return;
            }

            if (this.currentPresenceChannel) {
                window.Echo.leave(this.currentPresenceChannel);
            }

            this.currentPresenceChannel = presenceChannelName;

            window.Echo.join(presenceChannelName)
                .here((members) => {
                    this.applyPresenceSnapshot(members);
                })
                .joining((member) => {
                    this.applyPresenceJoin(member);
                })
                .leaving((member) => {
                    this.applyPresenceLeave(member);
                })
                .error((error) => {
                    if (error?.status === 403) {
                        this.markAuthFailure('Member presence access could not be verified.');
                    }
                });
        },

        handleDeletedMessage(channelId, messageId) {
            if (this.selectedChannel?.id === channelId) {
                this.messages = this.messages.filter((message) => message.id !== messageId);
                this.syncSearchResults();
            }
        },

        normalizePresenceMember(member, online = true) {
            const name = member?.name ?? 'Member';
            const initials = member?.initials ?? name
                .split(' ')
                .filter(Boolean)
                .map((segment) => segment[0] ?? '')
                .join('')
                .slice(0, 2)
                .toUpperCase();

            return {
                ...member,
                name,
                initials: initials || 'PD',
                accent: member?.accent ?? '#C9A96E',
                role: member?.role ?? 'member',
                online,
                is_self: Boolean(member?.id === this.user.id || member?.is_self),
            };
        },

        uniqueMembersById(members) {
            const seen = new Map();
            for (const member of members) {
                if (!seen.has(member.id)) seen.set(member.id, member);
            }
            return [...seen.values()];
        },

        sortMembers(members) {
            return [...members].sort((left, right) => {
                if (left.is_self !== right.is_self) {
                    return left.is_self ? -1 : 1;
                }

                return left.name.localeCompare(right.name);
            });
        },

        applyPresenceSnapshot(members) {
            const online = this.sortMembers(this.uniqueMembersById(
                members.map((member) => this.normalizePresenceMember(member, true))
            ));
            const onlineIds = new Set(online.map((member) => member.id));
            const offline = this.sortMembers(this.uniqueMembersById(
                (this.members.offline ?? [])
                    .map((member) => this.normalizePresenceMember(member, false))
                    .filter((member) => !onlineIds.has(member.id))
            ));

            this.members = {
                ...this.members,
                online,
                offline,
                total: online.length + offline.length,
            };
        },

        applyPresenceJoin(member) {
            const nextMember = this.normalizePresenceMember(member, true);
            const online = this.sortMembers(this.uniqueMembersById([
                ...(this.members.online ?? []).filter((item) => item.id !== nextMember.id),
                nextMember,
            ]));
            const offline = (this.members.offline ?? []).filter((item) => item.id !== nextMember.id);

            this.members = {
                ...this.members,
                online,
                offline,
                total: online.length + offline.length,
            };
        },

        applyPresenceLeave(member) {
            const knownMember = [...(this.members.online ?? []), ...(this.members.offline ?? [])]
                .find((item) => item.id === member?.id);
            const nextOfflineMember = this.normalizePresenceMember(knownMember ?? member, false);
            const online = (this.members.online ?? []).filter((item) => item.id !== nextOfflineMember.id);
            const offline = this.sortMembers(this.uniqueMembersById([
                ...(this.members.offline ?? []).filter((item) => item.id !== nextOfflineMember.id),
                nextOfflineMember,
            ]));

            this.members = {
                ...this.members,
                online,
                offline,
                total: online.length + offline.length,
            };
        },

        handleTypingWhisper(channelId, payload) {
            if (!payload || channelId !== this.selectedChannel?.id) {
                return;
            }

            const member = payload.member ? this.normalizePresenceMember(payload.member, true) : null;

            if (!member || member.id === this.user.id) {
                return;
            }

            if (payload.typing) {
                this.upsertTypingMember(member);
                return;
            }

            this.removeTypingMember(member.id);
        },

        upsertTypingMember(member) {
            const typing = [
                ...(this.members.typing ?? []).filter((item) => item.id !== member.id),
                member,
            ].sort((left, right) => left.name.localeCompare(right.name));

            this.members = {
                ...this.members,
                typing,
            };

            if (this.typingWhisperTimers[member.id]) {
                window.clearTimeout(this.typingWhisperTimers[member.id]);
            }

            this.typingWhisperTimers[member.id] = window.setTimeout(() => {
                this.removeTypingMember(member.id);
            }, TYPING_WHISPER_TTL_MS);
        },

        removeTypingMember(memberId) {
            if (this.typingWhisperTimers[memberId]) {
                window.clearTimeout(this.typingWhisperTimers[memberId]);
                delete this.typingWhisperTimers[memberId];
            }

            this.members = {
                ...this.members,
                typing: (this.members.typing ?? []).filter((member) => member.id !== memberId),
            };
        },

        clearTypingMembers() {
            Object.keys(this.typingWhisperTimers).forEach((memberId) => {
                window.clearTimeout(this.typingWhisperTimers[memberId]);
            });

            this.typingWhisperTimers = {};
            this.members = {
                ...this.members,
                typing: [],
            };
        },

        normalizeMessage(message) {
            if (!message) {
                return message;
            }

            const createdTs = Number.isFinite(message._createdTs)
                ? message._createdTs
                : Date.parse(message.created_at ?? '');

            return {
                ...message,
                _createdTs: Number.isNaN(createdTs) ? 0 : createdTs,
                _dateKey: message._dateKey ?? (message.created_at ? new Date(message.created_at).toDateString() : ''),
            };
        },

        hydrateMessages(messages) {
            return messages.map((message) => this.normalizeMessage(message));
        },

        insertMessageSorted(message) {
            const nextMessage = this.normalizeMessage(message);
            const insertAt = this.messages.findIndex((item) => (item._createdTs ?? 0) > nextMessage._createdTs);

            if (insertAt === -1) {
                this.messages.push(nextMessage);
            } else {
                this.messages.splice(insertAt, 0, nextMessage);
            }
        },

        quickChannels() {
            return this.accessibleChannels().slice(0, 7).map((channel) => ({
                ...channel,
                short_label: channel.name
                    .split('-')
                    .map((segment) => segment[0] ?? '')
                    .join('')
                    .slice(0, 2)
                    .toUpperCase(),
            }));
        },

        channelGroups() {
            const groups = {};

            this.filteredChannels().forEach((channel) => {
                const groupName = channel.category || 'Community';

                if (!groups[groupName]) {
                    groups[groupName] = [];
                }

                groups[groupName].push(channel);
            });

            return Object.entries(groups).map(([name, channels]) => ({ name, channels }));
        },

        globalChannels() {
            return this.filteredChannels().filter((c) => !c.course_id);
        },

        filteredCourseChannels() {
            const query = (this.courseChannelSearch || '').toLowerCase().trim();
            return this.filteredChannels()
                .filter((c) => c.course_id)
                .filter((c) => {
                    if (!query) return true;
                    return (c.course_name || c.name || '').toLowerCase().includes(query);
                });
        },

        hasCourseChannels() {
            return this.channels.some((c) => c.course_id);
        },

        filteredChannels() {
            return [...this.channels].sort((left, right) => left.order - right.order || left.name.localeCompare(right.name));
        },

        accessibleChannels() {
            return this.filteredChannels().filter((channel) => channel.can_access);
        },

        canOpenChannel(channel) {
            return Boolean(channel?.can_access);
        },

        handleChannelClick(channel) {
            if (!channel) {
                return;
            }

            if (!this.canOpenChannel(channel)) {
                this.channelNotice = {
                    tone: 'warning',
                    message: channel.denied_behavior === 'locked'
                        ? `#${channel.name} is visible, but you do not currently have permission to open it.`
                        : 'This private channel is not available to your account.',
                };
                return;
            }

            this.selectChannel(channel.slug);
        },

        canUseComposer() {
            return Boolean(this.selectedChannel?.can_post);
        },

        composerDisabledReason() {
            if (!this.selectedChannel) {
                return 'Select a channel to start chatting.';
            }

            if (!this.selectedChannel.can_access) {
                return 'You do not currently have access to this channel.';
            }

            if (this.selectedChannel.is_locked && !this.features.can_moderate_messages) {
                return `#${this.selectedChannel.name} is locked right now.`;
            }

            if (!this.selectedChannel.can_post) {
                return 'Posting is temporarily unavailable in this channel.';
            }

            return '';
        },

        pinnedMessages() {
            return this.messages.filter((message) => message.is_pinned);
        },

        typingIndicatorText() {
            const names = (this.members?.typing ?? []).map((member) => member.name);

            if (names.length === 0) {
                return '';
            }

            if (names.length === 1) {
                return `${names[0]} is typing...`;
            }

            if (names.length === 2) {
                return `${names[0]} and ${names[1]} are typing...`;
            }

            return 'Several people are typing...';
        },

        hasUnreadMessages() {
            return Boolean(this.firstUnreadMessageId && !this.activeSearchQuery);
        },

        isGrouped(index) {
            const message = this.messages[index];
            const previous = this.messages[index - 1];

            if (!message || !previous) {
                return false;
            }

            if (message.user.id !== previous.user.id) {
                return false;
            }

            const gap = Math.abs((message._createdTs ?? 0) - (previous._createdTs ?? 0));

            return gap < 5 * 60 * 1000;
        },

        showDateDivider(index) {
            const message = this.messages[index];
            const previous = this.messages[index - 1];

            if (!message) {
                return false;
            }

            if (!previous) {
                return true;
            }

            return message._dateKey !== previous._dateKey;
        },

        shouldShowUnreadDivider(index) {
            return Boolean(
                this.firstUnreadMessageId
                && !this.activeSearchQuery
                && this.messages[index]?.id === this.firstUnreadMessageId
            );
        },

        unreadDividerLabel() {
            const channel = this.channels.find((item) => item.id === this.selectedChannel?.id);
            const unreadCount = channel?.unread_count ?? 0;

            if (unreadCount <= 1) {
                return 'New message';
            }

            return `${unreadCount} new messages`;
        },

        searchResultSummary() {
            if (!this.activeSearchQuery) {
                return '';
            }

            if (this.searchResults.length === 0) {
                return `No results for "${this.activeSearchQuery}"`;
            }

            if (this.searchResults.length === 1) {
                return `1 result for "${this.activeSearchQuery}"`;
            }

            return `${this.searchResults.length} results for "${this.activeSearchQuery}"`;
        },

        composerPlaceholder() {
            if (!this.selectedChannel) {
                return 'Select a channel';
            }

            if (!this.canUseComposer()) {
                return this.composerDisabledReason();
            }

            return `Message #${this.selectedChannel.name}`;
        },

        messageStateCopy() {
            if (!this.selectedChannel) {
                return {
                    title: 'Select a channel',
                    body: 'Choose a channel to open the conversation and start chatting in real time.',
                };
            }

            if (this.activeSearchQuery && !this.searchingMessages && this.messages.length === 0) {
                return {
                    title: 'No messages matched your search',
                    body: 'Try a shorter phrase, another keyword, or clear the search to see the full conversation again.',
                };
            }

            if (this.selectedChannel.is_locked && !this.features.can_moderate_messages && this.messages.length === 0) {
                return {
                    title: `#${this.selectedChannel.name} is read-only`,
                    body: 'This channel is locked right now. You can still read updates here while posting is paused.',
                };
            }

            if (!this.selectedChannel.can_post && this.messages.length === 0) {
                return {
                    title: `#${this.selectedChannel.name} is view-only for you`,
                    body: 'You can follow the conversation here, but posting is currently unavailable on this account.',
                };
            }

            return {
                title: `Start the #${this.selectedChannel.name} conversation`,
                body: 'No messages yet. Be the first to say hello, ask a question, or share an update in this channel.',
            };
        },

        connectionLabel() {
            if (this.connectionState === 'connecting') {
                return 'Connecting to live chat...';
            }

            if (this.connectionState === 'reconnecting') {
                return 'Reconnecting to live chat...';
            }

            if (this.connectionState === 'disconnected') {
                return 'Live updates are disconnected. Refresh fallback is active.';
            }

            if (this.connectionState === 'failed_auth') {
                return 'Live updates could not authenticate this session. Refresh fallback is active.';
            }

            return '';
        },

        formatMessageTime(dateString) {
            if (!dateString) {
                return '';
            }

            return MESSAGE_TIME_FORMATTER.format(new Date(dateString));
        },

        formatMessageDate(dateString) {
            if (!dateString) {
                return '';
            }

            return MESSAGE_DATE_FORMATTER.format(new Date(dateString));
        },

        formatFullTimestamp(dateString) {
            if (!dateString) {
                return '';
            }

            return MESSAGE_TIMESTAMP_FORMATTER.format(new Date(dateString));
        },

        renderMessage(message, highlight = this.activeSearchQuery) {
            if (!message) {
                return '';
            }

            let rendered = escapeHtml(message);

            if (highlight) {
                const pattern = escapeRegExp(highlight.trim());

                if (pattern) {
                    rendered = rendered.replace(new RegExp(`(${pattern})`, 'gi'), '<mark class="rounded bg-[#c9a96e]/18 px-1 text-[#f7e5c0]">$1</mark>');
                }
            }

            rendered = rendered
                .replace(/(@[a-zA-Z0-9._-]+)/g, '<span class="rounded-full bg-[#c9a96e]/12 px-2 py-0.5 text-[#f4dfb8]">$1</span>')
                .replace(/\n/g, '<br>');

            return rendered;
        },

        route(template, value) {
            return template.replace(CHANNEL_PLACEHOLDER, value).replace(MESSAGE_PLACEHOLDER, value);
        },

        async loadChannelMessages(slug, options = {}) {
            const params = new URLSearchParams();

            if (options.beforeId) {
                params.set('before_id', options.beforeId);
            }

            if (options.afterId) {
                params.set('after_id', options.afterId);
            }

            if (options.searchQuery) {
                params.set('q', options.searchQuery);
            }

            const suffix = params.toString() ? `?${params.toString()}` : '';

            return fetchJson(`${this.route(this.routes.messages, slug)}${suffix}`);
        },

        async selectChannel(slug) {
            if (!slug || this.loadingMessages) {
                return;
            }

            const requestedChannel = this.channels.find((channel) => channel.slug === slug);

            if (requestedChannel && !requestedChannel.can_access) {
                this.handleChannelClick(requestedChannel);
                return;
            }

            this.loadingMessages = true;
            this.messageLoadError = null;
            this.channelNotice = null;
            this.replyTo = null;
            this.sendTypingState(false);

            try {
                const data = await this.loadChannelMessages(slug, {
                    searchQuery: this.activeSearchQuery || undefined,
                });

                const existingIndex = this.channels.findIndex((channel) => channel.id === data.channel.id);

                if (existingIndex !== -1) {
                    this.channels.splice(existingIndex, 1, { ...this.channels[existingIndex], ...data.channel });
                    this.selectedChannel = this.channels[existingIndex];
                } else {
                    this.selectedChannel = data.channel;
                }

                this.currentEchoChannel = this.selectedRealtimeChannelName();
                this.messages = this.hydrateMessages(data.messages);
                this.hasMoreMessages = data.has_more;
                this.firstUnreadMessageId = data.first_unread_message_id ?? null;
                this.clearTypingMembers();
                this.channelDrawerOpen = false;
                this.membersDrawerOpen = false;
                this.searchNotice = this.activeSearchQuery && data.messages.length === 0
                    ? { tone: 'muted', message: this.searchResultSummary() }
                    : null;
                this.syncSearchResults();
                window.history.replaceState({}, '', this.route(this.routes.channel, slug));
                this.subscribeToRealtime();
                await this.refreshPresence(true);
                this.$nextTick(() => this.scrollToRelevantAnchor(true));

                if (!this.activeSearchQuery) {
                    this.markCurrentChannelRead();
                }
            } catch (error) {
                const message = error.status === 403
                    ? 'You no longer have access to that channel.'
                    : error.message;

                this.channelNotice = { tone: 'error', message };
                this.messageLoadError = message;
            } finally {
                this.loadingMessages = false;
            }
        },

        async loadOlderMessages() {
            if (!this.selectedChannel || !this.messages.length || this.loadingOlder) {
                return;
            }

            this.loadingOlder = true;
            this.channelNotice = null;
            const scroller = this.$refs.messageScroller;
            const previousHeight = scroller?.scrollHeight ?? 0;

            try {
                const oldestId = this.messages[0].id;
                const data = await this.loadChannelMessages(this.selectedChannel.slug, {
                    beforeId: oldestId,
                    searchQuery: this.activeSearchQuery || undefined,
                });

                const existingIds = new Set(this.messages.map((m) => m.id));
                const incoming = this.hydrateMessages(data.messages).filter((m) => !existingIds.has(m.id));
                const combined = [...incoming, ...this.messages];
                this.messages = combined.length > MAX_MESSAGES_IN_DOM
                    ? combined.slice(0, MAX_MESSAGES_IN_DOM)
                    : combined;
                this.hasMoreMessages = data.has_more || combined.length > MAX_MESSAGES_IN_DOM;
                this.syncSearchResults();
                this.$nextTick(() => {
                    const nextHeight = scroller?.scrollHeight ?? previousHeight;

                    if (scroller) {
                        scroller.scrollTop = nextHeight - previousHeight;
                    }
                });
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            } finally {
                this.loadingOlder = false;
            }
        },

        reply(message) {
            this.replyTo = message;
            this.$nextTick(() => {
                this.$root.querySelector('textarea')?.focus();
            });
        },

        insertEmoji(emoji) {
            this.draft = `${this.draft}${emoji}`;
            this.handleDraftInput();
        },

        handleComposerEnter(event) {
            if (event.shiftKey) {
                this.draft = `${this.draft}\n`;
                return;
            }

            this.sendMessage();
        },

        handleDraftInput() {
            this.composerNotice = null;
            this.queueTypingState();
            this.autosizeComposer();
        },

        handleSearchInput() {
            if (this.searchDebounceTimer) {
                window.clearTimeout(this.searchDebounceTimer);
            }

            this.searchDebounceTimer = window.setTimeout(() => {
                const nextQuery = this.searchQuery.trim();
                const minChars = Math.max(1, this.communityPerformance().search_min_chars);

                if (!nextQuery && !this.activeSearchQuery) {
                    return;
                }

                if (!nextQuery && this.activeSearchQuery) {
                    this.clearSearch();
                    return;
                }

                if (nextQuery.length < minChars) {
                    this.searchNotice = { tone: 'muted', message: `Type at least ${minChars} characters to search messages.` };
                    return;
                }

                this.searchMessages();
            }, 400);
        },

        communityPerformance() {
            return {
                presence_refresh_interval_ms: 30000,
                search_min_chars: 2,
                search_preview_limit: 8,
                image_preview_max_bytes: 4 * 1024 * 1024,
                ...this.features?.performance,
            };
        },

        mergePresenceSummary(nextMembers) {
            const online = this.sortMembers(this.uniqueMembersById(
                (nextMembers?.online ?? []).map((member) => this.normalizePresenceMember(member, true))
            ));
            const onlineIds = new Set(online.map((member) => member.id));
            const offline = this.sortMembers(this.uniqueMembersById(
                [...(this.members.online ?? []), ...(this.members.offline ?? [])]
                    .filter((member) => !onlineIds.has(member.id))
                    .map((member) => this.normalizePresenceMember(member, false))
            ));
            const offlineCount = nextMembers?.offline_count ?? offline.length;

            this.members = {
                ...this.members,
                online,
                offline,
                offline_count: offlineCount,
                total: nextMembers?.total ?? (online.length + offlineCount),
                typing: nextMembers?.typing ?? this.members.typing ?? [],
            };
        },

        autosizeComposer() {
            this.$nextTick(() => {
                const composer = this.$refs.composerInput;

                if (!composer) {
                    return;
                }

                composer.style.height = 'auto';
                composer.style.height = `${Math.min(composer.scrollHeight, 144)}px`;
            });
        },

        queueTypingState() {
            if (!this.selectedChannel || !this.canUseComposer()) {
                return;
            }

            const isTyping = Boolean(this.draft.trim());

            if (!isTyping) {
                this.sendTypingState(false);
                return;
            }

            const now = Date.now();

            if (!this.typingSent || now - this.typingHeartbeatAt >= TYPING_HEARTBEAT_MS) {
                this.sendTypingState(true);
            }

            if (this.typingStopTimer) {
                window.clearTimeout(this.typingStopTimer);
            }

            this.typingStopTimer = window.setTimeout(() => this.sendTypingState(false), 3200);
        },

        async sendTypingState(typing) {
            if (!this.selectedChannel) {
                return;
            }

            if (typing && !this.canUseComposer()) {
                return;
            }

            if (!typing && !this.typingSent) {
                return;
            }

            this.typingSent = typing;
            this.typingHeartbeatAt = typing ? Date.now() : 0;

            const realtimeChannel = this.currentEchoChannel
                ? this.privateSubscriptions[this.currentEchoChannel]
                : null;

            if (!this.shouldUsePollingFallback() && realtimeChannel) {
                realtimeChannel.whisper('typing', {
                    typing,
                    member: {
                        id: this.user.id,
                        name: this.user.name,
                        initials: this.user.initials,
                        accent: this.user.accent,
                        role: this.user.role,
                        online: true,
                    },
                });

                return;
            }

            try {
                const data = await fetchJson(this.routes.presence_typing, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        channel_id: this.selectedChannel.id,
                        typing,
                    }),
                });

                this.members = {
                    ...this.members,
                    typing: data.typing ?? [],
                };
            } catch (error) {
                // Typing indicators should never block the composer.
            }
        },

        handleAttachment(event) {
            const [file] = event.target.files ?? [];

            this.acceptAttachmentFile(file);
        },

        validateAttachmentFile(file) {
            if (!file) {
                return 'Choose a file to upload.';
            }

            if (file.size > 10 * 1024 * 1024) {
                return 'Files must be 10 MB or smaller.';
            }

            return null;
        },

        acceptAttachmentFile(file) {
            if (!file) {
                return;
            }

            const validationError = this.validateAttachmentFile(file);

            if (validationError) {
                this.composerNotice = { tone: 'error', message: validationError };
                return;
            }

            this.composerNotice = null;
            this.clearAttachment();
            this.attachmentFile = file;
            this.attachmentName = file.name;

            if (file.type.startsWith('image/') && file.size <= this.communityPerformance().image_preview_max_bytes) {
                this.attachmentPreview = URL.createObjectURL(file);
            } else if (file.type.startsWith('image/')) {
                this.composerNotice = {
                    tone: 'warning',
                    message: 'Image selected. Preview is skipped for larger files to keep chat responsive.',
                };
            }
        },

        handleComposerDragOver(event) {
            event.preventDefault();

            if (!this.canUseComposer()) {
                return;
            }

            this.dragActive = true;
        },

        handleComposerDragLeave(event) {
            if (event.currentTarget?.contains(event.relatedTarget)) {
                return;
            }

            this.dragActive = false;
        },

        handleComposerDrop(event) {
            event.preventDefault();
            this.dragActive = false;

            if (!this.canUseComposer()) {
                return;
            }

            const [file] = event.dataTransfer?.files ?? [];
            this.acceptAttachmentFile(file);
        },

        clearAttachment() {
            if (this.attachmentPreview) {
                URL.revokeObjectURL(this.attachmentPreview);
            }

            this.attachmentPreview = null;
            this.attachmentFile = null;
            this.attachmentName = '';

            if (this.$refs.attachmentInput) {
                this.$refs.attachmentInput.value = '';
            }
        },

        buildOptimisticMessage() {
            return {
                id: `temp-${Date.now()}`,
                channel_id: this.selectedChannel.id,
                message: this.draft.trim() || null,
                attachment: this.attachmentPreview ? {
                    url: this.attachmentPreview,
                    name: this.attachmentName,
                    is_image: true,
                } : (this.attachmentName ? {
                    name: this.attachmentName,
                    url: '#',
                    is_image: false,
                } : null),
                reply_to: this.replyTo ? {
                    id: this.replyTo.id,
                    message: this.replyTo.message || this.replyTo.attachment?.name,
                    user_name: this.replyTo.user.name,
                } : null,
                is_pinned: false,
                created_at: new Date().toISOString(),
                user: {
                    id: this.user.id,
                    name: this.user.name,
                    initials: this.user.initials,
                    accent: this.user.accent,
                    is_current_user: true,
                },
                reactions: [],
                can_delete: true,
                can_pin: this.features.can_moderate_messages,
            };
        },

        async sendMessage() {
            if (!this.selectedChannel || this.sendingMessage) {
                return;
            }

            if (!this.canUseComposer()) {
                this.composerNotice = { tone: 'warning', message: this.composerDisabledReason() };
                return;
            }

            if (!this.draft.trim() && !this.attachmentFile) {
                return;
            }

            this.sendingMessage = true;
            this.composerNotice = null;
            const optimistic = this.buildOptimisticMessage();

            const draftText = this.draft.trim();
            const replyToId = this.replyTo?.id ?? null;
            this.draft = '';
            this.replyTo = null;
            this.$nextTick(() => this.autosizeComposer());

            this.insertMessageSorted(optimistic);
            this.syncSearchResults();
            this.$nextTick(() => this.scrollToBottom(true));

            try {
                const formData = new FormData();

                if (draftText) {
                    formData.append('message', draftText);
                }

                if (replyToId) {
                    formData.append('reply_to', replyToId);
                }

                if (this.attachmentFile) {
                    formData.append('attachment', this.attachmentFile);
                }

                const data = await fetchJson(this.route(this.routes.send_message, this.selectedChannel.slug), {
                    method: 'POST',
                    body: formData,
                    headers: {},
                });

                this.replaceMessage(optimistic.id, data.message);
                this.clearAttachment();
                this.sendTypingState(false);
                this.bumpChannelActivity(data.message.channel_id);
                this.markCurrentChannelRead();
                this.syncSearchResults();
                this.$nextTick(() => {
                    this.autosizeComposer();
                    this.scrollToBottom(true);
                });
            } catch (error) {
                this.messages = this.messages.filter((message) => message.id !== optimistic.id);
                this.syncSearchResults();
                this.composerNotice = {
                    tone: 'error',
                    message: this.attachmentFile
                        ? `Upload failed: ${error.message}`
                        : `Message not sent: ${error.message}`,
                };
            } finally {
                this.sendingMessage = false;
            }
        },

        async quickReact(message, emoji) {
            try {
                const data = await fetchJson(this.route(this.routes.toggle_reaction, message.id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ emoji }),
                });

                this.upsertMessage(data.message, false);
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        async togglePin(message) {
            try {
                const data = await fetchJson(this.route(this.routes.toggle_pin, message.id), {
                    method: 'POST',
                });

                this.upsertMessage(data.message, false);
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        async deleteMessage(message) {
            if (!window.confirm('Delete this message?')) {
                return;
            }

            try {
                await fetchJson(this.route(this.routes.delete_message, message.id), {
                    method: 'DELETE',
                });

                this.messages = this.messages.filter((item) => item.id !== message.id);
                this.syncSearchResults();
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        upsertMessage(message, shouldScroll) {
            if (!message || (this.selectedChannel && message.channel_id !== this.selectedChannel.id)) {
                if (message?.channel_id) {
                    this.bumpChannelActivity(message.channel_id, true);
                }

                return;
            }

            const wasNearBottom = this.isNearBottom();
            const existingIndex = this.messages.findIndex((item) => item.id === message.id);
            const nextMessage = this.normalizeMessage(message);

            if (existingIndex === -1) {
                this.insertMessageSorted(nextMessage);

                if (!nextMessage.user.is_current_user && !wasNearBottom) {
                    this.firstUnreadMessageId ??= nextMessage.id;
                    this.bumpChannelActivity(nextMessage.channel_id, true);
                }
            } else {
                this.messages.splice(existingIndex, 1);
                this.insertMessageSorted(nextMessage);
            }

            this.syncSearchResults();

            if (shouldScroll && wasNearBottom) {
                this.$nextTick(() => {
                    this.scrollToBottom(true);
                    this.markCurrentChannelRead();
                });
            }
        },

        replaceMessage(tempId, nextMessage) {
            const index = this.messages.findIndex((message) => message.id === tempId);

            if (index === -1) {
                this.insertMessageSorted(nextMessage);
            } else {
                this.messages.splice(index, 1);
                this.insertMessageSorted(nextMessage);
            }
        },

        bumpChannelActivity(channelId, increaseUnread = false) {
            this.channels = this.channels.map((channel) => {
                if (channel.id !== channelId) {
                    return channel;
                }

                return {
                    ...channel,
                    unread_count: increaseUnread ? (channel.unread_count ?? 0) + 1 : channel.unread_count,
                    last_message_at: new Date().toISOString(),
                };
            });

            if (this.selectedChannel?.id === channelId) {
                this.selectedChannel = {
                    ...this.selectedChannel,
                    unread_count: increaseUnread ? ((this.selectedChannel.unread_count ?? 0) + 1) : this.selectedChannel.unread_count,
                    last_message_at: new Date().toISOString(),
                };
            }
        },

        syncSearchResults() {
            if (!this.activeSearchQuery) {
                this.searchResults = [];
                return;
            }

            const query = this.activeSearchQuery.toLowerCase();

            this.searchResults = this.messages
                .filter((message) => {
                    const body = message.message?.toLowerCase() ?? '';
                    const attachment = message.attachment?.name?.toLowerCase() ?? '';

                    return body.includes(query) || attachment.includes(query);
                })
                .slice(0, this.communityPerformance().search_preview_limit)
                .map((message) => ({
                    id: message.id,
                    label: message.user.name,
                    excerpt: message.message || message.attachment?.name || 'Attachment',
                }));
        },

        async searchMessages() {
            if (!this.selectedChannel) {
                return;
            }

            const query = this.searchQuery.trim();

            if (!query) {
                await this.clearSearch();
                return;
            }

            this.searchingMessages = true;
            this.searchNotice = null;
            this.channelNotice = null;

            try {
                const data = await this.loadChannelMessages(this.selectedChannel.slug, {
                    searchQuery: query,
                });

                this.activeSearchQuery = query;
                this.hasSearched = true;
                this.messages = this.hydrateMessages(data.messages);
                this.hasMoreMessages = data.has_more;
                this.firstUnreadMessageId = null;
                this.searchNotice = data.messages.length === 0
                    ? { tone: 'muted', message: `No results for "${query}"` }
                    : null;
                this.syncSearchResults();
                this.$nextTick(() => {
                    if (this.searchResults[0]) {
                        this.jumpToMessage(this.searchResults[0].id, false);
                    } else {
                        this.scrollToTop();
                    }
                });
            } catch (error) {
                this.searchNotice = { tone: 'error', message: `Search failed: ${error.message}` };
            } finally {
                this.searchingMessages = false;
            }
        },

        async clearSearch() {
            if (this.searchDebounceTimer) {
                window.clearTimeout(this.searchDebounceTimer);
            }

            this.searchQuery = '';
            this.activeSearchQuery = '';
            this.searchResults = [];
            this.searchNotice = null;
            this.hasSearched = false;

            if (this.selectedChannel) {
                await this.selectChannel(this.selectedChannel.slug);
            }
        },

        async syncLatestMessages() {
            if (!this.shouldUsePollingFallback()) {
                return;
            }

            if (!this.selectedChannel) {
                return;
            }

            try {
                const lastRealMessage = [...this.messages].reverse().find((m) => typeof m.id === 'number');
                const latestId = lastRealMessage?.id ?? null;

                if (latestId) {
                    const data = await this.loadChannelMessages(this.selectedChannel.slug, {
                        afterId: latestId,
                    });

                    data.messages.forEach((message) => this.upsertMessage(message, true));
                    this.hasMoreMessages = data.has_more || this.hasMoreMessages;
                }

                if (this.channelNotice?.message === 'Message refresh is having trouble right now.') {
                    this.channelNotice = null;
                }
            } catch (error) {
                this.channelNotice ??= { tone: 'warning', message: 'Message refresh is having trouble right now.' };
            }
        },

        async refreshChannelRoster(silent = false) {
            try {
                const data = await fetchJson(this.routes.channels_index);
                const previousSelectedSlug = this.selectedChannel?.slug ?? null;

                this.channels = data.channels ?? [];
                this.archivedChannels = data.archived_channels ?? [];
                this.subscribeToRealtime();

                if (!previousSelectedSlug) {
                    return;
                }

                const refreshedSelected = this.channels.find((channel) => channel.slug === previousSelectedSlug);

                if (refreshedSelected?.can_access) {
                    this.selectedChannel = {
                        ...this.selectedChannel,
                        ...refreshedSelected,
                    };
                    return;
                }

                if (refreshedSelected && !refreshedSelected.can_access) {
                    const fallback = this.accessibleChannels()[0];

                    if (fallback && fallback.slug !== previousSelectedSlug) {
                        await this.selectChannel(fallback.slug);
                    } else if (!fallback) {
                        this.selectedChannel = null;
                        this.messages = [];
                        this.firstUnreadMessageId = null;
                    }

                    if (!silent) {
                        this.channelNotice = {
                            tone: 'warning',
                            message: `#${refreshedSelected.name} is no longer available to open with your current permissions.`,
                        };
                    }

                    return;
                }

                const fallback = this.accessibleChannels()[0];

                if (fallback) {
                    await this.selectChannel(fallback.slug);
                } else {
                    this.selectedChannel = null;
                    this.messages = [];
                    this.firstUnreadMessageId = null;
                }

                if (!silent) {
                    this.channelNotice = {
                        tone: 'warning',
                        message: 'The current channel is no longer available.',
                    };
                }
            } catch (error) {
                if (!silent) {
                    this.channelNotice = { tone: 'error', message: error.message };
                }
            }
        },

        async refreshPresence(typingOnly = false) {
            if (!this.selectedChannel && typingOnly) {
                return;
            }

            if (!this.shouldUsePollingFallback()) {
                return;
            }

            const now = Date.now();

            if (!typingOnly && now - this.lastPresenceRefreshAt < 4000) {
                return;
            }

            try {
                const params = new URLSearchParams();

                if (this.selectedChannel?.id) {
                    params.set('channel_id', this.selectedChannel.id);
                }

                params.set('summary', '1');

                const suffix = params.toString() ? `?${params.toString()}` : '';
                const data = await fetchJson(`${this.routes.presence}${suffix}`);

                if (typingOnly) {
                    this.members = {
                        ...this.members,
                        typing: data.members?.typing ?? [],
                    };
                    return;
                }

                this.lastPresenceRefreshAt = now;
                this.mergePresenceSummary(data.members);
            } catch (error) {
                if (!typingOnly) {
                    this.channelNotice ??= { tone: 'warning', message: 'Member presence is having trouble refreshing right now.' };
                }
            }
        },

        async markCurrentChannelRead() {
            if (!this.selectedChannel) {
                return;
            }

            this.firstUnreadMessageId = null;
            this.channels = this.channels.map((channel) => (
                channel.id === this.selectedChannel.id
                    ? { ...channel, unread_count: 0 }
                    : channel
            ));
            this.selectedChannel = {
                ...this.selectedChannel,
                unread_count: 0,
            };

            try {
                await fetchJson(this.route(this.routes.mark_read, this.selectedChannel.slug), {
                    method: 'POST',
                });
            } catch (error) {
                // Unread badges can recover on the next refresh.
            }
        },

        scrollToBottom(force = false) {
            const scroller = this.$refs.messageScroller;

            if (!scroller) {
                return;
            }

            if (force || this.isNearBottom()) {
                scroller.scrollTop = scroller.scrollHeight;
            }
        },

        scrollToTop() {
            const scroller = this.$refs.messageScroller;

            if (scroller) {
                scroller.scrollTop = 0;
            }
        },

        scrollToRelevantAnchor(force = false) {
            if (this.firstUnreadMessageId && !this.activeSearchQuery) {
                this.jumpToMessage(this.firstUnreadMessageId, force);
                return;
            }

            this.scrollToBottom(force);
        },

        jumpToMessage(messageId, smooth = true) {
            this.$nextTick(() => {
                const target = this.$refs.messageScroller?.querySelector(`[data-message-id="${messageId}"]`);

                if (!target) {
                    return;
                }

                target.scrollIntoView({
                    behavior: smooth ? 'smooth' : 'auto',
                    block: 'center',
                });
            });
        },

        handleScroller() {
            if (!this.scrollThrottleFrame) {
                this.scrollThrottleFrame = requestAnimationFrame(() => {
                    this.scrollThrottleFrame = null;
                    if (this.firstUnreadMessageId && this.isNearBottom()) {
                        this.markCurrentChannelRead();
                    }
                });
            }
        },

        isNearBottom() {
            const scroller = this.$refs.messageScroller;

            if (!scroller) {
                return false;
            }

            return scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight < 140;
        },

        openChannelModal(channel = null) {
            this.channelForm = channel
                ? {
                    id: channel.id,
                    slug: channel.slug,
                    name: channel.name,
                    description: channel.description ?? '',
                    category: channel.category ?? 'Community',
                    is_private: channel.is_private,
                    is_locked: channel.is_locked,
                    slowmode_seconds: channel.slowmode_seconds ?? 0,
                    access_mode: channel.access_mode ?? 'members',
                    denied_behavior: channel.denied_behavior ?? 'hidden',
                    allowed_roles: [...(channel.allowed_roles ?? [])],
                    invited_user_ids: [...(channel.invited_user_ids ?? [])],
                }
                : {
                    id: null,
                    slug: null,
                    name: '',
                    description: '',
                    category: channel?.category ?? 'Community',
                    is_private: false,
                    is_locked: false,
                    slowmode_seconds: 0,
                    access_mode: 'members',
                    denied_behavior: 'hidden',
                    allowed_roles: [],
                    invited_user_ids: [],
                };

            this.channelModalOpen = true;
        },

        closeChannelModal() {
            this.channelModalOpen = false;
        },

        async submitChannelForm() {
            try {
                const isEditing = Boolean(this.channelForm.id);
                const url = this.channelForm.slug
                    ? this.route(this.routes.update_channel, this.channelForm.slug)
                    : this.routes.create_channel;

                const data = await fetchJson(url, {
                    method: this.channelForm.slug ? 'PATCH' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.channelForm.name,
                        description: this.channelForm.description,
                        category: this.channelForm.category,
                        is_private: this.channelForm.is_private,
                        is_locked: this.channelForm.is_locked,
                        slowmode_seconds: this.channelForm.slowmode_seconds ?? 0,
                        access_mode: this.channelForm.access_mode,
                        denied_behavior: this.channelForm.denied_behavior,
                        allowed_roles: this.channelForm.allowed_roles,
                        invited_user_ids: this.channelForm.invited_user_ids,
                    }),
                });

                const channel = data.channel;
                const existingIndex = this.channels.findIndex((item) => item.id === channel.id);

                if (existingIndex === -1) {
                    this.channels.push(channel);
                } else {
                    this.channels.splice(existingIndex, 1, { ...this.channels[existingIndex], ...channel });
                }

                this.channels = this.filteredChannels();
                this.subscribeToRealtime();

                if (this.selectedChannel?.id === channel.id) {
                    this.selectedChannel = { ...this.selectedChannel, ...channel };
                }

                if (!this.selectedChannel) {
                    this.selectedChannel = channel;
                }

                this.closeChannelModal();
                await this.refreshChannelRoster(true);

                if (!isEditing) {
                    await this.selectChannel(channel.slug);
                }

                this.refreshModerationHistory();
                this.channelNotice = {
                    tone: 'success',
                    message: isEditing ? 'Channel updated.' : 'Channel created.',
                };
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        async archiveChannel(channel) {
            try {
                await fetchJson(this.route(this.routes.archive_channel, channel.slug), {
                    method: 'POST',
                });

                this.channels = this.channels.filter((item) => item.id !== channel.id);
                this.archivedChannels.unshift({ ...channel, is_archived: true });
                await this.refreshChannelRoster(true);
                this.subscribeToRealtime();

                if (this.selectedChannel?.id === channel.id) {
                    const fallback = this.accessibleChannels()[0];

                    if (fallback) {
                        await this.selectChannel(fallback.slug);
                    } else {
                        this.selectedChannel = null;
                        this.messages = [];
                    }
                }

                this.closeChannelModal();
                this.refreshModerationHistory();
                this.channelNotice = { tone: 'success', message: 'Channel archived.' };
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        async deleteChannelPermanently(channel) {
            if (!channel) {
                return;
            }

            try {
                const data = await fetchJson(this.route(this.routes.delete_channel, channel.slug), {
                    method: 'DELETE',
                });

                this.channels = this.channels.filter((item) => item.id !== channel.id);
                this.archivedChannels = this.archivedChannels.filter((item) => item.id !== channel.id);
                await this.refreshChannelRoster(true);
                this.subscribeToRealtime();

                if (this.selectedChannel?.id === channel.id) {
                    const fallback = this.accessibleChannels()[0];

                    if (fallback) {
                        await this.selectChannel(fallback.slug);
                    } else {
                        this.selectedChannel = null;
                        this.messages = [];
                    }
                }

                this.closeChannelModal();
                this.refreshModerationHistory();
                this.channelNotice = {
                    tone: 'success',
                    message: data.channel_name ? `Channel "${data.channel_name}" deleted.` : 'Channel deleted.',
                };
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        async archiveChannelFromModal() {
            if (!this.channelForm.slug || !window.confirm('Archive this channel?')) {
                return;
            }

            const channel = this.channels.find((item) => item.slug === this.channelForm.slug);

            if (channel) {
                await this.archiveChannel(channel);
            }
        },

        async deleteChannelFromModal() {
            if (!this.channelForm.slug || !window.confirm('Delete this channel permanently? This will remove its messages too.')) {
                return;
            }

            const channel = this.channels.find((item) => item.slug === this.channelForm.slug)
                ?? this.archivedChannels.find((item) => item.slug === this.channelForm.slug);

            if (channel) {
                await this.deleteChannelPermanently(channel);
            }
        },

        async moveChannel(channel, direction) {
            const ordered = this.filteredChannels();
            const index = ordered.findIndex((item) => item.id === channel.id);

            if (index === -1) {
                return;
            }

            const swapIndex = direction === 'up' ? index - 1 : index + 1;

            if (swapIndex < 0 || swapIndex >= ordered.length) {
                return;
            }

            [ordered[index], ordered[swapIndex]] = [ordered[swapIndex], ordered[index]];

            ordered.forEach((item, position) => {
                item.order = position + 1;
            });

            this.channels = ordered;

            try {
                const data = await fetchJson(this.routes.reorder_channels, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        channels: ordered.map((item) => item.id),
                    }),
                });

                this.channels = data.channels;
                this.refreshModerationHistory();
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        async restoreChannel(channel) {
            if (!channel) {
                return;
            }

            try {
                const data = await fetchJson(this.route(this.routes.restore_channel, channel.slug), {
                    method: 'POST',
                });

                this.archivedChannels = this.archivedChannels.filter((item) => item.id !== channel.id);
                this.channels.push(data.channel);
                this.channels = this.filteredChannels();
                this.subscribeToRealtime();
                this.refreshModerationHistory();
                this.channelNotice = { tone: 'success', message: 'Channel restored.' };
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        memberCanBeTimedOut(member) {
            if (!this.features.can_moderate_messages || !member || member.is_self) {
                return false;
            }

            if (member.role === 'admin') {
                return false;
            }

            if (member.role === 'moderator' && this.user.role !== 'admin') {
                return false;
            }

            return true;
        },

        async timeoutMember(member) {
            if (!this.memberCanBeTimedOut(member)) {
                return;
            }

            const durationValue = window.prompt(`Timeout ${member.name} for how many minutes?`, '60');

            if (durationValue === null) {
                return;
            }

            const durationMinutes = Number.parseInt(durationValue, 10);

            if (!Number.isFinite(durationMinutes) || durationMinutes <= 0) {
                this.channelNotice = { tone: 'error', message: 'Enter a valid timeout length in minutes.' };
                return;
            }

            const reason = window.prompt('Reason for timeout (optional)', '') ?? '';

            try {
                await fetchJson(this.route(this.routes.member_timeout, member.id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        channel_id: this.selectedChannel?.id ?? null,
                        duration_minutes: durationMinutes,
                        reason,
                    }),
                });

                this.refreshModerationHistory();
                this.channelNotice = { tone: 'success', message: `${member.name} has been timed out.` };
            } catch (error) {
                this.channelNotice = { tone: 'error', message: error.message };
            }
        },

        async refreshModerationHistory() {
            if (!this.features.can_moderate_messages) {
                return;
            }

            try {
                const data = await fetchJson(this.routes.moderation_logs);
                this.moderationLogs = data.logs ?? [];
            } catch (error) {
                // Keep the current moderation snapshot if refresh fails.
            }
        },

        readableModerationAction(action) {
            const labels = {
                channel_created: 'created a channel',
                channel_updated: 'updated a channel',
                channel_archived: 'archived a channel',
                channel_restored: 'restored a channel',
                channels_reordered: 'reordered channels',
                message_deleted: 'deleted a message',
                message_pinned: 'pinned a message',
                message_unpinned: 'unpinned a message',
                member_timeout: 'timed out a member',
                member_timeout_revoked: 'revoked a timeout',
            };

            return labels[action] ?? action.replaceAll('_', ' ');
        },

        formatModerationLog(log) {
            if (!log) {
                return '';
            }

            const actor = log.actor_name ?? 'Someone';
            const action = this.readableModerationAction(log.action);
            const target = log.target_name ? ` ${log.target_name}` : '';
            const channel = log.channel_name ? ` in #${log.channel_name}` : '';

            return `${actor} ${action}${target}${channel}`;
        },
    }));
});
