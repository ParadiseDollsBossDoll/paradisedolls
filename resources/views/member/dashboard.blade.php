<x-member-layout :shell-stats="$dashboardStats">
    @php
        $user = auth()->user();
        $hour = now()->hour;
        $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));
        $overallPercent = $dashboardStats['overall_percent'];
        $completedLessons = $dashboardStats['completed_lessons'];
        $totalLessons = $dashboardStats['total_lessons'];
        $remainingLessons = max($totalLessons - $completedLessons, 0);
        $nextCourseProgress = $nextActionCourse ? ($courseProgress[$nextActionCourse->id] ?? ['completed' => 0, 'total' => $nextActionCourse->lessons_count, 'percent' => 0]) : null;
        $nextActionUrl = $nextActionCourse
            ? (($nextCourseProgress['completed'] ?? 0) > 0
                ? route('member.courses.learn.show', $nextActionCourse->slug)
                : route('member.courses.show', $nextActionCourse->slug))
            : route('member.courses.index');
        $nextActionLabel = $nextActionCourse
            ? (($nextCourseProgress['completed'] ?? 0) > 0 ? __('Continue lesson') : __('Start a course'))
            : __('Browse academy');
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <section class="pd-dashboard-hero">
            <div class="pd-dashboard-hero-copy">
                <p class="pd-kicker text-boss-ivory/38">{{ $greeting }}</p>
                <h1 class="pd-heading pd-text-gradient mt-3">{{ $user->name }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-boss-ivory/45">
                    {{ __('Your academy overview is ready. Pick up where you left off or start the next course when you are.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ $nextActionUrl }}" class="pd-btn-primary">{{ $nextActionLabel }}</a>
                    <a href="{{ route('member.courses.index') }}" class="pd-btn-secondary">{{ __('View Academy') }}</a>
                </div>
            </div>

            <div class="pd-dashboard-hero-meter">
                <div class="pd-dashboard-ring" style="--progress: {{ $overallPercent }}%;">
                    <div>
                        <strong>{{ $overallPercent }}%</strong>
                        <span>{{ __('Overall') }}</span>
                    </div>
                </div>
                <div class="min-w-0">
                    <p class="text-[0.72rem] uppercase tracking-[0.18em] text-boss-ivory/30">{{ __('Lessons completed') }}</p>
                    <p class="mt-1 font-display text-[1.9rem] leading-none text-boss-ivory">
                        {{ $completedLessons }}<span class="text-boss-ivory/28">/{{ $totalLessons }}</span>
                    </p>
                    <p class="mt-2 text-[0.68rem] text-boss-ivory/28">
                        {{ trans_choice(':count lesson remaining|:count lessons remaining', $remainingLessons, ['count' => $remainingLessons]) }}
                    </p>
                    @if ($nextActionCourse)
                        <p class="mt-3 truncate text-[0.78rem] text-boss-ivory/40">{{ __('Next: :course', ['course' => $nextActionCourse->title]) }}</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="pd-dashboard-readiness">
            <div class="min-w-0">
                <p class="pd-kicker text-boss-ivory/32">{{ __('Onboarding Path') }}</p>
                <p class="mt-2 text-[0.92rem] text-boss-ivory">{{ $onboardingStatus }}</p>
            </div>
            <div class="pd-dashboard-readiness-meter">
                <div class="flex items-center justify-between text-[0.66rem] uppercase tracking-[0.12em] text-boss-ivory/30">
                    <span>{{ __('Readiness') }}</span>
                    <span class="text-boss-gold">{{ $onboardingPercent }}%</span>
                </div>
                <div class="pd-progress-track mt-2">
                    <div class="pd-progress-bar" style="width: {{ $onboardingPercent }}%"></div>
                </div>
                @if ($onboardingAction)
                    <a
                        href="{{ $onboardingAction['url'] }}"
                        class="mt-3 {{ $onboardingAction['style'] === 'primary' ? 'pd-btn-primary' : 'pd-btn-secondary' }}"
                        @if ($onboardingAction['external']) target="_blank" rel="noopener" @endif
                    >
                        {{ $onboardingAction['label'] }}
                    </a>
                @endif
            </div>
        </section>

        @if ($profile->verification_notes && $profile->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED)
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-100">
                <p class="font-semibold">{{ __('Resubmission instructions') }}</p>
                <p class="mt-1 whitespace-pre-line text-red-100/75">{{ $profile->verification_notes }}</p>
            </div>
        @endif

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                [__('In Progress'), $dashboardStats['in_progress_courses'], __('courses active')],
                [__('Completed'), $dashboardStats['completed_courses'], __('courses finished')],
                [__('Not Started'), $dashboardStats['not_started_courses'], __('ready when you are')],
                [__('Courses'), $dashboardStats['total_courses'], __('available now')],
            ] as $stat)
                <div class="pd-stat pd-dashboard-stat">
                    <p class="font-display text-[2rem] leading-none text-boss-gold">{{ $stat[1] }}</p>
                    <p class="mt-3 text-[0.7rem] uppercase tracking-[0.08em] text-boss-ivory/55">{{ $stat[0] }}</p>
                    <p class="mt-1 text-[0.64rem] text-boss-ivory/25">{{ $stat[2] }}</p>
                </div>
            @endforeach
        </section>

        @if ($continueCourses->isNotEmpty())
            <section>
                <div class="mb-4 flex items-center gap-3">
                    <span class="h-4 w-1.5 rounded-full bg-gradient-to-b from-boss-gold to-boss-gold-light"></span>
                    <h2 class="text-[0.7rem] uppercase tracking-[0.2em] text-boss-ivory/42">{{ __('Continue Learning') }}</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($continueCourses as $course)
                        @php
                            $progress = $courseProgress[$course->id];
                            $image = $course->overviewImageUrl();
                        @endphp
                        <a href="{{ route('member.courses.learn.show', $course->slug) }}" class="group pd-panel-strong overflow-hidden transition-all duration-300 hover:border-boss-gold/30 hover:shadow-glow">
                            <div class="h-1 bg-boss-gold/30">
                                <div class="h-full bg-gradient-to-r from-boss-gold to-boss-gold-light" style="width: {{ $progress['percent'] }}%"></div>
                            </div>
                            @if ($image)
                                <div class="relative h-28 overflow-hidden">
                                    <img src="{{ $image }}" alt="{{ $course->title }}" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-t from-boss-panel-strong to-transparent"></div>
                                </div>
                            @endif
                            <div class="p-5">
                                <div class="mb-3 flex items-center gap-2">
                                    <span class="pd-badge">{{ $course->platform_label ?: __('General') }}</span>
                                    <span class="text-[0.65rem] text-boss-ivory/30">{{ $progress['completed'] }}/{{ $progress['total'] }} {{ __('lessons') }}</span>
                                </div>
                                <h3 class="pd-heading text-[1.25rem] text-boss-ivory transition-colors group-hover:text-boss-gold-light">{{ $course->title }}</h3>
                                <p class="mt-3 line-clamp-2 text-[0.78rem] leading-relaxed text-boss-ivory/40">{{ $course->short_description ?: $course->description }}</p>
                                <div class="mt-5 flex items-center justify-between">
                                    <span class="text-[0.75rem] font-medium text-boss-gold">{{ __('Continue') }}</span>
                                    <span class="font-display text-boss-gold">{{ $progress['percent'] }}%</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <div class="mb-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="h-4 w-1.5 rounded-full bg-gradient-to-b from-boss-gold to-boss-gold-light"></span>
                    <h2 class="text-[0.7rem] uppercase tracking-[0.2em] text-boss-ivory/42">{{ __('All Courses') }}</h2>
                </div>
                <a href="{{ route('member.courses.index') }}" class="text-[0.72rem] text-boss-gold hover:text-boss-gold-light">{{ __('Browse all') }} -></a>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/[0.05] bg-boss-panel">
                @forelse ($courses as $course)
                    @php
                        $progress = $courseProgress[$course->id] ?? ['completed' => 0, 'total' => $course->lessons_count, 'percent' => 0];
                    @endphp
                    <a href="{{ route('member.courses.show', $course->slug) }}" class="group flex items-center gap-4 border-t border-white/[0.04] px-5 py-4 transition-colors first:border-t-0 hover:bg-white/[0.025]">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-boss-gold shadow-glow"></span>
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <p class="truncate text-[0.88rem] text-boss-ivory transition-colors group-hover:text-boss-gold-light">{{ $course->title }}</p>
                                @if ($progress['total'] > 0 && $progress['completed'] === $progress['total'])
                                    <span class="pd-badge">{{ __('Done') }}</span>
                                @endif
                            </div>
                            <div class="pd-progress-track">
                                <div class="pd-progress-bar" style="width: {{ $progress['percent'] }}%"></div>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-[0.72rem] text-boss-ivory/30">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                            <span class="min-w-10 text-right font-display text-[1rem] text-boss-gold">{{ $progress['percent'] }}%</span>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center text-[0.9rem] text-boss-ivory/35">{{ __('Courses are coming soon. Check back shortly.') }}</div>
                @endforelse
            </div>
        </section>

        @if ($freshCourses->isNotEmpty())
            <section>
                <div class="mb-4 flex items-center gap-3">
                    <span class="h-4 w-1.5 rounded-full bg-white/[0.08]"></span>
                    <h2 class="text-[0.7rem] uppercase tracking-[0.2em] text-boss-ivory/25">{{ __('Start Something New') }}</h2>
                </div>
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach ($freshCourses as $course)
                        @php
                            $image = $course->overviewImageUrl();
                        @endphp
                        <a href="{{ route('member.courses.show', $course->slug) }}" class="group rounded-2xl border border-white/[0.05] bg-boss-ink p-4 transition-colors hover:border-boss-gold/25">
                            @if ($image)
                                <div class="-mx-4 -mt-4 mb-4 h-24 overflow-hidden rounded-t-2xl">
                                    <img src="{{ $image }}" alt="{{ $course->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover opacity-85 transition duration-500 group-hover:scale-105">
                                </div>
                            @endif
                            <span class="pd-badge">{{ $course->platform_label ?: __('General') }}</span>
                            <h3 class="pd-heading mt-3 text-[1rem] text-boss-ivory/65 transition-colors group-hover:text-boss-ivory">{{ $course->title }}</h3>
                            <p class="mt-2 text-[0.68rem] text-boss-ivory/25">{{ trans_choice(':count lesson|:count lessons', $course->lessons_count, ['count' => $course->lessons_count]) }}</p>
                            <p class="mt-4 text-[0.72rem] text-boss-ivory/28 transition-colors group-hover:text-boss-gold">{{ __('Start course') }} -></p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-member-layout>


