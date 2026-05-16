<x-admin-layout>
    @php
        $pageCollection  = $courses->getCollection();
        $publishedCount  = $pageCollection->where('is_published', true)->count();
        $draftCount      = $pageCollection->where('is_published', false)->count();
        $totalLessons    = $pageCollection->sum('lessons_count');
    @endphp

    <div class="mx-auto max-w-6xl space-y-8 text-boss-ivory">

        {{-- ── Page header ─────────────────────────────────────────────── --}}
        <header class="flex items-start justify-between gap-4">
            <div>
                <p class="pd-kicker">{{ __('Admin') }}</p>
                <h1 class="pd-heading pd-text-gradient mt-1.5 text-[clamp(1.6rem,3.5vw,2.3rem)]">{{ __('Courses') }}</h1>
                <p class="mt-1 text-[0.76rem] text-boss-ivory/32">
                    {{ trans_choice(':count total course|:count total courses', $courses->total(), ['count' => $courses->total()]) }}
                </p>
            </div>
            <a href="{{ route('admin.courses.create') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-full border border-boss-gold/35 bg-boss-gold/[0.12] px-5 py-2.5 text-[0.76rem] font-semibold text-boss-gold transition-colors hover:bg-boss-gold/[0.22]">
                <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2.2]"><circle cx="8" cy="8" r="6"/><path d="M8 5v6M5 8h6"/></svg>
                {{ __('New Course') }}
            </a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        {{-- ── Quick stats ─────────────────────────────────────────────── --}}
        @if ($courses->total() > 0)
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([
                    ['label' => __('Total Courses'),   'value' => $courses->total(), 'gold' => false],
                    ['label' => __('Published'),        'value' => $publishedCount,   'gold' => $publishedCount > 0],
                    ['label' => __('Drafts'),           'value' => $draftCount,       'gold' => false],
                    ['label' => __('Total Lessons'),    'value' => $totalLessons,     'gold' => false],
                ] as $stat)
                    <div class="rounded-2xl border border-white/[0.05] bg-boss-panel px-5 py-4">
                        <p class="text-[0.56rem] uppercase tracking-[0.16em] text-boss-ivory/25">{{ $stat['label'] }}</p>
                        <p class="mt-1.5 font-display text-[1.7rem] font-semibold leading-none {{ $stat['gold'] ? 'text-boss-gold' : 'text-boss-ivory/80' }}">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── Course grid ─────────────────────────────────────────────── --}}
        @if ($courses->isEmpty())
            <div class="rounded-2xl border border-white/[0.05] bg-boss-ink px-6 py-20 text-center">
                <p class="font-display text-[1.1rem] text-boss-ivory/30">{{ __('No courses yet.') }}</p>
                <a href="{{ route('admin.courses.create') }}"
                   class="mt-4 inline-flex items-center gap-1.5 text-[0.8rem] text-boss-gold hover:text-boss-gold-light">
                    {{ __('Create your first course') }} →
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($courses as $course)
                    @php
                        $stats = $courseStats[$course->id] ?? ['started' => 0, 'finished' => 0, 'messages' => 0];
                        $color = $course->displayColor();
                        $bg    = $course->displayColorBackground();
                        $image = $course->overviewImageUrl();
                    @endphp

                    <article class="group flex flex-col overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-ink transition-all duration-300 hover:border-white/[0.10] hover:shadow-glow">

                        {{-- ── Image area ─────────────────────────────────── --}}
                        <div class="relative h-[210px] shrink-0 overflow-hidden">
                            @if ($image)
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $course->title }}"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-boss-ink via-boss-ink/15 to-transparent"></div>
                            @else
                                <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $course->displayColorBackground(0.45) }}, rgba(8,8,15,0.95) 70%);"></div>
                                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_20%,rgba(232,200,138,0.10),transparent_55%)]"></div>
                                <div class="absolute inset-0 flex items-center justify-center overflow-hidden px-6">
                                    <p class="select-none text-center font-display text-[2rem] font-bold leading-tight text-white opacity-[0.04]">{{ $course->title }}</p>
                                </div>
                            @endif

                            {{-- Top-left: platform + publish status --}}
                            <div class="absolute left-4 top-4 flex items-center gap-2">
                                <span class="rounded-full border px-2.5 py-0.5 text-[0.6rem] font-medium backdrop-blur-sm"
                                      style="background: {{ $bg }}; color: {{ $color }}; border-color: {{ $color }}22;">
                                    {{ $course->displayPlatform() }}
                                </span>
                                @if ($course->is_published)
                                    <span class="flex items-center gap-1 rounded-full border border-emerald-400/25 bg-black/40 px-2.5 py-0.5 text-[0.6rem] text-emerald-400 backdrop-blur-sm">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        {{ __('Live') }}
                                    </span>
                                @else
                                    <span class="flex items-center gap-1 rounded-full border border-white/[0.10] bg-black/40 px-2.5 py-0.5 text-[0.6rem] text-boss-ivory/45 backdrop-blur-sm">
                                        <span class="h-1.5 w-1.5 rounded-full bg-boss-ivory/30"></span>
                                        {{ __('Draft') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Top-right: lesson count --}}
                            <div class="absolute right-4 top-4">
                                <span class="rounded-full border border-white/[0.08] bg-black/40 px-2.5 py-0.5 text-[0.6rem] text-boss-ivory/45 backdrop-blur-sm">
                                    {{ trans_choice(':count lesson|:count lessons', $course->lessons_count, ['count' => $course->lessons_count]) }}
                                </span>
                            </div>

                            {{-- Bottom accent line --}}
                            <div class="absolute inset-x-0 bottom-0 h-[2px] opacity-50 transition-opacity duration-300 group-hover:opacity-90"
                                 style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}22);"></div>
                        </div>

                        {{-- ── Content area ────────────────────────────────── --}}
                        <div class="flex flex-1 flex-col p-5">

                            {{-- Title + description --}}
                            <div class="flex-1">
                                <h2 class="pd-heading line-clamp-2 text-[1.05rem] leading-snug text-boss-ivory transition-colors duration-300 group-hover:text-boss-gold-light">
                                    {{ $course->title }}
                                </h2>
                                <p class="mt-2 line-clamp-2 text-[0.74rem] leading-relaxed text-boss-ivory/30">
                                    {{ $course->description ?: __('No description provided.') }}
                                </p>
                            </div>

                            {{-- Admin engagement stats ───────────────────── --}}
                            <div class="mt-4 flex items-center border-t border-white/[0.04] pt-4">
                                <div class="flex flex-1 flex-col items-center">
                                    <span class="font-display text-[1.05rem] font-semibold text-boss-ivory">{{ $stats['started'] }}</span>
                                    <span class="mt-0.5 text-[0.56rem] uppercase tracking-[0.12em] text-boss-ivory/25">{{ __('started') }}</span>
                                </div>
                                <div class="h-6 w-px bg-white/[0.05]"></div>
                                <div class="flex flex-1 flex-col items-center">
                                    <span class="font-display text-[1.05rem] font-semibold {{ $stats['finished'] > 0 ? 'text-boss-gold' : 'text-boss-ivory' }}">{{ $stats['finished'] }}</span>
                                    <span class="mt-0.5 text-[0.56rem] uppercase tracking-[0.12em] text-boss-ivory/25">{{ __('finished') }}</span>
                                </div>
                                <div class="h-6 w-px bg-white/[0.05]"></div>
                                <div class="flex flex-1 flex-col items-center">
                                    <span class="font-display text-[1.05rem] font-semibold text-boss-ivory">{{ $stats['messages'] }}</span>
                                    <span class="mt-0.5 text-[0.56rem] uppercase tracking-[0.12em] text-boss-ivory/25">{{ __('messages') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ── Admin action toolbar ─────────────────────────── --}}
                        <div class="shrink-0 border-t border-white/[0.04] px-5 py-3.5">
                            <div class="flex items-center gap-2">

                                {{-- Publish / Draft toggle --}}
                                <form method="POST" action="{{ route('admin.courses.visibility', $course) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_published" value="{{ $course->is_published ? 0 : 1 }}">
                                    <button
                                        type="submit"
                                        class="flex w-full items-center justify-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.68rem] font-medium transition-colors
                                            {{ $course->is_published
                                                ? 'border-emerald-400/20 bg-emerald-400/[0.07] text-emerald-400 hover:bg-emerald-400/[0.13]'
                                                : 'border-white/[0.07] bg-white/[0.03] text-boss-ivory/40 hover:border-boss-gold/28 hover:text-boss-gold' }}"
                                    >
                                        @if ($course->is_published)
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                            {{ __('Published') }}
                                        @else
                                            <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2]"><path d="M8 2v12M3 7l5-5 5 5"/></svg>
                                            {{ __('Publish') }}
                                        @endif
                                    </button>
                                </form>

                                {{-- Divider --}}
                                <div class="h-5 w-px shrink-0 bg-white/[0.06]"></div>

                                {{-- Preview --}}
                                <a
                                    href="{{ route('admin.courses.preview', $course) }}"
                                    target="_blank"
                                    title="{{ __('Preview course') }}"
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.03] text-boss-ivory/38 transition-colors hover:border-white/[0.14] hover:text-boss-ivory/80"
                                >
                                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.7]">
                                        <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z"/>
                                        <circle cx="8" cy="8" r="2"/>
                                    </svg>
                                </a>

                                {{-- Edit --}}
                                <a
                                    href="{{ route('admin.courses.edit', $course) }}"
                                    title="{{ __('Edit course') }}"
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-boss-gold/22 bg-boss-gold/[0.08] text-boss-gold transition-colors hover:bg-boss-gold/[0.18]"
                                >
                                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.7]">
                                        <path d="M11 2l3 3-8 8H3v-3L11 2z"/>
                                    </svg>
                                </a>

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                      onsubmit="return confirm('{{ __('Delete this course? This cannot be undone.') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        title="{{ __('Delete course') }}"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/[0.06] bg-white/[0.02] text-red-400/50 transition-colors hover:border-red-400/22 hover:bg-red-400/[0.08] hover:text-red-400"
                                    >
                                        <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.7]">
                                            <path d="M2 4h12M5 4V2h6v2M6 7v5M10 7v5M3 4l1 9a1 1 0 001 1h6a1 1 0 001-1l1-9"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        {{-- ── Pagination ───────────────────────────────────────────────── --}}
        @if ($courses->hasPages())
            <div class="px-1">{{ $courses->links() }}</div>
        @endif

    </div>
</x-admin-layout>
