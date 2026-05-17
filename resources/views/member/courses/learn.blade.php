<x-member-layout :hide-sidebar="true" :player="true">
    @php
        $previewMode = (bool) ($previewMode ?? false);
        $previewExitUrl = $previewExitUrl ?? null;
        $color = $course->displayColor();
        $selectedDone = $selectedLesson ? in_array($selectedLesson->id, $progress['completedLessonIds'], true) : false;
        $mediaEmbedUrl = function (?string $url): ?string {
            if (blank($url)) return null;
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
            if (blank($url)) return null;
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
            return $url;
        };
        $pdfCanvasUrl = function (?string $url): ?string {
            if (blank($url)) return null;
            $url = trim($url);
            $parts = parse_url($url);
            $host = strtolower($parts['host'] ?? '');

            if ($host === 'drive.google.com') return null;

            return str_ends_with(strtolower($parts['path'] ?? $url), '.pdf') ? $url : null;
        };
        $courseOutlineUrl        = $course->courseOutlineUrl();
        $courseOutlineFileName   = $course->courseOutlineFileName() ?: __('Course Outline');
        $hasCourseOutlineItem    = $course->hasCourseOutlineMaterial() && filled($courseOutlineUrl);
        $courseOutlinePath       = $courseOutlineUrl ? (parse_url($courseOutlineUrl, PHP_URL_PATH) ?: $courseOutlineUrl) : '';
        $courseOutlineCanvasUrl  = $hasCourseOutlineItem && str_ends_with(strtolower($courseOutlinePath), '.pdf')
            ? $pdfCanvasUrl($courseOutlineUrl) : null;
        $courseOutlinePreviewUrl = $hasCourseOutlineItem && ! $courseOutlineCanvasUrl && str_ends_with(strtolower($courseOutlinePath), '.pdf')
            ? $pdfEmbedUrl($courseOutlineUrl) : null;
        $introVideoUrl   = $course->hasIntroMaterial() ? $mediaEmbedUrl($course->introVideoEmbedUrl()) : null;
        $hasIntroItem    = $course->hasIntroMaterial();
        $requestedCourseItem = request()->query('item');
        $selectedCourseItem  = null;
        if (! $selectedLesson) {
            if ($requestedCourseItem === 'intro' && $hasIntroItem) $selectedCourseItem = 'intro';
            elseif ($requestedCourseItem === 'outline' && $hasCourseOutlineItem) $selectedCourseItem = 'outline';
            elseif ($hasCourseOutlineItem) $selectedCourseItem = 'outline';
            elseif ($hasIntroItem) $selectedCourseItem = 'intro';
        }
        $lessonVideoUrl        = $selectedLesson ? $mediaEmbedUrl($selectedLesson->videoEmbedUrl()) : null;
        $lessonPdfCanvasUrl    = $selectedLesson ? $pdfCanvasUrl($selectedLesson->pdf_url) : null;
        $lessonPdfPreviewUrl   = $selectedLesson && ! $lessonPdfCanvasUrl ? $pdfEmbedUrl($selectedLesson->pdf_url) : null;
        $lessonBannerImage     = $selectedLesson?->lessonBannerImageUrl();
        $lessonImageUrls       = $selectedLesson?->lessonImageUrls() ?? [];
        $showInlineImageGallery = $lessonImageUrls !== [];
        $presentationOpenUrl   = $selectedLesson?->presentationOpenUrl();
        $lessonResourceItems   = $selectedLesson?->resourceItems() ?? [];
        $allLessonContentBlocks = $selectedLesson?->contentBlocks ?? collect();
        $hasLessonFlowBlocks    = $allLessonContentBlocks->isNotEmpty();
        $lessonContentBlocks    = $allLessonContentBlocks->filter(fn ($b) => $b->hasRenderableContent())->values();
        $hasLessonResources     = ! $hasLessonFlowBlocks && (filled($selectedLesson?->pdf_url) || $lessonResourceItems !== []);
        $unassignedLessons      = $course->lessons->whereNull('course_module_id');
        $lessonUrl              = fn ($lesson) => $previewMode
            ? route('admin.courses.lessons.preview', [$course, $lesson])
            : route('member.courses.lessons.show', [$course->slug, $lesson]);
        $courseMaterialUrl      = fn (string $item) => $previewMode
            ? route('admin.courses.preview', [$course, 'item' => $item])
            : route('member.courses.learn.show', [$course->slug, 'item' => $item]);
        $courseOverviewUrl      = $previewMode ? route('admin.courses.preview', $course) : route('member.courses.show', $course->slug);
        $academyUrl             = $previewMode ? route('admin.courses.edit', $course) : route('member.courses.index');
        $communityUrl           = $previewMode ? null : (
            isset($communityChannel) && $communityChannel
                ? route('community.channels.show', $communityChannel->slug)
                : route('member.courses.community', $course->slug)
        );
        $learningEntries = collect();
        if ($hasCourseOutlineItem) {
            $learningEntries->push(['key' => 'outline', 'type' => 'outline', 'title' => __('Course Outline / PDF Guide'), 'url' => $courseMaterialUrl('outline')]);
        }
        if ($hasIntroItem) {
            $learningEntries->push(['key' => 'intro', 'type' => 'intro', 'title' => $course->intro_title ?: __('Course Introduction'), 'url' => $courseMaterialUrl('intro')]);
        }
        foreach ($course->lessons as $entryLesson) {
            $learningEntries->push(['key' => 'lesson:'.$entryLesson->id, 'type' => 'lesson', 'title' => $entryLesson->title, 'url' => $lessonUrl($entryLesson)]);
        }
        $currentEntryKey   = $selectedLesson ? 'lesson:'.$selectedLesson->id : $selectedCourseItem;
        $currentEntryIndex = $learningEntries->search(fn ($e) => $e['key'] === $currentEntryKey);
        $previousEntry     = $currentEntryIndex !== false && $currentEntryIndex > 0 ? $learningEntries[$currentEntryIndex - 1] : null;
        $nextEntry         = $currentEntryIndex !== false && $currentEntryIndex < $learningEntries->count() - 1 ? $learningEntries[$currentEntryIndex + 1] : null;
        $currentItemTitle  = $selectedLesson?->title
            ?: ($selectedCourseItem === 'intro'
                ? ($course->intro_title ?: __('Course Introduction'))
                : ($selectedCourseItem === 'outline' ? __('Course Outline / PDF Guide') : __('Lessons')));
        $currentModuleId   = $selectedLesson?->course_module_id;
        $openModulesJson   = json_encode($currentModuleId ? [$currentModuleId] : ($course->modules->first() ? [$course->modules->first()->id] : []));
    @endphp

    {{-- ─────────────────────────────────────────────────────────────────── --}}
    {{-- Course Player: fills h-screen minus the site topbar                 --}}
    {{-- ─────────────────────────────────────────────────────────────────── --}}
    <div
        class="flex h-full flex-col text-boss-ivory"
        x-data="{
            sidebar: window.innerWidth >= 1024,
            mobileNav: false,
            openModules: [],
            _storageKey: 'pd_outline_{{ $course->id }}',
            _defaultOpen: {{ $openModulesJson }},
            _currentModuleId: {{ $currentModuleId ?? 'null' }},
            init() {
                try {
                    const saved = localStorage.getItem(this._storageKey);
                    this.openModules = saved ? JSON.parse(saved) : [...this._defaultOpen];
                } catch {
                    this.openModules = [...this._defaultOpen];
                }
                if (this._currentModuleId !== null && !this.openModules.includes(this._currentModuleId)) {
                    this.openModules = [...this.openModules, this._currentModuleId];
                }
            },
            toggleModule(id) {
                if (this.openModules.includes(id)) {
                    this.openModules = this.openModules.filter(m => m !== id);
                } else {
                    this.openModules = [...this.openModules, id];
                }
                try { localStorage.setItem(this._storageKey, JSON.stringify(this.openModules)); } catch {}
            },
            isOpen(id) { return this.openModules.includes(id); }
        }"
        @keydown.escape.window="mobileNav = false"
    >

        {{-- ── Preview banner ─────────────────────────────────────────────── --}}
        @if ($previewMode)
            <div class="flex shrink-0 flex-col gap-3 border-b border-boss-gold/20 bg-boss-gold/[0.06] px-5 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[0.6rem] font-semibold uppercase tracking-[0.18em] text-boss-gold/70">{{ __('Preview Mode') }}</p>
                    <p class="text-[0.72rem] text-boss-ivory/40">{{ __('Progress, enrollment and analytics are disabled in this admin preview.') }}</p>
                </div>
                <a href="{{ $previewExitUrl ?: route('admin.courses.edit', $course) }}" class="pd-btn-secondary self-start shrink-0 sm:self-auto">{{ __('Exit Preview') }}</a>
            </div>
        @endif

        @if (session('status'))
            <div class="shrink-0 border-b border-green-400/20 bg-green-400/10 px-5 py-2.5 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        {{-- ── Course header ───────────────────────────────────────────────── --}}
        <header class="flex shrink-0 items-center gap-3 border-b border-white/[0.05] bg-boss-panel px-4 py-0" style="height: 3rem;">

            {{-- Mobile nav toggle --}}
            <button
                type="button"
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-white/[0.07] bg-white/[0.03] text-boss-ivory/45 transition-colors hover:text-boss-ivory lg:hidden"
                @click="mobileNav = true"
                aria-label="{{ __('Course outline') }}"
            >
                <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.8]">
                    <path d="M2 4h12M2 8h12M2 12h8"/>
                </svg>
            </button>

            {{-- Desktop sidebar toggle --}}
            <button
                type="button"
                class="hidden h-7 w-7 shrink-0 items-center justify-center rounded-md border border-white/[0.07] bg-white/[0.03] text-boss-ivory/40 transition-colors hover:text-boss-ivory lg:flex"
                @click="sidebar = !sidebar"
                :title="sidebar ? '{{ __('Hide outline') }}' : '{{ __('Show outline') }}'"
            >
                <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.8] transition-transform duration-250"
                     :class="sidebar ? '' : 'rotate-180'">
                    <path d="M10 3l-5 5 5 5"/>
                </svg>
            </button>

            {{-- Breadcrumb --}}
            <div class="hidden min-w-0 items-center gap-1.5 text-[0.63rem] text-boss-ivory/28 sm:flex">
                <a href="{{ $academyUrl }}" class="shrink-0 transition-colors hover:text-boss-gold">{{ $previewMode ? __('Edit') : __('Academy') }}</a>
                <span class="opacity-40">/</span>
                <a href="{{ $courseOverviewUrl }}" class="max-w-[140px] truncate transition-colors hover:text-boss-gold">{{ $course->title }}</a>
                <span class="opacity-40">/</span>
            </div>

            {{-- Lesson title --}}
            <h1 class="min-w-0 flex-1 truncate font-display text-[0.92rem] font-semibold text-boss-ivory">{{ $currentItemTitle }}</h1>

            {{-- Progress --}}
            <div class="hidden shrink-0 items-center gap-3 sm:flex">
                <div class="min-w-[120px]">
                    <div class="mb-1 flex items-center justify-between text-[0.58rem] text-boss-ivory/28">
                        <span>{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                        <span class="text-boss-gold">{{ $progress['percent'] }}%</span>
                    </div>
                    <div class="pd-progress-track">
                        <div class="pd-progress-bar" style="width:{{ $progress['percent'] }}%"></div>
                    </div>
                </div>
            </div>

            @if ($communityUrl)
                <a href="{{ $communityUrl }}"
                   class="hidden shrink-0 items-center gap-1.5 rounded-md border border-white/[0.07] bg-white/[0.03] px-3 py-1.5 text-[0.68rem] text-boss-ivory/45 transition-colors hover:border-boss-gold/25 hover:text-boss-gold sm:inline-flex">
                    <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[1.6]"><path d="M14 10c0 1.1-.9 2-2 2H4l-3 3V4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2v6z"/></svg>
                    {{ __('Community') }}
                </a>
            @endif
        </header>

        {{-- ── Body: sidebar + content panel ─────────────────────────────── --}}
        <div class="flex flex-1 overflow-hidden">

            {{-- ── Course outline sidebar (desktop) ───────────────────────── --}}
            <aside
                class="hidden lg:flex shrink-0 flex-col overflow-hidden border-r border-white/[0.05] bg-[#0a0a11]"
                style="transition: width 260ms cubic-bezier(.4,0,.2,1);"
                :style="sidebar ? 'width: 272px;' : 'width: 0px;'"
            >
                {{-- Sidebar header --}}
                <div class="flex shrink-0 items-center justify-between border-b border-white/[0.04] px-3.5 py-2.5">
                    <span class="text-[0.57rem] uppercase tracking-[0.18em] text-boss-ivory/25">
                        {{ __('Course Outline') }}
                    </span>
                    <span class="text-[0.6rem] text-boss-gold/60">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                </div>

                {{-- Scrollable outline list --}}
                <div class="flex-1 overflow-y-auto py-2">

                    {{-- Start Here --}}
                    @if ($hasCourseOutlineItem || $hasIntroItem)
                        <div class="mb-1 px-2.5">
                            <p class="mb-1 px-1 pt-1 text-[0.55rem] uppercase tracking-[0.18em] text-boss-gold/50">{{ __('Start Here') }}</p>
                            @if ($hasCourseOutlineItem)
                                @php $isCurrent = $selectedCourseItem === 'outline'; @endphp
                                <a href="{{ $courseMaterialUrl('outline') }}"
                                   class="flex items-center gap-2.5 rounded-lg border px-2.5 py-2 text-[0.71rem] transition-colors {{ $isCurrent ? 'border-boss-gold/20 bg-boss-gold/[0.07] text-boss-ivory' : 'border-transparent text-boss-ivory/42 hover:bg-white/[0.03] hover:text-boss-ivory/70' }}">
                                    <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border text-[0.48rem] {{ $isCurrent ? 'border-boss-gold/30 bg-boss-gold/10 text-boss-gold' : 'border-white/10 text-boss-ivory/22' }}">
                                        <svg viewBox="0 0 16 16" class="h-2.5 w-2.5 fill-none stroke-current stroke-[2]"><path d="M4 2h5.5L12 4.5V14H4V2z"/><path d="M9.5 2v2.5H12"/></svg>
                                    </span>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Course Outline') }}</span>
                                </a>
                            @endif
                            @if ($hasIntroItem)
                                @php $isCurrent = $selectedCourseItem === 'intro'; @endphp
                                <a href="{{ $courseMaterialUrl('intro') }}"
                                   class="mt-0.5 flex items-center gap-2.5 rounded-lg border px-2.5 py-2 text-[0.71rem] transition-colors {{ $isCurrent ? 'border-boss-gold/20 bg-boss-gold/[0.07] text-boss-ivory' : 'border-transparent text-boss-ivory/42 hover:bg-white/[0.03] hover:text-boss-ivory/70' }}">
                                    <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border text-[0.48rem] {{ $isCurrent ? 'border-boss-gold/30 bg-boss-gold/10 text-boss-gold' : 'border-white/10 text-boss-ivory/22' }}">
                                        <svg viewBox="0 0 16 16" class="h-2.5 w-2.5 fill-none stroke-current stroke-[2]"><circle cx="8" cy="8" r="6"/><path d="M8 5v3.5L10 10"/></svg>
                                    </span>
                                    <span class="min-w-0 flex-1 truncate">{{ $course->intro_title ?: __('Introduction') }}</span>
                                </a>
                            @endif
                        </div>
                        <div class="mx-2.5 mb-1 mt-1 border-t border-white/[0.04]"></div>
                    @endif

                    {{-- Modules (collapsible) --}}
                    @forelse ($course->modules as $module)
                        @php
                            $ms      = $moduleProgress[$module->id] ?? ['completed' => 0, 'total' => $module->lessons->count(), 'percent' => 0];
                            $allDone = $ms['total'] > 0 && $ms['completed'] >= $ms['total'];
                        @endphp
                        <div class="px-2.5">
                            {{-- Module header (clickable, collapsible) --}}
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left transition-colors hover:bg-white/[0.025]"
                                @click="toggleModule({{ $module->id }})"
                            >
                                {{-- Progress ring placeholder --}}
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[0.48rem] font-bold {{ $allDone ? 'border-emerald-400/40 bg-emerald-400/10 text-emerald-400' : 'border-white/[0.10] text-boss-ivory/30' }}">
                                    @if ($allDone)
                                        <svg viewBox="0 0 16 16" class="h-2.5 w-2.5 fill-none stroke-current stroke-[2.5]"><path d="M3 8.5l3.5 3.5L13 5"/></svg>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[0.7rem] font-medium leading-snug text-boss-ivory/75">{{ $module->title }}</p>
                                    <p class="text-[0.58rem] {{ $allDone ? 'text-emerald-400/70' : 'text-boss-ivory/28' }}">
                                        {{ $ms['completed'] }}/{{ $ms['total'] }}
                                        @if (! $allDone) · {{ $ms['percent'] }}%@endif
                                    </p>
                                </div>

                                {{-- Chevron --}}
                                <svg viewBox="0 0 16 16"
                                     class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[2] text-boss-ivory/25 transition-transform duration-200"
                                     :class="isOpen({{ $module->id }}) ? 'rotate-180' : ''">
                                    <path d="M4 6l4 4 4-4"/>
                                </svg>
                            </button>

                            {{-- Lesson list (collapsible) --}}
                            <div
                                class="pd-collapse"
                                :style="{ 'grid-template-rows': isOpen({{ $module->id }}) ? '1fr' : '0fr' }"
                            >
                                <div>
                                    <div class="space-y-0.5 pb-1.5 pl-3.5 pr-1 pt-0.5">
                                        @foreach ($module->lessons as $lesson)
                                            @php
                                                $done      = in_array($lesson->id, $progress['completedLessonIds'], true);
                                                $isCurrent = $selectedLesson && $selectedLesson->id === $lesson->id;
                                            @endphp
                                            <a href="{{ $lessonUrl($lesson) }}"
                                               class="flex items-center gap-2 rounded-md border px-2 py-1.5 text-[0.69rem] transition-colors {{ $isCurrent ? 'border-boss-gold/18 bg-boss-gold/[0.06] text-boss-ivory' : 'border-transparent text-boss-ivory/38 hover:bg-white/[0.025] hover:text-boss-ivory/65' }}">
                                                <span class="flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full border {{ $done ? 'border-emerald-400/40 bg-emerald-400 text-[#080808]' : ($isCurrent ? 'border-boss-gold/35 text-boss-gold' : 'border-white/[0.10] text-boss-ivory/15') }}">
                                                    @if ($done)
                                                        <svg viewBox="0 0 16 16" class="h-2 w-2 fill-none stroke-current stroke-[3]"><path d="M3 8l3.5 3.5L13 5"/></svg>
                                                    @elseif ($isCurrent)
                                                        <svg viewBox="0 0 16 16" class="h-2 w-2 fill-current"><polygon points="5,3 13,8 5,13"/></svg>
                                                    @endif
                                                </span>
                                                <span class="min-w-0 flex-1 truncate leading-snug">{{ $lesson->title }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-[0.75rem] text-boss-ivory/22">{{ __('No modules yet.') }}</p>
                    @endforelse

                    {{-- Unassigned lessons --}}
                    @if ($unassignedLessons->isNotEmpty())
                        <div class="px-2.5">
                            <p class="mb-1 mt-2 px-2 text-[0.55rem] uppercase tracking-[0.18em] text-boss-ivory/22">{{ __('Lessons') }}</p>
                            @foreach ($unassignedLessons as $lesson)
                                @php $isCurrent = $selectedLesson && $selectedLesson->id === $lesson->id; @endphp
                                <a href="{{ $lessonUrl($lesson) }}"
                                   class="flex items-center gap-2 rounded-md border px-2 py-1.5 text-[0.69rem] {{ $isCurrent ? 'border-boss-gold/18 bg-boss-gold/[0.06] text-boss-ivory' : 'border-transparent text-boss-ivory/38 hover:bg-white/[0.025] hover:text-boss-ivory/65' }}">
                                    <span class="min-w-0 flex-1 truncate">{{ $lesson->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                </div>{{-- /scrollable outline --}}
            </aside>

            {{-- ── Lesson content panel ────────────────────────────────────── --}}
            <div class="flex flex-1 min-w-0 flex-col overflow-hidden">

                {{-- Scrollable reading area --}}
                <div class="flex-1 overflow-y-auto">
                    <div class="mx-auto w-full max-w-[900px] space-y-5 px-5 py-6 sm:px-8">

                        {{-- ── COURSE OUTLINE ITEM ──────────────────────────── --}}
                        @if ($selectedCourseItem === 'outline' && $hasCourseOutlineItem)
                            @if ($courseOutlineCanvasUrl)
                                <div
                                    x-data="pdfLessonViewer({{ Js::from($courseOutlineCanvasUrl) }})"
                                    x-init="init()"
                                    class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]"
                                >
                                    <div x-show="loading && !error" class="flex items-center justify-center gap-3 bg-[#060610] py-16">
                                        <svg class="h-5 w-5 animate-spin text-boss-gold/50" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span class="text-[0.8rem] text-boss-ivory/40">{{ __('Loading PDF...') }}</span>
                                    </div>
                                    <div x-show="error" class="bg-[#060610] px-5 py-5 text-[0.78rem] text-red-300/70" x-text="error"></div>
                                    <div x-show="!loading && !error" x-ref="wrap" class="pd-scroll overflow-y-auto bg-[#060610]" style="height: min(76vh, 860px); min-height: min(520px, 68vh); scrollbar-gutter: stable; scroll-behavior: smooth">
                                        <div x-ref="pages" class="mx-auto flex w-full max-w-[1120px] flex-col items-center gap-5 px-4 py-5 sm:gap-6 sm:px-6"></div>
                                    </div>
                                </div>
                            @elseif ($courseOutlinePreviewUrl)
                                <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
                                    <div class="aspect-video w-full">
                                        <iframe class="h-full w-full" src="{{ $courseOutlinePreviewUrl }}" title="{{ __('Course Outline') }}" loading="lazy"></iframe>
                                    </div>
                                </div>
                            @endif

                            <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-6 md:p-8">
                                <p class="pd-kicker">{{ __('Course Material') }}</p>
                                <h2 class="mt-2 font-display text-[clamp(1.5rem,4vw,2rem)] font-semibold text-boss-ivory">{{ __('Course Outline / PDF Guide') }}</h2>
                                <p class="mt-2 text-[0.85rem] text-boss-ivory/45">{{ $courseOutlineFileName }}</p>
                                <a href="{{ $courseOutlineUrl }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-2 rounded-lg border border-boss-gold/28 bg-boss-gold/[0.10] px-5 py-2.5 text-[0.78rem] font-medium text-boss-gold transition-colors hover:bg-boss-gold/[0.18]">{{ __('Open Guide') }}</a>
                            </section>

                        {{-- ── INTRO ITEM ───────────────────────────────────── --}}
                        @elseif ($selectedCourseItem === 'intro' && $hasIntroItem)
                            @if ($introVideoUrl)
                                <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f] shadow-luxe">
                                    <div class="aspect-video w-full">
                                        <iframe class="h-full w-full" src="{{ $introVideoUrl }}" title="{{ $course->intro_title ?: __('Course Introduction') }}" allowfullscreen loading="lazy"></iframe>
                                    </div>
                                </div>
                            @endif

                            <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-6 md:p-8">
                                <p class="pd-kicker">{{ __('Course Introduction') }}</p>
                                <h2 class="mt-2 font-display text-[clamp(1.5rem,4vw,2rem)] font-semibold text-boss-ivory">{{ $course->intro_title ?: __('Course Introduction') }}</h2>
                                @if ($course->intro_duration)
                                    <p class="mt-1.5 text-[0.72rem] text-boss-ivory/32">{{ $course->intro_duration }}</p>
                                @endif
                                @if ($course->intro_body)
                                    <p class="mt-4 whitespace-pre-line text-[1rem] leading-[1.85] text-boss-ivory/65">{{ $course->intro_body }}</p>
                                @endif
                            </section>

                        {{-- ── LESSON ───────────────────────────────────────── --}}
                        @elseif ($selectedLesson)

                            {{-- Banner image --}}
                            @if ($lessonBannerImage)
                                <div class="relative overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
                                    <img src="{{ $lessonBannerImage }}" alt="{{ $selectedLesson->title }}" class="h-[220px] w-full object-cover sm:h-[300px]">
                                    <div class="absolute inset-0 bg-gradient-to-t from-boss-ink via-boss-ink/40 to-transparent"></div>
                                    <div class="absolute bottom-0 left-0 right-0 p-5">
                                        <p class="pd-kicker">{{ $selectedLesson->module?->title ?: __('Lesson') }}</p>
                                        <h2 class="mt-1 font-display text-[clamp(1.3rem,3.5vw,2rem)] font-semibold text-boss-ivory">{{ $selectedLesson->title }}</h2>
                                    </div>
                                </div>
                            @endif

                            {{-- Lesson header (no banner) --}}
                            @if (! $lessonBannerImage)
                                <section class="rounded-2xl border border-white/[0.05] bg-boss-panel px-6 py-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="pd-kicker">{{ $selectedLesson->module?->title ?: __('Lesson') }}</p>
                                            <h2 class="mt-1.5 font-display text-[clamp(1.3rem,3vw,1.85rem)] font-semibold leading-tight text-boss-ivory">{{ $selectedLesson->title }}</h2>
                                            @if ($selectedLesson->duration)
                                                <p class="mt-1.5 text-[0.72rem] text-boss-ivory/30">{{ $selectedLesson->duration }}</p>
                                            @endif
                                        </div>
                                        <span class="shrink-0 rounded-full border border-white/[0.06] bg-white/[0.03] px-3 py-1.5 text-[0.65rem] text-boss-ivory/38">
                                            {{ $previewMode ? __('Preview') : ($selectedDone ? '✓ '.__('Completed') : __('In progress')) }}
                                        </span>
                                    </div>
                                </section>
                            @endif

                            {{-- Legacy video / presentation --}}
                            @if (! $hasLessonFlowBlocks)
                                @if ($lessonVideoUrl)
                                    <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f] shadow-luxe">
                                        <div class="aspect-video w-full">
                                            <iframe class="h-full w-full" src="{{ $lessonVideoUrl }}" title="{{ $selectedLesson->title }}" allowfullscreen loading="lazy"></iframe>
                                        </div>
                                    </div>
                                @endif
                                @if ($presentationOpenUrl)
                                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/[0.05] bg-boss-panel px-6 py-4">
                                        <div>
                                            <p class="pd-kicker">{{ __('Presentation') }}</p>
                                            <p class="mt-1 text-[0.9rem] text-boss-ivory">{{ __('Lesson Slides') }}</p>
                                        </div>
                                        <a href="{{ $presentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ __('Open') }}</a>
                                    </div>
                                @endif
                            @endif

                            {{-- Flow blocks --}}
                            @if ($hasLessonFlowBlocks)
                                <div class="space-y-5">
                                    @foreach ($lessonContentBlocks as $block)
                                        @include('member.courses.partials.lesson-content-block', ['block' => $block])
                                    @endforeach
                                </div>

                            {{-- Legacy lesson body --}}
                            @else
                                <section class="rounded-2xl border border-white/[0.05] bg-boss-panel px-6 py-6 md:px-8 md:py-7">
                                    <div class="space-y-7">
                                        @if ($selectedLesson->overview || $selectedLesson->body)
                                            <div>
                                                <p class="mb-3 text-[0.62rem] uppercase tracking-[0.18em] text-boss-ivory/28">{{ __('Overview') }}</p>
                                                <p class="whitespace-pre-line text-[1rem] leading-[1.9] text-boss-ivory/68">{{ $selectedLesson->overview ?: $selectedLesson->body }}</p>
                                            </div>
                                        @endif

                                        @if ($showInlineImageGallery)
                                            <div class="grid gap-3 {{ count($lessonImageUrls) === 1 ? '' : 'sm:grid-cols-2' }}">
                                                @foreach ($lessonImageUrls as $imageUrl)
                                                    <a href="{{ $imageUrl }}" target="_blank" rel="noopener"
                                                       class="group overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f] transition-colors hover:border-boss-gold/20">
                                                        <img src="{{ $imageUrl }}" alt="{{ $selectedLesson->title }} {{ $loop->iteration }}" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($selectedLesson->stepItems() !== [])
                                            <div>
                                                <p class="mb-3 text-[0.62rem] uppercase tracking-[0.18em] text-boss-ivory/28">{{ __('Step-by-step Guide') }}</p>
                                                <div class="space-y-2.5">
                                                    @foreach ($selectedLesson->stepItems() as $step)
                                                        <div class="flex gap-4 rounded-xl border border-white/[0.05] bg-white/[0.02] px-4 py-3.5">
                                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-boss-gold text-[0.62rem] font-bold text-boss-ink">{{ $loop->iteration }}</span>
                                                            <p class="text-[0.9rem] leading-relaxed text-boss-ivory/62">{{ $step }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if ($selectedLesson->tipItems() !== [])
                                            <div class="rounded-xl border border-boss-gold/15 bg-boss-gold/[0.05] p-5">
                                                <p class="mb-3 text-[0.62rem] uppercase tracking-[0.18em] text-boss-gold/65">{{ __('Important Tips') }}</p>
                                                <div class="space-y-2.5">
                                                    @foreach ($selectedLesson->tipItems() as $tip)
                                                        <div class="flex items-start gap-3">
                                                            <span class="mt-[3px] flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-boss-gold/30 bg-boss-gold/10 text-[0.55rem] text-boss-gold">✓</span>
                                                            <p class="text-[0.88rem] leading-relaxed text-boss-ivory/65">{{ $tip }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if ($selectedLesson->safetyItems() !== [])
                                            <div class="rounded-xl border border-boss-rose/15 bg-boss-rose/[0.04] p-5">
                                                <p class="mb-3 text-[0.62rem] uppercase tracking-[0.18em] text-boss-rose/65">{{ __('Safety Notes') }}</p>
                                                <div class="space-y-2">
                                                    @foreach ($selectedLesson->safetyItems() as $note)
                                                        <div class="flex items-start gap-3">
                                                            <span class="mt-[3px] flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-boss-rose/25 text-[0.6rem] text-boss-rose/70">!</span>
                                                            <p class="text-[0.88rem] leading-relaxed text-boss-ivory/60">{{ $note }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </section>
                            @endif

                            {{-- Resources --}}
                            @if ($hasLessonResources)
                                <section class="rounded-2xl border border-white/[0.05] bg-boss-panel px-6 py-5">
                                    <div class="mb-4 flex items-center justify-between gap-4">
                                        <div>
                                            <p class="pd-kicker">{{ __('Resources') }}</p>
                                            <h3 class="mt-1 font-display text-[1.05rem] font-semibold text-boss-ivory">{{ __('Lesson Support') }}</h3>
                                        </div>
                                        @if ($selectedLesson->pdf_url)
                                            <a href="{{ $selectedLesson->pdf_url }}" target="_blank" rel="noopener noreferrer" class="pd-btn-secondary shrink-0">{{ __('Open PDF') }}</a>
                                        @endif
                                    </div>
                                    @if ($lessonPdfCanvasUrl)
                                        <div
                                            x-data="pdfLessonViewer({{ Js::from($lessonPdfCanvasUrl) }})"
                                            x-init="init()"
                                            class="mb-4 overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f]"
                                        >
                                            <div x-show="loading && !error" class="flex items-center justify-center gap-3 bg-[#060610] py-16">
                                                <svg class="h-5 w-5 animate-spin text-boss-gold/50" viewBox="0 0 24 24" fill="none">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                                <span class="text-[0.8rem] text-boss-ivory/40">{{ __('Loading PDF...') }}</span>
                                            </div>
                                            <div x-show="error" class="bg-[#060610] px-5 py-5 text-[0.78rem] text-red-300/70" x-text="error"></div>
                                            <div x-show="!loading && !error" x-ref="wrap" class="pd-scroll overflow-y-auto bg-[#060610]" style="height: min(76vh, 860px); min-height: min(520px, 68vh); scrollbar-gutter: stable; scroll-behavior: smooth">
                                                <div x-ref="pages" class="mx-auto flex w-full max-w-[1120px] flex-col items-center gap-5 px-4 py-5 sm:gap-6 sm:px-6"></div>
                                            </div>
                                        </div>
                                    @elseif ($lessonPdfPreviewUrl)
                                        <div class="mb-4 overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f]">
                                            <div class="aspect-video w-full">
                                                <iframe class="h-full w-full" src="{{ $lessonPdfPreviewUrl }}" title="{{ __('PDF Guide') }}" loading="lazy"></iframe>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($lessonResourceItems !== [])
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            @foreach ($lessonResourceItems as $resource)
                                                <a href="{{ $resource['url'] }}" target="_blank" rel="noopener noreferrer"
                                                   class="rounded-xl border border-white/[0.05] bg-white/[0.02] px-4 py-3 text-[0.82rem] text-boss-ivory/50 transition-colors hover:border-boss-gold/22 hover:text-boss-gold">
                                                    {{ $resource['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </section>
                            @endif

                        {{-- ── NO LESSON ────────────────────────────────────── --}}
                        @else
                            <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-10 text-center">
                                <p class="font-display text-[1.4rem] text-boss-ivory">{{ __('Lessons are coming soon.') }}</p>
                                <p class="mt-2 text-[0.85rem] text-boss-ivory/35">{{ __('This course is published, but no lessons are available yet.') }}</p>
                            </section>
                        @endif

                    </div>{{-- /reading column --}}
                </div>{{-- /scrollable area --}}

                {{-- ── Fixed lesson navigation footer ─────────────────────── --}}
                @if ($selectedLesson || $selectedCourseItem)
                    <div class="shrink-0 border-t border-white/[0.05] bg-boss-panel px-5 py-3">
                        <div class="mx-auto flex max-w-[900px] flex-wrap items-center justify-between gap-3">

                            {{-- Left: Previous + Mark Complete --}}
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($previousEntry)
                                    <a href="{{ $previousEntry['url'] }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.08] bg-white/[0.03] px-3.5 py-2 text-[0.73rem] text-boss-ivory/50 transition-colors hover:border-white/[0.14] hover:text-boss-ivory">
                                        <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2]"><path d="M10 3L6 8l4 5"/></svg>
                                        <span class="hidden sm:inline">{{ $previousEntry['type'] === 'lesson' ? __('Previous Lesson') : __('Previous') }}</span>
                                        <span class="sm:hidden">{{ __('Prev') }}</span>
                                    </a>
                                @endif

                                @if ($selectedLesson && ! $previewMode)
                                    <form method="POST" action="{{ route('member.lessons.progress', $selectedLesson) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if ($selectedDone)
                                            <button type="submit" name="completed" value="0"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.08] bg-white/[0.03] px-3.5 py-2 text-[0.73rem] text-boss-ivory/45 transition-colors hover:text-boss-ivory">
                                                {{ __('Mark Incomplete') }}
                                            </button>
                                        @else
                                            <button type="submit" name="completed" value="1"
                                                class="inline-flex items-center gap-2 rounded-lg border border-boss-gold/30 bg-boss-gold/[0.12] px-4 py-2 text-[0.73rem] font-medium text-boss-gold transition-colors hover:bg-boss-gold/[0.20]">
                                                <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2.5]"><path d="M3 8l3.5 3.5L13 5"/></svg>
                                                {{ __('Mark Complete') }}
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </div>

                            {{-- Right: Community + Next --}}
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($communityUrl)
                                    <a href="{{ $communityUrl }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.07] bg-white/[0.03] px-3 py-2 text-[0.7rem] text-boss-ivory/40 transition-colors hover:border-boss-gold/20 hover:text-boss-gold">
                                        <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[1.6]"><path d="M14 10c0 1.1-.9 2-2 2H4l-3 3V4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2v6z"/></svg>
                                        <span class="hidden sm:inline">{{ __('Ask in Community') }}</span>
                                    </a>
                                @endif
                                @if ($nextEntry)
                                    <a href="{{ $nextEntry['url'] }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-boss-gold/28 bg-boss-gold/[0.10] px-4 py-2 text-[0.73rem] font-medium text-boss-gold transition-colors hover:bg-boss-gold/[0.18]">
                                        <span>{{ $nextEntry['type'] === 'lesson' ? __('Next Lesson') : __('Next') }}</span>
                                        <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2]"><path d="M6 3l4 5-4 5"/></svg>
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>
                @endif

            </div>{{-- /lesson content panel --}}
        </div>{{-- /body row --}}

        {{-- ── Mobile nav overlay ─────────────────────────────────────── --}}
        <div
            x-show="mobileNav"
            x-cloak
            class="fixed inset-0 z-50 lg:hidden"
            x-transition:enter="transition-opacity duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="mobileNav = false"></div>

            <aside
                class="absolute inset-y-0 left-0 flex w-[280px] flex-col bg-[#0a0a11] shadow-luxe"
                x-transition:enter="transition-transform duration-250 ease-out"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform duration-200 ease-in"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
            >
                <div class="flex shrink-0 items-center justify-between border-b border-white/[0.05] px-4 py-3.5">
                    <div>
                        <p class="text-[0.58rem] uppercase tracking-[0.18em] text-boss-ivory/25">{{ __('Course Outline') }}</p>
                        <p class="text-[0.65rem] text-boss-gold/60">{{ $progress['completed'] }}/{{ $progress['total'] }} {{ __('done') }} · {{ $progress['percent'] }}%</p>
                    </div>
                    <button type="button" @click="mobileNav = false" class="flex h-7 w-7 items-center justify-center rounded-lg text-boss-ivory/40 transition-colors hover:text-boss-ivory">
                        <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[2]"><path d="M3 3l10 10M13 3L3 13"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto py-2">
                    {{-- Start Here (mobile) --}}
                    @if ($hasCourseOutlineItem || $hasIntroItem)
                        <div class="mb-1 px-2.5">
                            <p class="mb-1 px-1 pt-1 text-[0.55rem] uppercase tracking-[0.18em] text-boss-gold/50">{{ __('Start Here') }}</p>
                            @if ($hasCourseOutlineItem)
                                @php $isCurrent = $selectedCourseItem === 'outline'; @endphp
                                <a href="{{ $courseMaterialUrl('outline') }}" @click="mobileNav = false"
                                   class="flex items-center gap-2.5 rounded-lg border px-2.5 py-2 text-[0.71rem] {{ $isCurrent ? 'border-boss-gold/20 bg-boss-gold/[0.07] text-boss-ivory' : 'border-transparent text-boss-ivory/42' }}">
                                    <span class="min-w-0 flex-1 truncate">{{ __('Course Outline') }}</span>
                                </a>
                            @endif
                            @if ($hasIntroItem)
                                @php $isCurrent = $selectedCourseItem === 'intro'; @endphp
                                <a href="{{ $courseMaterialUrl('intro') }}" @click="mobileNav = false"
                                   class="mt-0.5 flex items-center gap-2.5 rounded-lg border px-2.5 py-2 text-[0.71rem] {{ $isCurrent ? 'border-boss-gold/20 bg-boss-gold/[0.07] text-boss-ivory' : 'border-transparent text-boss-ivory/42' }}">
                                    <span class="min-w-0 flex-1 truncate">{{ $course->intro_title ?: __('Introduction') }}</span>
                                </a>
                            @endif
                        </div>
                        <div class="mx-2.5 mb-1 mt-1 border-t border-white/[0.04]"></div>
                    @endif

                    {{-- Modules (collapsible on mobile too) --}}
                    @forelse ($course->modules as $module)
                        @php
                            $ms      = $moduleProgress[$module->id] ?? ['completed' => 0, 'total' => $module->lessons->count(), 'percent' => 0];
                            $allDone = $ms['total'] > 0 && $ms['completed'] >= $ms['total'];
                        @endphp
                        <div class="px-2.5">
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left transition-colors hover:bg-white/[0.025]"
                                @click="toggleModule({{ $module->id }})"
                            >
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[0.48rem] font-bold {{ $allDone ? 'border-emerald-400/40 bg-emerald-400/10 text-emerald-400' : 'border-white/[0.10] text-boss-ivory/30' }}">
                                    @if ($allDone)
                                        <svg viewBox="0 0 16 16" class="h-2.5 w-2.5 fill-none stroke-current stroke-[2.5]"><path d="M3 8.5l3.5 3.5L13 5"/></svg>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[0.7rem] font-medium text-boss-ivory/75">{{ $module->title }}</p>
                                    <p class="text-[0.58rem] {{ $allDone ? 'text-emerald-400/70' : 'text-boss-ivory/28' }}">{{ $ms['completed'] }}/{{ $ms['total'] }}</p>
                                </div>
                                <svg viewBox="0 0 16 16"
                                     class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[2] text-boss-ivory/25 transition-transform duration-200"
                                     :class="isOpen({{ $module->id }}) ? 'rotate-180' : ''">
                                    <path d="M4 6l4 4 4-4"/>
                                </svg>
                            </button>

                            <div
                                style="display: grid; transition: grid-template-rows 220ms ease;"
                                :style="isOpen({{ $module->id }}) ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'"
                            >
                                <div style="overflow: hidden; min-height: 0;">
                                    <div class="space-y-0.5 pb-1.5 pl-3.5 pr-1 pt-0.5">
                                        @foreach ($module->lessons as $lesson)
                                            @php
                                                $done      = in_array($lesson->id, $progress['completedLessonIds'], true);
                                                $isCurrent = $selectedLesson && $selectedLesson->id === $lesson->id;
                                            @endphp
                                            <a href="{{ $lessonUrl($lesson) }}" @click="mobileNav = false"
                                               class="flex items-center gap-2 rounded-md border px-2 py-1.5 text-[0.69rem] {{ $isCurrent ? 'border-boss-gold/18 bg-boss-gold/[0.06] text-boss-ivory' : 'border-transparent text-boss-ivory/38' }}">
                                                <span class="flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full border {{ $done ? 'border-emerald-400/40 bg-emerald-400 text-[#080808]' : ($isCurrent ? 'border-boss-gold/35 text-boss-gold' : 'border-white/[0.10] text-boss-ivory/15') }}">
                                                    @if ($done)
                                                        <svg viewBox="0 0 16 16" class="h-2 w-2 fill-none stroke-current stroke-[3]"><path d="M3 8l3.5 3.5L13 5"/></svg>
                                                    @elseif ($isCurrent)
                                                        <svg viewBox="0 0 16 16" class="h-2 w-2 fill-current"><polygon points="5,3 13,8 5,13"/></svg>
                                                    @endif
                                                </span>
                                                <span class="min-w-0 flex-1 truncate leading-snug">{{ $lesson->title }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-[0.75rem] text-boss-ivory/22">{{ __('No modules yet.') }}</p>
                    @endforelse
                </div>
            </aside>
        </div>

    </div>{{-- /course player --}}
</x-member-layout>
