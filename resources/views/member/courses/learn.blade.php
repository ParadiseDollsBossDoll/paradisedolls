<x-member-layout :hide-sidebar="true">
    @php
        $previewMode = (bool) ($previewMode ?? false);
        $previewExitUrl = $previewExitUrl ?? null;
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
        $lessonBannerImage = $selectedLesson?->lessonBannerImageUrl();
        $lessonImageUrls = $selectedLesson?->lessonImageUrls() ?? [];
        $showInlineImageGallery = $lessonImageUrls !== [];
        $presentationEmbedUrl = $selectedLesson?->canvaPresentationEmbedUrl();
        $presentationOpenUrl = $selectedLesson?->presentationOpenUrl();
        $lessonResourceItems = $selectedLesson?->resourceItems() ?? [];
        $lessonContentBlocks = ($selectedLesson?->contentBlocks ?? collect())
            ->filter(fn ($block) => $block->hasRenderableContent())
            ->values();
        $hasLessonContentBlocks = $lessonContentBlocks->isNotEmpty();
        $hasLessonResources = ! $hasLessonContentBlocks && (filled($selectedLesson?->pdf_url) || $lessonResourceItems !== []);
        $unassignedLessons = $course->lessons->whereNull('course_module_id');
        $lessonUrl = fn ($lesson) => $previewMode
            ? route('admin.courses.lessons.preview', [$course, $lesson])
            : route('member.courses.lessons.show', [$course->slug, $lesson]);
        $courseOverviewUrl = $previewMode
            ? route('admin.courses.preview', $course)
            : route('member.courses.show', $course->slug);
        $academyUrl = $previewMode
            ? route('admin.courses.edit', $course)
            : route('member.courses.index');
        $communityUrl = $previewMode ? null : (
            isset($communityChannel) && $communityChannel
                ? route('community.channels.show', $communityChannel->slug)
                : route('member.courses.community', $course->slug)
        );
    @endphp

    <div x-data="{ courseOutlineOpen: false }" class="mx-auto w-full max-w-[1500px] space-y-5">
        @if ($previewMode)
            <div class="flex flex-col gap-3 rounded-2xl border border-boss-gold/20 bg-boss-gold/[0.06] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="pd-kicker">{{ __('Preview Mode') }}</p>
                    <p class="mt-1 text-[0.78rem] text-boss-ivory/45">{{ __('Progress, enrollment, chat activity, and analytics are disabled in this admin preview.') }}</p>
                </div>
                <a href="{{ $previewExitUrl ?: route('admin.courses.edit', $course) }}" class="pd-btn-secondary self-start sm:self-auto">{{ __('Exit Preview') }}</a>
            </div>
        @endif

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <header class="shrink-0 rounded-2xl border border-white/[0.05] bg-boss-panel p-4 md:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <a href="{{ $courseOverviewUrl }}" class="rounded-full border border-white/[0.06] bg-white/[0.03] px-3 py-1.5 text-[0.72rem] text-boss-ivory/45 transition-colors hover:border-boss-gold/25 hover:text-boss-gold">&larr; {{ $previewMode ? __('Preview Home') : __('Course Overview') }}</a>
                        <span class="text-boss-ivory/12">/</span>
                        <a href="{{ $academyUrl }}" class="text-[0.72rem] text-boss-ivory/35 transition-colors hover:text-boss-gold">{{ $previewMode ? __('Edit Course') : __('Academy') }}</a>
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
                    @if ($communityUrl)
                        <a href="{{ $communityUrl }}" class="pd-btn-secondary">{{ __('Community') }}</a>
                    @endif
                </div>
            </div>
        </header>

        <div class="lg:hidden">
            <button
                type="button"
                class="flex w-full items-center justify-between rounded-2xl border border-white/[0.05] bg-boss-panel px-4 py-3 text-left"
                @click="courseOutlineOpen = ! courseOutlineOpen"
                :aria-expanded="courseOutlineOpen.toString()"
            >
                <span>
                    <span class="block text-[0.62rem] uppercase tracking-[0.18em] text-boss-ivory/30">{{ __('Course Outline') }}</span>
                    <span class="mt-1 block truncate text-[0.84rem] text-boss-ivory/70">{{ $selectedLesson?->title ?: __('Lessons') }}</span>
                </span>
                <span class="text-[0.75rem] text-boss-gold" x-text="courseOutlineOpen ? @js(__('Hide')) : @js(__('Show'))"></span>
            </button>
        </div>

        <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)] lg:grid-cols-[340px_minmax(0,1fr)]">
            <aside
                class="hidden rounded-2xl border border-white/[0.05] bg-boss-panel lg:sticky lg:top-24 lg:block lg:max-h-[calc(100vh-7.5rem)] lg:self-start lg:overflow-hidden"
                :class="courseOutlineOpen ? '!block' : ''"
            >
                <div class="border-b border-white/[0.05] px-4 py-4">
                    <p class="text-[0.62rem] uppercase tracking-[0.2em] text-boss-ivory/30">
                        {{ $progress['completed'] }}/{{ $progress['total'] }} {{ __('lessons complete') }}
                    </p>
                </div>

                <div class="space-y-3 overflow-y-auto p-3 lg:max-h-[calc(100vh-11.5rem)]">
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
                                    <a href="{{ $lessonUrl($lesson) }}" class="group flex items-center gap-3 rounded-lg border px-3 py-2 transition-colors {{ $isCurrent ? 'border-boss-gold/25 bg-boss-gold/[0.08]' : 'border-transparent hover:bg-white/[0.035]' }}">
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
                                    <a href="{{ $lessonUrl($lesson) }}" class="block rounded-lg px-3 py-2 text-[0.76rem] text-boss-ivory/48 transition-colors hover:bg-white/[0.035] hover:text-boss-ivory/70">{{ $lesson->title }}</a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </aside>

            <main class="min-w-0 space-y-4">
                @if ($selectedLesson)
                    @if ($lessonBannerImage)
                        <section class="relative min-h-[220px] overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f] md:min-h-[300px]">
                            <img src="{{ $lessonBannerImage }}" alt="{{ $selectedLesson->title }}" class="absolute inset-0 h-full w-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-boss-ink via-boss-ink/40 to-black/10"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-5 md:p-7">
                                <p class="pd-kicker">{{ __('Lesson Banner') }}</p>
                                <h2 class="pd-heading mt-2 max-w-3xl text-[clamp(1.7rem,4vw,2.8rem)] text-boss-ivory">{{ $selectedLesson->title }}</h2>
                            </div>
                        </section>
                    @endif

                    @if (! $hasLessonContentBlocks)
                    @if ($lessonVideoUrl)
                        <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
                            <div class="aspect-video w-full">
                                <iframe class="h-full w-full" src="{{ $lessonVideoUrl }}" title="{{ $selectedLesson->title }}" allowfullscreen loading="lazy"></iframe>
                            </div>
                        </section>
                    @endif

                    @if ($presentationEmbedUrl || $presentationOpenUrl)
                        <section
                            class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6"
                            @if ($presentationEmbedUrl)
                                x-data="{ presentationBlocked: false, presentationLoaded: false, presentationTimer: null }"
                                x-init="presentationTimer = window.setTimeout(() => { if (! presentationLoaded) presentationBlocked = true }, 7000)"
                            @endif
                        >
                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="pd-kicker">{{ __('Presentation') }}</p>
                                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Lesson Slides') }}</h3>
                                </div>
                                @if ($presentationOpenUrl)
                                    <a href="{{ $presentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-secondary">{{ __('Open Presentation') }}</a>
                                @endif
                            </div>

                            @if ($presentationEmbedUrl)
                                <div x-show="! presentationBlocked" class="presentation-wrapper relative aspect-video w-full overflow-hidden rounded-2xl border border-boss-gold/25 bg-[#0f0d0b]">
                                    <iframe
                                        src="{{ $presentationEmbedUrl }}"
                                        title="{{ $selectedLesson->title }} {{ __('presentation') }}"
                                        loading="lazy"
                                        allowfullscreen
                                        allow="fullscreen"
                                        frameborder="0"
                                        class="absolute inset-0 h-full w-full border-0"
                                        x-on:load="presentationLoaded = true; if (presentationTimer) window.clearTimeout(presentationTimer)"
                                        x-on:error="presentationBlocked = true"
                                    ></iframe>
                                </div>
                                <div x-cloak x-show="presentationBlocked" class="rounded-2xl border border-boss-gold/20 bg-boss-gold/[0.045] p-5 md:p-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-boss-gold/25 bg-boss-gold/10 font-display text-[1.25rem] text-boss-gold-light">
                                            C
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="pd-heading text-[1.25rem] text-boss-ivory">{{ __('Presentation cannot be embedded') }}</h4>
                                            <p class="mt-2 text-[0.82rem] leading-relaxed text-boss-ivory/45">{{ __('Canva is preventing this presentation from loading inside the academy.') }}</p>
                                        </div>
                                        @if ($presentationOpenUrl)
                                            <a href="{{ $presentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ __('Open Presentation') }}</a>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($presentationOpenUrl)
                                <div class="rounded-2xl border border-boss-gold/20 bg-boss-gold/[0.045] p-5 md:p-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-boss-gold/25 bg-boss-gold/10 font-display text-[1.25rem] text-boss-gold-light">
                                            C
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="pd-heading text-[1.25rem] text-boss-ivory">{{ __('Presentation cannot be embedded') }}</h4>
                                            <p class="mt-2 text-[0.82rem] leading-relaxed text-boss-ivory/45">{{ __('Open the presentation in a new tab to view it on Canva.') }}</p>
                                        </div>
                                        <a href="{{ $presentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ __('Open Presentation') }}</a>
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif
                    @endif

                    @if ($hasLessonContentBlocks)
                    <div class="space-y-4">
                        @foreach ($lessonContentBlocks as $block)
                            @include('member.courses.partials.lesson-content-block', ['block' => $block])
                        @endforeach
                    </div>
                    @else
                    <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-7">
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
                                    {{ $previewMode ? __('Preview') : ($selectedDone ? __('Completed') : __('In progress')) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <h3 class="text-[0.7rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Overview') }}</h3>
                                <p class="mt-3 max-w-3xl whitespace-pre-line text-[0.95rem] leading-8 text-boss-ivory/60">{{ $selectedLesson->overview ?: $selectedLesson->body ?: __('Lesson notes will appear here when they are added.') }}</p>
                            </div>

                            @if ($showInlineImageGallery)
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($lessonImageUrls as $imageUrl)
                                        <a href="{{ $imageUrl }}" target="_blank" rel="noopener" class="group overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f] transition-colors hover:border-boss-gold/25">
                                            <img src="{{ $imageUrl }}" alt="{{ $selectedLesson->title }} {{ $loop->iteration }}" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105">
                                        </a>
                                    @endforeach
                                </div>
                            @endif

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
                    @endif

                    @if ($hasLessonResources)
                        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <div>
                                    <p class="pd-kicker">{{ __('Resources') }}</p>
                                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Lesson Support') }}</h3>
                                </div>
                                @if ($selectedLesson->pdf_url)
                                    <a href="{{ $selectedLesson->pdf_url }}" target="_blank" rel="noopener noreferrer" class="pd-btn-secondary">{{ __('Open PDF') }}</a>
                                @endif
                            </div>

                            @if ($lessonPdfPreviewUrl)
                                <div class="mb-4 overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f]">
                                    <div class="aspect-video w-full">
                                        <iframe class="h-full w-full" src="{{ $lessonPdfPreviewUrl }}" title="{{ __('PDF Guide') }}" loading="lazy"></iframe>
                                    </div>
                                </div>
                            @endif

                            @if ($lessonResourceItems !== [])
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($lessonResourceItems as $resource)
                                        <a href="{{ $resource['url'] }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-white/[0.05] bg-white/[0.025] px-4 py-3 text-[0.82rem] text-boss-ivory/52 transition-colors hover:border-boss-gold/25 hover:text-boss-gold">{{ $resource['label'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-wrap items-center gap-3">
                                @if ($previousLesson)
                                    <a href="{{ $lessonUrl($previousLesson) }}" class="pd-btn-secondary">{{ __('Previous Lesson') }}</a>
                                @endif

                                @if (! $previewMode)
                                    <form method="POST" action="{{ route('member.lessons.progress', $selectedLesson) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if ($selectedDone)
                                            <button type="submit" name="completed" value="0" class="pd-btn-secondary">{{ __('Mark Incomplete') }}</button>
                                        @else
                                            <button type="submit" name="completed" value="1" class="pd-btn-primary">{{ __('Mark Complete') }}</button>
                                        @endif
                                    </form>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                @if ($communityUrl)
                                    <a href="{{ $communityUrl }}" class="pd-btn-secondary inline-flex items-center gap-2">
                                        <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.6]"><path d="M14 10c0 1.1-.9 2-2 2H4l-3 3V4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2v6z"/></svg>
                                        {{ __('Ask In Community') }}
                                    </a>
                                @endif
                                @if ($nextLesson)
                                    <a href="{{ $lessonUrl($nextLesson) }}" class="pd-btn-primary">{{ __('Next Lesson') }}</a>
                                @endif
                            </div>
                        </div>
                    </section>

                    @if (! $previewMode)
                    <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="pd-kicker">{{ __('Community') }}</p>
                                <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Course Discussion') }}</h3>
                            </div>
                            <a href="{{ $communityUrl }}" class="text-[0.76rem] text-boss-gold hover:text-boss-gold-light">{{ __('Open community') }} -></a>
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
                    @endif
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
