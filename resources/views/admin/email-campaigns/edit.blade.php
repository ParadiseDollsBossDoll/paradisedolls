<x-admin-layout>
    <div class="pd-email-campaigns mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="pd-kicker">{{ __('Email Campaigns') }} / {{ $campaign->statusLabel() }}</p>
                <h1 class="pd-heading mt-2 truncate text-[clamp(1.9rem,4vw,2.6rem)]">{{ $campaign->name }}</h1>
            </div>
            <a href="{{ route('admin.email-campaigns.index') }}" class="pd-btn-secondary w-full shrink-0 sm:w-auto">{{ __('Back') }}</a>
        </header>

        @if (session('status'))
            <div class="rounded-lg border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <form
                method="POST"
                action="{{ route('admin.email-campaigns.update', $campaign) }}"
                class="space-y-5"
                x-data="{ audience: @js(old('audience', $campaign->audience)) }"
            >
                @csrf
                @method('PUT')
                @include('admin.email-campaigns.partials.form')

                <button type="submit" class="pd-btn-primary w-full sm:w-auto">{{ __('Save Changes') }}</button>
            </form>

            <aside class="space-y-5 xl:sticky xl:top-6">
                <section class="pd-panel space-y-5 p-5">
                    <div>
                        <p class="pd-kicker">{{ __('Campaign Control') }}</p>
                        <h2 class="pd-heading mt-1 text-xl">{{ __('Delivery Status') }}</h2>
                    </div>

                    <dl class="space-y-3 border-y border-white/[0.06] py-4 text-xs">
                        <div class="flex justify-between gap-4">
                            <dt class="text-boss-ivory/40">{{ __('Status') }}</dt>
                            <dd class="font-medium text-boss-ivory/75">{{ $campaign->statusLabel() }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-boss-ivory/40">{{ __('Campaign runs') }}</dt>
                            <dd class="font-medium text-boss-ivory/75">{{ number_format($campaign->total_runs) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-boss-ivory/40">{{ __('Frequency') }}</dt>
                            <dd class="text-right font-medium text-boss-ivory/75">{{ $campaign->repeatLabel() }}</dd>
                        </div>
                        @if ($campaign->next_send_at)
                            <div class="flex justify-between gap-4">
                                <dt class="text-boss-ivory/40">{{ __('Next send') }}</dt>
                                <dd class="text-right font-medium text-boss-ivory/75">
                                    {{ $campaign->nextSendAtForAdmin()?->format('M j, Y g:i A') }}
                                    <span class="block text-[0.65rem] font-normal text-boss-ivory/40">{{ __('UK time') }} &middot; {{ now(\App\Models\EmailCampaign::schedulingTimezone())->format('T') }}</span>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <div class="grid gap-2">
                        <form method="POST" action="{{ route('admin.email-campaigns.send', $campaign) }}" onsubmit="return confirm('{{ __('Queue this campaign now?') }}');">
                            @csrf
                            <button type="submit" class="pd-btn-primary w-full">{{ __('Send Now') }}</button>
                        </form>

                        @if (in_array($campaign->status, [\App\Models\EmailCampaign::STATUS_ACTIVE, \App\Models\EmailCampaign::STATUS_SCHEDULED], true))
                            <form method="POST" action="{{ route('admin.email-campaigns.pause', $campaign) }}">
                                @csrf
                                <button type="submit" class="pd-btn-secondary w-full">{{ __('Pause Automation') }}</button>
                            </form>
                        @elseif ($campaign->status === \App\Models\EmailCampaign::STATUS_PAUSED)
                            <form method="POST" action="{{ route('admin.email-campaigns.resume', $campaign) }}">
                                @csrf
                                <button type="submit" class="pd-btn-secondary w-full">{{ __('Resume Automation') }}</button>
                            </form>
                        @endif
                    </div>
                </section>

                <form method="POST" action="{{ route('admin.email-campaigns.schedule', $campaign) }}" class="space-y-3">
                    @csrf
                    @include('admin.email-campaigns.partials.schedule', [
                        'includeDeliveryMode' => false,
                    ])
                    <button type="submit" class="pd-btn-secondary w-full">{{ __('Save Schedule') }}</button>
                </form>

                @if ($campaign->runs()->doesntExist())
                    <form method="POST" action="{{ route('admin.email-campaigns.destroy', $campaign) }}" onsubmit="return confirm('{{ __('Delete this draft campaign?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pd-btn-danger w-full">{{ __('Delete Campaign') }}</button>
                    </form>
                @endif
            </aside>
        </div>

        <section class="space-y-4">
            <div>
                <p class="pd-kicker">{{ __('Delivery History') }}</p>
                <h2 class="pd-heading mt-2 text-2xl">{{ __('Campaign Runs') }}</h2>
            </div>

            <div class="pd-panel overflow-hidden">
                @if ($runs->isNotEmpty())
                    <div class="hidden md:block">
                        <table class="pd-table">
                            <thead>
                                <tr>
                                    <th class="text-left">{{ __('Started') }}</th>
                                    <th class="text-left">{{ __('Status') }}</th>
                                    <th class="text-left">{{ __('Recipients') }}</th>
                                    <th class="text-left">{{ __('Sent') }}</th>
                                    <th class="text-left">{{ __('Failed') }}</th>
                                    <th class="text-left">{{ __('Skipped') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($runs as $run)
                                    <tr>
                                        <td class="text-sm font-medium text-boss-ivory">{{ $run->started_at->format('M j, Y g:i A') }}</td>
                                        <td class="text-xs capitalize text-boss-ivory/55">{{ $run->status }}</td>
                                        <td class="text-boss-ivory/65">{{ number_format($run->recipient_count) }}</td>
                                        <td class="text-green-300">{{ number_format($run->sent_count) }}</td>
                                        <td class="text-red-300">{{ number_format($run->failed_count) }}</td>
                                        <td class="text-boss-ivory/45">{{ number_format($run->skipped_count) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-white/[0.06] md:hidden">
                        @foreach ($runs as $run)
                            <article class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-boss-ivory">{{ $run->started_at->format('M j, Y') }}</p>
                                        <p class="mt-1 text-xs text-boss-ivory/40">{{ $run->started_at->format('g:i A') }}</p>
                                    </div>
                                    <span class="pd-campaign-status pd-campaign-status-completed capitalize">{{ $run->status }}</span>
                                </div>
                                <dl class="mt-4 grid grid-cols-4 gap-2 text-center text-xs">
                                    <div><dt class="text-boss-ivory/35">{{ __('Total') }}</dt><dd class="mt-1 font-semibold text-boss-ivory">{{ $run->recipient_count }}</dd></div>
                                    <div><dt class="text-boss-ivory/35">{{ __('Sent') }}</dt><dd class="mt-1 font-semibold text-green-300">{{ $run->sent_count }}</dd></div>
                                    <div><dt class="text-boss-ivory/35">{{ __('Failed') }}</dt><dd class="mt-1 font-semibold text-red-300">{{ $run->failed_count }}</dd></div>
                                    <div><dt class="text-boss-ivory/35">{{ __('Skipped') }}</dt><dd class="mt-1 font-semibold text-boss-ivory/55">{{ $run->skipped_count }}</dd></div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <p class="pd-heading text-lg text-boss-ivory/70">{{ __('No deliveries yet') }}</p>
                        <p class="mt-2 text-sm text-boss-ivory/40">{{ __('Delivery results will appear here after this campaign is sent.') }}</p>
                    </div>
                @endif
            </div>

            @if ($runs->hasPages())
                {{ $runs->links() }}
            @endif
        </section>
    </div>
</x-admin-layout>
