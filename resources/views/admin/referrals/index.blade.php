<x-admin-layout>
    <div class="mx-auto max-w-[1500px] space-y-7 text-boss-ivory">
        <header class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Referral Program') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2.15rem,4vw,3rem)]">{{ __('Referrals') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-boss-ivory/[0.45]">
                    {{ __('Track which models are referring candidates, review reward eligibility, and follow recent referral activity.') }}
                </p>
            </div>

            <a href="{{ route('admin.applications.index') }}" class="pd-btn-secondary h-11">
                {{ __('Open Applications') }}
            </a>
        </header>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Total Referrals') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['total']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.32]">{{ __('All submitted referral records') }}</p>
            </div>

            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Referrers') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['active_referrers']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.32]">{{ __('Models with referrals') }}</p>
            </div>

            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Needs Review') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['leads']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.32]">{{ __('Member-submitted leads') }}</p>
            </div>

            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Joined') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['joined']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.32]">{{ __('Approved referred models') }}</p>
            </div>

            <div class="rounded-2xl border border-boss-gold/15 bg-boss-gold/[0.07] p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold/70">{{ __('Rewards Due') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['eligible_rewards']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.38]">{{ number_format($summary['paid_rewards']) }} {{ __('paid rewards') }}</p>
            </div>
        </section>

        <section class="rounded-2xl border border-white/[0.07] bg-boss-panel-strong p-4 shadow-[0_24px_70px_rgba(0,0,0,0.22)]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/[0.35]">{{ __('Model Directory') }}</p>
                    <h2 class="mt-1 font-display text-2xl text-boss-ivory">{{ __('Referral Counts By Model') }}</h2>
                    <p class="mt-1 text-xs text-boss-ivory/[0.35]">
                        @if ($referrers->total() > 0)
                            {{ __('Showing') }} {{ $referrers->firstItem() }}-{{ $referrers->lastItem() }} {{ __('of') }} {{ number_format($referrers->total()) }}
                        @else
                            {{ __('No matching models') }}
                        @endif
                    </p>
                </div>

                <form method="GET" action="{{ route('admin.referrals.index') }}" class="flex w-full flex-col gap-3 lg:max-w-3xl">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-boss-ivory/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.75 18.5a7.75 7.75 0 1 1 0-15.5 7.75 7.75 0 0 1 0 15.5Z" />
                            </svg>
                            <label for="referral-search" class="sr-only">{{ __('Search models') }}</label>
                            <input
                                id="referral-search"
                                name="search"
                                type="search"
                                value="{{ $search }}"
                                class="pd-input h-12 pl-10"
                                placeholder="{{ __('Search models or referral code') }}"
                            >
                        </div>

                        <button type="submit" class="pd-btn-secondary h-12 whitespace-nowrap">{{ __('Search') }}</button>

                        @if ($search !== '' || $onlyWithReferrals)
                            <a href="{{ route('admin.referrals.index') }}" class="pd-btn-secondary h-12 whitespace-nowrap">{{ __('Clear') }}</a>
                        @endif
                    </div>

                    <label for="with-referrals" class="flex w-fit items-center gap-2 text-xs text-boss-ivory/[0.42]">
                        <input id="with-referrals" type="checkbox" name="with_referrals" value="1" class="rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" @checked($onlyWithReferrals)>
                        <span>{{ __('Only show models with referrals') }}</span>
                    </label>
                </form>
            </div>

            <div class="mt-5 overflow-hidden rounded-xl border border-white/[0.06]">
                <div class="overflow-x-auto">
                    <table class="pd-table min-w-full">
                        <thead>
                            <tr>
                                <th class="text-left">{{ __('Model') }}</th>
                                <th class="text-left">{{ __('Code') }}</th>
                                <th class="text-center">{{ __('Total') }}</th>
                                <th class="text-center">{{ __('Leads') }}</th>
                                <th class="text-center">{{ __('Applications') }}</th>
                                <th class="text-center">{{ __('Joined') }}</th>
                                <th class="text-center">{{ __('Rewards') }}</th>
                                <th class="text-left">{{ __('Latest') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($referrers as $referrer)
                                <tr>
                                    <td class="align-middle">
                                        <div class="flex items-center gap-3">
                                            <div class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-boss-gold/[0.22] bg-[radial-gradient(circle_at_top,rgba(238, 180, 195, 0.32),rgba(19,15,18,0.94)_70%)] font-display text-sm text-boss-gold-light">
                                                <span>{{ $referrer->initials() }}</span>
                                                @if ($referrer->profilePhotoUrl())
                                                    <img class="absolute inset-0 h-full w-full object-cover" src="{{ $referrer->profilePhotoUrl() }}" alt="{{ __('Profile photo') }}" onerror="this.remove()">
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-boss-ivory">{{ $referrer->name }}</p>
                                                <p class="mt-0.5 truncate text-xs text-boss-ivory/[0.38]">{{ $referrer->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if ($referrer->referral_code)
                                            <div class="space-y-1">
                                                <p class="font-mono text-xs text-boss-gold-light">{{ $referrer->referral_code }}</p>
                                                <a href="{{ route('apply', ['ref' => $referrer->referral_code]) }}" target="_blank" rel="noopener" class="text-[0.68rem] text-boss-ivory/[0.34] transition hover:text-boss-gold">
                                                    {{ __('Open link') }}
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-xs text-boss-ivory/[0.28]">{{ __('No code') }}</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center font-display text-xl text-boss-gold-light">{{ number_format($referrer->referrals_count) }}</td>
                                    <td class="align-middle text-center text-sm text-boss-ivory/60">{{ number_format($referrer->lead_referrals_count) }}</td>
                                    <td class="align-middle text-center text-sm text-boss-ivory/60">{{ number_format($referrer->pending_referrals_count) }}</td>
                                    <td class="align-middle text-center text-sm text-emerald-200">{{ number_format($referrer->joined_referrals_count) }}</td>
                                    <td class="align-middle text-center">
                                        <span class="rounded-full border border-boss-gold/15 bg-boss-gold/[0.07] px-2.5 py-1 text-[0.65rem] text-boss-gold-light">
                                            {{ number_format($referrer->eligible_rewards_count) }} {{ __('due') }}
                                        </span>
                                        <span class="ml-1 text-[0.65rem] text-boss-ivory/[0.35]">
                                            {{ number_format($referrer->paid_rewards_count) }} {{ __('paid') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-sm text-boss-ivory/[0.42]">
                                        @if ($referrer->latest_referral_at)
                                            {{ \Illuminate\Support\Carbon::parse($referrer->latest_referral_at)->diffForHumans() }}
                                        @else
                                            {{ __('None yet') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-boss-ivory/35">{{ __('No models match that search.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($referrers->hasPages())
                <div class="mt-5 flex flex-col gap-3 border-t border-white/[0.06] pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-boss-ivory/[0.35]">
                        {{ __('Page') }} {{ $referrers->currentPage() }} {{ __('of') }} {{ $referrers->lastPage() }}
                    </p>
                    <div class="flex items-center gap-2">
                        @if ($referrers->onFirstPage())
                            <span class="pd-btn-secondary h-10 cursor-not-allowed opacity-35">{{ __('Previous') }}</span>
                        @else
                            <a href="{{ $referrers->previousPageUrl() }}" class="pd-btn-secondary h-10">{{ __('Previous') }}</a>
                        @endif

                        @if ($referrers->hasMorePages())
                            <a href="{{ $referrers->nextPageUrl() }}" class="pd-btn-secondary h-10">{{ __('Next') }}</a>
                        @else
                            <span class="pd-btn-secondary h-10 cursor-not-allowed opacity-35">{{ __('Next') }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-white/[0.07] bg-boss-panel-strong p-4 shadow-[0_24px_70px_rgba(0,0,0,0.22)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/[0.35]">{{ __('Activity') }}</p>
                    <h2 class="mt-1 font-display text-2xl text-boss-ivory">{{ __('Recent Referrals') }}</h2>
                </div>
                <p class="text-xs text-boss-ivory/[0.36]">{{ __('Convert and reward actions still use the same Applications workflow.') }}</p>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse ($recentReferrals as $referral)
                    @php
                        $statusClasses = match ($referral->status) {
                            \App\Models\ModelReferral::STATUS_JOINED => 'border-emerald-300/20 bg-emerald-300/10 text-emerald-200',
                            \App\Models\ModelReferral::STATUS_PENDING => 'border-boss-gold/20 bg-boss-gold/10 text-boss-gold-light',
                            \App\Models\ModelReferral::STATUS_REJECTED => 'border-red-400/20 bg-red-400/10 text-red-300',
                            default => 'border-white/[0.08] bg-white/[0.03] text-boss-ivory/55',
                        };
                    @endphp

                    <article class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-boss-ivory">{{ $referral->candidate_name }}</p>
                                    <span class="rounded-full border px-2.5 py-1 text-[0.65rem] {{ $statusClasses }}">{{ $referral->statusLabel() }}</span>
                                    <span class="rounded-full border border-boss-gold/15 bg-boss-gold/[0.07] px-2.5 py-1 text-[0.65rem] text-boss-gold-light">{{ $referral->rewardStatusLabel() }}</span>
                                </div>

                                <p class="mt-1 truncate text-sm text-boss-ivory/[0.42]">
                                    {{ $referral->candidate_email ?? $referral->candidate_phone ?? $referral->candidate_social_handle }}
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2 text-[0.68rem] text-boss-ivory/[0.34]">
                                    <span>{{ __('Referred by') }} <span class="text-boss-gold-light">{{ $referral->referrer?->name }}</span></span>
                                    <span>{{ __('Submitted') }} {{ $referral->created_at->toFormattedDateString() }}</span>
                                    <span>{{ \Illuminate\Support\Str::of($referral->source)->replace('_', ' ')->title() }}</span>
                                    <span>{{ count($referral->photo_paths ?? []) }} {{ __('photos') }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                @if ($referral->model_application_id)
                                    <a href="{{ route('admin.applications.index') }}" class="pd-btn-secondary h-10">{{ __('View Applications') }}</a>
                                @elseif ($referral->status !== \App\Models\ModelReferral::STATUS_REJECTED)
                                    @if ($referral->candidate_email)
                                        <form method="POST" action="{{ route('admin.applications.referrals.convert', $referral) }}">
                                            @csrf
                                            <button type="submit" class="pd-btn-primary">{{ __('Convert') }}</button>
                                        </form>
                                    @else
                                        <span class="rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-[0.68rem] text-boss-ivory/38">{{ __('Needs email') }}</span>
                                    @endif

                                    <form method="POST" action="{{ route('admin.applications.referrals.reject', $referral) }}">
                                        @csrf
                                        <button type="submit" class="rounded-xl border border-red-400/25 bg-red-400/10 px-3 py-2 text-[0.68rem] font-semibold text-red-300 transition hover:bg-red-400/20">{{ __('Reject') }}</button>
                                    </form>
                                @endif

                                @if ($referral->reward_status === \App\Models\ModelReferral::REWARD_ELIGIBLE)
                                    <form method="POST" action="{{ route('admin.applications.referrals.reward-paid', $referral) }}">
                                        @csrf
                                        <button type="submit" class="rounded-xl border border-boss-gold/25 bg-boss-gold/10 px-3 py-2 text-[0.68rem] font-semibold text-boss-gold transition hover:bg-boss-gold hover:text-boss-ink">{{ __('Mark Paid') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-white/[0.08] bg-white/[0.02] p-8 text-center text-sm text-boss-ivory/[0.38]">
                        {{ __('No referral activity yet.') }}
                    </div>
                @endforelse
            </div>

            @if ($recentReferrals->hasPages())
                <div class="mt-5">{{ $recentReferrals->links() }}</div>
            @endif
        </section>
    </div>
</x-admin-layout>


