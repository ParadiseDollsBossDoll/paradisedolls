@php
    $selectedMemberId = $selectedProgress['id'] ?? null;
    $selectedMemberQuery = $selectedMemberId ? ['member' => $selectedMemberId] : [];
    $directoryQuery = array_filter([
        'search' => $search !== '' ? $search : null,
        'page' => $directoryMembers->currentPage() > 1 ? $directoryMembers->currentPage() : null,
    ]);
    $modalCloseUrl = route('admin.models.progress', $directoryQuery);
@endphp

<x-admin-layout>
    <div class="mx-auto max-w-[1500px] space-y-7 text-boss-ivory">
        <header class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Members') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2.15rem,4vw,3rem)]">{{ __('Member Progress') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-boss-ivory/[0.45]">
                    {{ __('Search, page through, and open a focused progress view without leaving the member directory.') }}
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-boss-gold/15 bg-boss-gold/[0.07] px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] text-boss-gold">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                {{ __('Academy Overview') }}
            </div>
        </header>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Members') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['members']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.32]">{{ __('Model accounts being tracked') }}</p>
            </div>

            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Courses') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['courses']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.32]">{{ number_format($summary['lessons']) }} {{ __('lessons total') }}</p>
            </div>

            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Average') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ $summary['average_progress'] }}%</p>
                <div class="pd-progress-track mt-3">
                    <div class="pd-progress-bar" style="width: {{ $summary['average_progress'] }}%"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/[0.06] bg-boss-panel-strong p-5 shadow-[0_18px_45px_rgba(0,0,0,0.2)]">
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Active') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold-light">{{ number_format($summary['active_members']) }}</p>
                <p class="mt-1 text-xs text-boss-ivory/[0.32]">{{ __('Members with completed lessons') }}</p>
            </div>
        </section>

        @if ($summary['members'] === 0)
            <div class="rounded-2xl border border-dashed border-white/[0.08] bg-boss-panel-strong p-12 text-center">
                <p class="font-display text-2xl text-boss-ivory">{{ __('No member accounts yet.') }}</p>
                <p class="mt-2 text-sm text-boss-ivory/[0.38]">{{ __('Approved members will appear here once their accounts are created.') }}</p>
            </div>
        @else
            <section class="grid items-start gap-5">
                <div class="rounded-2xl border border-white/[0.07] bg-boss-panel-strong p-4 shadow-[0_24px_70px_rgba(0,0,0,0.22)]">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/[0.35]">{{ __('Directory') }}</p>
                            <h2 class="mt-1 font-display text-2xl text-boss-ivory">{{ __('All Members') }}</h2>
                            <p class="mt-1 text-xs text-boss-ivory/[0.35]">
                                @if ($directoryMembers->total() > 0)
                                    {{ __('Showing') }} {{ $directoryMembers->firstItem() }}-{{ $directoryMembers->lastItem() }} {{ __('of') }} {{ number_format($directoryMembers->total()) }}
                                @else
                                    {{ __('No matching members') }}
                                @endif
                            </p>
                        </div>

                        <form method="GET" action="{{ route('admin.models.progress') }}" class="flex w-full flex-col gap-3 sm:flex-row lg:max-w-xl">
                            @if ($selectedMemberId)
                                <input type="hidden" name="member" value="{{ $selectedMemberId }}">
                            @endif

                            <div class="relative flex-1">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-boss-ivory/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.75 18.5a7.75 7.75 0 1 1 0-15.5 7.75 7.75 0 0 1 0 15.5Z" />
                                </svg>
                                <label for="member-progress-search" class="sr-only">{{ __('Search members') }}</label>
                                <input
                                    id="member-progress-search"
                                    name="search"
                                    type="search"
                                    value="{{ $search }}"
                                    class="pd-input h-12 pl-10"
                                    placeholder="{{ __('Search members') }}"
                                >
                            </div>

                            <button type="submit" class="pd-btn-secondary h-12 whitespace-nowrap">{{ __('Search') }}</button>

                            @if ($search !== '')
                                <a href="{{ route('admin.models.progress', $selectedMemberQuery) }}" class="pd-btn-secondary h-12 whitespace-nowrap">{{ __('Clear') }}</a>
                            @endif
                        </form>
                    </div>

                    @if ($memberCards->isNotEmpty())
                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            @foreach ($memberCards as $member)
                                @php
                                    $memberUrl = route('admin.models.progress', array_filter([
                                        'member' => $member['id'],
                                        'search' => $search !== '' ? $search : null,
                                        'page' => $directoryMembers->currentPage() > 1 ? $directoryMembers->currentPage() : null,
                                    ]));
                                    $isSelected = $selectedMemberId === $member['id'];
                                @endphp

                                <a
                                    href="{{ $memberUrl }}"
                                    class="block rounded-xl border p-4 text-left transition {{ $isSelected ? 'border-boss-gold/35 bg-boss-gold/[0.09] shadow-[0_18px_38px_rgba(238, 180, 195, 0.1)]' : 'border-white/[0.055] bg-white/[0.025] hover:border-white/[0.12] hover:bg-white/[0.04]' }}"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-boss-gold/[0.22] bg-[radial-gradient(circle_at_top,rgba(238, 180, 195, 0.32),rgba(19,15,18,0.94)_70%)] font-display text-sm text-boss-gold-light">
                                            <span>{{ $member['initials'] }}</span>
                                            @if ($member['profile_photo_url'])
                                                <img class="absolute inset-0 h-full w-full object-cover" src="{{ $member['profile_photo_url'] }}" alt="{{ __('Profile photo') }}" onerror="this.remove()">
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-boss-ivory">{{ $member['name'] }}</p>
                                                    <p class="mt-0.5 truncate text-xs text-boss-ivory/[0.38]">{{ $member['email'] }}</p>
                                                </div>
                                                <span class="font-display text-lg text-boss-gold-light">{{ $member['overall_percent'] }}%</span>
                                            </div>

                                            <div class="pd-progress-track mt-3">
                                                <div class="pd-progress-bar" style="width: {{ $member['overall_percent'] }}%"></div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between gap-3 text-[0.68rem] text-boss-ivory/[0.35]">
                                                <span>{{ $member['completed_lessons'] }} / {{ $member['total_lessons'] }} {{ __('lessons') }}</span>
                                                <span>{{ $member['completed_courses'] }} / {{ $courses->count() }} {{ __('courses') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        @if ($directoryMembers->hasPages())
                            <div class="mt-5 flex flex-col gap-3 border-t border-white/[0.06] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-boss-ivory/[0.35]">
                                    {{ __('Page') }} {{ $directoryMembers->currentPage() }} {{ __('of') }} {{ $directoryMembers->lastPage() }}
                                </p>

                                <div class="flex items-center gap-2">
                                    @if ($directoryMembers->onFirstPage())
                                        <span class="pd-btn-secondary h-10 cursor-not-allowed opacity-35">{{ __('Previous') }}</span>
                                    @else
                                        <a href="{{ $directoryMembers->previousPageUrl() }}" class="pd-btn-secondary h-10">{{ __('Previous') }}</a>
                                    @endif

                                    @if ($directoryMembers->hasMorePages())
                                        <a href="{{ $directoryMembers->nextPageUrl() }}" class="pd-btn-secondary h-10">{{ __('Next') }}</a>
                                    @else
                                        <span class="pd-btn-secondary h-10 cursor-not-allowed opacity-35">{{ __('Next') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="mt-5 rounded-xl border border-dashed border-white/[0.08] bg-white/[0.02] p-8 text-center text-sm text-boss-ivory/[0.38]">
                            {{ __('No members match that search.') }}
                        </div>
                    @endif
                </div>

            </section>

            @if ($selectedProgress)
                <div
                    x-data="{
                        open: true,
                        course: 'all',
                        closeUrl: @js($modalCloseUrl),
                        lockScroll() {
                            document.documentElement.classList.add('overflow-hidden');
                            document.body.classList.add('overflow-hidden');
                        },
                        unlockScroll() {
                            document.documentElement.classList.remove('overflow-hidden');
                            document.body.classList.remove('overflow-hidden');
                        },
                        close() {
                            this.unlockScroll();
                            this.open = false;
                            window.location.href = this.closeUrl;
                        },
                    }"
                    x-init="lockScroll()"
                    x-show="open"
                    x-cloak
                    x-transition.opacity.duration.150ms
                    class="fixed inset-0 z-[70] overflow-y-auto bg-black/75 px-3 py-4 backdrop-blur-sm sm:px-5 lg:py-6"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="member-progress-modal-title"
                    @keydown.escape.window="close()"
                    @click.self="close()"
                >
                    <article
                        x-show="open"
                        x-transition:enter="transition duration-180 ease-out"
                        x-transition:enter-start="translate-y-4 scale-[0.98] opacity-0"
                        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                        class="mx-auto w-full max-w-6xl overflow-hidden rounded-2xl border border-white/[0.08] bg-[linear-gradient(135deg,rgba(23,17,22,0.99),rgba(10,8,10,0.98))] text-boss-ivory shadow-[0_30px_90px_rgba(0,0,0,0.55)]"
                    >
                        <div class="relative overflow-hidden border-b border-white/[0.06] bg-[radial-gradient(circle_at_top_left,rgba(238, 180, 195, 0.13),transparent_35%),linear-gradient(135deg,rgba(29,22,28,0.98),rgba(15,11,14,0.98))] p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-start gap-4">
                                    <div class="relative flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-boss-gold/[0.28] bg-[radial-gradient(circle_at_top,rgba(238, 180, 195, 0.34),rgba(19,15,18,0.94)_70%)] font-display text-sm text-boss-gold-light shadow-[0_16px_34px_rgba(238, 180, 195, 0.1)] sm:h-16 sm:w-16">
                                        <span>{{ $selectedProgress['initials'] }}</span>
                                        @if ($selectedProgress['profile_photo_url'])
                                            <img class="absolute inset-0 h-full w-full object-cover" src="{{ $selectedProgress['profile_photo_url'] }}" alt="{{ __('Profile photo') }}" onerror="this.remove()">
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold/70">{{ __('Selected Member') }}</p>
                                        <h2 id="member-progress-modal-title" class="mt-1 truncate font-display text-[clamp(1.35rem,3vw,1.85rem)] text-boss-ivory">{{ $selectedProgress['name'] }}</h2>
                                        <p class="mt-1 truncate text-sm text-boss-ivory/[0.42]">{{ $selectedProgress['email'] }}</p>

                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            @if ($selectedProgress['last_activity'])
                                                <span class="rounded-full border border-emerald-300/[0.18] bg-emerald-300/10 px-2.5 py-1 text-[0.64rem] text-emerald-200">{{ __('Active') }}</span>
                                            @endif
                                            <span class="rounded-full border border-boss-gold/15 bg-boss-gold/[0.07] px-2.5 py-1 text-[0.64rem] text-boss-gold-light">
                                                {{ $selectedProgress['completed_courses'] }} / {{ $courses->count() }} {{ __('courses complete') }}
                                            </span>
                                            <span class="rounded-full border border-white/[0.08] bg-white/[0.035] px-2.5 py-1 text-[0.64rem] text-boss-ivory/[0.42]">
                                                {{ $selectedProgress['completed_lessons'] }} / {{ $selectedProgress['total_lessons'] }} {{ __('lessons') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <a
                                    href="{{ $modalCloseUrl }}"
                                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/[0.08] bg-white/[0.03] text-boss-ivory/50 transition hover:border-boss-gold/25 hover:text-boss-gold-light"
                                    aria-label="{{ __('Close progress modal') }}"
                                    @click.prevent="close()"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="border-b border-white/[0.06] p-3 sm:p-4">
                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-xl border border-boss-gold/[0.12] bg-white/[0.025] p-3">
                                    <div class="flex items-end justify-between gap-3">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.16em] text-boss-ivory/[0.34]">{{ __('Overall Progress') }}</p>
                                            <p class="mt-1 font-display text-3xl leading-none text-boss-gold-light">{{ $selectedProgress['overall_percent'] }}%</p>
                                        </div>
                                        <p class="pb-0.5 text-right text-[0.68rem] leading-4 text-boss-ivory/[0.38]">
                                            {{ $selectedProgress['completed_lessons'] }} / {{ $selectedProgress['total_lessons'] }}<br>{{ __('lessons') }}
                                        </p>
                                    </div>
                                    <div class="pd-progress-track mt-3">
                                        <div class="pd-progress-bar" style="width: {{ $selectedProgress['overall_percent'] }}%"></div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-3">
                                    <p class="text-[0.62rem] uppercase tracking-[0.16em] text-boss-ivory/[0.32]">{{ __('In Progress') }}</p>
                                    <p class="mt-1 font-display text-2xl text-boss-gold">{{ $selectedProgress['in_progress_courses'] }}</p>
                                </div>

                                <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-3">
                                    <p class="text-[0.62rem] uppercase tracking-[0.16em] text-boss-ivory/[0.32]">{{ __('Completed Lessons') }}</p>
                                    <p class="mt-1 font-display text-2xl text-boss-gold">{{ $selectedProgress['completed_lessons'] }}</p>
                                </div>

                                <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-3">
                                    <p class="text-[0.62rem] uppercase tracking-[0.16em] text-boss-ivory/[0.32]">{{ __('Last Activity') }}</p>
                                    <p class="mt-1 truncate text-sm text-boss-ivory/[0.72]">
                                        {{ $selectedProgress['last_activity'] ? $selectedProgress['last_activity']->diffForHumans() : __('None yet') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 sm:p-4">
                            <div class="min-w-0 overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.025]">
                                <div class="border-b border-white/[0.06] p-4 sm:p-5">
                                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                        <div>
                                            <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/[0.32]">{{ __('Course Progress') }}</p>
                                            <h3 class="mt-1 font-display text-[clamp(1.45rem,2vw,1.85rem)] leading-tight text-boss-ivory">{{ __('Course Breakdown') }}</h3>
                                        </div>

                                        <div class="flex flex-col gap-2 sm:flex-row xl:justify-end">
                                            <label for="member-progress-course" class="sr-only">{{ __('Course') }}</label>
                                            <select id="member-progress-course" x-model="course" class="pd-input h-11 min-w-full sm:min-w-[230px]">
                                                <option value="all">{{ __('All courses') }}</option>
                                                @foreach ($courses as $course)
                                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                                @endforeach
                                            </select>

                                            <a href="{{ route('admin.courses.create') }}" class="pd-btn-secondary h-11 justify-center whitespace-nowrap">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14" />
                                                </svg>
                                                {{ __('New Course') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                @if ($selectedProgress['courses']->isNotEmpty())
                                    <div class="grid gap-2 p-3 sm:p-4 xl:grid-cols-2">
                                        @foreach ($selectedProgress['courses'] as $progress)
                                            @php
                                                $statusLabel = match ($progress['status']) {
                                                    'complete' => __('Complete'),
                                                    'progress' => __('In progress'),
                                                    'empty' => __('No lessons'),
                                                    default => __('Not started'),
                                                };

                                                $statusClass = match ($progress['status']) {
                                                    'complete' => 'border-emerald-300/[0.18] bg-emerald-300/10 text-emerald-200',
                                                    'progress' => 'border-boss-gold/20 bg-boss-gold/10 text-boss-gold-light',
                                                    'empty' => 'border-white/[0.06] bg-white/[0.03] text-boss-ivory/[0.32]',
                                                    default => 'border-white/[0.06] bg-white/[0.03] text-boss-ivory/[0.42]',
                                                };
                                            @endphp
                                            <div
                                                x-show="course === 'all' || course === '{{ $progress['id'] }}'"
                                                x-transition.opacity.duration.150ms
                                                class="group rounded-xl border border-white/[0.055] bg-[#151015]/78 p-4 transition hover:border-boss-gold/[0.18] hover:bg-white/[0.035]"
                                            >
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $progress['color'] }}"></span>
                                                            <p class="truncate text-sm font-semibold text-boss-ivory transition group-hover:text-boss-gold-light">{{ $progress['title'] }}</p>
                                                        </div>
                                                        <p class="mt-1 truncate text-[0.68rem] uppercase tracking-[0.12em] text-boss-ivory/30">{{ $progress['platform'] }}</p>
                                                    </div>

                                                    <div class="flex shrink-0 items-center gap-2 sm:justify-end">
                                                        <span class="rounded-full border px-2.5 py-1 text-[0.62rem] {{ $statusClass }}">{{ $statusLabel }}</span>
                                                        <span class="font-display text-xl leading-none text-boss-gold-light">{{ $progress['percent'] }}%</span>
                                                    </div>
                                                </div>

                                                <div class="mt-3 flex items-center justify-between gap-4">
                                                    <p class="text-xs text-boss-ivory/[0.34]">{{ $progress['completed'] }} / {{ $progress['total'] }} {{ __('lessons') }}</p>
                                                    <p class="text-[0.66rem] uppercase tracking-[0.14em] text-boss-ivory/[0.26]">{{ __('Completion') }}</p>
                                                </div>

                                                <div class="pd-progress-track mt-2">
                                                    <div class="pd-progress-bar" style="width: {{ $progress['percent'] }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="m-4 rounded-xl border border-dashed border-white/[0.08] bg-white/[0.02] p-8 text-center text-sm text-boss-ivory/35">
                                        {{ __('No courses have been created yet.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @endif
        @endif
    </div>
</x-admin-layout>

