<x-admin-layout>
    @php
        $snapshot = $timesheet->calculation_snapshot ?? [];
        $editable = $timesheet->status !== \App\Models\ChatterTimesheet::STATUS_APPROVED;
    @endphp
    <div class="mx-auto max-w-[1400px] space-y-6 text-boss-ivory">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('admin.chatter-hours.attendance') }}" class="text-xs text-boss-gold/75 hover:text-boss-gold">{{ __('Back to Weekly Attendance') }}</a>
                <p class="pd-kicker mt-4">{{ __('Timesheet review') }}</p>
                <h1 class="pd-heading mt-2 text-3xl sm:text-4xl">{{ $timesheet->user->name }}</h1>
                <p class="mt-2 text-sm text-boss-ivory/45">{{ $timesheet->period_start->format('D d M') }} - {{ $timesheet->period_end->format('D d M Y') }} - {{ __('Europe/London payroll week') }}</p>
            </div>
            <span class="w-fit rounded-full border border-white/[0.08] bg-white/[0.04] px-4 py-2 text-xs capitalize text-boss-ivory/65">{{ $timesheet->statusLabel() }}</span>
        </div>

        @if (session('status'))<div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-200"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
            @php
                $summary = [
                    [__('Paid hours'), number_format($timesheet->ordinary_minutes / 60, 2)],
                    [__('Night'), number_format($timesheet->night_minutes / 60, 2).'h'],
                    [__('Weekend'), number_format($timesheet->weekend_minutes / 60, 2).'h'],
                    [__('Overtime'), number_format($timesheet->overtime_minutes / 60, 2).'h'],
                    [__('Adjustments USD'), '$'.number_format($timesheet->adjustment_pence / 100, 2)],
                    [__('Gross USD'), '$'.number_format($timesheet->gross_pay_pence / 100, 2)],
                    [__('Final pay PHP'), '₱'.number_format($grossPayPhpCentavos / 100, 2)],
                ];
            @endphp
            @foreach ($summary as [$label, $value])
                <article class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-4"><p class="text-[0.57rem] uppercase tracking-[0.13em] text-boss-ivory/35">{{ $label }}</p><p class="mt-2 font-display text-xl text-boss-gold">{{ $value }}</p></article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.45fr_0.75fr]">
            <div class="space-y-6">
                <div class="overflow-hidden rounded-xl border border-white/[0.07] bg-white/[0.025]">
                    <div class="border-b border-white/[0.06] px-5 py-4"><p class="pd-kicker">{{ __('Recorded time') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Shifts') }}</h2></div>
                    <div class="divide-y divide-white/[0.06]">
                        @forelse ($shifts as $shift)
                            @php
                                $shiftStart = $shift->clocked_in_at->timezone('Europe/London');
                                $shiftEnd = $shift->clocked_out_at?->timezone('Europe/London');
                                $breakMinutes = $shift->breaks->sum(fn ($break) => $break->ended_at ? $break->started_at->diffInMinutes($break->ended_at) : 0);
                            @endphp
                            <details class="group p-5" @if (!$editable) open @endif>
                                <summary class="flex cursor-pointer list-none flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div><div class="flex flex-wrap items-center gap-2"><p class="font-semibold">{{ $shiftStart->format('D d M Y') }}</p><span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.65rem] text-boss-gold">{{ $shift->workRole?->name ?? __('Chatter') }} · ${{ number_format(($shift->hourly_rate_pence ?? 0) / 100, 2) }} USD/hr</span></div><p class="mt-1 text-xs text-boss-ivory/40">{{ $shiftStart->format('H:i') }} - {{ $shiftEnd?->format('H:i') ?? __('Open') }} UK</p></div>
                                    <span class="text-xs text-boss-gold">{{ $shiftEnd ? number_format(max(0, $shiftStart->diffInMinutes($shiftEnd) - $breakMinutes) / 60, 2).'h' : __('Needs attention') }}</span>
                                </summary>

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

                <div class="overflow-hidden rounded-xl border border-white/[0.07] bg-white/[0.025]">
                    <div class="border-b border-white/[0.06] px-5 py-4"><p class="pd-kicker">{{ __('Traceability') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Audit history') }}</h2></div>
                    <div class="divide-y divide-white/[0.05]">
                        @forelse ($timesheet->audits->sortByDesc('created_at') as $audit)
                            <div class="px-5 py-4"><div class="flex flex-wrap items-center justify-between gap-2"><p class="text-sm font-medium capitalize">{{ str($audit->action)->replace('_', ' ') }}</p><p class="text-xs text-boss-ivory/30">{{ $audit->created_at->timezone('Europe/London')->format('d M Y H:i') }} UK</p></div><p class="mt-1 text-xs text-boss-ivory/40">{{ $audit->actor?->name ?? __('System') }}@if ($audit->reason) - {{ $audit->reason }}@endif</p></div>
                        @empty
                            <p class="px-5 py-10 text-center text-sm text-boss-ivory/35">{{ __('No corrections or decisions have been recorded yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <section class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-5">
                    <p class="pd-kicker">{{ __('Pay') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Adjustments') }}</h2>
                    <div class="mt-4 space-y-2">
                        @forelse ($timesheet->adjustments as $adjustment)
                            <div class="rounded-lg border border-white/[0.05] bg-white/[0.02] p-3"><div class="flex items-start justify-between gap-3"><div><p class="text-sm">{{ $adjustment->label }}</p>@if ($adjustment->note)<p class="mt-1 text-xs text-boss-ivory/35">{{ $adjustment->note }}</p>@endif</div><span class="text-sm font-semibold {{ $adjustment->amount_pence >= 0 ? 'text-emerald-200' : 'text-red-200' }}">{{ $adjustment->amount_pence >= 0 ? '+' : '-' }}${{ number_format(abs($adjustment->amount_pence) / 100, 2) }} USD</span></div>@if($editable)<form method="POST" action="{{ route('admin.chatter-hours.adjustments.destroy', [$timesheet, $adjustment]) }}" class="mt-3 flex gap-2">@csrf @method('DELETE')<input class="pd-input" name="reason" placeholder="{{ __('Removal reason required') }}" required><button class="rounded-lg border border-red-400/20 px-3 text-xs text-red-200">{{ __('Remove') }}</button></form>@endif</div>
                        @empty
                            <p class="text-sm text-boss-ivory/35">{{ __('No bonuses or deductions.') }}</p>
                        @endforelse
                    </div>
                    @if ($editable)
                        <form method="POST" action="{{ route('admin.chatter-hours.adjustments.store', $timesheet) }}" class="mt-5 space-y-3 border-t border-white/[0.05] pt-5">@csrf<input class="pd-input" name="label" placeholder="{{ __('Bonus or deduction label') }}" required><input class="pd-input" type="number" step="0.01" name="amount" placeholder="{{ __('USD; use a minus for deduction') }}" required><textarea class="pd-input" name="note" rows="2" placeholder="{{ __('Note') }}"></textarea><button class="pd-btn-secondary w-full rounded-lg px-4 py-2.5 text-xs">{{ __('Add adjustment') }}</button></form>
                    @endif
                </section>

                <section class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-5">
                    <p class="pd-kicker">{{ __('Calculation') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Rate snapshot') }}</h2>
                    <p class="mt-3 text-xs leading-relaxed text-boss-ivory/40">{{ __('Approved timesheets keep this calculated breakdown permanently, so later rate changes do not alter historical records.') }}</p>
                    <dl class="mt-4 space-y-2 text-sm text-boss-ivory/55">
                        <div class="flex justify-between gap-3"><dt>{{ __('Base currency') }}</dt><dd class="text-boss-ivory">{{ $snapshot['currency'] ?? 'USD' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('USD to PHP rate') }}</dt><dd class="text-boss-ivory">₱{{ number_format((float) $usdToPhpRate, 4) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('Final pay PHP') }}</dt><dd class="font-semibold text-emerald-200">₱{{ number_format($grossPayPhpCentavos / 100, 2) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('Calculated') }}</dt><dd class="text-right text-boss-ivory">{{ isset($snapshot['generated_at']) ? \Illuminate\Support\Carbon::parse($snapshot['generated_at'])->timezone('Europe/London')->format('d M Y H:i') : __('Pending') }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ __('Rate versions') }}</dt><dd class="text-boss-ivory">{{ count($snapshot['rate_versions'] ?? []) }}</dd></div>
                    </dl>
                </section>
            </aside>
        </section>
    </div>
</x-admin-layout>
