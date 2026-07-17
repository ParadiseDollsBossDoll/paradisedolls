<x-admin-layout>
    <div class="pd-email-campaigns mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Communication') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.9rem,4vw,2.6rem)]">{{ __('Email Campaigns') }}</h1>
            </div>
            <a href="{{ route('admin.email-campaigns.create') }}" class="pd-btn-primary w-full shrink-0 sm:w-auto">
                {{ __('New Campaign') }}
            </a>
        </header>

        @if (session('status'))
            <div class="rounded-lg border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="{{ __('Campaign overview') }}">
            <div class="pd-panel min-h-[7.25rem] p-4 sm:p-5">
                <p class="pd-kicker">{{ __('Total Subscribers') }}</p>
                <p class="pd-heading mt-3 text-3xl">{{ number_format($allModelsCount) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/40">{{ __('All models') }}</p>
            </div>
            <div class="pd-panel min-h-[7.25rem] p-4 sm:p-5">
                <p class="pd-kicker">{{ __('Fully Onboarded') }}</p>
                <p class="pd-heading mt-3 text-3xl">{{ number_format($onboardedModelsCount) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/40">{{ __('Ready for campaigns') }}</p>
            </div>
            <div class="pd-panel min-h-[7.25rem] p-4 sm:p-5">
                <p class="pd-kicker">{{ __('Emails Sent') }}</p>
                <p class="pd-heading mt-3 text-3xl">{{ number_format($emailsSentCount) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/40">{{ __('All time') }}</p>
            </div>
            <div class="pd-panel min-h-[7.25rem] p-4 sm:p-5">
                <p class="pd-kicker">{{ __('Preferences') }}</p>
                <p class="pd-heading mt-3 text-3xl">{{ number_format($unsubscribedCount) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/40">{{ __('Unsubscribed') }}</p>
            </div>
        </section>

        @php
            $filters = [
                'all' => __('All'),
                'sent' => __('Sent'),
                'scheduled' => __('Scheduled'),
                'draft' => __('Drafts'),
            ];
            $statusClasses = [
                \App\Models\EmailCampaign::STATUS_ACTIVE => 'pd-campaign-status pd-campaign-status-active',
                \App\Models\EmailCampaign::STATUS_SCHEDULED => 'pd-campaign-status pd-campaign-status-scheduled',
                \App\Models\EmailCampaign::STATUS_PAUSED => 'pd-campaign-status pd-campaign-status-paused',
                \App\Models\EmailCampaign::STATUS_COMPLETED => 'pd-campaign-status pd-campaign-status-completed',
                \App\Models\EmailCampaign::STATUS_DRAFT => 'pd-campaign-status pd-campaign-status-draft',
            ];
            $campaignTimezoneAbbreviation = now(\App\Models\EmailCampaign::schedulingTimezone())->format('T');
        @endphp

        <section class="space-y-4" aria-label="{{ __('Campaign list') }}">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <nav class="flex max-w-full gap-1 overflow-x-auto pb-1" aria-label="{{ __('Campaign filters') }}">
                    @foreach ($filters as $value => $label)
                        <a
                            href="{{ route('admin.email-campaigns.index', array_filter(['filter' => $value === 'all' ? null : $value, 'search' => $search])) }}"
                            @class([
                                'inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-xs transition',
                                'bg-boss-gold/10 font-semibold text-boss-gold' => $filter === $value,
                                'text-boss-ivory/45 hover:bg-white/[0.04] hover:text-boss-ivory/75' => $filter !== $value,
                            ])
                            @if ($filter === $value) aria-current="page" @endif
                        >
                            <span>{{ $label }}</span>
                            <span class="rounded-full bg-white/[0.06] px-1.5 py-0.5 text-[0.6rem]">{{ $filterCounts[$value] }}</span>
                        </a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('admin.email-campaigns.index') }}" class="flex w-full gap-2 md:w-auto">
                    @if ($filter !== 'all')
                        <input type="hidden" name="filter" value="{{ $filter }}">
                    @endif
                    <label for="campaign-search" class="sr-only">{{ __('Search campaigns') }}</label>
                    <input
                        id="campaign-search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        class="pd-input min-w-0 flex-1 md:w-64"
                        placeholder="{{ __('Search campaigns') }}"
                    >
                    <button type="submit" class="pd-btn-secondary shrink-0">{{ __('Search') }}</button>
                </form>
            </div>

            <div class="pd-panel overflow-hidden">
                @if ($campaigns->isNotEmpty())
                    <div class="hidden lg:block">
                        <table class="pd-table table-fixed">
                            <thead>
                                <tr>
                                    <th class="w-[34%] text-left">{{ __('Campaign') }}</th>
                                    <th class="w-[13%] text-left">{{ __('Status') }}</th>
                                    <th class="w-[17%] text-left">{{ __('Recipients') }}</th>
                                    <th class="w-[10%] text-left">{{ __('Sent') }}</th>
                                    <th class="w-[18%] text-left">{{ __('Schedule') }}</th>
                                    <th class="w-[8%] text-right"><span class="sr-only">{{ __('Action') }}</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($campaigns as $campaign)
                                    <tr>
                                        <td>
                                            <p class="truncate font-semibold text-boss-ivory">{{ $campaign->name }}</p>
                                            <p class="mt-1 truncate text-xs text-boss-ivory/45">{{ $campaign->subject }}</p>
                                        </td>
                                        <td><span class="{{ $statusClasses[$campaign->status] ?? $statusClasses[\App\Models\EmailCampaign::STATUS_DRAFT] }}">{{ $campaign->statusLabel() }}</span></td>
                                        <td class="text-xs text-boss-ivory/55">{{ $campaign->audienceLabel() }}</td>
                                        <td class="font-semibold text-boss-ivory">
                                            @if ($campaign->delivered_count > 0)
                                                {{ number_format($campaign->delivered_count) }}
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td class="text-xs text-boss-ivory/45">
                                            @if ($campaign->next_send_at)
                                                <p class="text-boss-ivory/65">{{ $campaign->nextSendAtForAdmin()?->format('M j, Y') }}</p>
                                                <p class="mt-1">{{ $campaign->nextSendAtForAdmin()?->format('g:i A') }} {{ $campaignTimezoneAbbreviation }} @if ($campaign->repeats()) &middot; {{ $campaign->repeatLabel() }} @endif</p>
                                            @elseif ($campaign->last_sent_at)
                                                <p>{{ __('Last sent :date', ['date' => $campaign->lastSentAtForAdmin()?->format('M j, Y')]) }}</p>
                                            @else
                                                <span>&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" class="pd-btn-secondary px-3 py-2">
                                                {{ $campaign->status === \App\Models\EmailCampaign::STATUS_DRAFT ? __('Edit') : __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-white/[0.06] lg:hidden">
                        @foreach ($campaigns as $campaign)
                            <article class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="truncate text-sm font-semibold text-boss-ivory">{{ $campaign->name }}</h2>
                                        <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-boss-ivory/45">{{ $campaign->subject }}</p>
                                    </div>
                                    <span class="{{ $statusClasses[$campaign->status] ?? $statusClasses[\App\Models\EmailCampaign::STATUS_DRAFT] }} shrink-0">{{ $campaign->statusLabel() }}</span>
                                </div>
                                <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-white/[0.06] pt-3 text-xs">
                                    <div>
                                        <dt class="pd-kicker text-[0.56rem]">{{ __('Recipients') }}</dt>
                                        <dd class="mt-1 text-boss-ivory/65">{{ $campaign->audienceLabel() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="pd-kicker text-[0.56rem]">{{ __('Sent') }}</dt>
                                        <dd class="mt-1 text-boss-ivory/65">{{ number_format($campaign->delivered_count ?? 0) }}</dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="pd-kicker text-[0.56rem]">{{ __('Schedule') }}</dt>
                                        <dd class="mt-1 text-boss-ivory/65">
                                            @if ($campaign->next_send_at)
                                                {{ $campaign->nextSendAtForAdmin()?->format('M j, Y g:i A') }} {{ $campaignTimezoneAbbreviation }}
                                                @if ($campaign->repeats()) &middot; {{ $campaign->repeatLabel() }} @endif
                                            @elseif ($campaign->last_sent_at)
                                                {{ __('Last sent :date', ['date' => $campaign->lastSentAtForAdmin()?->format('M j, Y g:i A')]) }} {{ $campaignTimezoneAbbreviation }}
                                            @else
                                                {{ __('Not scheduled') }}
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                                <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" class="pd-btn-secondary mt-4 w-full">
                                    {{ $campaign->status === \App\Models\EmailCampaign::STATUS_DRAFT ? __('Edit Campaign') : __('View Campaign') }}
                                </a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-16 text-center">
                        <p class="pd-heading text-xl text-boss-ivory/70">{{ __('No campaigns found') }}</p>
                        <p class="mx-auto mt-2 max-w-md text-sm text-boss-ivory/40">
                            {{ $search !== '' || $filter !== 'all'
                                ? __('Try another search or campaign status.')
                                : __('Create your first campaign to email your models now or schedule it for later.') }}
                        </p>
                        @if ($search === '' && $filter === 'all')
                            <a href="{{ route('admin.email-campaigns.create') }}" class="pd-btn-primary mt-5">{{ __('New Campaign') }}</a>
                        @endif
                    </div>
                @endif
            </div>

            @if ($campaigns->hasPages())
                {{ $campaigns->links() }}
            @endif
        </section>
    </div>
</x-admin-layout>
