<x-admin-layout>
    <div class="mx-auto max-w-5xl space-y-6 text-boss-ivory">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <p class="pd-kicker">{{ __('Email Campaigns') }} / {{ $campaign->statusLabel() }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ $campaign->name }}</h1>
            </div>
            <a href="{{ route('admin.email-campaigns.index') }}" class="pd-btn-secondary shrink-0">{{ __('Back') }}</a>
        </header>

        @if (session('status'))
            <div class="border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <section class="pd-panel p-6">
            <div class="flex flex-wrap items-end gap-3">
                <form method="POST" action="{{ route('admin.email-campaigns.send', $campaign) }}" onsubmit="return confirm('{{ __('Queue this campaign now?') }}');">
                    @csrf
                    <x-primary-button>{{ __('Send Now') }}</x-primary-button>
                </form>

                @if (in_array($campaign->status, [\App\Models\EmailCampaign::STATUS_ACTIVE, \App\Models\EmailCampaign::STATUS_SCHEDULED], true))
                    <form method="POST" action="{{ route('admin.email-campaigns.pause', $campaign) }}">
                        @csrf
                        <x-secondary-button type="submit">{{ __('Pause') }}</x-secondary-button>
                    </form>
                @elseif ($campaign->status === \App\Models\EmailCampaign::STATUS_PAUSED)
                    <form method="POST" action="{{ route('admin.email-campaigns.resume', $campaign) }}">
                        @csrf
                        <x-secondary-button type="submit">{{ __('Resume') }}</x-secondary-button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.email-campaigns.schedule', $campaign) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div>
                        <x-input-label for="scheduled_for" :value="__('Schedule')" />
                        <x-text-input id="scheduled_for" name="scheduled_for" type="datetime-local" class="mt-1 min-w-[14rem]" required />
                    </div>
                    <x-secondary-button type="submit">{{ __('Set Date') }}</x-secondary-button>
                </form>
            </div>

            <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-t border-white/[0.06] pt-4 text-xs text-boss-ivory/40">
                <span>{{ __('Status: :status', ['status' => $campaign->statusLabel()]) }}</span>
                <span>{{ __('Runs: :count', ['count' => $campaign->total_runs]) }}</span>
                @if ($campaign->next_send_at)
                    <span>{{ __('Next: :date', ['date' => $campaign->next_send_at->format('M j, Y g:i A')]) }}</span>
                @endif
            </div>
        </section>

        <form method="POST" action="{{ route('admin.email-campaigns.update', $campaign) }}" class="pd-panel space-y-6 p-6">
            @csrf
            @method('PUT')
            @include('admin.email-campaigns.partials.form')
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
        </form>

        <section class="space-y-3">
            <div>
                <p class="pd-kicker">{{ __('Delivery History') }}</p>
                <h2 class="pd-heading mt-2 text-xl">{{ __('Campaign Runs') }}</h2>
            </div>

            @forelse ($runs as $run)
                <div class="pd-panel flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-sm font-medium text-boss-ivory">{{ $run->started_at->format('M j, Y g:i A') }}</p>
                        <p class="mt-1 text-xs text-boss-ivory/35">{{ ucfirst($run->status) }}</p>
                    </div>
                    <div class="flex gap-5 text-xs text-boss-ivory/45">
                        <span>{{ $run->recipient_count }} {{ __('recipients') }}</span>
                        <span class="text-green-300">{{ $run->sent_count }} {{ __('sent') }}</span>
                        @if ($run->failed_count)
                            <span class="text-red-300">{{ $run->failed_count }} {{ __('failed') }}</span>
                        @endif
                        @if ($run->skipped_count)
                            <span>{{ $run->skipped_count }} {{ __('skipped') }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="pd-panel py-10 text-center text-sm text-boss-ivory/35">{{ __('No deliveries yet.') }}</div>
            @endforelse

            {{ $runs->links() }}
        </section>

        @if ($campaign->runs()->doesntExist())
            <form method="POST" action="{{ route('admin.email-campaigns.destroy', $campaign) }}" onsubmit="return confirm('{{ __('Delete this draft campaign?') }}');">
                @csrf
                @method('DELETE')
                <x-danger-button type="submit">{{ __('Delete Campaign') }}</x-danger-button>
            </form>
        @endif
    </div>
</x-admin-layout>
