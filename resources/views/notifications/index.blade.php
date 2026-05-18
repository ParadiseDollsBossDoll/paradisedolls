<x-dynamic-component :component="auth()->user()->isAdmin() ? 'admin-layout' : 'member-layout'">
    <div class="mx-auto max-w-4xl space-y-6 text-boss-ivory">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="pd-kicker">{{ __('System') }}</p>
                <h1 class="pd-heading mt-2 text-3xl text-boss-ivory">{{ __('Notifications') }}</h1>
                <p class="mt-2 text-sm text-boss-ivory/40">{{ __('Updates about course access, onboarding, verification, and new academy content.') }}</p>
            </div>

            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2 text-sm font-semibold text-boss-ivory/60 transition hover:border-boss-gold/25 hover:text-boss-gold">
                        {{ __('Mark all read') }}
                    </button>
                </form>
            @endif
        </div>

        <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                @endphp
                <form method="POST" action="{{ route('notifications.open', $notification) }}" class="border-b border-white/[0.05] last:border-b-0">
                    @csrf
                    <button
                        type="submit"
                        class="block w-full px-5 py-4 text-left transition hover:bg-white/[0.025] {{ $isUnread ? 'bg-boss-gold/[0.035]' : '' }}"
                    >
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $isUnread ? 'bg-boss-gold' : 'bg-white/15' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="font-medium text-boss-ivory">{{ $data['title'] ?? __('Notification') }}</p>
                                <span class="text-[0.68rem] text-boss-ivory/28">{{ $notification->created_at?->diffForHumans() }}</span>
                            </div>
                            @if (filled($data['body'] ?? null))
                                <p class="mt-1 text-sm leading-relaxed text-boss-ivory/45">{{ $data['body'] }}</p>
                            @endif
                            <p class="mt-2 text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-boss-gold/70">{{ __('Open') }}</p>
                        </div>
                    </div>
                    </button>
                </form>
            @empty
                <div class="px-5 py-14 text-center">
                    <p class="pd-heading text-xl text-boss-ivory/35">{{ __('No notifications yet') }}</p>
                    <p class="mt-2 text-sm text-boss-ivory/28">{{ __('Important updates will appear here when there is something to review.') }}</p>
                </div>
            @endforelse
        </section>

        @if ($notifications->hasPages())
            <div>{{ $notifications->links() }}</div>
        @endif
    </div>
</x-dynamic-component>
