<x-member-layout>
    <div class="mx-auto max-w-6xl space-y-7">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <header class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="pd-kicker">{{ __('Training Academy') }}</p>
                <h1 class="pd-heading pd-text-gradient mt-2 text-[clamp(2rem,4vw,2.7rem)]">{{ __('All Courses') }}</h1>
                <p class="mt-2 text-[0.82rem] text-boss-ivory/35">
                    {{ trans_choice(':count course available|:count courses available', $courses->count(), ['count' => $courses->count()]) }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'all' => __('All'),
                    'new' => __('New'),
                    'in-progress' => __('In Progress'),
                    'completed' => __('Completed'),
                ] as $key => $label)
                    <a
                        href="{{ route('member.courses.index', ['filter' => $key]) }}"
                        class="rounded-full px-3.5 py-1.5 text-[0.72rem] transition-colors {{ $filter === $key ? 'bg-boss-gold font-semibold text-boss-ink' : 'border border-white/[0.07] bg-white/[0.04] text-boss-ivory/45 hover:border-boss-gold/25 hover:text-boss-gold' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </header>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($filteredCourses as $course)
                @php
                    $progress = $courseProgress[$course->id] ?? ['completed' => 0, 'total' => $course->lessons_count, 'percent' => 0, 'status' => 'new'];
                    $isCompleted = $progress['status'] === 'completed';
                    $isStarted = $progress['status'] === 'in-progress';
                    $color = $course->displayColor();
                    $bg = $course->displayColorBackground();
                @endphp

                <a href="{{ route('member.courses.show', $course->slug) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-ink transition-all duration-300 hover:shadow-glow" style="--platform-color: {{ $color }};">
                    <div class="h-1 w-full shrink-0 transition-opacity duration-300 group-hover:opacity-100" style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}44); opacity: .72;"></div>

                    <div class="flex flex-1 flex-col gap-3 p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2.5 py-0.5 text-[0.65rem] font-medium" style="background-color: {{ $bg }}; color: {{ $color }}; border-color: {{ $color }}20;">
                                {{ $course->displayPlatform() }}
                            </span>
                            @if ($isCompleted)
                                <span class="rounded-full border border-boss-gold/20 bg-boss-gold/10 px-2 py-0.5 text-[0.62rem] text-boss-gold">{{ __('Completed') }}</span>
                            @elseif ($isStarted)
                                <span class="rounded-full border border-white/[0.06] bg-white/[0.04] px-2 py-0.5 text-[0.62rem] text-boss-ivory/35">{{ __('In Progress') }}</span>
                            @endif
                            <span class="ml-auto text-[0.62rem] text-boss-ivory/28">{{ trans_choice(':count lesson|:count lessons', $course->lessons_count, ['count' => $course->lessons_count]) }}</span>
                        </div>

                        <h2 class="pd-heading line-clamp-2 text-[1.15rem] text-boss-ivory transition-colors group-hover:text-boss-gold-light">{{ $course->title }}</h2>
                        <p class="line-clamp-2 flex-1 text-[0.78rem] leading-relaxed text-boss-ivory/38">{{ $course->description }}</p>

                        <div class="space-y-2 pt-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[0.6rem] uppercase tracking-[0.08em] text-boss-ivory/28">{{ __('Progress') }}</span>
                                <span class="text-[0.72rem] font-semibold" style="color: {{ $progress['percent'] > 0 ? $color : 'rgba(240,237,232,0.25)' }}">{{ $progress['percent'] }}%</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-white/[0.05]">
                                <div class="h-full rounded-full transition-all duration-700" style="width: {{ $progress['percent'] }}%; background: linear-gradient(90deg, {{ $color }}, #E8C88A);"></div>
                            </div>
                        </div>

                        @if ($course->lessons->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-1 pt-1">
                                @foreach ($course->lessons as $lesson)
                                    @php($done = in_array($lesson->id, $completedLessonIds, true))
                                    <span
                                        title="{{ $lesson->title }}"
                                        class="flex h-4 w-4 items-center justify-center rounded-full border text-[0.45rem]"
                                        style="{{ $done ? 'background: linear-gradient(135deg, '.$color.', #E8C88A); border-color: '.$color.'40; color: #07070C;' : 'background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.08); color: rgba(240,237,232,0.2);' }}"
                                    >
                                        {{ $done ? 'OK' : $loop->iteration }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center justify-between border-t border-white/[0.04] px-5 py-4 text-[0.75rem] transition-all duration-300 group-hover:bg-boss-gold/[0.04]">
                        <span class="text-boss-ivory/35 transition-colors group-hover:text-boss-gold">
                            {{ $isStarted ? __('Continue Learning') : ($isCompleted ? __('Review Course') : __('Start Course')) }}
                        </span>
                        <span class="text-boss-ivory/20 transition-all group-hover:translate-x-1 group-hover:text-boss-gold">-></span>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-white/[0.05] bg-boss-ink px-6 py-16 text-center">
                    <p class="text-[0.9rem] text-boss-ivory/35">
                        {{ $courses->isEmpty() ? __('Courses coming soon. Check back shortly!') : __('No courses match this filter yet.') }}
                    </p>
                    @if (! $courses->isEmpty())
                        <a href="{{ route('member.courses.index') }}" class="mt-3 inline-flex text-[0.78rem] text-boss-gold">{{ __('View all courses') }} -></a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</x-member-layout>
