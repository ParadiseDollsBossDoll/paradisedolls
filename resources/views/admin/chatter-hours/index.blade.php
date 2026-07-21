<x-admin-layout>
    @php
        $mode = $mode ?? 'accounts';
        $formatMinutes = fn (int $minutes) => intdiv($minutes, 60).'h '.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT).'m';
        $applicationUrl = route('chatter.apply');
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
                    <a href="{{ route('admin.chatter-hours.export.xlsx', request()->query()) }}" class="pd-btn-secondary inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-xs">{{ __('Export Excel') }}</a>
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
                        [__('Gross USD'), '$'.number_format($stats['gross_pay_pence'] / 100, 2)],
                        [__('Gross PHP'), 'PHP '.number_format($stats['gross_pay_php_centavos'] / 100, 2)],
                    ]
                    : [
                        [__('Chatters'), $stats['chatters']],
                        [__('Working now'), $stats['working']],
                        [__('Join requests'), $stats['requests']],
                    ];
            @endphp
            <section class="grid grid-cols-2 gap-3 md:grid-cols-3 {{ $mode === 'attendance' ? 'xl:grid-cols-7' : 'xl:grid-cols-3' }}">
            {{-- @foreach ([
                [__('Chatters'), $stats['chatters']],
                [__('Working now'), $stats['working']],
                [__('Pending review'), $stats['pending']],
                [__('Join requests'), $stats['requests']],
                [__('Filtered hours'), $formatMinutes($stats['total_minutes'])],
                [__('Gross USD'), '$'.number_format($stats['gross_pay_pence'] / 100, 2)],
                [__('Gross PHP'), '₱'.number_format($stats['gross_pay_php_centavos'] / 100, 2)],
            ] as [$label, $value]) --}}
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
            <div class="mb-4">
                <p class="pd-kicker">{{ __('Report controls') }}</p>
                <h2 class="mt-1 font-display text-xl">{{ __('Filter attendance and payroll') }}</h2>
            </div>
            <form method="GET" action="{{ route('admin.chatter-hours.attendance') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.3fr_1fr_1fr_1fr_0.8fr_0.8fr_auto] xl:items-end">
                <label><span class="pd-label">{{ __('Search') }}</span><input class="pd-input mt-2" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('Name or email') }}"></label>
                <label><span class="pd-label">{{ __('Chatter') }}</span><select class="pd-input mt-2" name="chatter_id"><option value="">{{ __('All chatters') }}</option>@foreach ($chatterOptions as $option)<option value="{{ $option->id }}" @selected((int) $filters['chatter_id'] === $option->id)>{{ $option->name }}</option>@endforeach</select></label>
                <label><span class="pd-label">{{ __('Work role') }}</span><select class="pd-input mt-2" name="role_id"><option value="">{{ __('All roles') }}</option>@foreach ($workRoles as $role)<option value="{{ $role->id }}" @selected((int) $filters['role_id'] === $role->id)>{{ $role->name }}</option>@endforeach</select></label>
                <label><span class="pd-label">{{ __('Timesheet status') }}</span><select class="pd-input mt-2" name="status"><option value="">{{ __('All statuses') }}</option>@foreach (['draft', 'submitted', 'changes_requested', 'approved', 'rejected'] as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>@endforeach</select></label>
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
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="border-b border-white/[0.06] text-[0.6rem] uppercase tracking-[0.13em] text-boss-ivory/35">
                        <tr><th class="px-5 py-3">{{ __('Date / time in') }}</th><th class="px-4 py-3">{{ __('Date / time out') }}</th><th class="px-4 py-3">{{ __('Employee') }}</th><th class="px-4 py-3">{{ __('Work role & rate') }}</th><th class="px-4 py-3">{{ __('Hours worked') }}</th><th class="px-5 py-3">{{ __('Status') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($attendanceShifts as $shift)
                            @php
                                $activeBreak = $shift->breaks->firstWhere('ended_at', null);
                                $overdue = !$shift->clocked_out_at && $shift->clocked_in_at->lt(now()->subHours(16));
                            @endphp
                            <tr class="hover:bg-white/[0.02]">
                                <td class="whitespace-nowrap px-5 py-4"><p class="font-medium">{{ $shift->clocked_in_at->timezone('Europe/London')->format('D, d M Y') }}</p><p class="mt-0.5 text-xs text-boss-ivory/40">{{ $shift->clocked_in_at->timezone('Europe/London')->format('g:i A T') }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4">@if($shift->clocked_out_at)<p>{{ $shift->clocked_out_at->timezone('Europe/London')->format('D, d M Y') }}</p><p class="mt-0.5 text-xs text-boss-ivory/40">{{ $shift->clocked_out_at->timezone('Europe/London')->format('g:i A T') }}</p>@else<span class="text-emerald-200">{{ __('Still working') }}</span>@endif</td>
                                <td class="px-4 py-4"><p class="font-medium">{{ $shift->user->name }}</p><p class="mt-0.5 text-xs text-boss-ivory/35">{{ $shift->user->email }}</p></td>
                                <td class="px-4 py-4"><span class="rounded-full bg-boss-gold/10 px-2.5 py-1 text-xs text-boss-gold">{{ $shift->workRole?->name ?? __('Chatter') }}</span><p class="mt-2 text-xs text-boss-ivory/40">${{ number_format(($shift->hourly_rate_pence ?? 0) / 100, 2) }} USD/hr</p></td>
                                <td class="px-4 py-4 font-semibold text-boss-gold">{{ $formatMinutes((int) $shift->getAttribute('worked_minutes')) }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[0.65rem] font-semibold {{ $overdue ? 'bg-red-400/15 text-red-200' : ($activeBreak ? 'bg-amber-400/15 text-amber-200' : ($shift->clocked_out_at ? 'bg-white/[0.06] text-boss-ivory/55' : 'bg-emerald-400/15 text-emerald-200')) }}">{{ $overdue ? __('Overdue') : ($activeBreak ? __('On break') : ($shift->clocked_out_at ? __('Completed') : __('Working'))) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-boss-ivory/35">{{ __('No attendance records match these filters.') }}</td></tr>
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
                <table class="w-full min-w-[1660px] text-left text-sm">
                    <thead class="border-b border-white/[0.06] text-[0.6rem] uppercase tracking-[0.13em] text-boss-ivory/35">
                        <tr><th class="px-5 py-3">{{ __('Chatter') }}</th><th class="px-4 py-3">{{ __('Payroll week') }}</th><th class="px-4 py-3">{{ __('Total hours') }}</th><th class="px-4 py-3">{{ __('Rate') }}</th><th class="px-4 py-3">{{ __('Basic pay') }}</th><th class="px-4 py-3">{{ __('Additional') }}</th><th class="px-4 py-3">{{ __('US final pay') }}</th><th class="px-4 py-3">{{ __('PH final pay') }}</th><th class="px-4 py-3">{{ __('Notes') }}</th><th class="px-4 py-3">{{ __('Status') }}</th><th class="px-5 py-3 text-right">{{ __('Action') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($timesheets as $sheet)
                            <tr>
                                <td class="px-5 py-4"><p class="font-medium">{{ $sheet->user->name }}</p><p class="mt-0.5 text-xs text-boss-ivory/35">{{ $sheet->user->email }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $sheet->period_start->format('d M') }} - {{ $sheet->period_end->format('d M Y') }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $formatMinutes($sheet->ordinary_minutes) }}</td>
                                <td class="px-4 py-4">
                                    <div class="space-y-2">
                                        @forelse($sheet->getAttribute('payroll_rates') as $rate)
                                            <div class="whitespace-nowrap">
                                                @if($rate['role'])<p class="text-xs text-boss-ivory/45">{{ $rate['role'] }}</p>@endif
                                                <p class="font-medium">${{ number_format($rate['hourly_rate_pence'] / 100, 2) }} USD/hr</p>
                                            </div>
                                        @empty
                                            <p class="text-xs text-boss-ivory/35">{{ __('No rate recorded') }}</p>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-4"><p class="font-semibold">${{ number_format($sheet->getAttribute('basic_pay_pence') / 100, 2) }}</p><p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('Before manual adjustments') }}</p></td>
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
                                <td class="px-4 py-4 font-semibold text-boss-gold">${{ number_format($sheet->gross_pay_pence / 100, 2) }}</td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-emerald-200">₱{{ number_format($currency->phpCentavosForTimesheet($sheet) / 100, 2) }}</p>
                                    <p class="mt-1 text-[0.65rem] text-boss-ivory/35">{{ __('1 USD = ₱:rate', ['rate' => $currency->rateForTimesheet($sheet)]) }}</p>
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
                                <td class="px-4 py-4"><span class="rounded-full bg-white/[0.06] px-2.5 py-1 text-xs">{{ $sheet->statusLabel() }}</span></td>
                                <td class="px-5 py-4 text-right"><a class="pd-btn-secondary inline-flex rounded-lg px-3 py-2 text-xs" href="{{ route('admin.chatter-hours.timesheets.show', $sheet) }}">{{ __('Manage payroll') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-5 py-12 text-center text-boss-ivory/35">{{ __('No payroll periods match these filters yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if($timesheets->total() > 0)
                        <tfoot class="border-t border-white/[0.08] bg-boss-gold/[0.05] font-semibold">
                            <tr><td class="px-5 py-4" colspan="2">{{ __('Filtered total') }}</td><td class="px-4 py-4">{{ $formatMinutes($stats['total_minutes']) }}</td><td></td><td class="px-4 py-4">${{ number_format($stats['basic_pay_pence'] / 100, 2) }}</td><td class="px-4 py-4">${{ number_format($stats['adjustment_pence'] / 100, 2) }}</td><td class="px-4 py-4 text-boss-gold">${{ number_format($stats['gross_pay_pence'] / 100, 2) }}</td><td class="px-4 py-4 text-emerald-200">₱{{ number_format($stats['gross_pay_php_centavos'] / 100, 2) }}</td><td colspan="3"></td></tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            @if($timesheets->hasPages())<div class="border-t border-white/[0.06] px-5 py-4">{{ $timesheets->links() }}</div>@endif
        </section>

        @else
        <section class="grid gap-6 xl:grid-cols-[1.35fr_1fr]">
            <div class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
                <div class="border-b border-white/[0.06] px-5 py-4"><p class="pd-kicker">{{ __('Access & pay setup') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Chatter accounts') }}</h2></div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($chatters as $chatter)
                        @php
                            $rate = $chatter->chatterPayRates->first();
                            $profile = $chatter->chatterProfile;
                            $open = $chatter->chatterShifts->first();
                            $assignments = $chatter->chatterRoleAssignments->sortBy(fn ($assignment) => $assignment->workRole?->sort_order);
                            $unassignedRoles = $workRoles->whereNotIn('id', $assignments->pluck('chatter_work_role_id'));
                        @endphp
                        <div class="px-5 py-4" x-data="{ accountOpen: false, deleteOpen: false }">
                            <div class="flex items-center justify-between gap-3">
                                <button type="button" class="min-w-0 flex-1 text-left" @click="accountOpen = true">
                                    <p class="truncate font-medium">{{ $chatter->name }}</p>
                                    <p class="truncate text-xs text-boss-ivory/35">{{ $chatter->email }}</p>
                                </button>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-[0.6rem] {{ $profile?->isActive() ? 'bg-emerald-400/15 text-emerald-200' : 'bg-red-400/15 text-red-200' }}">{{ ucfirst($profile?->employment_status ?? 'inactive') }}</span>
                                    <button type="button" class="pd-btn-secondary rounded-lg px-3 py-2 text-xs" @click="accountOpen = true">{{ __('Manage') }}</button>
                                </div>
                            </div>
                            <div x-show="accountOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="accountOpen = false">
                                <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="accountOpen = false"></div>
                                <div class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg border border-white/[0.08] bg-boss-panel p-6 shadow-2xl">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="pd-kicker">{{ __('Chatter account') }}</p>
                                            <h2 class="mt-1 truncate font-display text-2xl">{{ $chatter->name }}</h2>
                                            <p class="mt-1 truncate text-sm text-boss-ivory/40">{{ $chatter->email }}</p>
                                        </div>
                                        <button type="button" class="text-xl text-boss-ivory/45" @click="accountOpen = false" aria-label="Close">&times;</button>
                                    </div>
                                    <div class="mt-5 space-y-5 border-t border-white/[0.05] pt-5">
                                <div class="grid gap-4 text-sm text-boss-ivory/55 sm:grid-cols-2"><p>{{ __('Timezone') }}<span class="mt-1 block text-boss-ivory">{{ $profile?->timezone }}</span></p><p>{{ __('Current shift') }}<span class="mt-1 block text-boss-ivory">{{ $open ? ($open->workRole?->name ?? __('Chatter')).' · '.$open->clocked_in_at->timezone('Europe/London')->format('d M H:i').' UK' : __('Not working') }}</span></p></div>
                                <div>
                                    <p class="pd-label">{{ __('Assigned work roles') }}</p>
                                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                                        @foreach($assignments as $assignment)
                                            <form method="POST" action="{{ route('admin.chatter-hours.chatters.roles', $chatter) }}" class="grid gap-2 rounded-lg border border-white/[0.06] bg-white/[0.02] p-3 sm:grid-cols-[1fr_120px_auto] sm:items-end">@csrf
                                                <input type="hidden" name="work_role_id" value="{{ $assignment->chatter_work_role_id }}"><input type="hidden" name="is_active" value="0">
                                                <div><p class="font-medium">{{ $assignment->workRole?->name }}</p><label class="mt-2 flex items-center gap-2 text-xs text-boss-ivory/45"><input type="checkbox" name="is_active" value="1" @checked($assignment->is_active)>{{ __('Available at clock-in') }}</label></div>
                                                <label><span class="pd-label">{{ __('USD/hr') }}</span><input class="pd-input mt-1" type="number" name="hourly_rate" step="0.01" min="0" value="{{ number_format($assignment->hourly_rate_pence / 100, 2, '.', '') }}" required></label>
                                                <button class="pd-btn-secondary h-[43px] rounded-lg px-3 text-xs">{{ __('Save') }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                    @if($unassignedRoles->isNotEmpty())
                                        <form method="POST" action="{{ route('admin.chatter-hours.chatters.roles', $chatter) }}" class="mt-3 grid gap-2 rounded-lg border border-dashed border-boss-gold/20 p-3 sm:grid-cols-[1fr_140px_auto] sm:items-end">@csrf<input type="hidden" name="is_active" value="1"><label><span class="pd-label">{{ __('Add another role') }}</span><select class="pd-input mt-1" name="work_role_id" required>@foreach($unassignedRoles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></label><label><span class="pd-label">{{ __('USD/hr') }}</span><input class="pd-input mt-1" type="number" name="hourly_rate" step="0.01" min="0" value="{{ number_format(($rate?->base_rate_pence ?? 0) / 100, 2, '.', '') }}" required></label><button class="pd-btn-primary h-[43px] rounded-lg px-3 text-xs">{{ __('Assign role') }}</button></form>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-2">
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

            <div class="overflow-hidden rounded-lg border border-white/[0.07] bg-white/[0.025]">
                <div class="border-b border-white/[0.06] px-5 py-4"><p class="pd-kicker">{{ __('Registration') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Chatter requests') }}</h2><div class="mt-3 rounded-lg border border-white/[0.06] bg-white/[0.02] p-3"><p class="break-all text-xs text-boss-ivory/45">{{ $applicationUrl }}</p><button type="button" class="mt-2 text-xs font-semibold text-boss-gold" @click="copyApplicationLink()" x-text="copied ? @js(__('Copied')) : @js(__('Copy link'))"></button></div></div>
                <div class="divide-y divide-white/[0.05]">@forelse($requests as $joinRequest)<div class="p-5"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="truncate font-medium">{{ $joinRequest->name }}</p><p class="truncate text-xs text-boss-ivory/35">{{ $joinRequest->email }}</p></div><span class="rounded-full bg-white/[0.06] px-2 py-1 text-[0.6rem] capitalize">{{ $joinRequest->status }}</span></div><p class="mt-2 text-xs text-boss-ivory/35">{{ $joinRequest->timezone }} · {{ $joinRequest->created_at->diffForHumans() }}</p>@if($joinRequest->status === 'pending')<div class="mt-4 grid gap-3"><form method="POST" action="{{ route('admin.chatter-hours.requests.approve', $joinRequest) }}" class="grid grid-cols-2 gap-2">@csrf<input class="pd-input" type="number" step="0.01" name="base_hourly_rate" value="12.00" aria-label="Hourly rate" required><input class="pd-input" type="date" name="effective_from" value="{{ now('Europe/London')->toDateString() }}" required><input type="hidden" name="overtime_threshold_hours" value="40"><input type="hidden" name="overtime_multiplier" value="1.5"><input type="hidden" name="night_premium_multiplier" value="1.2"><input type="hidden" name="weekend_premium_multiplier" value="1.25"><input type="hidden" name="night_starts_at" value="22:00"><input type="hidden" name="night_ends_at" value="06:00"><button class="pd-btn-primary col-span-2 rounded-lg px-3 py-2 text-xs">{{ __('Approve and invite') }}</button></form><form method="POST" action="{{ route('admin.chatter-hours.requests.reject', $joinRequest) }}" class="flex gap-2">@csrf<input class="pd-input" name="admin_note" placeholder="{{ __('Reason required') }}" required><button class="pd-btn-secondary rounded-lg px-3 text-xs">{{ __('Reject') }}</button></form></div>@elseif($joinRequest->admin_note)<p class="mt-3 text-xs text-boss-ivory/45">{{ $joinRequest->admin_note }}</p>@endif</div>@empty<p class="px-5 py-10 text-center text-sm text-boss-ivory/35">{{ __('No chatter requests yet.') }}</p>@endforelse</div>
            </div>
        </section>

        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="createOpen = false">
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="createOpen = false"></div>
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-white/[0.08] bg-boss-panel p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4"><div><p class="pd-kicker">{{ __('Account invitation') }}</p><h2 class="mt-1 font-display text-2xl">{{ __('Create chatter') }}</h2><p class="mt-2 text-xs text-boss-ivory/40">{{ __('The Chatter role is assigned first. Additional work roles can be added from the account manager.') }}</p></div><button type="button" class="text-xl text-boss-ivory/45" @click="createOpen = false" aria-label="Close">&times;</button></div>
                <form method="POST" action="{{ route('admin.chatter-hours.chatters.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">@csrf
                    <label><span class="pd-label">{{ __('Name') }}</span><input class="pd-input mt-2" name="name" required></label><label><span class="pd-label">{{ __('Email') }}</span><input class="pd-input mt-2" type="email" name="email" required></label><label><span class="pd-label">{{ __('Timezone') }}</span><input class="pd-input mt-2" name="timezone" value="Europe/London" required></label><label><span class="pd-label">{{ __('Chatter USD/hr') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="base_hourly_rate" value="12.00" required></label><label><span class="pd-label">{{ __('Overtime after hours') }}</span><input class="pd-input mt-2" type="number" step="0.25" name="overtime_threshold_hours" value="40" required></label><label><span class="pd-label">{{ __('Overtime multiplier') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="overtime_multiplier" value="1.5" required></label><label><span class="pd-label">{{ __('Night premium') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="night_premium_multiplier" value="1.2" required></label><label><span class="pd-label">{{ __('Weekend premium') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="weekend_premium_multiplier" value="1.25" required></label><label><span class="pd-label">{{ __('Night starts') }}</span><input class="pd-input mt-2" type="time" name="night_starts_at" value="22:00" required></label><label><span class="pd-label">{{ __('Night ends') }}</span><input class="pd-input mt-2" type="time" name="night_ends_at" value="06:00" required></label><label><span class="pd-label">{{ __('Effective from') }}</span><input class="pd-input mt-2" type="date" name="effective_from" value="{{ now('Europe/London')->toDateString() }}" required></label><div class="flex items-end justify-end gap-2"><button type="button" class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs" @click="createOpen = false">{{ __('Cancel') }}</button><button class="pd-btn-primary rounded-lg px-4 py-2.5 text-xs">{{ __('Create and invite') }}</button></div>
                </form>
            </div>
        </div>
        @endif
    </div>
</x-admin-layout>
