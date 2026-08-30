<x-admin-layout>
    @php
        $mode = $mode ?? 'accounts';
        $formatMinutes = fn (int $minutes) => intdiv($minutes, 60).'h '.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT).'m';
        $applicationUrl = route('chatter.apply');
        $defaultWorkRoleId = $workRoles->firstWhere('slug', 'chatter')?->id ?? $workRoles->first()?->id;
        $timezoneOptions = $timezoneOptions ?? collect();
        $earningsFormatter = $earnings ?? app(\App\Services\ChatterPlatformEarnings::class);
        $exportQuery = $mode === 'attendance'
            ? collect($filters ?? [])->except('role_id')->filter(fn ($value) => filled($value))->all()
            : request()->query();
    @endphp
    <div
        class="mx-auto max-w-[1500px] space-y-6 text-boss-ivory"
        x-data="{
            createOpen: false,
            copied: false,
            applicationUrl: @js($applicationUrl),
            copyApplicationLink() {
                const done = () => { this.copied = true; setTimeout(() => this.copied = false, 2200) };
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(this.applicationUrl).then(done);
                    return;
                }
                const field = document.createElement('textarea');
                field.value = this.applicationUrl;
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.appendChild(field);
                field.select();
                document.execCommand('copy');
                field.remove();
                done();
            }
        }"
    >
        <header class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Workforce') }}</p>
                <h1 class="pd-heading mt-2 text-3xl sm:text-4xl">{{ __('Chatter Hours') }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-boss-ivory/45">{{ $mode === 'attendance' ? __('Weekly attendance, timesheet review, and estimated payroll. Reports use Europe/London time, USD base pay, and PHP conversion.') : __('Manage chatter accounts, invitations, work roles, and hourly rates.') }}</p>
            </div>
            <div class="grid gap-2 sm:grid-cols-2 xl:flex xl:flex-wrap xl:justify-end">
                @if ($mode === 'attendance')
                    <a href="{{ route('admin.chatter-hours.index') }}" class="pd-btn-secondary inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-xs">{{ __('Chatter accounts') }}</a>
                    <a href="{{ route('admin.chatter-hours.export.xlsx', $exportQuery) }}" class="pd-btn-secondary inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-xs">{{ __('Export Excel') }}</a>
                @else
                    <button type="button" class="pd-btn-secondary inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-xs" @click="copyApplicationLink()">
                        <svg class="h-4 w-4 fill-none stroke-current" viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M15 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3"/></svg>
                        <span x-text="copied ? @js(__('Link copied')) : @js(__('Copy application link'))"></span>
                    </button>
                    <a href="{{ route('admin.chatter-hours.attendance') }}" class="pd-btn-secondary inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-xs">{{ __('Weekly attendance') }}</a>
                    <button type="button" class="pd-btn-primary rounded-lg px-4 py-2.5 text-xs" @click="createOpen = true">{{ __('Create chatter') }}</button>
                @endif
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
        @endif
        <datalist id="chatter-work-role-options">
            @foreach($workRoles as $role)
                <option value="{{ $role->name }}"></option>
            @endforeach
        </datalist>
        @if ($errors->any())
            <div class="rounded-lg border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-200">
                <p class="font-semibold">{{ __('Please check the highlighted information.') }}</p>
                <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

            @php
                $statCards = $mode === 'attendance'
                    ? [
                        [__('Chatters'), $stats['chatters']],
                        [__('Working now'), $stats['working']],
                        [__('Pending review'), $stats['pending']],
                        [__('Join requests'), $stats['requests']],
                        [__('Filtered hours'), $formatMinutes($stats['total_minutes'])],
                        [__('Generated USD'), '$'.number_format(($stats['generated_usd_pence'] ?? 0) / 100, 2)],
                        [__('Commission USD'), '$'.number_format(($stats['commission_pence'] ?? 0) / 100, 2)],
                        [__('Commission GBP'), 'GBP '.number_format(($stats['foreign_commission_pence'] ?? 0) / 100, 2)],
                        [__('Gross USD'), '$'.number_format($stats['gross_pay_pence'] / 100, 2)],
                        [__('Gross PHP'), 'PHP '.number_format($stats['gross_pay_php_centavos'] / 100, 2)],
                    ]
                    : [
                        [__('Chatters'), $stats['chatters']],
                        [__('Working now'), $stats['working']],
                        [__('Join requests'), $stats['requests']],
                    ];
            @endphp
            <section class="grid grid-cols-2 gap-3 md:grid-cols-3 {{ $mode === 'attendance' ? 'xl:grid-cols-10' : 'xl:grid-cols-3' }}">
            @foreach ($statCards as [$label, $value])
                <article class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-4">
                    <p class="text-[0.58rem] uppercase tracking-[0.14em] text-boss-ivory/35">{{ $label }}</p>
                    <p class="mt-2 font-display text-xl text-boss-gold sm:text-2xl">{{ $value }}</p>
                </article>
            @endforeach
        </section>

        @if ($mode === 'attendance')
        <section class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="pd-kicker">{{ __('Currency conversion') }}</p>
                    <h2 class="mt-1 font-display text-xl">{{ __('USD to Philippine peso') }}</h2>
                    <p class="mt-2 text-xs text-boss-ivory/40">{{ __('The latest reference rate is refreshed automatically. Approved timesheets keep the exact rate and PHP total saved at approval.') }}</p>
                </div>
                <div class="min-w-0 rounded-lg border border-white/[0.07] bg-black/10 px-4 py-3 text-left lg:min-w-[300px] lg:text-right">
                    <p class="pd-label">{{ __('Automatic reference rate') }}</p>
                    <p class="mt-1 text-lg font-semibold text-boss-gold">{{ __('1 USD = :rate PHP', ['rate' => $usdToPhpRate]) }}</p>
                    @if ($currencyDetails['is_fallback'])
                        <p class="mt-1 text-xs text-amber-200/80">{{ __('Protected fallback rate in use. Automatic refresh will retry shortly.') }}</p>
                    @elseif ($currencyDetails['is_stale'])
                        <p class="mt-1 text-xs text-amber-200/80">{{ __('Last known rate in use. Automatic refresh will retry shortly.') }}</p>
                    @else
                        <p class="mt-1 text-xs text-boss-ivory/40">
                            {{ __('Rate date: :date', ['date' => $currencyDetails['rate_date'] ?: __('latest available')]) }}
                            @if ($currencyDetails['fetched_at'])
                                · {{ __('Updated :time', ['time' => \Carbon\CarbonImmutable::parse($currencyDetails['fetched_at'])->timezone('Europe/London')->format('d M Y, H:i')]) }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-5">
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="pd-kicker">{{ __('Report controls') }}</p>
                    <h2 class="mt-1 font-display text-xl">{{ __('Filter attendance and payroll') }}</h2>
                </div>
                <div class="min-w-0 rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-4 py-3 text-sm lg:text-right">
                    <p class="pd-label">{{ __('UK payroll time') }}</p>
                    <p class="mt-1 font-semibold text-boss-gold">{{ $ukNow->format('D, d M Y g:i A') }} {{ __('UK Time') }}</p>
                    <p class="mt-1 text-xs text-boss-ivory/45">{{ __('Viewing payroll week: :period', ['period' => $payrollPeriodLabel]) }}</p>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.chatter-hours.attendance') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.3fr_1fr_1fr_1fr_0.8fr_0.8fr_auto] xl:items-end">
                <label><span class="pd-label">{{ __('Search') }}</span><input class="pd-input mt-2" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('Name or email') }}"></label>
                <label><span class="pd-label">{{ __('Chatter') }}</span><select class="pd-input mt-2" name="chatter_id"><option value="">{{ __('All chatters') }}</option>@foreach ($chatterOptions as $option)<option value="{{ $option->id }}" @selected((int) $filters['chatter_id'] === $option->id)>{{ $option->name }}</option>@endforeach</select></label>
                <label><span class="pd-label">{{ __('Work role') }}</span><select class="pd-input mt-2" name="role_id"><option value="">{{ __('All roles') }}</option>@foreach ($workRoles as $role)<option value="{{ $role->id }}" @selected((int) $filters['role_id'] === $role->id)>{{ $role->name }}</option>@endforeach</select></label>
                <label><span class="pd-label">{{ __('Timesheet status') }}</span><select class="pd-input mt-2" name="status"><option value="">{{ __('All statuses') }}</option>@foreach (['in_progress' => __('In progress'), 'ready_for_review' => __('Ready for review'), 'approved' => __('Approved'), 'closed' => __('Closed - No payroll')] as $status => $label)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $label }}</option>@endforeach</select></label>
                <label><span class="pd-label">{{ __('From') }}</span><input class="pd-input mt-2" type="date" name="from" value="{{ $filters['from'] }}"></label>
                <label><span class="pd-label">{{ __('To') }}</span><input class="pd-input mt-2" type="date" name="to" value="{{ $filters['to'] }}"></label>
                <button class="pd-btn-primary h-[43px] rounded-lg px-5 text-xs">{{ __('Apply') }}</button>
            </form>
            @if($filters['role_id'])<p class="mt-3 text-xs text-boss-ivory/35">{{ __('The role filter narrows the attendance log. Payroll totals remain complete for each matching chatter and payroll period.') }}</p>@endif
        </section>

        <section class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
            <div class="flex flex-col gap-2 border-b border-white/[0.06] px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="pd-kicker">{{ __('Attendance log') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Recorded work sessions') }}</h2></div>
                <p class="text-xs text-boss-ivory/35">{{ $attendanceShifts->total() }} {{ __('shifts') }} · {{ __('Times shown in UK time') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1240px] text-left text-sm tabular-nums">
                    <thead class="border-b border-white/[0.06] text-[0.6rem] uppercase tracking-[0.13em] text-boss-ivory/35">
                        <tr><th class="px-5 py-3">{{ __('Date / time in') }}</th><th class="px-4 py-3">{{ __('Date / time out') }}</th><th class="px-4 py-3">{{ __('Employee') }}</th><th class="px-4 py-3">{{ __('Work role / site / model') }}</th><th class="px-4 py-3">{{ __('Earnings') }}</th><th class="px-4 py-3">{{ __('Hours worked') }}</th><th class="px-5 py-3">{{ __('Status') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($attendanceShifts as $shift)
                            @php
                                $activeBreak = $shift->breaks->firstWhere('ended_at', null);
                                $overdue = !$shift->clocked_out_at && $shift->clocked_in_at->lt(now()->subHours(16));
                                $segmentStart = $shift->getAttribute('segment_clocked_in_at') ?? $shift->clocked_in_at;
                                $segmentEnd = $shift->getAttribute('segment_clocked_out_at') ?? $shift->clocked_out_at;
                                $segmentIsOpen = (bool) $shift->getAttribute('segment_is_open');
                                $shiftCommission = [
                                    'currency' => $shift->getAttribute('segment_commission_currency') ?: ($shift->commission_currency ?? 'USD'),
                                    'pence' => (int) $shift->getAttribute('segment_commission_pence'),
                                ];
                            @endphp
                            <tr class="align-top transition hover:bg-white/[0.02]">
                                <td class="whitespace-nowrap px-5 py-4"><p class="font-medium">{{ $segmentStart->timezone('Europe/London')->format('D, d M Y') }}</p><p class="mt-0.5 text-xs text-boss-ivory/40">{{ $segmentStart->timezone('Europe/London')->format('g:i A') }} UK Time</p></td>
                                <td class="whitespace-nowrap px-4 py-4">@if($segmentEnd && ! $segmentIsOpen)<p>{{ $segmentEnd->timezone('Europe/London')->format('D, d M Y') }}</p><p class="mt-0.5 text-xs text-boss-ivory/40">{{ $segmentEnd->timezone('Europe/London')->format('g:i A') }} UK Time</p>@else<span class="text-emerald-200">{{ __('Still working') }}</span>@endif</td>
                                <td class="px-4 py-4"><p class="max-w-[13rem] truncate font-medium" title="{{ $shift->user->name }}">{{ $shift->user->name }}</p><p class="mt-0.5 max-w-[13rem] truncate text-xs text-boss-ivory/35" title="{{ $shift->user->email }}">{{ $shift->user->email }}</p></td>
                                <td class="min-w-[16rem] px-4 py-4">
                                    <span class="inline-flex max-w-[15rem] rounded-full bg-boss-gold/10 px-2.5 py-1 text-xs text-boss-gold"><span class="truncate" title="{{ $shift->workRole?->name ?? __('Chatter') }}">{{ $shift->workRole?->name ?? __('Chatter') }}</span></span>
                                    <p class="mt-2 text-xs text-boss-ivory/40">${{ number_format(($shift->hourly_rate_pence ?? 0) / 100, 2) }} USD/hr</p>
                                    @if($shift->platform)
                                        <p class="mt-1 max-w-[15rem] truncate text-xs text-boss-ivory/35" title="{{ $shift->platform }}">{{ $shift->platform }}</p>
                                    @endif
                                    @if($shift->model)
                                        <p class="mt-1 max-w-[15rem] truncate text-xs text-boss-ivory/35" title="{{ $shift->model->name }}">{{ __('Model: :model', ['model' => $shift->model->name]) }}</p>
                                    @endif
                                </td>
                                <td class="min-w-[15rem] px-4 py-4">
                                    <div class="grid gap-1.5 text-xs text-boss-ivory/45">
                                        <p class="flex min-w-0 items-center justify-between gap-3"><span>{{ __('Balance in') }}</span><span class="truncate text-right text-boss-ivory" title="{{ $earningsFormatter->formatBalance($shift->clock_in_earning_balance_minor, $shift->earning_unit, $shift->earning_currency) }}">{{ $earningsFormatter->formatBalance($shift->clock_in_earning_balance_minor, $shift->earning_unit, $shift->earning_currency) }}</span></p>
                                        <p class="flex min-w-0 items-center justify-between gap-3"><span>{{ __('Balance out') }}</span><span class="truncate text-right text-boss-ivory" title="{{ $earningsFormatter->formatBalance($shift->clock_out_earning_balance_minor, $shift->earning_unit, $shift->earning_currency) }}">{{ $earningsFormatter->formatBalance($shift->clock_out_earning_balance_minor, $shift->earning_unit, $shift->earning_currency) }}</span></p>
                                        <p class="flex min-w-0 items-center justify-between gap-3"><span>{{ __('Generated') }}</span><span class="truncate text-right text-boss-ivory" title="{{ $earningsFormatter->formatGenerated($shift->generated_earning_units, $shift->generated_earning_pence, $shift->earning_unit, $shift->earning_currency) }}">{{ $earningsFormatter->formatGenerated($shift->generated_earning_units, $shift->generated_earning_pence, $shift->earning_unit, $shift->earning_currency) }}</span></p>
                                        <p class="flex min-w-0 items-center justify-between gap-3 font-semibold"><span>{{ __('Commission') }}</span><span class="truncate text-right text-emerald-200" title="{{ $earningsFormatter->formatCommission($shiftCommission['pence'], $shiftCommission['currency']) }}">{{ $earningsFormatter->formatCommission($shiftCommission['pence'], $shiftCommission['currency']) }}</span></p>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-semibold text-boss-gold">{{ $formatMinutes((int) $shift->getAttribute('worked_minutes')) }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[0.65rem] font-semibold {{ $overdue ? 'bg-red-400/15 text-red-200' : ($activeBreak ? 'bg-amber-400/15 text-amber-200' : ($shift->clocked_out_at ? 'bg-white/[0.06] text-boss-ivory/55' : 'bg-emerald-400/15 text-emerald-200')) }}">{{ $overdue ? __('Overdue') : ($activeBreak ? __('On break') : ($shift->clocked_out_at ? __('Completed') : __('Working'))) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-boss-ivory/35">{{ __('No attendance records match these filters.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendanceShifts->hasPages())<div class="border-t border-white/[0.06] px-5 py-4">{{ $attendanceShifts->links() }}</div>@endif
        </section>

        <section class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
            <div class="flex flex-col gap-2 border-b border-white/[0.06] px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="pd-kicker">{{ __('Payroll') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Weekly payroll') }}</h2></div>
                <p class="text-xs text-boss-ivory/35">{{ $timesheets->total() }} {{ __('payroll periods') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1720px] text-left text-sm tabular-nums">
                    <thead class="border-b border-white/[0.06] text-[0.6rem] uppercase tracking-[0.13em] text-boss-ivory/35">
                        <tr><th class="px-5 py-3">{{ __('Chatter') }}</th><th class="px-4 py-3">{{ __('Payroll week') }}</th><th class="px-4 py-3">{{ __('Total hours') }}</th><th class="px-4 py-3">{{ __('Rate') }}</th><th class="px-4 py-3">{{ __('Base pay') }}</th><th class="px-4 py-3">{{ __('USD commission') }}</th><th class="px-4 py-3">{{ __('GBP commission') }}</th><th class="px-4 py-3">{{ __('Additional') }}</th><th class="px-4 py-3">{{ __('US final pay') }}</th><th class="px-4 py-3">{{ __('PH final pay') }}</th><th class="px-4 py-3">{{ __('Notes') }}</th><th class="px-4 py-3">{{ __('Status') }}</th><th class="px-5 py-3 text-right">{{ __('Action') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($timesheets as $sheet)
                            @php
                                $earningSummary = $sheet->getAttribute('earning_summary') ?? [];
                                $displayCommissionPence = (int) ($earningSummary['commission_usd_pence'] ?? $sheet->commission_pence ?? 0);
                                $displayForeignCommissionPence = (int) ($earningSummary['commission_gbp_pence'] ?? $sheet->foreign_commission_pence ?? 0);
                                $generatedUsdPence = (int) ($earningSummary['generated_usd_pence'] ?? 0);
                                $generatedGbpPence = (int) ($earningSummary['generated_gbp_pence'] ?? 0);
                            @endphp
                            <tr class="align-top transition hover:bg-white/[0.02]">
                                <td class="px-5 py-4"><p class="max-w-[13rem] truncate font-medium" title="{{ $sheet->user->name }}">{{ $sheet->user->name }}</p><p class="mt-0.5 max-w-[13rem] truncate text-xs text-boss-ivory/35" title="{{ $sheet->user->email }}">{{ $sheet->user->email }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $sheet->period_start->format('d M') }} - {{ $sheet->period_end->format('d M Y') }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $formatMinutes($sheet->ordinary_minutes) }}</td>
                                <td class="px-4 py-4">
                                    <div class="space-y-2">
                                        @forelse($sheet->getAttribute('payroll_rates') as $rate)
                                            <div class="min-w-0">
                                                @if($rate['role'])<p class="max-w-[10rem] truncate text-xs text-boss-ivory/45" title="{{ $rate['role'] }}">{{ $rate['role'] }}</p>@endif
                                                <p class="font-medium">${{ number_format($rate['hourly_rate_pence'] / 100, 2) }} USD/hr</p>
                                            </div>
                                        @empty
                                            <p class="text-xs text-boss-ivory/35">{{ __('No rate recorded') }}</p>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-4"><p class="font-semibold">${{ number_format($sheet->getAttribute('basic_pay_pence') / 100, 2) }}</p><p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('Hourly only') }}</p></td>
                                <td class="px-4 py-4"><p class="font-semibold text-emerald-200">${{ number_format($displayCommissionPence / 100, 2) }}</p><p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('Generated: $:amount USD', ['amount' => number_format($generatedUsdPence / 100, 2)]) }}</p></td>
                                <td class="px-4 py-4"><p class="font-semibold text-emerald-200">GBP {{ number_format($displayForeignCommissionPence / 100, 2) }}</p><p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('Generated: GBP :amount', ['amount' => number_format($generatedGbpPence / 100, 2)]) }}</p><p class="mt-1 text-[0.65rem] text-boss-ivory/30">{{ __('Not in USD total') }}</p></td>
                                @php
                                    $additionalPence = (int) $sheet->adjustment_pence;
                                @endphp
                                <td class="px-4 py-4 font-semibold {{ $additionalPence > 0 ? 'text-emerald-200' : ($additionalPence < 0 ? 'text-red-200' : '') }}">
                                    @if($additionalPence === 0)
                                        $0.00
                                    @else
                                        {{ $additionalPence > 0 ? '+' : '-' }}${{ number_format(abs($additionalPence) / 100, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-4 font-semibold text-boss-gold">${{ number_format(($sheet->getAttribute('display_gross_pay_pence') ?? $sheet->gross_pay_pence) / 100, 2) }}</td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-emerald-200">PHP {{ number_format(($sheet->getAttribute('display_gross_pay_php_centavos') ?? $currency->phpCentavosForTimesheet($sheet)) / 100, 2) }}</p>
                                    <p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('1 USD = PHP :rate', ['rate' => $currency->rateForTimesheet($sheet)]) }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="max-w-xs space-y-2">
                                        @forelse($sheet->adjustments as $adjustment)
                                            <div class="border-b border-white/[0.05] pb-2 last:border-0 last:pb-0">
                                                <p class="font-medium">{{ $adjustment->label }}</p>
                                                @if($adjustment->note)<p class="mt-1 break-words text-xs text-boss-ivory/45">{{ $adjustment->note }}</p>@endif
                                            </div>
                                        @empty
                                            <p class="text-xs text-boss-ivory/35">{{ __('No adjustment notes') }}</p>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-4"><span class="inline-flex rounded-full border border-white/[0.08] bg-white/[0.06] px-2.5 py-1 text-xs">{{ $sheet->workflowStatusLabel() }}</span></td>
                                <td class="px-5 py-4 text-right"><a class="pd-btn-secondary inline-flex whitespace-nowrap rounded-lg px-3 py-2 text-xs" href="{{ route('admin.chatter-hours.timesheets.show', $sheet) }}">{{ __('Manage payroll') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="13" class="px-5 py-12 text-center text-boss-ivory/35">{{ __('No payroll periods match these filters yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if($timesheets->total() > 0)
                        <tfoot class="border-t border-white/[0.08] bg-boss-gold/[0.05] font-semibold">
                            <tr><td class="px-5 py-4" colspan="2">{{ __('Filtered total') }}</td><td class="px-4 py-4">{{ $formatMinutes($stats['total_minutes']) }}</td><td></td><td class="px-4 py-4">${{ number_format($stats['basic_pay_pence'] / 100, 2) }}</td><td class="px-4 py-4 text-emerald-200"><p>${{ number_format(($stats['commission_pence'] ?? 0) / 100, 2) }}</p><p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('Generated: $:amount USD', ['amount' => number_format(($stats['generated_usd_pence'] ?? 0) / 100, 2)]) }}</p></td><td class="px-4 py-4 text-emerald-200"><p>GBP {{ number_format(($stats['foreign_commission_pence'] ?? 0) / 100, 2) }}</p><p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('Generated: GBP :amount', ['amount' => number_format(($stats['generated_gbp_pence'] ?? 0) / 100, 2)]) }}</p></td><td class="px-4 py-4">${{ number_format($stats['adjustment_pence'] / 100, 2) }}</td><td class="px-4 py-4 text-boss-gold">${{ number_format($stats['gross_pay_pence'] / 100, 2) }}</td><td class="px-4 py-4 text-emerald-200">PHP {{ number_format($stats['gross_pay_php_centavos'] / 100, 2) }}</td><td colspan="3"></td></tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            @if($timesheets->hasPages())<div class="border-t border-white/[0.06] px-5 py-4">{{ $timesheets->links() }}</div>@endif
        </section>

        @else
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,0.75fr)]">
            <div class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
                <div class="flex flex-col gap-2 border-b border-white/[0.06] px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="pd-kicker">{{ __('Access & pay setup') }}</p>
                        <h2 class="mt-1 font-display text-xl">{{ __('Chatter accounts') }}</h2>
                    </div>
                    <p class="text-xs text-boss-ivory/35">{{ __('Account status is separate from live work status.') }}</p>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($chatters as $chatter)
                        @php
                            $rate = $chatter->chatterPayRates->first();
                            $profile = $chatter->chatterProfile;
                            $open = $chatter->chatterShifts->first();
                            $assignments = $chatter->chatterRoleAssignments->sortBy(fn ($assignment) => $assignment->workRole?->sort_order);
                            $unassignedRoles = $workRoles->whereNotIn('id', $assignments->pluck('chatter_work_role_id'));
                            $assignedModels = $chatter->activeAssignedModels ?? collect();
                            $availableModels = ($modelOptions ?? collect())->whereNotIn('id', $assignedModels->pluck('id'));
                            $assignedCourses = $chatter->courseEnrollments
                                ->filter(fn ($enrollment) => $enrollment->course?->is_published)
                                ->sortBy(fn ($enrollment) => $enrollment->course?->sort_order ?? 999999)
                                ->values();
                            $availableCourses = ($courseOptions ?? collect())->whereNotIn('id', $assignedCourses->pluck('course_id'));
                            $profileTimezone = $profile?->timezone ?: 'Europe/London';
                            $profileTimezoneLabel = $timezoneOptions->firstWhere('value', $profileTimezone)['label'] ?? $profileTimezone;
                        @endphp
                        <div class="px-5 py-4 transition hover:bg-white/[0.02]" x-data="{ accountOpen: false, deleteOpen: false }">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <button type="button" class="min-w-0 flex-1 text-left" @click="accountOpen = true">
                                    <p class="truncate font-medium" title="{{ $chatter->name }}">{{ $chatter->name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-boss-ivory/35" title="{{ $chatter->email }}">{{ $chatter->email }}</p>
                                    <p class="mt-2 flex flex-wrap gap-2 text-[0.68rem] text-boss-ivory/35">
                                        <span class="rounded-full bg-white/[0.04] px-2 py-1">{{ $assignments->count() }} {{ trans_choice('role|roles', $assignments->count()) }}</span>
                                        <span class="rounded-full bg-white/[0.04] px-2 py-1">{{ $profileTimezone }}</span>
                                    </p>
                                </button>
                                <div class="flex shrink-0 items-center gap-2 self-start sm:self-center">
                                    <span class="rounded-full border px-2.5 py-1 text-[0.6rem] font-semibold {{ $profile?->isActive() ? 'border-emerald-300/20 bg-emerald-400/10 text-emerald-200' : 'border-red-300/20 bg-red-400/10 text-red-200' }}">
                                        {{ $profile?->isActive() ? __('Account active') : __('Account suspended') }}
                                    </span>
                                    <button type="button" class="pd-btn-secondary rounded-lg px-3 py-2 text-xs" @click="accountOpen = true">{{ __('Manage') }}</button>
                                </div>
                            </div>
                            <div x-show="accountOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="accountOpen = false">
                                <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="accountOpen = false"></div>
                                <div class="relative max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-lg border border-white/[0.08] bg-boss-panel shadow-2xl">
                                    <div class="flex items-start justify-between gap-4 border-b border-white/[0.06] p-6">
                                        <div class="min-w-0">
                                            <p class="pd-kicker">{{ __('Chatter account') }}</p>
                                            <h2 class="mt-1 truncate font-display text-2xl">{{ $chatter->name }}</h2>
                                            <p class="mt-1 truncate text-sm text-boss-ivory/40">{{ $chatter->email }}</p>
                                        </div>
                                        <button type="button" class="text-xl text-boss-ivory/45" @click="accountOpen = false" aria-label="Close">&times;</button>
                                    </div>
                                    <div class="space-y-5 p-6">
                                <div class="grid gap-4 text-sm text-boss-ivory/55 lg:grid-cols-2">
                                    <form method="POST" action="{{ route('admin.chatter-hours.chatters.timezone', $chatter) }}" class="rounded-lg border border-white/[0.06] bg-white/[0.02] p-4">@csrf @method('PATCH')
                                        <span class="pd-label">{{ __('Timezone') }}</span>
                                        <div class="mt-2 grid min-w-0 gap-2 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start">
                                            <div class="relative min-w-0" x-data="{ open: false, search: @js($profileTimezoneLabel), selected: @js($profileTimezone), selectedLabel: @js($profileTimezoneLabel), timezones: @js($timezoneOptions), get filtered() { const q = this.search.trim().toLowerCase(); return q ? this.timezones.filter((timezone) => timezone.search.toLowerCase().includes(q)).slice(0, 40) : this.timezones.slice(0, 40); }, choose(timezone) { this.selected = timezone.value; this.selectedLabel = timezone.label; this.search = timezone.label; this.open = false; }, openDropdown() { this.open = true; this.$nextTick(() => this.$refs.timezoneSearch?.focus()); } }" @keydown.escape.window="open = false" @click.outside="open = false">
                                                <input type="hidden" name="timezone" x-model="selected" required>
                                                <button type="button" class="pd-input pd-combobox-trigger min-w-0" aria-haspopup="listbox" :aria-expanded="open.toString()" @click="openDropdown()"><span class="min-w-0 flex-1 truncate" x-text="selectedLabel"></span><svg class="h-3.5 w-3.5 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4" /></svg></button>
                                                <div x-cloak x-show="open" x-transition class="pd-combobox-menu absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-md shadow-luxe" role="listbox">
                                                    <div class="pd-combobox-search-wrap p-2"><input x-ref="timezoneSearch" type="text" x-model="search" placeholder="{{ __('Search timezone, country, city, or UTC offset...') }}" class="pd-combobox-search" autocomplete="off" @input="selected = ''; open = true" @keydown.escape.stop="open = false"></div>
                                                    <div class="max-h-56 overflow-y-auto py-1"><template x-if="filtered.length === 0"><p class="pd-combobox-empty">{{ __('No timezones found') }}</p></template><template x-for="timezone in filtered" :key="timezone.value"><button type="button" class="pd-combobox-option" :class="selected === timezone.value ? 'is-selected' : ''" role="option" :aria-selected="(selected === timezone.value).toString()" @click="choose(timezone)"><span class="font-semibold" x-text="timezone.label"></span><span class="pd-combobox-option-meta" x-text="timezone.value"></span></button></template></div>
                                                </div>
                                            </div>
                                            <button class="pd-btn-secondary h-[43px] w-full rounded-lg px-3 text-xs xl:w-auto">{{ __('Save') }}</button>
                                        </div>
                                    </form>
                                    <div class="rounded-lg border border-white/[0.06] bg-white/[0.02] p-4">
                                        <p class="pd-label">{{ __('Current shift') }}</p>
                                        <p class="mt-2 break-words text-sm text-boss-ivory">{{ $open ? ($open->workRole?->name ?? __('Chatter')).($open->platform ? ' - '.$open->platform : '').($open->model ? ' - '.$open->model->name : '').' - '.$open->clocked_in_at->timezone('Europe/London')->format('d M g:i A').' UK Time' : __('Not working') }}</p>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-white/[0.06] bg-white/[0.015] p-4">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <p class="pd-label">{{ __('Assigned work roles') }}</p>
                                            <p class="mt-1 text-xs text-boss-ivory/35">{{ __('Role names and USD/hr rates stay paired for future clock-ins.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid gap-3 xl:grid-cols-2">
                                        @foreach($assignments as $assignment)
                                            <form method="POST" action="{{ route('admin.chatter-hours.chatters.roles', $chatter) }}" class="grid gap-3 rounded-lg border border-white/[0.06] bg-white/[0.02] p-3 md:grid-cols-[minmax(0,1.5fr)_7rem_auto] md:items-start">@csrf
                                                <input type="hidden" name="work_role_id" value="{{ $assignment->chatter_work_role_id }}"><input type="hidden" name="is_active" value="0">
                                                <div class="min-w-0">
                                                    <label class="block min-w-0"><span class="pd-label">{{ __('Role') }}</span><input class="pd-input mt-1 w-full min-w-0" name="work_role_name" list="chatter-work-role-options" value="{{ $assignment->workRole?->name }}" required></label>
                                                    <label class="mt-2 flex items-center gap-2 text-xs text-boss-ivory/45"><input class="shrink-0" type="checkbox" name="is_active" value="1" @checked($assignment->is_active)><span>{{ __('Available at clock-in') }}</span></label>
                                                </div>
                                                <label class="block min-w-0"><span class="pd-label">{{ __('USD/hr') }}</span><input class="pd-input mt-1 w-full" type="number" name="hourly_rate" step="0.01" min="0" value="{{ number_format($assignment->hourly_rate_pence / 100, 2, '.', '') }}" required></label>
                                                <button class="pd-btn-secondary h-[43px] w-full rounded-lg px-3 text-xs md:mt-5 md:w-auto">{{ __('Save') }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                    <form method="POST" action="{{ route('admin.chatter-hours.chatters.roles', $chatter) }}" class="mt-3 grid gap-3 rounded-lg border border-dashed border-boss-gold/20 bg-boss-gold/[0.025] p-3 md:grid-cols-[minmax(0,1.5fr)_7rem_auto] md:items-start">
                                        @csrf
                                        <input type="hidden" name="is_active" value="1">
                                        <label class="block min-w-0">
                                            <span class="pd-label">{{ __('Add another role') }}</span>
                                            <input class="pd-input mt-1 w-full min-w-0" name="work_role_name" list="chatter-work-role-options" placeholder="{{ __('Role name') }}" required>
                                        </label>
                                        <label class="block min-w-0">
                                            <span class="pd-label">{{ __('USD/hr') }}</span>
                                            <input class="pd-input mt-1 w-full" type="number" name="hourly_rate" step="0.01" min="0" value="{{ number_format(($rate?->base_rate_pence ?? 0) / 100, 2, '.', '') }}" required>
                                        </label>
                                        <button class="pd-btn-primary h-[43px] w-full rounded-lg px-3 text-xs md:mt-5 md:w-auto">{{ __('Assign role') }}</button>
                                    </form>
                                </div>
                                <div class="rounded-lg border border-white/[0.06] bg-white/[0.015] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="pd-label">{{ __('Course access') }}</p>
                                            <p class="mt-1 text-xs text-boss-ivory/40">{{ __('Unlock only the training courses this chatter should use for website/platform prep.') }}</p>
                                        </div>
                                        <span class="rounded-full bg-boss-gold/10 px-2.5 py-1 text-[0.65rem] font-semibold text-boss-gold">{{ trans_choice(':count course|:count courses', $assignedCourses->count(), ['count' => $assignedCourses->count()]) }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        @forelse($assignedCourses as $enrollment)
                                            @php
                                                $course = $enrollment->course;
                                            @endphp
                                            <div class="flex flex-col gap-3 rounded-lg border border-white/[0.06] bg-white/[0.025] p-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <p class="truncate font-medium" title="{{ $course->title }}">{{ $course->title }}</p>
                                                    <p class="mt-0.5 truncate text-xs text-boss-ivory/35">{{ $course->platform_label ?: __('General training') }}</p>
                                                </div>
                                                <form method="POST" action="{{ route('admin.chatter-hours.chatters.courses.destroy', [$chatter, $course]) }}" class="shrink-0">@csrf @method('DELETE')<button class="pd-btn-secondary w-full rounded-lg px-3 py-2 text-xs sm:w-auto">{{ __('Revoke') }}</button></form>
                                            </div>
                                        @empty
                                            <p class="rounded-lg border border-white/[0.06] bg-white/[0.02] p-3 text-xs text-boss-ivory/35">{{ __('No course access unlocked yet.') }}</p>
                                        @endforelse
                                    </div>
                                    @if($availableCourses->isNotEmpty())
                                        <form method="POST" action="{{ route('admin.chatter-hours.chatters.courses', $chatter) }}" class="mt-3 grid gap-2 rounded-lg border border-dashed border-boss-gold/20 bg-boss-gold/[0.025] p-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">@csrf
                                            <label class="min-w-0"><span class="pd-label">{{ __('Unlock course') }}</span><select class="pd-input mt-1 w-full min-w-0" name="course_id" required>@foreach($availableCourses as $course)<option value="{{ $course->id }}">{{ $course->title }} @if($course->platform_label) - {{ $course->platform_label }} @endif</option>@endforeach</select></label>
                                            <button class="pd-btn-primary h-[43px] w-full rounded-lg px-3 text-xs sm:w-auto">{{ __('Grant access') }}</button>
                                        </form>
                                    @else
                                        <p class="mt-3 text-xs text-boss-ivory/35">{{ __('All published courses are already unlocked for this chatter.') }}</p>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-white/[0.06] bg-white/[0.015] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="pd-label">{{ __('Assigned models') }}</p>
                                            <p class="mt-1 text-xs text-boss-ivory/40">{{ __('Only these models appear in this chatter weekly review checklist.') }}</p>
                                        </div>
                                        <span class="rounded-full bg-boss-pink/10 px-2.5 py-1 text-[0.65rem] font-semibold text-boss-rose">{{ trans_choice(':count model|:count models', $assignedModels->count(), ['count' => $assignedModels->count()]) }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        @forelse($assignedModels as $model)
                                            <div class="flex flex-col gap-3 rounded-lg border border-white/[0.06] bg-white/[0.025] p-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold" title="{{ $model->name }}">{{ $model->name }}</p>
                                                    <p class="truncate text-xs text-boss-ivory/35" title="{{ $model->email }}">{{ $model->email }}</p>
                                                </div>
                                                <form method="POST" action="{{ route('admin.chatter-hours.chatters.models.destroy', [$chatter, $model]) }}" class="shrink-0">@csrf @method('DELETE')<button class="pd-btn-secondary w-full rounded-lg px-3 py-2 text-xs sm:w-auto">{{ __('Remove') }}</button></form>
                                            </div>
                                        @empty
                                            <p class="rounded-lg border border-dashed border-white/[0.06] bg-white/[0.02] p-3 text-xs text-boss-ivory/40">{{ __('No models assigned yet. This chatter will not be asked for weekly model reviews until at least one model is assigned.') }}</p>
                                        @endforelse
                                    </div>
                                    @if($availableModels->isNotEmpty())
                                        <form method="POST" action="{{ route('admin.chatter-hours.chatters.models', $chatter) }}" class="mt-3 grid gap-2 rounded-lg border border-dashed border-boss-gold/20 bg-boss-gold/[0.025] p-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                                            @csrf
                                            <label class="min-w-0">
                                                <span class="pd-label">{{ __('Assign model') }}</span>
                                                <div
                                                        class="relative mt-1 min-w-0"
                                                    x-data="{
                                                        open: false,
                                                        search: '',
                                                        selected: '',
                                                        models: @js($availableModels->map(fn ($model) => [
                                                            'id' => $model->id,
                                                            'name' => $model->name,
                                                            'email' => $model->email,
                                                            'label' => trim($model->name.' - '.$model->email),
                                                        ])->values()),
                                                        get filtered() {
                                                            const q = this.search.trim().toLowerCase();

                                                            if (! q || this.models.some((model) => model.id === this.selected && model.label === this.search)) {
                                                                return this.models;
                                                            }

                                                            return this.models.filter((model) =>
                                                                `${model.name || ''} ${model.email || ''} ${model.label || ''}`.toLowerCase().includes(q)
                                                            );
                                                        },
                                                        choose(model) {
                                                            this.selected = model.id;
                                                            this.search = model.label;
                                                            this.open = false;
                                                        },
                                                        openDropdown() {
                                                            this.open = true;
                                                            this.$nextTick(() => this.$refs.assignModelSearch?.focus());
                                                        },
                                                    }"
                                                    @keydown.escape.window="open = false"
                                                    @click.outside="open = false"
                                                >
                                                    <input type="hidden" name="model_id" x-model="selected" required>
                                                    <button
                                                        type="button"
                                                        class="pd-input pd-combobox-trigger min-w-0"
                                                        aria-haspopup="listbox"
                                                        :aria-expanded="open.toString()"
                                                        @click="openDropdown()"
                                                    >
                                                        <span class="min-w-0 flex-1 truncate" x-text="search || '{{ __('Choose a model') }}'"></span>
                                                        <svg class="h-3.5 w-3.5 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M4 6l4 4 4-4" />
                                                        </svg>
                                                    </button>

                                                    <div
                                                        x-cloak
                                                        x-show="open"
                                                        x-transition
                                                        class="pd-combobox-menu absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-md shadow-luxe"
                                                        role="listbox"
                                                    >
                                                        <div class="pd-combobox-search-wrap p-2">
                                                            <input
                                                                x-ref="assignModelSearch"
                                                                type="text"
                                                                x-model="search"
                                                                placeholder="{{ __('Search model name or email...') }}"
                                                                class="pd-combobox-search"
                                                                autocomplete="off"
                                                                @input="selected = ''; open = true"
                                                                @keydown.escape.stop="open = false"
                                                            >
                                                        </div>
                                                        <div class="max-h-56 overflow-y-auto py-1">
                                                            <template x-if="filtered.length === 0">
                                                                <p class="pd-combobox-empty">{{ __('No models found') }}</p>
                                                            </template>
                                                            <template x-for="model in filtered" :key="model.id">
                                                                <button
                                                                    type="button"
                                                                    class="pd-combobox-option"
                                                                    :class="selected === model.id ? 'is-selected' : ''"
                                                                    role="option"
                                                                    :aria-selected="(selected === model.id).toString()"
                                                                    @click="choose(model)"
                                                                >
                                                                    <span class="font-semibold" x-text="model.name"></span>
                                                                    <span class="pd-combobox-option-meta" x-text="model.email"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                            <button class="pd-btn-primary h-[43px] w-full rounded-lg px-3 text-xs sm:w-auto">{{ __('Assign model') }}</button>
                                        </form>
                                    @else
                                        <p class="mt-3 text-xs text-boss-ivory/35">{{ __('All available models are already assigned to this chatter.') }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-2 border-t border-white/[0.06] pt-5 sm:flex-row sm:flex-wrap sm:justify-end">
                                    <form method="POST" action="{{ route('admin.chatter-hours.chatters.invitation', $chatter) }}">@csrf<button class="pd-btn-secondary rounded-lg px-3 py-2 text-xs">{{ __('Resend invite') }}</button></form>
                                    <form method="POST" action="{{ route('admin.chatter-hours.chatters.status', $chatter) }}">@csrf @method('PATCH')<input type="hidden" name="employment_status" value="{{ $profile?->isActive() ? 'suspended' : 'active' }}"><input type="hidden" name="reason" value="{{ $profile?->isActive() ? 'Suspended from chatter account manager' : 'Reactivated from chatter account manager' }}"><button class="pd-btn-secondary rounded-lg px-3 py-2 text-xs">{{ $profile?->isActive() ? __('Suspend') : __('Reactivate') }}</button></form>
                                    <button type="button" class="rounded-lg border border-red-400/25 bg-red-400/10 px-3 py-2 text-xs font-semibold text-red-300 transition hover:bg-red-400/15" @click="deleteOpen = true">{{ __('Delete account') }}</button>
                                </div>
                                    </div>
                                </div>
                            </div>
                            <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.escape.window="deleteOpen = false">
                                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="deleteOpen = false"></div>
                                <div role="dialog" aria-modal="true" aria-labelledby="delete-chatter-title-{{ $chatter->id }}" class="relative w-full max-w-lg overflow-hidden rounded-lg border border-red-400/20 bg-boss-panel shadow-2xl">
                                    <div class="border-b border-white/[0.06] p-6">
                                        <p class="pd-kicker text-red-300">{{ __('Permanent action') }}</p>
                                        <h2 id="delete-chatter-title-{{ $chatter->id }}" class="mt-2 font-display text-2xl">{{ __('Delete chatter account?') }}</h2>
                                        <p class="mt-3 text-sm leading-6 text-boss-ivory/55">{{ __('This permanently deletes :name\'s login, shifts, attendance, timesheets, pay history, adjustments, and all related chatter records. This cannot be undone.', ['name' => $chatter->name]) }}</p>
                                    </div>
                                    <div class="flex flex-col-reverse gap-3 p-6 sm:flex-row sm:justify-end">
                                        <button type="button" class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs" @click="deleteOpen = false">{{ __('Cancel') }}</button>
                                        <form method="POST" action="{{ route('admin.chatter-hours.chatters.destroy', $chatter) }}">@csrf @method('DELETE')<button class="w-full rounded-lg bg-red-500 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-red-600 sm:w-auto">{{ __('Delete chatter') }}</button></form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty<p class="px-5 py-10 text-center text-sm text-boss-ivory/35">{{ __('No chatter accounts yet.') }}</p>@endforelse
                </div>
                @if($chatters->hasPages())<div class="border-t border-white/[0.06] px-5 py-4">{{ $chatters->links() }}</div>@endif
            </div>

            <div class="space-y-6">
                <div class="overflow-hidden rounded-lg border border-emerald-300/15 bg-emerald-400/[0.035]">
                    <div class="border-b border-emerald-300/10 px-5 py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="pd-kicker text-emerald-700">{{ __('Live status') }}</p>
                                <h2 class="mt-1 font-display text-xl text-boss-ink">{{ __('Working now') }}</h2>
                            </div>
                            <span class="w-fit rounded-full bg-emerald-300/20 px-2.5 py-1 text-[0.65rem] font-semibold text-emerald-700">{{ $openShifts->count() }} {{ trans_choice('open shift|open shifts', $openShifts->count()) }}</span>
                        </div>
                    </div>
                    <div class="max-h-[32rem] divide-y divide-boss-rose/10 overflow-y-auto overscroll-contain">
                        @forelse($openShifts as $shift)
                            @php
                                $activeBreak = $shift->breaks->firstWhere('ended_at', null);
                                $clockedInUk = $shift->clocked_in_at->timezone('Europe/London');
                            @endphp
                            <details class="group px-5 py-3 transition hover:bg-emerald-300/[0.04] open:bg-black/10">
                                <summary class="flex cursor-pointer list-none items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <p class="truncate font-medium" title="{{ $shift->user?->name ?? __('Unknown chatter') }}">{{ $shift->user?->name ?? __('Unknown chatter') }}</p>
                                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[0.58rem] font-semibold {{ $activeBreak ? 'border-amber-300/30 bg-amber-400/15 text-amber-700' : 'border-emerald-300/30 bg-emerald-400/15 text-emerald-700' }}">
                                                {{ $activeBreak ? __('On break') : __('Working') }}
                                            </span>
                                        </div>
                                        <p class="mt-1 truncate text-xs text-boss-ink/55" title="{{ $shift->user?->email }}">{{ $shift->user?->email }}</p>
                                        <p class="mt-2 truncate text-xs text-boss-ink/65" title="{{ trim(($shift->platform ?: __('Not recorded')).' - '.($shift->model?->name ?? __('No model selected'))) }}">
                                            <span class="font-medium text-boss-ink/80">{{ $shift->platform ?: __('Not recorded') }}</span>
                                            <span class="text-boss-ink/30"> / </span>
                                            {{ $shift->model?->name ?? __('No model selected') }}
                                        </p>
                                        <p class="mt-1 text-xs text-boss-ink/55 tabular-nums">{{ $clockedInUk->format('g:i A') }} UK Time</p>
                                    </div>
                                    <svg class="mt-1 h-4 w-4 shrink-0 fill-none stroke-current text-boss-ink/45 transition group-open:rotate-180" viewBox="0 0 16 16" aria-hidden="true">
                                        <path d="M4 6l4 4 4-4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </summary>
                                <dl class="mt-3 grid gap-2 border-t border-boss-rose/10 pt-3 text-xs text-boss-ink/55 sm:grid-cols-2">
                                    <div class="min-w-0 rounded-md bg-white/45 p-3">
                                        <dt class="pd-label">{{ __('Clocked in') }}</dt>
                                        <dd class="mt-1 text-boss-ink tabular-nums">{{ $clockedInUk->format('D, d M g:i A') }} UK Time</dd>
                                    </div>
                                    <div class="min-w-0 rounded-md bg-white/45 p-3">
                                        <dt class="pd-label">{{ __('Role / rate') }}</dt>
                                        <dd class="mt-1 truncate text-boss-ink">
                                            {{ $shift->workRole?->name ?? __('Chatter') }}
                                            @if(! is_null($shift->hourly_rate_pence))
                                                - ${{ number_format($shift->hourly_rate_pence / 100, 2) }} USD/hr
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </details>
                        @empty
                            <p class="px-5 py-10 text-center text-sm text-boss-ink/45">{{ __('No chatters currently working.') }}</p>
                        @endforelse
                    </div>
                    @if($openShifts->isNotEmpty())
                        <div class="border-t border-emerald-300/10 px-5 py-3 text-right">
                            <a href="{{ route('admin.chatter-hours.attendance') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">{{ __('View full attendance') }}</a>
                        </div>
                    @endif
                </div>

                <div class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
                    <div class="border-b border-white/[0.06] px-5 py-4">
                        <p class="pd-kicker">{{ __('Registration') }}</p>
                        <h2 class="mt-1 font-display text-xl">{{ __('Chatter requests') }}</h2>
                        <div class="mt-3 rounded-lg border border-white/[0.06] bg-white/[0.02] p-3">
                            <p class="break-all text-xs text-boss-ivory/45">{{ $applicationUrl }}</p>
                            <button
                                type="button"
                                class="mt-2 text-xs font-semibold text-boss-gold"
                                @click="copyApplicationLink()"
                                x-text="copied ? @js(__('Copied')) : @js(__('Copy link'))"
                            ></button>
                        </div>
                    </div>
                    <div class="divide-y divide-white/[0.05]">
                        @forelse($requests as $joinRequest)
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $joinRequest->name }}</p>
                                        <p class="truncate text-xs text-boss-ivory/35">{{ $joinRequest->email }}</p>
                                        <p class="mt-2 text-xs text-boss-ivory/35">{{ $joinRequest->timezone }} &middot; {{ $joinRequest->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-white/[0.06] px-2 py-1 text-[0.6rem] capitalize">{{ $joinRequest->status }}</span>
                                </div>

                                @if($joinRequest->status === 'pending')
                                    <div class="mt-4 space-y-3">
                                        <form method="POST" action="{{ route('admin.chatter-hours.requests.approve', $joinRequest) }}" class="rounded-lg border border-white/[0.06] bg-white/[0.025] p-4">
                                            @csrf
                                            <div>
                                                <p class="pd-kicker">{{ __('Approval setup') }}</p>
                                                <p class="mt-1 text-xs leading-5 text-boss-ivory/45">{{ __('Choose the starting role and fixed hourly rate before sending their account invite.') }}</p>
                                            </div>
                                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                                <label>
                                                    <span class="pd-label">{{ __('Role') }}</span>
                                                    <input class="pd-input mt-2" name="work_role_name" list="chatter-work-role-options" value="{{ $workRoles->firstWhere('id', $defaultWorkRoleId)?->name ?? 'Chatter' }}" required>
                                                </label>
                                                <label>
                                                    <span class="pd-label">{{ __('Rate USD/hr') }}</span>
                                                    <div class="relative mt-2">
                                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-boss-ivory/35">$</span>
                                                        <input class="pd-input pl-7" type="number" step="0.01" min="0" name="base_hourly_rate" value="3.00" aria-label="{{ __('Hourly rate in USD') }}" required>
                                                    </div>
                                                </label>
                                            </div>
                                            <button class="pd-btn-primary mt-4 w-full justify-center rounded-lg px-3 py-2 text-xs">{{ __('Approve and send invite') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.chatter-hours.requests.reject', $joinRequest) }}" class="grid gap-2 rounded-lg border border-red-400/10 bg-red-400/5 p-4 sm:grid-cols-[1fr_auto] sm:items-end">
                                            @csrf
                                            <label>
                                                <span class="pd-label text-red-300">{{ __('Reject request') }}</span>
                                                <input class="pd-input mt-2" name="admin_note" placeholder="{{ __('Reason required before rejecting') }}" required>
                                            </label>
                                            <button class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs">{{ __('Reject') }}</button>
                                        </form>
                                    </div>
                                @elseif($joinRequest->admin_note)
                                    <p class="mt-3 text-xs text-boss-ivory/45">{{ $joinRequest->admin_note }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-sm text-boss-ivory/35">{{ __('No chatter requests yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="createOpen = false">
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="createOpen = false"></div>
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-white/[0.08] bg-boss-panel p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4"><div><p class="pd-kicker">{{ __('Account invitation') }}</p><h2 class="mt-1 font-display text-2xl">{{ __('Create chatter') }}</h2><p class="mt-2 text-xs text-boss-ivory/40">{{ __('The Chatter role is assigned first. Additional work roles can be added from the account manager.') }}</p></div><button type="button" class="text-xl text-boss-ivory/45" @click="createOpen = false" aria-label="Close">&times;</button></div>
                <form method="POST" action="{{ route('admin.chatter-hours.chatters.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">@csrf
                    <label><span class="pd-label">{{ __('Name') }}</span><input class="pd-input mt-2" name="name" required></label><label><span class="pd-label">{{ __('Email') }}</span><input class="pd-input mt-2" type="email" name="email" required></label><label class="sm:col-span-2"><span class="pd-label">{{ __('Timezone') }}</span><div class="relative mt-2" x-data="{ open: false, search: '', selected: 'Europe/London', selectedLabel: '', timezones: @js($timezoneOptions), init() { const match = this.timezones.find((timezone) => timezone.value === this.selected); this.selectedLabel = match?.label || this.selected; this.search = this.selectedLabel; }, get filtered() { const q = this.search.trim().toLowerCase(); return q ? this.timezones.filter((timezone) => timezone.search.toLowerCase().includes(q)).slice(0, 40) : this.timezones.slice(0, 40); }, choose(timezone) { this.selected = timezone.value; this.selectedLabel = timezone.label; this.search = timezone.label; this.open = false; }, openDropdown() { this.open = true; this.$nextTick(() => this.$refs.timezoneSearch?.focus()); } }" @keydown.escape.window="open = false" @click.outside="open = false"><input type="hidden" name="timezone" x-model="selected" required><button type="button" class="pd-input pd-combobox-trigger" aria-haspopup="listbox" :aria-expanded="open.toString()" @click="openDropdown()"><span class="min-w-0 flex-1 truncate" x-text="selectedLabel"></span><svg class="h-3.5 w-3.5 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4" /></svg></button><div x-cloak x-show="open" x-transition class="pd-combobox-menu absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-md shadow-luxe" role="listbox"><div class="pd-combobox-search-wrap p-2"><input x-ref="timezoneSearch" type="text" x-model="search" placeholder="{{ __('Search timezone, country, city, or UTC offset...') }}" class="pd-combobox-search" autocomplete="off" @input="selected = ''; open = true" @keydown.escape.stop="open = false"></div><div class="max-h-56 overflow-y-auto py-1"><template x-if="filtered.length === 0"><p class="pd-combobox-empty">{{ __('No timezones found') }}</p></template><template x-for="timezone in filtered" :key="timezone.value"><button type="button" class="pd-combobox-option" :class="selected === timezone.value ? 'is-selected' : ''" role="option" :aria-selected="(selected === timezone.value).toString()" @click="choose(timezone)"><span class="font-semibold" x-text="timezone.label"></span><span class="pd-combobox-option-meta" x-text="timezone.value"></span></button></template></div></div></div></label><label><span class="pd-label">{{ __('Role') }}</span><input class="pd-input mt-2" name="work_role_name" list="chatter-work-role-options" value="{{ $workRoles->firstWhere('id', $defaultWorkRoleId)?->name ?? 'Chatter' }}" required></label><label><span class="pd-label">{{ __('Rate USD/hr') }}</span><input class="pd-input mt-2" type="number" step="0.01" min="0" name="base_hourly_rate" value="3.00" required></label><div class="flex items-end justify-end gap-2 sm:col-span-2"><button type="button" class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs" @click="createOpen = false">{{ __('Cancel') }}</button><button class="pd-btn-primary rounded-lg px-4 py-2.5 text-xs">{{ __('Create and invite') }}</button></div>
                </form>
            </div>
        </div>
        @endif
    </div>
</x-admin-layout>
