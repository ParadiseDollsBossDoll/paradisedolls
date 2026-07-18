<x-admin-layout>
    <div class="mx-auto max-w-[1500px] space-y-6 text-boss-ivory" x-data="{ createOpen: false }">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Workforce') }}</p>
                <h1 class="pd-heading mt-2 text-3xl sm:text-4xl">{{ __('Chatter Hours') }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-boss-ivory/45">{{ __('Live attendance, weekly timesheets, estimated pay, and account access. Payroll weeks use Europe/London time.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="pd-btn-primary rounded-lg px-4 py-2.5 text-xs" @click="createOpen = true">
                    {{ __('Create chatter') }}
                </button>
                <a href="{{ route('admin.chatter-hours.export.xlsx', request()->query()) }}" class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs">{{ __('Export Excel') }}</a>
                <a href="{{ route('admin.chatter-hours.export.csv', request()->query()) }}" class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs">{{ __('Export CSV') }}</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-200">
                <p class="font-semibold">{{ __('Please check the highlighted information.') }}</p>
                <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-8">
            @php
                $cards = [
                    ['label' => __('Chatters'), 'value' => $stats['chatters']],
                    ['label' => __('Working now'), 'value' => $stats['working']],
                    ['label' => __('On break'), 'value' => $stats['on_break']],
                    ['label' => __('Overdue'), 'value' => $stats['overdue']],
                    ['label' => __('Pending review'), 'value' => $stats['pending']],
                    ['label' => __('Join requests'), 'value' => $stats['requests']],
                    ['label' => __('Filtered hours'), 'value' => number_format($stats['total_minutes'] / 60, 1)],
                    ['label' => __('Estimated gross'), 'value' => 'GBP '.number_format($stats['gross_pay_pence'] / 100, 2)],
                ];
            @endphp
            @foreach ($cards as $card)
                <article class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-4">
                    <p class="text-[0.58rem] uppercase tracking-[0.14em] text-boss-ivory/35">{{ $card['label'] }}</p>
                    <p class="mt-2 font-display text-2xl text-boss-gold">{{ $card['value'] }}</p>
                </article>
            @endforeach
        </section>

        @if ($openShifts->isNotEmpty())
            <section class="overflow-hidden rounded-xl border border-white/[0.07] bg-white/[0.025]">
                <div class="border-b border-white/[0.06] px-5 py-4">
                    <p class="pd-kicker">{{ __('Live status') }}</p>
                    <h2 class="mt-1 font-display text-xl">{{ __('Currently working') }}</h2>
                </div>
                <div class="grid gap-px bg-white/[0.05] sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($openShifts as $shift)
                        @php
                            $activeBreak = $shift->breaks->firstWhere('ended_at', null);
                            $overdue = $shift->clocked_in_at->lt(now()->subHours(16));
                        @endphp
                        <div class="bg-boss-panel p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold">{{ $shift->user->name }}</p>
                                    <p class="truncate text-xs text-boss-ivory/35">{{ $shift->user->email }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-[0.6rem] font-semibold uppercase tracking-[0.1em] {{ $overdue ? 'bg-red-400/15 text-red-200' : ($activeBreak ? 'bg-amber-400/15 text-amber-200' : 'bg-emerald-400/15 text-emerald-200') }}">
                                    {{ $overdue ? __('Overdue') : ($activeBreak ? __('On break') : __('Working')) }}
                                </span>
                            </div>
                            <p class="mt-3 text-xs text-boss-ivory/45">{{ __('Clocked in :time UK', ['time' => $shift->clocked_in_at->timezone('Europe/London')->format('D d M, H:i')]) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-5">
            <form method="GET" action="{{ route('admin.chatter-hours.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_0.8fr_0.8fr_auto] xl:items-end">
                <label class="block">
                    <span class="pd-label">{{ __('Search') }}</span>
                    <input class="pd-input mt-2" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('Name or email') }}">
                </label>
                <label class="block">
                    <span class="pd-label">{{ __('Chatter') }}</span>
                    <select class="pd-input mt-2" name="chatter_id">
                        <option value="">{{ __('All chatters') }}</option>
                        @foreach ($chatterOptions as $option)
                            <option value="{{ $option->id }}" @selected((int) $filters['chatter_id'] === $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="pd-label">{{ __('Status') }}</span>
                    <select class="pd-input mt-2" name="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['draft', 'submitted', 'changes_requested', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block"><span class="pd-label">{{ __('From') }}</span><input class="pd-input mt-2" type="date" name="from" value="{{ $filters['from'] }}"></label>
                <label class="block"><span class="pd-label">{{ __('To') }}</span><input class="pd-input mt-2" type="date" name="to" value="{{ $filters['to'] }}"></label>
                <button class="pd-btn-primary h-[43px] rounded-lg px-5 text-xs">{{ __('Apply') }}</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-xl border border-white/[0.07] bg-white/[0.025]">
            <div class="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <div><p class="pd-kicker">{{ __('Payroll review') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Weekly timesheets') }}</h2></div>
                <p class="text-xs text-boss-ivory/35">{{ $timesheets->total() }} {{ __('records') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="border-b border-white/[0.06] text-[0.6rem] uppercase tracking-[0.13em] text-boss-ivory/35">
                        <tr><th class="px-5 py-3">{{ __('Chatter') }}</th><th class="px-4 py-3">{{ __('Week') }}</th><th class="px-4 py-3">{{ __('Hours') }}</th><th class="px-4 py-3">{{ __('Estimated pay') }}</th><th class="px-4 py-3">{{ __('Status') }}</th><th class="px-5 py-3 text-right">{{ __('Action') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse ($timesheets as $sheet)
                            <tr class="hover:bg-white/[0.02]">
                                <td class="px-5 py-4"><p class="font-medium">{{ $sheet->user->name }}</p><p class="text-xs text-boss-ivory/35">{{ $sheet->user->email }}</p></td>
                                <td class="px-4 py-4 text-boss-ivory/65">{{ $sheet->period_start->format('d M') }} - {{ $sheet->period_end->format('d M Y') }}</td>
                                <td class="px-4 py-4">{{ number_format($sheet->ordinary_minutes / 60, 2) }}</td>
                                <td class="px-4 py-4 font-semibold text-boss-gold">GBP {{ number_format($sheet->gross_pay_pence / 100, 2) }}</td>
                                <td class="px-4 py-4"><span class="rounded-full bg-white/[0.06] px-2.5 py-1 text-[0.66rem] capitalize text-boss-ivory/65">{{ $sheet->statusLabel() }}</span></td>
                                <td class="px-5 py-4 text-right"><a class="pd-btn-secondary inline-flex rounded-lg px-3 py-2 text-xs" href="{{ route('admin.chatter-hours.timesheets.show', $sheet) }}">{{ __('Review') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-boss-ivory/35">{{ __('No timesheets match these filters yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($timesheets->hasPages())<div class="border-t border-white/[0.06] px-5 py-4">{{ $timesheets->links() }}</div>@endif
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.35fr_1fr]">
            <div class="overflow-hidden rounded-xl border border-white/[0.07] bg-white/[0.025]">
                <div class="border-b border-white/[0.06] px-5 py-4"><p class="pd-kicker">{{ __('Access') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Chatter accounts') }}</h2></div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse ($chatters as $chatter)
                        @php $rate = $chatter->chatterPayRates->first(); $profile = $chatter->chatterProfile; $open = $chatter->chatterShifts->first(); @endphp
                        <details class="group px-5 py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                <div class="min-w-0"><p class="truncate font-medium">{{ $chatter->name }}</p><p class="truncate text-xs text-boss-ivory/35">{{ $chatter->email }}</p></div>
                                <div class="flex items-center gap-2"><span class="rounded-full px-2.5 py-1 text-[0.6rem] {{ $profile?->isActive() ? 'bg-emerald-400/15 text-emerald-200' : 'bg-red-400/15 text-red-200' }}">{{ ucfirst($profile?->employment_status ?? 'inactive') }}</span><svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current text-boss-gold transition group-open:rotate-180"><path d="M4 6l4 4 4-4"/></svg></div>
                            </summary>
                            <div class="mt-5 grid gap-5 border-t border-white/[0.05] pt-5 lg:grid-cols-2">
                                <div class="space-y-3 text-sm text-boss-ivory/55">
                                    <p>{{ __('Timezone') }}: <span class="text-boss-ivory">{{ $profile?->timezone }}</span></p>
                                    <p>{{ __('Current rate') }}: <span class="text-boss-ivory">GBP {{ number_format(($rate?->base_rate_pence ?? 0) / 100, 2) }}/hr</span></p>
                                    <p>{{ __('Shift') }}: <span class="text-boss-ivory">{{ $open ? __('Open since :time UK', ['time' => $open->clocked_in_at->timezone('Europe/London')->format('d M H:i')]) : __('Not working') }}</span></p>
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        <form method="POST" action="{{ route('admin.chatter-hours.chatters.invitation', $chatter) }}">@csrf<button class="pd-btn-secondary rounded-lg px-3 py-2 text-xs">{{ __('Resend invite') }}</button></form>
                                        <form method="POST" action="{{ route('admin.chatter-hours.chatters.status', $chatter) }}">@csrf @method('PATCH')<input type="hidden" name="employment_status" value="{{ $profile?->isActive() ? 'suspended' : 'active' }}"><input type="hidden" name="reason" value="{{ $profile?->isActive() ? 'Suspended from chatter account manager' : 'Reactivated from chatter account manager' }}"><button class="pd-btn-secondary rounded-lg px-3 py-2 text-xs">{{ $profile?->isActive() ? __('Suspend') : __('Reactivate') }}</button></form>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.chatter-hours.chatters.pay-rates', $chatter) }}" class="grid grid-cols-2 gap-3">@csrf
                                    <label><span class="pd-label">{{ __('Rate GBP/hr') }}</span><input class="pd-input mt-1" type="number" step="0.01" min="0" name="base_hourly_rate" value="{{ number_format(($rate?->base_rate_pence ?? 1200) / 100, 2, '.', '') }}" required></label>
                                    <label><span class="pd-label">{{ __('Effective') }}</span><input class="pd-input mt-1" type="date" name="effective_from" value="{{ now('Europe/London')->toDateString() }}" required></label>
                                    <label><span class="pd-label">{{ __('OT after hours') }}</span><input class="pd-input mt-1" type="number" step="0.25" name="overtime_threshold_hours" value="{{ ($rate?->overtime_threshold_minutes ?? 2400) / 60 }}" required></label>
                                    <label><span class="pd-label">{{ __('OT multiplier') }}</span><input class="pd-input mt-1" type="number" step="0.01" name="overtime_multiplier" value="{{ ($rate?->overtime_multiplier_bps ?? 15000) / 10000 }}" required></label>
                                    <label><span class="pd-label">{{ __('Night multiplier') }}</span><input class="pd-input mt-1" type="number" step="0.01" name="night_premium_multiplier" value="{{ ($rate?->night_premium_bps ?? 12000) / 10000 }}" required></label>
                                    <label><span class="pd-label">{{ __('Weekend multiplier') }}</span><input class="pd-input mt-1" type="number" step="0.01" name="weekend_premium_multiplier" value="{{ ($rate?->weekend_premium_bps ?? 12500) / 10000 }}" required></label>
                                    <label><span class="pd-label">{{ __('Night starts') }}</span><input class="pd-input mt-1" type="time" name="night_starts_at" value="{{ substr($rate?->night_starts_at ?? '22:00', 0, 5) }}" required></label>
                                    <label><span class="pd-label">{{ __('Night ends') }}</span><input class="pd-input mt-1" type="time" name="night_ends_at" value="{{ substr($rate?->night_ends_at ?? '06:00', 0, 5) }}" required></label>
                                    <button class="pd-btn-primary col-span-2 rounded-lg px-3 py-2 text-xs">{{ __('Save effective rate') }}</button>
                                </form>
                            </div>
                        </details>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-boss-ivory/35">{{ __('No chatter accounts yet.') }}</p>
                    @endforelse
                </div>
                @if ($chatters->hasPages())<div class="border-t border-white/[0.06] px-5 py-4">{{ $chatters->links() }}</div>@endif
            </div>

            <div class="overflow-hidden rounded-xl border border-white/[0.07] bg-white/[0.025]">
                <div class="border-b border-white/[0.06] px-5 py-4"><p class="pd-kicker">{{ __('Registration') }}</p><h2 class="mt-1 font-display text-xl">{{ __('Chatter requests') }}</h2></div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse ($requests as $joinRequest)
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="truncate font-medium">{{ $joinRequest->name }}</p><p class="truncate text-xs text-boss-ivory/35">{{ $joinRequest->email }}</p></div><span class="rounded-full bg-white/[0.06] px-2 py-1 text-[0.6rem] capitalize">{{ $joinRequest->status }}</span></div>
                            <p class="mt-2 text-xs text-boss-ivory/35">{{ $joinRequest->timezone }} - {{ $joinRequest->created_at->diffForHumans() }}</p>
                            @if ($joinRequest->status === 'pending')
                                <div class="mt-4 grid gap-3">
                                    <form method="POST" action="{{ route('admin.chatter-hours.requests.approve', $joinRequest) }}" class="grid grid-cols-2 gap-2">@csrf
                                        <input class="pd-input" type="number" step="0.01" name="base_hourly_rate" value="12.00" aria-label="Hourly rate" required><input class="pd-input" type="date" name="effective_from" value="{{ now('Europe/London')->toDateString() }}" required>
                                        <input type="hidden" name="overtime_threshold_hours" value="40"><input type="hidden" name="overtime_multiplier" value="1.5"><input type="hidden" name="night_premium_multiplier" value="1.2"><input type="hidden" name="weekend_premium_multiplier" value="1.25"><input type="hidden" name="night_starts_at" value="22:00"><input type="hidden" name="night_ends_at" value="06:00">
                                        <button class="pd-btn-primary col-span-2 rounded-lg px-3 py-2 text-xs">{{ __('Approve and invite') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.chatter-hours.requests.reject', $joinRequest) }}" class="flex gap-2">@csrf<input class="pd-input" name="admin_note" placeholder="{{ __('Reason required') }}" required><button class="pd-btn-secondary rounded-lg px-3 text-xs">{{ __('Reject') }}</button></form>
                                </div>
                            @elseif ($joinRequest->admin_note)
                                <p class="mt-3 text-xs text-boss-ivory/45">{{ $joinRequest->admin_note }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-boss-ivory/35">{{ __('No chatter requests yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </section>

        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="createOpen = false">
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="createOpen = false"></div>
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-white/[0.08] bg-boss-panel p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4"><div><p class="pd-kicker">{{ __('Account invitation') }}</p><h2 class="mt-1 font-display text-2xl">{{ __('Create chatter') }}</h2></div><button type="button" class="text-xl text-boss-ivory/45" @click="createOpen = false" aria-label="Close">&times;</button></div>
                <form method="POST" action="{{ route('admin.chatter-hours.chatters.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">@csrf
                    <label><span class="pd-label">{{ __('Name') }}</span><input class="pd-input mt-2" name="name" required></label>
                    <label><span class="pd-label">{{ __('Email') }}</span><input class="pd-input mt-2" type="email" name="email" required></label>
                    <label><span class="pd-label">{{ __('Timezone') }}</span><input class="pd-input mt-2" name="timezone" value="Europe/London" required></label>
                    <label><span class="pd-label">{{ __('Base GBP/hr') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="base_hourly_rate" value="12.00" required></label>
                    <label><span class="pd-label">{{ __('Overtime after hours') }}</span><input class="pd-input mt-2" type="number" step="0.25" name="overtime_threshold_hours" value="40" required></label>
                    <label><span class="pd-label">{{ __('Overtime multiplier') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="overtime_multiplier" value="1.5" required></label>
                    <label><span class="pd-label">{{ __('Night premium') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="night_premium_multiplier" value="1.2" required></label>
                    <label><span class="pd-label">{{ __('Weekend premium') }}</span><input class="pd-input mt-2" type="number" step="0.01" name="weekend_premium_multiplier" value="1.25" required></label>
                    <label><span class="pd-label">{{ __('Night starts') }}</span><input class="pd-input mt-2" type="time" name="night_starts_at" value="22:00" required></label>
                    <label><span class="pd-label">{{ __('Night ends') }}</span><input class="pd-input mt-2" type="time" name="night_ends_at" value="06:00" required></label>
                    <label><span class="pd-label">{{ __('Effective from') }}</span><input class="pd-input mt-2" type="date" name="effective_from" value="{{ now('Europe/London')->toDateString() }}" required></label>
                    <div class="flex items-end justify-end gap-2"><button type="button" class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs" @click="createOpen = false">{{ __('Cancel') }}</button><button class="pd-btn-primary rounded-lg px-4 py-2.5 text-xs">{{ __('Create and invite') }}</button></div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
