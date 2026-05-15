<x-member-layout>
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));
        $initials = collect(explode(' ', trim(auth()->user()->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'M';
        $continueCourses = $courses->filter(fn ($course) => ($courseProgress[$course->id]['completed'] ?? 0) > 0 && ($courseProgress[$course->id]['completed'] ?? 0) < ($courseProgress[$course->id]['total'] ?? 0));
        $freshCourses = $courses->filter(fn ($course) => ($courseProgress[$course->id]['completed'] ?? 0) === 0)->take(3);
        $discordInviteUrl = config('paradise.community_url');

        $onboardingAction = null;

        if (! $profile->hasInformationForm()) {
            $onboardingAction = [
                'url' => route('member.onboarding.edit'),
                'label' => __('Complete information'),
                'style' => 'primary',
                'external' => false,
            ];
        } elseif ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED) {
            $onboardingAction = [
                'url' => route('member.verification.edit'),
                'label' => __('Resubmit verification'),
                'style' => 'primary',
                'external' => false,
            ];
        } elseif (! $profile->hasVerificationSubmission()) {
            $onboardingAction = [
                'url' => route('member.verification.edit'),
                'label' => __('Complete verification'),
                'style' => 'primary',
                'external' => false,
            ];
        } elseif ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_SUBMITTED) {
            $onboardingAction = [
                'url' => route('member.verification.edit'),
                'label' => __('View verification'),
                'style' => 'secondary',
                'external' => false,
            ];
        } elseif ($profile->isCommunityInvited() && ! $profile->isCommunityRoleAssigned() && $discordInviteUrl) {
            $onboardingAction = [
                'url' => $discordInviteUrl,
                'label' => __('Open Discord invite'),
                'style' => 'primary',
                'external' => true,
            ];
        } elseif ($profile->isCommunityRoleAssigned()) {
            $onboardingAction = [
                'url' => route('community.show'),
                'label' => __('Open Community Chat'),
                'style' => 'secondary',
                'external' => false,
            ];
        } elseif ($profile->isVerified()) {
            $onboardingAction = [
                'url' => route('member.verification.edit'),
                'label' => __('View verification'),
                'style' => 'secondary',
                'external' => false,
            ];
        }
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <section class="pd-panel-strong p-5 md:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="pd-kicker text-boss-ivory/35">{{ __('Boss Doll Blueprint') }}</p>
                    <h2 class="pd-heading mt-2 text-[clamp(1.45rem,3vw,2rem)] text-boss-ivory">{{ __('Onboarding Path') }}</h2>
                    <p class="mt-2 max-w-xl text-[0.82rem] text-boss-ivory/35">{{ $profile->onboardingStatusLabel() }}</p>
                </div>
                <div class="w-full lg:w-72">
                    <div class="mb-2 flex items-center justify-between text-[0.66rem] uppercase tracking-[0.12em] text-boss-ivory/30">
                        <span>{{ __('Readiness') }}</span>
                        <span class="text-boss-gold">{{ $profile->onboardingPercent() }}%</span>
                    </div>
                    <div class="pd-progress-track">
                        <div class="pd-progress-bar" style="width: {{ $profile->onboardingPercent() }}%"></div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($onboardingAction)
                            <a
                                href="{{ $onboardingAction['url'] }}"
                                class="{{ $onboardingAction['style'] === 'primary' ? 'pd-btn-primary' : 'pd-btn-secondary' }}"
                                @if ($onboardingAction['external']) target="_blank" rel="noopener" @endif
                            >
                                {{ $onboardingAction['label'] }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if ($profile->verification_notes && $profile->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED)
                <div class="mt-5 rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-100">
                    <p class="font-semibold">{{ __('Resubmission instructions') }}</p>
                    <p class="mt-1 whitespace-pre-line text-red-100/75">{{ $profile->verification_notes }}</p>
                </div>
            @endif
        </section>

        <section class="pd-panel relative overflow-hidden p-6 md:p-8">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(201,169,110,0.12),transparent_34%)]"></div>
            <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-boss-gold/35 bg-boss-gold/10 font-display text-[1.2rem] text-boss-gold-light shadow-glow">
                        {{ $initials }}
                    </div>
                    <div>
                        <p class="pd-kicker text-boss-ivory/35">{{ $greeting }}</p>
                        <h1 class="pd-heading pd-text-gradient mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ auth()->user()->name }}</h1>
                        <p class="mt-2 text-[0.82rem] text-boss-ivory/35">{{ __('Your academy progress is ready when you are.') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative flex h-24 w-24 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/[0.04]">
                        <div class="absolute inset-2 rounded-full border border-white/[0.06]"></div>
                        <div class="text-center">
                            <p class="font-display text-[1.5rem] leading-none text-boss-gold">{{ $overallPercent }}%</p>
                            <p class="mt-1 text-[0.55rem] uppercase tracking-[0.12em] text-boss-ivory/28">{{ __('Overall') }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[0.86rem] text-boss-ivory">{{ $completedLessons }} <span class="text-boss-ivory/35">/ {{ $totalLessons }}</span></p>
                        <p class="mt-1 text-[0.62rem] uppercase tracking-[0.12em] text-boss-ivory/28">{{ __('Lessons done') }}</p>
                        <a href="{{ route('member.courses.index') }}" class="mt-3 inline-flex items-center text-[0.75rem] font-medium text-boss-gold transition-colors hover:text-boss-gold-light">
                            {{ __('View Academy') }} ->
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                [__('Progress'), $overallPercent.'%', __('overall completion')],
                [__('In Progress'), $inProgressCount, __('courses active')],
                [__('Completed'), $completedCoursesCount, __('courses finished')],
                [__('Remaining'), $notStartedCount, __('to start')],
            ] as $stat)
                <div class="pd-stat">
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
                                    <img src="{{ $image }}" alt="{{ $course->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
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
                                    <img src="{{ $image }}" alt="{{ $course->title }}" class="h-full w-full object-cover opacity-85 transition duration-500 group-hover:scale-105">
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
