<x-admin-layout>
    <div class="mx-auto max-w-6xl space-y-8 text-boss-ivory">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <header>
            <p class="pd-kicker">{{ __('Admin') }}</p>
            <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.7rem)] text-boss-ivory">{{ __('Overview') }}</h1>
        </header>

        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                [__('Pending Applications'), $pendingApplications, __('awaiting review'), route('admin.applications.index')],
                [__('Verification'), $verificationReviewCount, __('awaiting ID review'), route('admin.onboarding.index')],
                [__('Published'), $publishedCoursesCount, __('visible to members'), route('admin.courses.index')],
                [__('Members'), $modelsCount, __('active accounts'), route('admin.onboarding.index')],
            ] as $stat)
                <a href="{{ $stat[3] }}" class="pd-stat block transition-all duration-200 hover:border-boss-gold/25 hover:shadow-glow">
                    <p class="font-display text-[2.25rem] leading-none text-boss-gold">{{ $stat[1] }}</p>
                    <p class="mt-3 text-[0.72rem] uppercase tracking-[0.08em] text-boss-ivory/55">{{ $stat[0] }}</p>
                    <p class="mt-1 text-[0.65rem] text-boss-ivory/25">{{ $stat[2] }}</p>
                </a>
            @endforeach
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="pd-panel-strong p-6">
                <div class="mb-5 flex items-center justify-between">
                    <p class="text-[0.7rem] uppercase tracking-[0.16em] text-boss-ivory/35">{{ __('Recent Applications') }}</p>
                    <a href="{{ route('admin.applications.index') }}" class="text-[0.72rem] text-boss-gold hover:text-boss-gold-light">{{ __('Review') }} -></a>
                </div>

                <div class="space-y-3">
                    @forelse ($recentApplications as $application)
                        <div class="flex items-center gap-3 rounded-sm border border-white/[0.05] bg-white/[0.025] p-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/10 font-display text-[0.72rem] text-boss-gold">
                                {{ strtoupper(substr($application->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[0.84rem] text-boss-ivory">{{ $application->name }}</p>
                                <p class="truncate text-[0.68rem] text-boss-ivory/30">{{ $application->email }}</p>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-[0.62rem] capitalize {{ $application->status === \App\Models\ModelApplication::STATUS_PENDING ? 'bg-boss-gold/10 text-boss-gold' : ($application->status === \App\Models\ModelApplication::STATUS_APPROVED ? 'bg-green-400/10 text-green-300' : 'bg-red-400/10 text-red-300') }}">
                                {{ __($application->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="py-8 text-center text-[0.85rem] text-boss-ivory/30">{{ __('No applications yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="pd-panel-strong p-6">
                <div class="mb-5 flex items-center justify-between">
                    <p class="text-[0.7rem] uppercase tracking-[0.16em] text-boss-ivory/35">{{ __('Recent Courses') }}</p>
                    <a href="{{ route('admin.courses.create') }}" class="text-[0.72rem] text-boss-gold hover:text-boss-gold-light">{{ __('New Course') }} -></a>
                </div>

                <div class="space-y-4">
                    @forelse ($recentCourses as $course)
                        <a href="{{ route('admin.courses.edit', $course) }}" class="group block rounded-sm border border-white/[0.05] bg-white/[0.025] p-4 transition-colors hover:border-boss-gold/20">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="pd-badge">{{ $course->platform_label ?: __('General') }}</span>
                                <span class="text-[0.65rem] text-boss-ivory/28">{{ trans_choice(':count lesson|:count lessons', $course->lessons_count, ['count' => $course->lessons_count]) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <p class="truncate text-[0.88rem] text-boss-ivory group-hover:text-boss-gold-light">{{ $course->title }}</p>
                                <span class="text-[0.62rem] uppercase tracking-[0.1em] {{ $course->is_published ? 'text-green-300/80' : 'text-boss-ivory/25' }}">
                                    {{ $course->is_published ? __('Live') : __('Draft') }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="py-8 text-center text-[0.85rem] text-boss-ivory/30">{{ __('No courses yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-admin-layout>
