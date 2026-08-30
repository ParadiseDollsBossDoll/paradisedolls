<x-admin-layout>
    @php
        $snapshot = $timesheet->calculation_snapshot ?? [];
        $editable = $timesheet->status !== \App\Models\ChatterTimesheet::STATUS_APPROVED;
        $earningsFormatter = $earnings ?? app(\App\Services\ChatterPlatformEarnings::class);
        $earningSummary = $timesheet->getAttribute('earning_summary') ?? [];
        $displayCommissionPence = (int) ($earningSummary['commission_usd_pence'] ?? $timesheet->commission_pence ?? 0);
        $displayForeignCommissionPence = (int) ($earningSummary['commission_gbp_pence'] ?? $timesheet->foreign_commission_pence ?? 0);
        $generatedUsdPence = (int) ($earningSummary['generated_usd_pence'] ?? 0);
        $generatedGbpPence = (int) ($earningSummary['generated_gbp_pence'] ?? 0);
        $displayGrossPayPence = (int) ($timesheet->getAttribute('display_gross_pay_pence') ?? $timesheet->gross_pay_pence);
        $displayGrossPayPhpCentavos = (int) ($timesheet->getAttribute('display_gross_pay_php_centavos') ?? $grossPayPhpCentavos);
    @endphp
    <div class="mx-auto max-w-[1500px] space-y-6 text-boss-ivory">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('admin.chatter-hours.attendance') }}" class="text-xs text-boss-gold/75 hover:text-boss-gold">{{ __('Back to Weekly Attendance') }}</a>
                <p class="pd-kicker mt-4">{{ __('Timesheet review') }}</p>
                <h1 class="pd-heading mt-2 text-3xl sm:text-4xl">{{ $timesheet->user->name }}</h1>
                <p class="mt-2 text-sm text-boss-ivory/45">{{ $timesheet->period_start->format('D d M') }} - {{ $timesheet->period_end->format('D d M Y') }} - {{ __('Europe/London payroll week') }}</p>
            </div>
            <span class="w-fit rounded-full border border-boss-gold/20 bg-boss-gold/10 px-4 py-2 text-xs font-semibold capitalize text-boss-gold">{{ $timesheet->workflowStatusLabel() }}</span>
        </div>

        @if (session('status'))<div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-200"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-9">
            @php
                $basePayPence = (int) ($timesheet->base_pay_pence ?? data_get($snapshot, 'base_pay_pence', $timesheet->gross_pay_pence - $timesheet->adjustment_pence - $timesheet->commission_pence));
                $summary = [
                    [__('Paid hours'), number_format($timesheet->ordinary_minutes / 60, 2)],
                    [__('Base pay USD'), '$'.number_format($basePayPence / 100, 2)],
                    [__('Generated USD'), '$'.number_format($generatedUsdPence / 100, 2)],
                    [__('Commission USD'), '$'.number_format($displayCommissionPence / 100, 2)],
                    [__('Generated GBP'), 'GBP '.number_format($generatedGbpPence / 100, 2)],
                    [__('Commission GBP'), 'GBP '.number_format($displayForeignCommissionPence / 100, 2)],
                    [__('Adjustments USD'), '$'.number_format($timesheet->adjustment_pence / 100, 2)],
                    [__('Gross USD'), '$'.number_format($displayGrossPayPence / 100, 2)],
                    [__('Final pay PHP'), 'PHP '.number_format($displayGrossPayPhpCentavos / 100, 2)],
                ];
            @endphp
            @foreach ($summary as [$label, $value])
                <article class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-4">
                    <p class="text-[0.57rem] uppercase tracking-[0.13em] text-boss-ivory/35">{{ $label }}</p>
                    <p class="mt-2 font-display text-xl text-boss-gold tabular-nums">{{ $value }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(22rem,0.75fr)]">
            <div class="space-y-6">
                <div class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
                    <div class="flex flex-col gap-2 border-b border-white/[0.06] px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="pd-kicker">{{ __('Recorded time') }}</p>
                            <h2 class="mt-1 font-display text-xl">{{ __('Shifts') }}</h2>
                        </div>
                        <p class="text-xs text-boss-ivory/35">{{ __('Times shown in UK time') }}</p>
                    </div>
                    <div class="divide-y divide-white/[0.06]">
                        @forelse ($shifts as $shift)
                            @php
                                $shiftStart = $shift->clocked_in_at->timezone('Europe/London');
                                $shiftEnd = $shift->clocked_out_at?->timezone('Europe/London');
                                $segmentStart = ($shift->getAttribute('segment_clocked_in_at') ?? $shift->clocked_in_at)->timezone('Europe/London');
                                $segmentEnd = $shift->getAttribute('segment_clocked_out_at')?->timezone('Europe/London');
                                $segmentIsOpen = (bool) $shift->getAttribute('segment_is_open');
                                $workedMinutes = (int) $shift->getAttribute('worked_minutes');
                                $segmentCommissionPence = (int) $shift->getAttribute('segment_commission_pence');
                                $segmentForeignCommissionPence = (int) $shift->getAttribute('segment_foreign_commission_pence');
                                $segmentCommissionCurrency = $shift->getAttribute('segment_foreign_commission_currency') ?: ($shift->commission_currency ?? 'USD');
                                $displayCommissionPence = $segmentForeignCommissionPence > 0 ? $segmentForeignCommissionPence : $segmentCommissionPence;
                            @endphp
                            <details class="group p-5 transition open:bg-white/[0.015] hover:bg-white/[0.02]" @if (!$editable) open @endif>
                                <summary class="flex cursor-pointer list-none flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold">{{ $segmentStart->format('D d M Y') }}</p>
                                            <span class="inline-flex max-w-full rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.65rem] text-boss-gold">
                                                <span class="truncate">
                                                {{ $shift->workRole?->name ?? __('Chatter') }} - ${{ number_format(($shift->hourly_rate_pence ?? 0) / 100, 2) }} USD/hr
                                                </span>
                                            </span>
                                            @if ($shift->platform)
                                                <span class="inline-flex max-w-[14rem] rounded-full bg-white/[0.06] px-2 py-0.5 text-[0.65rem] text-boss-ivory/55">
                                                    <span class="truncate" title="{{ $shift->platform }}">
                                                    {{ $shift->platform }}
                                                    </span>
                                                </span>
                                            @endif
                                            @if ($shift->model)
                                                <span class="inline-flex max-w-[14rem] rounded-full bg-white/[0.06] px-2 py-0.5 text-[0.65rem] text-boss-ivory/55">
                                                    <span class="truncate" title="{{ $shift->model->name }}">
                                                    {{ __('Model: :model', ['model' => $shift->model->name]) }}
                                                    </span>
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-boss-ivory/40 tabular-nums">
                                            {{ $segmentStart->format('g:i A') }} - {{ ($segmentEnd && ! $segmentIsOpen) ? $segmentEnd->format('g:i A') : __('Open') }} UK Time
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-white/[0.06] px-3 py-1 text-xs font-semibold text-boss-gold tabular-nums">{{ $segmentIsOpen ? __('Needs attention') : number_format($workedMinutes / 60, 2).'h' }}</span>
                                </summary>

                                <dl class="mt-4 grid gap-2 border-t border-white/[0.05] pt-4 text-xs sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="min-w-0 rounded-lg border border-white/[0.05] bg-white/[0.02] p-3">
                                        <dt class="pd-label">{{ __('Clock-in balance') }}</dt>
                                        <dd class="mt-1 truncate text-boss-ivory tabular-nums">{{ $earningsFormatter->formatBalance($shift->clock_in_earning_balance_minor, $shift->earning_unit, $shift->earning_currency) }}</dd>
                                    </div>
                                    <div class="min-w-0 rounded-lg border border-white/[0.05] bg-white/[0.02] p-3">
                                        <dt class="pd-label">{{ __('Clock-out balance') }}</dt>
                                        <dd class="mt-1 truncate text-boss-ivory tabular-nums">{{ $earningsFormatter->formatBalance($shift->clock_out_earning_balance_minor, $shift->earning_unit, $shift->earning_currency) }}</dd>
                                    </div>
                                    <div class="min-w-0 rounded-lg border border-white/[0.05] bg-white/[0.02] p-3">
                                        <dt class="pd-label">{{ __('Generated earnings') }}</dt>
                                        <dd class="mt-1 truncate text-boss-ivory tabular-nums">{{ $earningsFormatter->formatGenerated($shift->generated_earning_units, $shift->generated_earning_pence, $shift->earning_unit, $shift->earning_currency) }}</dd>
                                    </div>
                                    <div class="min-w-0 rounded-lg border border-white/[0.05] bg-white/[0.02] p-3">
                                        <dt class="pd-label">{{ __('Commission') }}</dt>
                                        <dd class="mt-1 truncate font-semibold text-emerald-200 tabular-nums">{{ $earningsFormatter->formatCommission($displayCommissionPence, $segmentCommissionCurrency) }}</dd>
                                    </div>
                                </dl>

                                @if ($editable && $shiftEnd)
                                    <form method="POST" action="{{ route('admin.chatter-hours.shifts.update', [$timesheet, $shift]) }}" class="mt-5 grid gap-3 border-t border-white/[0.05] pt-5 sm:grid-cols-2">@csrf @method('PATCH')
                                        <label><span class="pd-label">{{ __('Clock in UK') }}</span><input class="pd-input mt-2" type="datetime-local" name="clocked_in_at" value="{{ $shiftStart->format('Y-m-d\TH:i') }}" required></label>
                                        <label><span class="pd-label">{{ __('Clock out UK') }}</span><input class="pd-input mt-2" type="datetime-local" name="clocked_out_at" value="{{ $shiftEnd->format('Y-m-d\TH:i') }}" required></label>
                                        <label class="sm:col-span-2"><span class="pd-label">{{ __('Correction reason') }}</span><input class="pd-input mt-2" name="reason" required placeholder="{{ __('Required for the audit history') }}"></label>
                                        <button class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs sm:col-span-2">{{ __('Save shift correction') }}</button>
                                    </form>
                                @endif

                            </details>
                        @empty
                            <p class="px-5 py-12 text-center text-sm text-boss-ivory/35">{{ __('No shifts were recorded in this payroll week.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
                    <div class="border-b border-white/[0.06] px-5 py-4">
                        <p class="pd-kicker">{{ __('Traceability') }}</p>
                        <h2 class="mt-1 font-display text-xl">{{ __('Audit history') }}</h2>
                    </div>
                    <div class="divide-y divide-white/[0.05]">
                        @forelse ($timesheet->audits->sortByDesc('created_at') as $audit)
                            <div class="px-5 py-4">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-medium capitalize">{{ str($audit->action)->replace('_', ' ') }}</p>
                                    <p class="text-xs text-boss-ivory/30 tabular-nums">{{ $audit->created_at->timezone('Europe/London')->format('d M Y H:i') }} UK</p>
                                </div>
                                <p class="mt-1 break-words text-xs text-boss-ivory/40">{{ $audit->actor?->name ?? __('System') }}@if ($audit->reason) - {{ $audit->reason }}@endif</p>
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-sm text-boss-ivory/35">{{ __('No corrections or decisions have been recorded yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <section class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-5">
                    <p class="pd-kicker">{{ __('Review') }}</p>
                    <h2 class="mt-1 font-display text-xl">{{ __('Payroll decision') }}</h2>
                    @if ($timesheet->review_note)
                        <p class="mt-3 break-words rounded-lg border border-white/[0.06] bg-white/[0.025] p-3 text-xs leading-5 text-boss-ivory/55">{{ $timesheet->review_note }}</p>
                    @endif

                    @if ($timesheet->status === \App\Models\ChatterTimesheet::STATUS_APPROVED)
                        <p class="mt-3 text-xs leading-5 text-boss-ivory/40">{{ __('Approved payroll is frozen. Reopen it before correcting shifts, breaks, or adjustments.') }}</p>
                        <form method="POST" action="{{ route('admin.chatter-hours.timesheets.review', $timesheet) }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="decision" value="reopen">
                            <textarea class="pd-input" name="note" rows="2" placeholder="{{ __('Reason for reopening') }}" required></textarea>
                            <button class="pd-btn-secondary w-full rounded-lg px-4 py-2.5 text-xs">{{ __('Reopen payroll') }}</button>
                        </form>
                    @elseif ($timesheet->status === \App\Models\ChatterTimesheet::STATUS_REJECTED)
                        <p class="mt-3 text-xs leading-5 text-boss-ivory/40">{{ __('This legacy payroll record is closed and cannot be approved without an administrative data review.') }}</p>
                    @elseif (! $timesheet->periodHasEnded())
                        <p class="mt-3 text-xs leading-5 text-boss-ivory/40">{{ __('This payroll week is still in progress. Approval becomes available after the week ends in UK time.') }}</p>
                    @else
                        @if ($missingModelReviews > 0)
                            <p class="mt-3 rounded-lg border border-amber-400/20 bg-amber-400/10 p-3 text-xs leading-5 text-amber-100">
                                {{ trans_choice(':count required weekly model review is missing.|:count required weekly model reviews are missing.', $missingModelReviews, ['count' => $missingModelReviews]) }}
                            </p>
                        @endif
                        <p class="mt-3 text-xs leading-5 text-boss-ivory/40">{{ __('Review recorded time and adjustments, then approve to freeze the payroll calculation.') }}</p>
                        <form method="POST" action="{{ route('admin.chatter-hours.timesheets.review', $timesheet) }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="decision" value="approve">
                            <textarea class="pd-input" name="note" rows="2" placeholder="{{ __('Optional approval note') }}"></textarea>
                            <button class="pd-btn-primary w-full rounded-lg px-4 py-2.5 text-xs" @disabled($missingModelReviews > 0)>{{ __('Approve payroll') }}</button>
                        </form>
                    @endif
                </section>

                <section class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-5">
                    <p class="pd-kicker">{{ __('Pay') }}</p>
                    <h2 class="mt-1 font-display text-xl">{{ __('Adjustments') }}</h2>
                    <div class="mt-4 space-y-2">
                        @forelse ($timesheet->adjustments as $adjustment)
                            <div class="rounded-lg border border-white/[0.05] bg-white/[0.02] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm" title="{{ $adjustment->label }}">{{ $adjustment->label }}</p>
                                        @if ($adjustment->note)
                                            <p class="mt-1 break-words text-xs text-boss-ivory/35">{{ $adjustment->note }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold tabular-nums {{ $adjustment->amount_pence >= 0 ? 'text-emerald-200' : 'text-red-200' }}">{{ $adjustment->amount_pence >= 0 ? '+' : '-' }}${{ number_format(abs($adjustment->amount_pence) / 100, 2) }} USD</span>
                                </div>
                                @if($editable)
                                    <form method="POST" action="{{ route('admin.chatter-hours.adjustments.destroy', [$timesheet, $adjustment]) }}" class="mt-3 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]">
                                        @csrf
                                        @method('DELETE')
                                        <input class="pd-input min-w-0" name="reason" placeholder="{{ __('Removal reason required') }}" required>
                                        <button class="rounded-lg border border-red-400/20 px-3 py-2 text-xs text-red-200">{{ __('Remove') }}</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-boss-ivory/35">{{ __('No bonuses or deductions.') }}</p>
                        @endforelse
                    </div>
                    @if ($editable)
                        <form method="POST" action="{{ route('admin.chatter-hours.adjustments.store', $timesheet) }}" class="mt-5 space-y-3 border-t border-white/[0.05] pt-5">@csrf<input class="pd-input" name="label" placeholder="{{ __('Bonus or deduction label') }}" required><input class="pd-input" type="number" step="0.01" name="amount" placeholder="{{ __('USD; use a minus for deduction') }}" required><textarea class="pd-input" name="note" rows="2" placeholder="{{ __('Note') }}"></textarea><button class="pd-btn-secondary w-full rounded-lg px-4 py-2.5 text-xs">{{ __('Add adjustment') }}</button></form>
                    @endif
                </section>

                <section class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-5">
                    <p class="pd-kicker">{{ __('Calculation') }}</p>
                    <h2 class="mt-1 font-display text-xl">{{ __('Rate snapshot') }}</h2>
                    <p class="mt-3 text-xs leading-relaxed text-boss-ivory/40">{{ __('Approved timesheets keep this calculated breakdown permanently, so later rate changes do not alter historical records.') }}</p>
                    <dl class="mt-4 space-y-2 text-sm text-boss-ivory/55 tabular-nums">
                        <div class="flex justify-between gap-3"><dt>{{ __('Base currency') }}</dt><dd class="text-boss-ivory">{{ $snapshot['currency'] ?? 'USD' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('USD to PHP rate') }}</dt><dd class="text-boss-ivory">PHP {{ number_format((float) $usdToPhpRate, 4) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('Final pay PHP') }}</dt><dd class="font-semibold text-emerald-200">PHP {{ number_format($grossPayPhpCentavos / 100, 2) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('Calculated') }}</dt><dd class="text-right text-boss-ivory">{{ isset($snapshot['generated_at']) ? \Illuminate\Support\Carbon::parse($snapshot['generated_at'])->timezone('Europe/London')->format('d M Y H:i') : __('Pending') }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('Rate versions') }}</dt><dd class="text-boss-ivory">{{ count($snapshot['rate_versions'] ?? []) }}</dd></div>
                    </dl>
                </section>
            </aside>
        </section>
    </div>
</x-admin-layout>
