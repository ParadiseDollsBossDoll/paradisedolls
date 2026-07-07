<x-admin-layout>
    <div class="mx-auto max-w-6xl space-y-6 text-boss-ivory">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <p class="pd-kicker">{{ __('Communication') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ __('Email Campaigns') }}</h1>
            </div>
            <a href="{{ route('admin.email-campaigns.create') }}" class="pd-btn-primary shrink-0">{{ __('New Campaign') }}</a>
        </header>

        @if (session('status'))
            <div class="border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="pd-panel p-4">
                <p class="pd-kicker">{{ __('Available') }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($allModelsCount) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ __('All models') }}</p>
            </div>
            <div class="pd-panel p-4">
                <p class="pd-kicker">{{ __('Available') }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($onboardedModelsCount) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ __('Fully onboarded') }}</p>
            </div>
            <div class="pd-panel p-4">
                <p class="pd-kicker">{{ __('Preferences') }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($unsubscribedCount) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ __('Unsubscribed') }}</p>
            </div>
        </div>

        <div class="space-y-3">
            @forelse ($campaigns as $campaign)
                @php
                    $statusClass = match ($campaign->status) {
                        \App\Models\EmailCampaign::STATUS_ACTIVE => 'bg-green-400/10 text-green-300',
                        \App\Models\EmailCampaign::STATUS_SCHEDULED => 'bg-blue-400/10 text-blue-200',
                        \App\Models\EmailCampaign::STATUS_PAUSED => 'bg-amber-400/10 text-amber-200',
                        \App\Models\EmailCampaign::STATUS_COMPLETED => 'bg-white/[0.05] text-boss-ivory/45',
                        default => 'bg-boss-gold/10 text-boss-gold',
                    };
                @endphp
                <article class="pd-panel p-5">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-1 text-[0.62rem] uppercase tracking-[0.1em] {{ $statusClass }}">{{ $campaign->statusLabel() }}</span>
                                <span class="text-[0.68rem] text-boss-ivory/35">{{ $campaign->audienceLabel() }}</span>
                                @if ($campaign->repeats())
                                    <span class="text-[0.68rem] text-boss-gold">{{ __('Every :days days', ['days' => $campaign->repeat_every_days]) }}</span>
                                @endif
                            </div>
                            <h2 class="mt-3 truncate text-base font-semibold text-boss-ivory">{{ $campaign->name }}</h2>
                            <p class="mt-1 truncate text-sm text-boss-ivory/55">{{ $campaign->subject }}</p>
                            <p class="mt-2 text-xs text-boss-ivory/30">
                                @if ($campaign->next_send_at)
                                    {{ __('Next: :date', ['date' => $campaign->next_send_at->format('M j, Y g:i A')]) }}
                                @elseif ($campaign->last_sent_at)
                                    {{ __('Last sent: :date', ['date' => $campaign->last_sent_at->format('M j, Y g:i A')]) }}
                                @else
                                    {{ __('Not scheduled') }}
                                @endif
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @if ($campaign->latestRun)
                                <span class="mr-2 text-xs text-boss-ivory/35">
                                    {{ $campaign->latestRun->sent_count }}/{{ $campaign->latestRun->recipient_count }} {{ __('sent') }}
                                    @if ($campaign->latestRun->failed_count > 0)
                                        &middot; {{ $campaign->latestRun->failed_count }} {{ __('failed') }}
                                    @endif
                                    @if ($campaign->latestRun->skipped_count > 0)
                                        &middot; {{ $campaign->latestRun->skipped_count }} {{ __('skipped') }}
                                    @endif
                                </span>
                            @endif
                            <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" class="pd-btn-secondary">{{ __('Open') }}</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="pd-panel py-16 text-center">
                    <p class="text-sm text-boss-ivory/40">{{ __('No email campaigns yet.') }}</p>
                </div>
            @endforelse
        </div>

        {{ $campaigns->links() }}
    </div>
</x-admin-layout>
