<x-member-layout>
    @php
        // ── Progress calculations ─────────────────────────────────────────────
        $totalCompleted  = count($completedLessonIds);
        $totalAvailable  = $courses->sum('lessons_count');
        $totalEnrolled   = count($enrolledCourseIds);
        $overallPercent  = $totalAvailable > 0 ? (int) round(($totalCompleted / $totalAvailable) * 100) : 0;
        $totalModules    = $courses->sum('modules_count');

        // ── In-progress courses (up to 3, most complete first) ────────────────
        $inProgressCourses = $courses
            ->filter(fn ($c) =>
                in_array($c->id, $enrolledCourseIds, true) &&
                ($courseProgress[$c->id]['status'] ?? 'new') === 'in-progress'
            )
            ->sortByDesc(fn ($c) => $courseProgress[$c->id]['percent'] ?? 0)
            ->take(3)
            ->values();

        // Fall back to enrolled-but-not-started if nothing in progress
        $readyCourses = $inProgressCourses->isEmpty()
            ? $courses
                ->filter(fn ($c) =>
                    in_array($c->id, $enrolledCourseIds, true) &&
                    ($courseProgress[$c->id]['status'] ?? 'new') === 'new'
                )
                ->take(2)
                ->values()
            : collect();

        $continueCourses = $inProgressCourses->merge($readyCourses);
    @endphp

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
    @endif

    <div class="mx-auto max-w-6xl space-y-8">

        {{-- ─────────────────────────────────────────────────────────────── --}}
        {{-- 1. Continue Learning compact panel                              --}}
        {{-- ─────────────────────────────────────────────────────────────── --}}
        @if ($continueCourses->isNotEmpty())
            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-[0.6rem] font-semibold uppercase tracking-[0.2em] text-boss-ivory/30">
                        {{ $inProgressCourses->isNotEmpty() ? __('Continue Learning') : __('Ready to Start') }}
                    </h2>
                    @if ($inProgressCourses->count() > 3)
                        <a href="{{ route('member.courses.index', ['filter' => 'in-progress']) }}"
                           class="text-[0.62rem] text-boss-ivory/28 transition-colors hover:text-boss-gold">
                            {{ __('View all') }} →
                        </a>
                    @endif
                </div>

                <div class="space-y-2.5">
                    @foreach ($continueCourses as $cc)
                        @php
                            $ccProgress  = $courseProgress[$cc->id];
                            $ccStatus    = $ccProgress['status'] ?? 'new';
                            $ccColor     = $cc->displayColor();
                            $ccImage     = $cc->overviewImageUrl();
                            $ccLesson    = $cc->lessons->first(fn ($l) => ! in_array($l->id, $completedLessonIds, true))
                                            ?: $cc->lessons->last();
                            $ccUrl       = $ccLesson
                                ? route('member.courses.lessons.show', [$cc->slug, $ccLesson])
                                : route('member.courses.learn.show', $cc->slug);
                            $ccCTAText   = $ccStatus === 'in-progress' ? __('Continue') : __('Start');
                        @endphp

                        <div class="group flex items-center gap-4 overflow-hidden rounded-2xl border border-white/[0.05] bg-boss-panel px-4 py-4 transition-all duration-300 hover:border-white/[0.09]">

                            {{-- Thumbnail --}}
                            <div class="relative h-14 w-20 shrink-0 overflow-hidden rounded-xl border border-white/[0.06]">
                                @if ($ccImage)
                                    <img src="{{ $ccImage }}" alt="{{ $cc->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.06]">
                                    <div class="absolute inset-0 bg-gradient-to-tr from-black/30 to-transparent"></div>
                                @else
                                    <div class="h-full w-full" style="background: linear-gradient(135deg, {{ $cc->displayColorBackground(0.5) }}, #080810);"></div>
                                @endif
                                {{-- Color accent bottom line --}}
                                <div class="absolute inset-x-0 bottom-0 h-[2px]" style="background: {{ $ccColor }};"></div>
                            </div>

                            {{-- Info --}}
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[0.57rem] uppercase tracking-[0.12em] text-boss-ivory/28">{{ $cc->title }}</p>
                                @if ($ccLesson)
                                    <p class="mt-0.5 truncate text-[0.82rem] font-medium text-boss-ivory/80">{{ $ccLesson->title }}</p>
                                @endif
                                <div class="mt-2 space-y-1">
                                    <div class="flex items-center justify-between text-[0.56rem] text-boss-ivory/25">
                                        <span>{{ $ccProgress['completed'] }}/{{ $ccProgress['total'] }} {{ __('lessons') }}</span>
                                        <span class="font-semibold" style="color: {{ $ccColor }};">{{ $ccProgress['percent'] }}%</span>
                                    </div>
                                    <div class="h-[3px] overflow-hidden rounded-full" style="background: rgba(255,255,255,0.06);">
                                        <div class="h-full rounded-full transition-all duration-700"
                                             style="width: {{ max($ccProgress['percent'], $ccProgress['percent'] > 0 ? 2 : 0) }}%; background: linear-gradient(90deg, {{ $ccColor }}, var(--pd-primary-hover));"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- CTAs --}}
                            <div class="flex shrink-0 flex-col items-end gap-1.5">
                                <a href="{{ $ccUrl }}"
                                   class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-[0.7rem] font-semibold text-boss-ink transition-all hover:opacity-90"
                                   style="background: linear-gradient(135deg, {{ $ccColor }}, var(--pd-primary-hover));">
                                    {{ $ccCTAText }}
                                    <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2.5]"><path d="M3 8h10M9 5l4 3-4 3"/></svg>
                                </a>
                                <a href="{{ route('member.courses.show', $cc->slug) }}"
                                   class="text-[0.62rem] text-boss-ivory/25 transition-colors hover:text-boss-ivory/55">
                                    {{ __('Overview') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ─────────────────────────────────────────────────────────────── --}}
        {{-- 2. Quick stats                                                   --}}
        {{-- ─────────────────────────────────────────────────────────────── --}}
        @if ($totalEnrolled > 0 || $courses->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @php
                    $stats = [
                        [
                            'label' => __('Lessons Done'),
                            'value' => $totalCompleted,
                            'sub'   => __('of') . ' ' . $totalAvailable . ' ' . __('total'),
                            'gold'  => $totalCompleted > 0,
                        ],
                        [
                            'label' => __('Enrolled'),
                            'value' => $totalEnrolled,
                            'sub'   => trans_choice(':count course|:count courses', $courses->count(), ['count' => $courses->count()]) . ' ' . __('available'),
                            'gold'  => false,
                        ],
                        [
                            'label' => __('Overall Progress'),
                            'value' => $overallPercent . '%',
                            'sub'   => __('across all courses'),
                            'gold'  => $overallPercent > 0,
                        ],
                        [
                            'label' => __('Modules'),
                            'value' => $totalModules ?: '—',
                            'sub'   => __('across all courses'),
                            'gold'  => false,
                        ],
                    ];
                @endphp

                @foreach ($stats as $stat)
                    <div class="rounded-2xl border border-white/[0.05] bg-boss-panel px-5 py-4">
                        <p class="text-[0.56rem] uppercase tracking-[0.16em] text-boss-ivory/25">{{ $stat['label'] }}</p>
                        <p class="mt-1.5 font-display text-[1.7rem] font-semibold leading-none {{ $stat['gold'] ? 'text-boss-gold' : 'text-boss-ivory/80' }}">
                            {{ $stat['value'] }}
                        </p>
                        <p class="mt-1 text-[0.6rem] text-boss-ivory/22">{{ $stat['sub'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────── --}}
        {{-- 3. Course Library                                                --}}
        {{-- ─────────────────────────────────────────────────────────────── --}}
        <section>
            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="pd-kicker">{{ __('Training Academy') }}</p>
                    <h1 class="pd-heading pd-text-gradient mt-1.5 text-[clamp(1.5rem,3vw,2.1rem)]">{{ __('Course Library') }}</h1>
                    <p class="mt-1 text-[0.76rem] text-boss-ivory/30">
                        {{ trans_choice(':count course available|:count courses available', $coursesPaginator->total(), ['count' => $coursesPaginator->total()]) }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ([
                        'all'         => __('All'),
                        'new'         => __('New'),
                        'in-progress' => __('In Progress'),
                        'completed'   => __('Completed'),
                    ] as $key => $label)
                        <a
                            href="{{ route('member.courses.index', ['filter' => $key]) }}"
                            class="rounded-full px-3.5 py-1.5 text-[0.7rem] transition-colors {{ $filter === $key ? 'bg-boss-gold font-semibold text-boss-ink' : 'border border-white/[0.07] bg-white/[0.03] text-boss-ivory/40 hover:border-boss-gold/25 hover:text-boss-gold' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ── Course grid ──────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($filteredCourses as $course)
                    @php
                        $progress    = $courseProgress[$course->id] ?? ['completed' => 0, 'total' => $course->lessons_count, 'percent' => 0, 'status' => 'new'];
                        $isCompleted = $progress['status'] === 'completed';
                        $isInProgress = $progress['status'] === 'in-progress';
                        $isEnrolled  = in_array($course->id, $enrolledCourseIds, true);
                        $accessRequest = $accessRequestsByCourse->get($course->id);
                        $color       = $course->displayColor();
                        $bg          = $course->displayColorBackground();
                        $image       = $course->overviewImageUrl();
                        $nextLesson  = $isEnrolled && ! $isCompleted
                            ? ($course->lessons->first(fn ($l) => ! in_array($l->id, $completedLessonIds, true)) ?: null)
                            : null;
                        $primaryUrl  = $isEnrolled
                            ? ($nextLesson
                                ? route('member.courses.lessons.show', [$course->slug, $nextLesson])
                                : route('member.courses.learn.show', $course->slug))
                            : route('member.courses.show', $course->slug);
                        $ctaLabel = match (true) {
                            ! $isEnrolled && $accessRequest?->isPending() => __('View Request'),
                            ! $isEnrolled => __('Request Access'),
                            $isCompleted  => __('Review Course'),
                            $isInProgress => __('Continue Learning'),
                            default       => __('Start Course'),
                        };
                    @endphp

                    <article
                        class="pd-course-card group flex flex-col overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-ink transition-all duration-300 hover:border-white/[0.10] hover:shadow-glow"
                        style="--platform-color: {{ $color }}; --pd-course-card-accent: {{ $color }};"
                    >
                        {{-- Course image / banner --}}
                        <div class="pd-course-card-media relative h-[210px] shrink-0 overflow-hidden">
                            <a
                                href="{{ $primaryUrl }}"
                                class="absolute inset-0 z-10"
                                aria-label="{{ __('Open :course', ['course' => $course->title]) }}"
                            ></a>

                            @if ($image)
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $course->title }}"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                                >
                                <div class="pd-course-card-image-overlay absolute inset-0 bg-gradient-to-t from-boss-ink via-boss-ink/15 to-transparent"></div>
                            @else
                                <div class="pd-course-card-fallback absolute inset-0" style="background: linear-gradient(135deg, {{ $course->displayColorBackground(0.45) }}, rgba(8,8,15,0.95) 70%);"></div>
                                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_20%,rgba(238,180,195,0.10),transparent_55%)]"></div>
                                <div class="absolute inset-0 flex items-center justify-center overflow-hidden px-6">
                                    <p class="select-none text-center font-display text-[2rem] font-bold leading-tight text-white opacity-[0.04]">{{ $course->title }}</p>
                                </div>
                            @endif

                            {{-- Status badge overlay --}}
                            <div class="pointer-events-none absolute left-4 top-4 z-20 flex items-center gap-2">
                                <span class="pd-course-card-platform-badge rounded-full border px-2.5 py-0.5 text-[0.6rem] font-medium backdrop-blur-sm" style="background: {{ $bg }}; color: {{ $color }}; border-color: {{ $color }}22;">
                                    {{ $course->displayPlatform() }}
                                </span>
                                @if ($isCompleted)
                                    <span class="pd-course-card-state-badge rounded-full border border-emerald-400/25 bg-black/40 px-2.5 py-0.5 text-[0.6rem] text-emerald-400 backdrop-blur-sm">✓ {{ __('Completed') }}</span>
                                @elseif ($isInProgress)
                                    <span class="pd-course-card-state-badge rounded-full border border-white/[0.10] bg-black/35 px-2.5 py-0.5 text-[0.6rem] text-boss-ivory/55 backdrop-blur-sm">{{ __('In Progress') }}</span>
                                @elseif ($isEnrolled)
                                    <span class="pd-course-card-state-badge rounded-full border border-boss-gold/22 bg-black/35 px-2.5 py-0.5 text-[0.6rem] text-boss-gold/80 backdrop-blur-sm">{{ __('Enrolled') }}</span>
                                @else
                                    <span class="pd-course-card-state-badge rounded-full border border-boss-gold/22 bg-black/35 px-2.5 py-0.5 text-[0.6rem] text-boss-gold/80 backdrop-blur-sm">{{ __('Locked') }}</span>
                                @endif
                            </div>

                            {{-- Lesson count badge --}}
                            <div class="pointer-events-none absolute right-4 top-4 z-20">
                                <span class="pd-course-card-lesson-badge rounded-full border border-white/[0.08] bg-black/40 px-2.5 py-0.5 text-[0.6rem] text-boss-ivory/45 backdrop-blur-sm">
                                    {{ $course->lessons_count }} {{ __('lessons') }}
                                </span>
                            </div>

                            {{-- Bottom accent line --}}
                            <div class="absolute inset-x-0 bottom-0 h-[2px] opacity-50 transition-opacity duration-300 group-hover:opacity-90"
                                 style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}22);"></div>
                        </div>

                        {{-- Card content --}}
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex-1">
                                @if ($course->modules_count > 0)
                                    <p class="pd-course-card-meta mb-1.5 text-[0.58rem] uppercase tracking-[0.12em] text-boss-ivory/25">
                                        {{ $course->modules_count }} {{ __('modules') }} · {{ $course->lessons_count }} {{ __('lessons') }}
                                    </p>
                                @endif

                                <h2 class="pd-course-card-title pd-heading line-clamp-2 text-[1.05rem] leading-snug text-boss-ivory transition-colors duration-300 group-hover:text-boss-gold-light">
                                    {{ $course->title }}
                                </h2>

                                @if ($course->short_description ?: $course->description)
                                    <p class="pd-course-card-description mt-2 line-clamp-2 text-[0.74rem] leading-relaxed text-boss-ivory/30">
                                        {{ $course->short_description ?: $course->description }}
                                    </p>
                                @endif
                            </div>

                            {{-- Progress + next lesson --}}
                            @if ($isEnrolled)
                                <div class="mt-4 space-y-2.5">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-[0.58rem]">
                                            <span class="text-boss-ivory/28">{{ $progress['completed'] }}/{{ $progress['total'] }} {{ __('complete') }}</span>
                                            <span class="font-semibold" style="color: {{ $progress['percent'] > 0 ? $color : 'rgba(240,237,232,0.22)' }};">
                                                {{ $progress['percent'] }}%
                                            </span>
                                        </div>
                                        <div class="h-1 overflow-hidden rounded-full" style="background: rgba(255,255,255,0.06);">
                                            <div class="h-full rounded-full transition-all duration-700"
                                                 style="width: {{ $progress['percent'] }}%; background: linear-gradient(90deg, {{ $color }}, var(--pd-primary-hover));"></div>
                                        </div>
                                    </div>

                                    @if ($nextLesson)
                                        <div class="flex items-start gap-2.5 rounded-xl border border-white/[0.04] bg-white/[0.02] px-3 py-2.5">
                                            <svg viewBox="0 0 16 16" class="mt-[3px] h-2.5 w-2.5 shrink-0 fill-current opacity-60" style="color: {{ $color }};"><polygon points="4,3 12,8 4,13"/></svg>
                                            <div class="min-w-0">
                                                <p class="text-[0.53rem] uppercase tracking-[0.13em] text-boss-ivory/25">{{ __('Up Next') }}</p>
                                                <p class="mt-0.5 truncate text-[0.7rem] text-boss-ivory/60">{{ $nextLesson->title }}</p>
                                            </div>
                                        </div>
                                    @elseif ($isCompleted)
                                        <div class="flex items-center gap-2 rounded-xl border border-emerald-400/12 bg-emerald-400/[0.04] px-3 py-2.5">
                                            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 shrink-0 fill-none stroke-current stroke-[2.2] text-emerald-400"><path d="M3 8.5l3.5 3.5L13 5"/></svg>
                                            <p class="text-[0.7rem] text-emerald-400/70">{{ __('All lessons completed!') }}</p>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 rounded-xl border border-white/[0.04] bg-white/[0.02] px-3 py-2.5">
                                            <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[1.8] opacity-40" style="color: {{ $color }};"><path d="M8 2v4l2.5 2.5M2 8a6 6 0 1012 0A6 6 0 002 8z"/></svg>
                                            <p class="text-[0.7rem] text-boss-ivory/32">{{ __('Ready to begin') }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-4 rounded-xl border border-white/[0.04] bg-white/[0.015] px-3 py-2.5">
                                    <p class="pd-course-card-status-note text-[0.7rem] text-boss-ivory/30">{{ $accessRequest?->isPending() ? __('Access request sent') : __('Locked pending Kayla approval') }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Footer CTA --}}
                        <div class="shrink-0 border-t border-white/[0.04] p-5 pt-4 transition-colors duration-300 group-hover:bg-boss-gold/[0.04]">
                            @if ($isEnrolled)
                                <a href="{{ $primaryUrl }}" class="flex h-11 items-center justify-between gap-3 rounded-xl border px-4 text-[0.72rem] font-semibold uppercase tracking-[0.12em] transition hover:-translate-y-0.5"
                                   style="border-color: {{ $color }}55; background: {{ $course->displayColorBackground(0.14) }}; color: {{ $color }};">
                                    <span>{{ $ctaLabel }}</span>
                                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2] transition-transform duration-300 group-hover:translate-x-1"
                                         style="color: {{ $color }};"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                                </a>
                            @else
                                <a href="{{ route('member.courses.show', $course->slug) }}" class="flex h-11 items-center justify-between gap-3 rounded-xl border border-boss-gold/35 bg-boss-gold/15 px-4 text-[0.72rem] font-semibold uppercase tracking-[0.12em] text-boss-gold transition hover:-translate-y-0.5 hover:bg-boss-gold/22 hover:text-boss-gold-light">
                                    <span>{{ $ctaLabel }}</span>
                                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2] transition-transform duration-300 group-hover:translate-x-1"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-white/[0.05] bg-boss-ink px-6 py-16 text-center">
                        <p class="font-display text-[1.1rem] text-boss-ivory/30">
                            {{ $courses->isEmpty() ? __('Courses are coming soon.') : __('No courses match this filter yet.') }}
                        </p>
                        @if (! $courses->isEmpty())
                            <a href="{{ route('member.courses.index') }}" class="mt-3 inline-flex text-[0.78rem] text-boss-gold hover:text-boss-gold-light">
                                {{ __('View all courses') }} →
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            @if ($coursesPaginator->hasPages())
                <div class="mt-6">
                    {{ $coursesPaginator->links() }}
                </div>
            @endif
        </section>

    </div>
</x-member-layout>
