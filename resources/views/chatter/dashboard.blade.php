<x-chatter-layout>
@php
    $formatMinutes = fn(int $minutes) => intdiv($minutes, 60).'h '.str_pad((string)($minutes % 60), 2, '0', STR_PAD_LEFT).'m';
    $earningsFormatter = app(\App\Services\ChatterPlatformEarnings::class);
@endphp
<div class="mx-auto max-w-7xl space-y-5 text-boss-ivory">
    @if(session('status'))<div class="rounded-md border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="rounded-md border border-red-400/25 bg-red-400/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>@endif

    <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="pd-kicker">{{ __('Working Hours') }}</p><h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,3rem)]">{{ __('Time Tracker') }}</h1><p class="mt-2 text-sm text-boss-ivory/45">{{ __('Your time: :timezone - Payroll weeks use Europe/London', ['timezone'=>$tz]) }}</p></div></div>

    @if($trainingCourses->isNotEmpty())
        <section class="rounded-md border border-white/[0.08] bg-white/[0.025] p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="pd-kicker">{{ __('Training Academy') }}</p>
                    <h2 class="mt-1 font-display text-2xl">{{ __('Unlocked courses') }}</h2>
                </div>
                <a href="{{ route('chatter.courses.index') }}" class="pd-btn-secondary inline-flex justify-center rounded-lg px-4 py-2.5 text-xs">{{ __('View academy') }}</a>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($trainingCourses->take(3) as $course)
                    <a href="{{ route('chatter.courses.show', $course->slug) }}" class="group rounded-md border border-white/[0.07] bg-white/[0.025] p-4 transition hover:border-boss-gold/25 hover:bg-boss-gold/[0.04]">
                        <p class="truncate font-semibold text-boss-ivory group-hover:text-boss-gold">{{ $course->title }}</p>
                        <p class="mt-1 text-xs text-boss-ivory/40">{{ $course->platform_label ?: __('General training') }} - {{ trans_choice(':count lesson|:count lessons', (int) $course->published_lessons_count, ['count' => (int) $course->published_lessons_count]) }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="grid gap-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(280px,0.75fr)]">
        <div
            x-data="chatterTimeTracker({
                stateUrl: @js(route('chatter.state')),
                initialWorkedSeconds: @js($activeWorkedSeconds),
                initialTimerRunning: @js($activeTimerRunning),
                initialOnBreak: @js((bool) $activeBreak),
                hasOpenShift: @js((bool) $openShift),
            })"
            class="rounded-md border border-white/[0.08] bg-white/[0.035] p-6 sm:p-8"
        >
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/40">{{ $openShift ? ($activeBreak ? __('On break') : __('Active shift')) : __('Ready') }}</p>
                    <p class="mt-3 font-display text-4xl sm:text-5xl" x-text="{{ $openShift ? 'format(displaySeconds)' : '\'00:00:00\'' }}"></p>
                    @if($openShift)
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-boss-gold/20 bg-boss-gold/10 px-2.5 py-1 text-xs text-boss-gold">{{ $openShift->workRole?->name ?? __('Chatter') }}</span>
                            @if($openShift->platform)
                                <span class="rounded-full border border-white/10 bg-white/[0.04] px-2.5 py-1 text-xs text-boss-ivory/60">{{ $openShift->platform }}</span>
                            @endif
                            @if($openShift->model)
                                <span class="rounded-full border border-white/10 bg-white/[0.04] px-2.5 py-1 text-xs text-boss-ivory/60">{{ $openShift->model->name }}</span>
                            @endif
                            <span class="text-xs text-boss-ivory/40">${{ number_format(($openShift->hourly_rate_pence ?? 0) / 100, 2) }} USD/hr</span>
                        </div>
                        <p class="mt-2 text-xs text-boss-ivory/40 tabular-nums">
                            {{ __('Starting balance: :balance', ['balance' => $earningsFormatter->formatBalance($openShift->clock_in_earning_balance_minor, $openShift->earning_unit, $openShift->earning_currency)]) }}
                        </p>
                        <p class="mt-2 text-sm text-boss-ivory/45">{{ __('Clocked in :time', ['time' => $openShift->clocked_in_at->timezone($tz)->format('D, j M - g:i A T')]) }}</p>
                        <p class="mt-1 text-xs text-boss-ivory/35">{{ __('Worked time excludes breaks and is recalculated from server records after refresh.') }}</p>
                    @endif
                </div>
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs {{ $openShift ? 'border-emerald-400/25 bg-emerald-400/10 text-emerald-200' : 'border-white/10 text-boss-ivory/45' }}"><span class="h-2 w-2 rounded-full {{ $openShift ? 'bg-emerald-400' : 'bg-white/25' }}"></span>{{ $openShift ? ($activeBreak ? __('On break') : __('Clocked in')) : __('Clocked out') }}</span>
            </div>
            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                @if(!$openShift)<form
                    method="POST"
                    action="{{ route('chatter.clock-in') }}"
                    class="space-y-3 sm:col-span-2"
                    x-data="{
                        open: false,
                        search: '',
                        selected: @js((string) old('model_id', '')),
                        selectedLabel: '',
                        activeIndex: -1,
                        models: @js($availableModels->map(fn ($model) => [
                            'id' => (string) $model->id,
                            'name' => $model->name,
                            'email' => $model->email,
                            'label' => trim($model->name.' - '.$model->email),
                        ])->values()),
                        init() {
                            const match = this.models.find((model) => model.id === this.selected);
                            this.selectedLabel = match?.label || '';
                        },
                        get filtered() {
                            const q = this.search.trim().toLowerCase();

                            return q
                                ? this.models.filter((model) => `${model.name || ''} ${model.email || ''}`.toLowerCase().includes(q))
                                : this.models;
                        },
                        choose(model) {
                            this.selected = model.id;
                            this.selectedLabel = model.label;
                            this.search = '';
                            this.activeIndex = -1;
                            this.open = false;
                        },
                        openDropdown() {
                            this.open = true;
                            this.search = '';
                            this.activeIndex = -1;
                            this.$nextTick(() => this.$refs.modelSearch?.focus());
                        },
                        filterModels() {
                            this.selected = '';
                            this.selectedLabel = '';
                            this.activeIndex = this.filtered.length ? 0 : -1;
                            this.open = true;
                        },
                        moveActive(step) {
                            this.open = true;

                            if (! this.filtered.length) {
                                this.activeIndex = -1;
                                return;
                            }

                            if (this.activeIndex < 0) {
                                this.activeIndex = step > 0 ? 0 : this.filtered.length - 1;
                                return;
                            }

                            this.activeIndex = (this.activeIndex + step + this.filtered.length) % this.filtered.length;
                        },
                        chooseActive() {
                            if (this.open && this.activeIndex >= 0 && this.filtered[this.activeIndex]) {
                                this.choose(this.filtered[this.activeIndex]);
                            }
                        },
                        ensureValidSelection(event) {
                            if (this.models.some((model) => model.id === this.selected)) {
                                return;
                            }

                            event.preventDefault();
                            this.openDropdown();
                        },
                    }"
                    @submit="ensureValidSelection($event)"
                    @keydown.escape.window="open = false"
                >@csrf
                    @if($availableModels->isNotEmpty())
                        <label class="block">
                            <span class="pd-label">{{ __('Model') }}</span>
                            <div class="relative mt-2" @click.outside="open = false">
                                <input type="hidden" name="model_id" x-model="selected">
                                <button
                                    type="button"
                                    class="pd-input pd-combobox-trigger"
                                    aria-haspopup="listbox"
                                    :aria-expanded="open.toString()"
                                    @click="openDropdown()"
                                >
                                    <span class="min-w-0 flex-1 truncate" x-text="selectedLabel || '{{ __('Choose the model you are working with') }}'"></span>
                                    <svg class="h-3.5 w-3.5 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 6l4 4 4-4" />
                                    </svg>
                                </button>

                                <div x-cloak x-show="open" x-transition class="pd-combobox-menu absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-md shadow-luxe" role="listbox">
                                    <div class="pd-combobox-search-wrap p-2">
                                        <input
                                            x-ref="modelSearch"
                                            type="text"
                                            x-model="search"
                                            placeholder="{{ __('Search model name or email...') }}"
                                            class="pd-combobox-search"
                                            role="combobox"
                                            aria-autocomplete="list"
                                            :aria-expanded="open.toString()"
                                            autocomplete="off"
                                            @input="filterModels()"
                                            @keydown.arrow-down.prevent="moveActive(1)"
                                            @keydown.arrow-up.prevent="moveActive(-1)"
                                            @keydown.enter.prevent="chooseActive()"
                                            @keydown.escape.stop="open = false"
                                        >
                                    </div>
                                    <div class="max-h-56 overflow-y-auto py-1">
                                        <template x-if="filtered.length === 0">
                                            <p class="pd-combobox-empty">{{ __('No models found') }}</p>
                                        </template>
                                        <template x-for="(model, index) in filtered" :key="model.id">
                                            <button
                                                type="button"
                                                class="pd-combobox-option"
                                                :class="selected === model.id || activeIndex === index ? 'is-selected' : ''"
                                                role="option"
                                                :aria-selected="(selected === model.id).toString()"
                                                @mouseenter="activeIndex = index"
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
                    @else
                        <div class="rounded-md border border-amber-400/20 bg-amber-400/10 px-4 py-3 text-sm text-amber-100">{{ __('Ask admin to assign at least one model before clocking in.') }}</div>
                    @endif
                    <label class="block"><span class="pd-label">{{ __('Website / platform') }}</span><input class="pd-input mt-2" name="platform" list="chatter-platform-options" placeholder="{{ __('Type the site you are working on') }}" required><datalist id="chatter-platform-options"><option value="Chaturbate"></option><option value="Stripchat"></option><option value="Babestation"></option><option value="OnlyFans"></option><option value="Fansly"></option><option value="ManyVids"></option></datalist></label>
                    <label class="block">
                        <span class="pd-label">{{ __('Starting earning balance') }}</span>
                        <input class="pd-input mt-2" type="text" name="clock_in_earning_balance" inputmode="decimal" value="{{ old('clock_in_earning_balance') }}" placeholder="{{ __('Tokens, or GBP amount for Babestation') }}" required>
                    </label>
                    @if($availableWorkRoles->count() > 1)
                        <label class="block"><span class="pd-label">{{ __('What are you working on?') }}</span><select class="pd-input mt-2" name="work_role_id" required>@foreach($availableWorkRoles as $assignment)<option value="{{ $assignment->chatter_work_role_id }}">{{ $assignment->workRole->name }} - ${{ number_format($assignment->hourly_rate_pence / 100, 2) }} USD/hr</option>@endforeach</select></label>
                    @elseif($availableWorkRoles->isNotEmpty())
                        <input type="hidden" name="work_role_id" value="{{ $availableWorkRoles->first()->chatter_work_role_id }}">
                        <div class="flex items-center justify-between rounded-md border border-white/[0.07] bg-white/[0.025] px-4 py-3 text-sm"><span>{{ $availableWorkRoles->first()->workRole->name }}</span><span class="text-boss-ivory/45">${{ number_format($availableWorkRoles->first()->hourly_rate_pence / 100, 2) }} USD/hr</span></div>
                    @endif
                    <button class="pd-btn-primary h-12 w-full" @disabled($availableModels->isEmpty())>{{ __('Clock In') }}</button></form>@else
                    @if($activeBreak)<form method="POST" action="{{ route('chatter.breaks.end') }}">@csrf<button class="pd-btn-secondary h-12 w-full">{{ __('Resume Work') }}</button></form>@else<form method="POST" action="{{ route('chatter.breaks.start') }}">@csrf<button class="pd-btn-secondary h-12 w-full">{{ __('Start Break') }}</button></form>@endif
                    <form method="POST" action="{{ route('chatter.clock-out') }}" class="space-y-3 sm:col-span-2">@csrf
                        <label class="block">
                            <span class="pd-label">{{ __('Ending earning balance') }}</span>
                            <input class="pd-input mt-2" type="text" name="clock_out_earning_balance" inputmode="decimal" value="{{ old('clock_out_earning_balance') }}" placeholder="{{ __('Tokens, or GBP amount for Babestation') }}" required>
                        </label>
                        <button class="h-12 w-full rounded-md border border-red-400/30 bg-red-400/10 px-5 text-sm font-bold text-red-200 transition hover:bg-red-400/20">{{ __('Clock Out') }}</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            @foreach([[__('Today worked'),$formatMinutes($todayTotals['worked_minutes'])],[__('This week worked'),$formatMinutes($currentTimesheet->ordinary_minutes)],[__('This month worked'),$formatMinutes($monthTotals['worked_minutes'])],[__('Estimated pay USD'),'$'.number_format($currentTimesheet->gross_pay_pence/100,2)],[__('Estimated pay PHP'),'₱'.number_format($currentPayPhpCentavos/100,2)],[__('USD to PHP rate'),'₱'.number_format((float) $usdToPhpRate,2)]] as [$label,$value])<div class="rounded-md border border-white/[0.08] bg-white/[0.035] p-4"><p class="text-[0.62rem] uppercase tracking-[0.14em] text-boss-ivory/35">{{ $label }}</p><p class="mt-3 font-display text-2xl">{{ $value }}</p></div>@endforeach
        </div>
    </section>

    <section class="rounded-md border border-white/[0.08] bg-white/[0.025] p-5"><div class="flex flex-wrap items-end justify-between gap-3"><div><p class="pd-kicker">{{ __('Current Week') }}</p><h2 class="mt-1 font-display text-2xl">{{ $currentTimesheet->period_start->format('j M') }} - {{ $currentTimesheet->period_end->format('j M Y') }}</h2></div><span class="rounded-full border border-white/10 px-3 py-1 text-xs text-boss-ivory/50">{{ $currentTimesheet->workflowStatusLabel() }}</span></div>
        <div class="mt-5 overflow-x-auto"><table class="pd-table min-w-[1240px]"><thead><tr><th>{{ __('Date') }}</th><th>{{ __('Work role') }}</th><th>{{ __('Model') }}</th><th>{{ __('Platform') }}</th><th>{{ __('Clock in') }}</th><th>{{ __('Clock out') }}</th><th>{{ __('Generated') }}</th><th>{{ __('Commission') }}</th><th>{{ __('Hours Worked') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>@forelse($currentShifts as $shift)<tr><td>{{ $shift->clocked_in_at->timezone($tz)->format('D, j M') }}</td><td><span class="font-medium">{{ $shift->workRole?->name ?? __('Chatter') }}</span><span class="mt-0.5 block text-xs text-boss-ivory/35">${{ number_format(($shift->hourly_rate_pence ?? 0) / 100, 2) }} USD/hr</span></td><td>{{ $shift->model?->name ?? '-' }}</td><td>{{ $shift->platform ?? '-' }}</td><td>{{ $shift->clocked_in_at->timezone($tz)->format('g:i A') }}</td><td>{{ $shift->clocked_out_at?->timezone($tz)->format('g:i A') ?? __('Active') }}</td><td>{{ $earningsFormatter->formatGenerated($shift->generated_earning_units, $shift->generated_earning_pence, $shift->earning_unit, $shift->earning_currency) }}</td><td>{{ $earningsFormatter->formatCommission($shift->commission_pence, $shift->commission_currency) }}</td><td>{{ $formatMinutes((int) $shift->getAttribute('worked_minutes')) }}</td><td>{{ $shift->isOpen() ? ($activeBreak && $openShift?->is($shift) ? __('On break') : __('In progress')) : __('Recorded') }}</td></tr>@empty<tr><td colspan="10" class="text-center text-boss-ivory/40">{{ __('No shifts recorded this week.') }}</td></tr>@endforelse</tbody></table></div>
    </section>

    <section class="rounded-md border border-white/[0.08] bg-white/[0.025] p-5">
        <p class="pd-kicker">{{ __('Timesheet History') }}</p>
        <div class="mt-4 space-y-3">
            @foreach($timesheets as $sheet)
                <div class="flex flex-col gap-3 rounded-md border border-white/[0.07] bg-white/[0.025] p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold">{{ $sheet->period_start->format('j M') }} - {{ $sheet->period_end->format('j M Y') }}</p>
                        <p class="mt-1 text-xs text-boss-ivory/45">
                            {{ $formatMinutes($sheet->ordinary_minutes) }} · ${{ number_format($sheet->gross_pay_pence/100,2) }} USD · ₱{{ number_format($currency->phpCentavosForTimesheet($sheet)/100,2) }} PHP · {{ $sheet->workflowStatusLabel() }}
                        </p>
                    </div>
                    @if($sheet->periodHasEnded())
                        <form method="POST" action="{{ route('chatter.timesheets.problem', $sheet) }}" class="flex min-w-0 flex-col gap-2 sm:flex-row">
                            @csrf
                            <input name="reason" class="pd-input h-9 min-w-0" placeholder="{{ __('Describe the timesheet problem') }}" required>
                            <button class="pd-btn-secondary h-9 whitespace-nowrap">{{ __('Report a problem') }}</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        @if($timesheets->hasPages())<div class="mt-5">{{ $timesheets->links() }}</div>@endif
    </section>
</div>
</x-chatter-layout>
