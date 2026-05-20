@php
    $notificationUser = auth()->user();
    [$unreadNotificationCount, $recentNotifications] = $notificationUser
        ? \Illuminate\Support\Facades\Cache::remember(
            'notification_bell_'.$notificationUser->id,
            now()->addSeconds(15),
            fn () => [
                $notificationUser->unreadNotifications()->count(),
                $notificationUser->notifications()
                    ->latest()
                    ->take(6)
                    ->get()
                    ->map(fn ($notification) => [
                        'id' => $notification->id,
                        'data' => $notification->data,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                    ]),
            ]
        )
        : [0, collect()];
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <button
        type="button"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/[0.08] bg-white/[0.04] text-boss-ivory/55 transition hover:border-[#EEB4C3]/25 hover:text-[#EEB4C3]"
        title="{{ __('Notifications') }}"
        aria-label="{{ __('Notifications') }}"
        aria-haspopup="menu"
        :aria-expanded="open.toString()"
        @click="open = ! open"
    >
        <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.7]">
            <path d="M12.5 6.7c0-2.5-1.6-4.2-4.5-4.2S3.5 4.2 3.5 6.7c0 3-1 4-1.5 4.8h12c-.5-.8-1.5-1.8-1.5-4.8z"/>
            <path d="M6.2 13c.3.7.9 1.2 1.8 1.2s1.5-.5 1.8-1.2"/>
        </svg>
        @if ($unreadNotificationCount > 0)
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#EEB4C3] px-1 text-[0.6rem] font-bold text-boss-ink">
                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
            </span>
        @endif
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        role="menu"
        class="absolute right-0 z-50 mt-3 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-white/[0.08] bg-[#10101b]/95 shadow-[0_24px_80px_rgba(0,0,0,0.45)] ring-1 ring-white/[0.03] backdrop-blur-xl sm:w-96"
    >
        <div class="flex items-center justify-between gap-3 border-b border-white/[0.06] px-4 py-3">
            <div>
                <p class="text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-[#EEB4C3]/70">{{ __('Notifications') }}</p>
                <p class="mt-0.5 text-[0.72rem] text-boss-ivory/35">
                    {{ trans_choice(':count unread update|:count unread updates', $unreadNotificationCount, ['count' => $unreadNotificationCount]) }}
                </p>
            </div>
            @if ($unreadNotificationCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-white/[0.07] bg-white/[0.035] px-2.5 py-1.5 text-[0.68rem] font-semibold text-boss-ivory/45 transition hover:border-[#EEB4C3]/20 hover:text-[#EEB4C3]">
                        {{ __('Mark read') }}
                    </button>
                </form>
            @endif
        </div>

        <div class="max-h-[24rem] overflow-y-auto">
            @forelse ($recentNotifications as $notification)
                @php
                    $data = $notification['data'];
                    $isUnread = $notification['read_at'] === null;
                @endphp
                <form method="POST" action="{{ route('notifications.open', $notification['id']) }}" class="border-b border-white/[0.05] last:border-b-0">
                    @csrf
                    <button
                        type="submit"
                        role="menuitem"
                        class="block w-full px-4 py-3 text-left transition hover:bg-white/[0.035] {{ $isUnread ? 'bg-[#EEB4C3]/[0.045]' : '' }}"
                        @click="open = false"
                    >
                    <div class="flex gap-3">
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $isUnread ? 'bg-[#EEB4C3] shadow-[0_0_16px_rgba(238, 180, 195, 0.55)]' : 'bg-white/12' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="line-clamp-1 text-sm font-semibold text-boss-ivory/82">{{ $data['title'] ?? __('Notification') }}</p>
                                <span class="shrink-0 text-[0.62rem] text-boss-ivory/24">{{ $notification['created_at']?->diffForHumans(null, true) }}</span>
                            </div>
                            @if (filled($data['body'] ?? null))
                                <p class="mt-1 line-clamp-2 text-[0.75rem] leading-relaxed text-boss-ivory/40">{{ $data['body'] }}</p>
                            @endif
                        </div>
                    </div>
                    </button>
                </form>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm font-medium text-boss-ivory/45">{{ __('No notifications yet') }}</p>
                    <p class="mt-1 text-[0.75rem] text-boss-ivory/28">{{ __('Important updates will appear here.') }}</p>
                </div>
            @endforelse
        </div>

        <a href="{{ route('notifications.index') }}" class="flex items-center justify-between border-t border-white/[0.06] px-4 py-3 text-[0.75rem] font-semibold text-[#EEB4C3]/75 transition hover:bg-[#EEB4C3]/[0.05] hover:text-[#EEB4C3]">
            <span>{{ __('View all notifications') }}</span>
            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2]"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </a>
    </div>
</div>

