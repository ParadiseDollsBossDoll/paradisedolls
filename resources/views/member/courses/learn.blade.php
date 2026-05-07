<x-member-layout>
    @php
        $color = $course->displayColor();
        $selectedDone = $selectedLesson ? in_array($selectedLesson->id, $progress['completedLessonIds'], true) : false;
        $mediaEmbedUrl = function (?string $url): ?string {
            if (blank($url)) {
                return null;
            }

            $url = trim($url);
            $parts = parse_url($url);
            $host = strtolower($parts['host'] ?? '');
            $path = trim($parts['path'] ?? '', '/');

            if ($host === 'player.mediadelivery.net' && str_starts_with($path, 'play/')) {
                $segments = explode('/', $path);

                if (count($segments) >= 3) {
                    return 'https://iframe.mediadelivery.net/embed/'.$segments[1].'/'.$segments[2].'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
                }
            }

            return $url;
        };
        $pdfEmbedUrl = function (?string $url): ?string {
            if (blank($url)) {
                return null;
            }

            $url = trim($url);
            $parts = parse_url($url);
            $host = strtolower($parts['host'] ?? '');
            $path = trim($parts['path'] ?? '', '/');
            parse_str($parts['query'] ?? '', $query);

            if ($host === 'drive.google.com') {
                $fileId = null;

                if (preg_match('#(?:^|/)file/d/([^/]+)#', $path, $matches)) {
                    $fileId = $matches[1];
                } elseif (isset($query['id']) && is_string($query['id'])) {
                    $fileId = $query['id'];
                }

                if ($fileId !== null && $fileId !== '') {
                    return 'https://drive.google.com/file/d/'.rawurlencode($fileId).'/preview';
                }
            }

            if (str_ends_with(strtolower($parts['path'] ?? ''), '.pdf')) {
                return $url.(str_contains($url, '#') ? '&' : '#').'page=1&view=FitH';
            }

            return $url;
        };
        $lessonVideoUrl = $selectedLesson ? $mediaEmbedUrl($selectedLesson->videoEmbedUrl()) : null;
        $lessonPdfPreviewUrl = $selectedLesson ? $pdfEmbedUrl($selectedLesson->pdf_url) : null;
        $unassignedLessons = $course->lessons->whereNull('course_module_id');
    @endphp

    <div class="mx-auto max-w-[1280px] space-y-5">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <header class="rounded-2xl border border-white/[0.05] bg-boss-panel p-4 md:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <a href="{{ route('member.courses.show', $course->slug) }}" class="text-[0.72rem] text-boss-ivory/35 transition-colors hover:text-boss-gold"><- {{ __('Overview') }}</a>
                        <span class="text-boss-ivory/12">/</span>
                        <span class="rounded-full border px-2.5 py-1 text-[0.65rem]" style="background-color: {{ $course->displayColorBackground() }}; color: {{ $color }}; border-color: {{ $color }}25;">{{ $course->displayPlatform() }}</span>
                    </div>
                    <h1 class="pd-heading truncate text-[clamp(1.4rem,3vw,2.2rem)] text-boss-ivory">{{ $course->title }}</h1>
                    @if ($selectedLesson)
                        <p class="mt-2 text-[0.78rem] text-boss-ivory/38">{{ __('Current lesson:') }} <span class="text-boss-ivory/70">{{ $selectedLesson->title }}</span></p>
                    @endif
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="min-w-[180px]">
                        <div class="mb-1 flex items-center justify-between text-[0.68rem] text-boss-ivory/35">
                            <span>{{ __('Course progress') }}</span>
                            <span class="text-boss-gold">{{ $progress['percent'] }}%</span>
                        </div>
                        <div class="pd-progress-track">
                            <div class="pd-progress-bar" style="width: {{ $progress['percent'] }}%"></div>
                        </div>
                    </div>
                    <a href="{{ route('member.courses.community', $course->slug) }}" class="pd-btn-secondary">{{ __('Community') }}</a>
                </div>
            </div>
        </header>

        <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
            <aside class="rounded-2xl border border-white/[0.05] bg-boss-panel">
                <div class="border-b border-white/[0.05] px-4 py-4">
                    <p class="text-[0.62rem] uppercase tracking-[0.2em] text-boss-ivory/30">
                        {{ $progress['completed'] }}/{{ $progress['total'] }} {{ __('lessons complete') }}
                    </p>
                </div>

                <div class="max-h-[calc(100vh-220px)] space-y-3 overflow-y-auto p-3">
                    @forelse ($course->modules as $module)
                        @php
                            $moduleStats = $moduleProgress[$module->id] ?? ['completed' => 0, 'total' => $module->lessons->count(), 'percent' => 0];
                        @endphp
                        <section class="rounded-xl border border-white/[0.05] bg-white/[0.02] p-3">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[0.78rem] font-medium text-boss-ivory">{{ $module->title }}</p>
                                    <p class="mt-1 text-[0.62rem] text-boss-ivory/28">{{ $moduleStats['completed'] }}/{{ $moduleStats['total'] }} {{ __('done') }}</p>
                                </div>
                                <span class="font-display text-[0.9rem] text-boss-gold">{{ $moduleStats['percent'] }}%</span>
                            </div>

                            <div class="space-y-1">
                                @foreach ($module->lessons as $lesson)
                                    @php
                                        $done = in_array($lesson->id, $progress['completedLessonIds'], true);
                                        $isCurrent = $selectedLesson && $selectedLesson->id === $lesson->id;
                                    @endphp
                                    <a href="{{ route('member.courses.lessons.show', [$course->slug, $lesson]) }}" class="group flex items-center gap-3 rounded-lg border px-3 py-2 transition-colors {{ $isCurrent ? 'border-boss-gold/25 bg-boss-gold/[0.08]' : 'border-transparent hover:bg-white/[0.035]' }}">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[0.58rem] {{ $done ? 'border-boss-gold/30 bg-boss-gold text-boss-ink' : ($isCurrent ? 'border-boss-gold/30 text-boss-gold' : 'border-white/[0.12] text-boss-ivory/24') }}">
                                            {{ $done ? 'OK' : ($isCurrent ? '>' : 'O') }}
                                        </span>
                                        <span class="min-w-0 flex-1 truncate text-[0.76rem] {{ $isCurrent ? 'text-boss-ivory' : 'text-boss-ivory/48 group-hover:text-boss-ivory/70' }}">{{ $lesson->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <p class="px-4 py-8 text-center text-[0.82rem] text-boss-ivory/30">{{ __('No modules are published yet.') }}</p>
                    @endforelse

                    @if ($unassignedLessons->isNotEmpty())
                        <section class="rounded-xl border border-white/[0.05] bg-white/[0.02] p-3">
                            <p class="mb-3 text-[0.78rem] font-medium text-boss-ivory">{{ __('Core Training') }}</p>
                            <div class="space-y-1">
                                @foreach ($unassignedLessons as $lesson)
                                    <a href="{{ route('member.courses.lessons.show', [$course->slug, $lesson]) }}" class="block rounded-lg px-3 py-2 text-[0.76rem] text-boss-ivory/48 transition-colors hover:bg-white/[0.035] hover:text-boss-ivory/70">{{ $lesson->title }}</a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </aside>

            <main class="space-y-4">
                @if ($selectedLesson)
                    <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
                        @if ($lessonVideoUrl)
                            <div class="aspect-video w-full">
                                <iframe class="h-full w-full" src="{{ $lessonVideoUrl }}" title="{{ $selectedLesson->title }}" allowfullscreen loading="lazy"></iframe>
                            </div>
                        @else
                            <div class="flex aspect-video flex-col items-center justify-center p-8 text-center" style="background: linear-gradient(135deg, {{ $course->displayColorBackground(0.18) }}, rgba(255,255,255,0.02));">
                                <p class="pd-heading max-w-md text-[1.35rem] text-boss-ivory/70">{{ $selectedLesson->title }}</p>
                                <p class="mt-2 max-w-md text-[0.78rem] leading-relaxed text-boss-ivory/30">{{ __('Video will appear here when it is added by the admin team.') }}</p>
                            </div>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
                        <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="pd-kicker">{{ $selectedLesson->module?->title ?: __('Lesson') }}</p>
                                <h2 class="pd-heading mt-2 text-[clamp(1.6rem,4vw,2.4rem)] text-boss-ivory">{{ $selectedLesson->title }}</h2>
                                @if ($selectedLesson->duration)
                                    <p class="mt-2 text-[0.76rem] text-boss-ivory/35">{{ $selectedLesson->duration }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.72rem] text-boss-ivory/45">
                                    {{ $selectedDone ? __('Completed') : __('In progress') }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <h3 class="text-[0.7rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Overview') }}</h3>
                                <p class="mt-3 whitespace-pre-line text-[0.9rem] leading-relaxed text-boss-ivory/55">{{ $selectedLesson->overview ?: $selectedLesson->body ?: __('Lesson notes will appear here when they are added.') }}</p>
                            </div>

                            @if ($selectedLesson->stepItems() !== [])
                                <div>
                                    <h3 class="text-[0.7rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step-by-step Guide') }}</h3>
                                    <div class="mt-3 space-y-2">
                                        @foreach ($selectedLesson->stepItems() as $step)
                                            <div class="flex gap-3 rounded-xl border border-white/[0.05] bg-white/[0.025] p-3">
                                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-boss-gold text-[0.65rem] font-semibold text-boss-ink">{{ $loop->iteration }}</span>
                                                <p class="text-[0.84rem] leading-relaxed text-boss-ivory/55">{{ $step }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="grid gap-4 md:grid-cols-2">
                                @if ($selectedLesson->tipItems() !== [])
                                    <div>
                                        <h3 class="text-[0.7rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Important Tips') }}</h3>
                                        <div class="mt-3 space-y-2">
                                            @foreach ($selectedLesson->tipItems() as $tip)
                                                <p class="rounded-lg border border-white/[0.05] bg-white/[0.025] px-3 py-2 text-[0.82rem] text-boss-ivory/50">{{ $tip }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($selectedLesson->safetyItems() !== [])
                                    <div>
                                        <h3 class="text-[0.7rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Safety Notes') }}</h3>
                                        <div class="mt-3 space-y-2">
                                            @foreach ($selectedLesson->safetyItems() as $note)
                                                <p class="rounded-lg border border-boss-gold/10 bg-boss-gold/[0.04] px-3 py-2 text-[0.82rem] text-boss-ivory/52">{{ $note }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>

                    @if ($selectedLesson->pdf_url || $selectedLesson->resourceItems() !== [])
                        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <div>
                                    <p class="pd-kicker">{{ __('Resources') }}</p>
                                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Lesson Support') }}</h3>
                                </div>
                                @if ($selectedLesson->pdf_url)
                                    <a href="{{ $selectedLesson->pdf_url }}" target="_blank" rel="noopener" class="pd-btn-secondary">{{ __('Open PDF') }}</a>
                                @endif
                            </div>

                            @if ($lessonPdfPreviewUrl)
                                <div class="mb-4 overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f]">
                                    <div class="aspect-video w-full">
                                        <iframe class="h-full w-full" src="{{ $lessonPdfPreviewUrl }}" title="{{ __('PDF Guide') }}" loading="lazy"></iframe>
                                    </div>
                                </div>
                            @endif

                            @if ($selectedLesson->resourceItems() !== [])
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($selectedLesson->resourceItems() as $resource)
                                        <a href="{{ $resource['url'] }}" target="_blank" rel="noopener" class="rounded-xl border border-white/[0.05] bg-white/[0.025] px-4 py-3 text-[0.82rem] text-boss-ivory/52 transition-colors hover:border-boss-gold/25 hover:text-boss-gold">{{ $resource['label'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-wrap items-center gap-3">
                                @if ($previousLesson)
                                    <a href="{{ route('member.courses.lessons.show', [$course->slug, $previousLesson]) }}" class="pd-btn-secondary">{{ __('Previous Lesson') }}</a>
                                @endif

                                <form method="POST" action="{{ route('member.lessons.progress', $selectedLesson) }}">
                                    @csrf
                                    @method('PATCH')
                                    @if ($selectedDone)
                                        <button type="submit" name="completed" value="0" class="pd-btn-secondary">{{ __('Mark Incomplete') }}</button>
                                    @else
                                        <button type="submit" name="completed" value="1" class="pd-btn-primary">{{ __('Mark Complete') }}</button>
                                    @endif
                                </form>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('member.courses.community', $course->slug) }}" class="pd-btn-secondary">{{ __('Ask In Community') }}</a>
                                @if ($nextLesson)
                                    <a href="{{ route('member.courses.lessons.show', [$course->slug, $nextLesson]) }}" class="pd-btn-primary">{{ __('Next Lesson') }}</a>
                                @endif
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="pd-kicker">{{ __('Community') }}</p>
                                <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Course Discussion') }}</h3>
                            </div>
                            <a href="{{ route('member.courses.community', $course->slug) }}" class="text-[0.76rem] text-boss-gold hover:text-boss-gold-light">{{ __('Open community') }} -></a>
                        </div>

                        <div class="space-y-3">
                            @forelse ($messages as $message)
                                <div class="rounded-xl border border-white/[0.05] bg-white/[0.025] p-3">
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-[0.8rem] font-medium text-boss-ivory">{{ $message->user->name }}</p>
                                        <p class="text-[0.66rem] text-boss-ivory/25">{{ $message->created_at->diffForHumans() }}</p>
                                    </div>
                                    <p class="mt-2 line-clamp-2 text-[0.78rem] text-boss-ivory/45">{{ $message->body }}</p>
                                </div>
                            @empty
                                <p class="text-[0.82rem] text-boss-ivory/35">{{ __('No discussion yet. Start with a question or note from this lesson.') }}</p>
                            @endforelse
                        </div>
                    </section>
                @else
                    <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-10 text-center">
                        <p class="pd-heading text-[1.5rem] text-boss-ivory">{{ __('Lessons are coming soon.') }}</p>
                        <p class="mt-2 text-[0.85rem] text-boss-ivory/35">{{ __('This course is published, but no lessons are available yet.') }}</p>
                    </section>
                @endif
            </main>
        </div>
    </div>
</x-member-layout>
