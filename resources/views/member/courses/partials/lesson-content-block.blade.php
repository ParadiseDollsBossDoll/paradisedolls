@php
    $blockType                = $block->flowType();
    $blockImageUrl            = $block->imageUrl();
    $blockFileUrl             = $block->fileUrl();
    $blockVideoUrl            = $block->videoEmbedUrl();
    $blockPresentationOpenUrl = $block->presentationOpenUrl();
@endphp

@switch($blockType)

    {{-- ── IMAGE ─────────────────────────────────────────────────────── --}}
    @case('image')
        <figure class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
            <img
                src="{{ $blockImageUrl }}"
                alt="{{ $block->title ?: $selectedLesson->title }}"
                class="max-h-[640px] w-full object-cover"
            >
            @if ($block->title)
                <figcaption class="border-t border-white/[0.05] bg-boss-panel px-5 py-3">
                    <p class="text-[0.8rem] text-boss-ivory/55">{{ $block->title }}</p>
                </figcaption>
            @endif
        </figure>
        @break

    {{-- ── VIDEO ─────────────────────────────────────────────────────── --}}
    @case('video')
        <div class="space-y-3">
            @if ($block->title)
                <div>
                    <p class="pd-kicker">{{ __('Video') }}</p>
                    <h3 class="mt-1.5 font-display text-[1.2rem] font-semibold text-boss-ivory">{{ $block->title }}</h3>
                </div>
            @endif
            <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f] shadow-luxe">
                <div class="aspect-video w-full">
                    <iframe
                        class="h-full w-full"
                        src="{{ $blockVideoUrl }}"
                        title="{{ $block->title ?: $selectedLesson->title }}"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                </div>
            </div>
        </div>
        @break

    {{-- ── PDF ──────────────────────────────────────────────────────── --}}
    @case('pdf_resource')
        <div class="flex items-center gap-4 rounded-2xl border border-white/[0.05] bg-boss-panel px-5 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.04] text-boss-ivory/40">
                <svg viewBox="0 0 16 16" class="h-5 w-5 fill-none stroke-current stroke-[1.5]">
                    <path d="M4 2h5.5L12 4.5V14H4V2z"/><path d="M9.5 2v2.5H12"/><path d="M6 7h4M6 9.5h4M6 12h2"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="pd-kicker">{{ __('PDF Resource') }}</p>
                <p class="mt-0.5 truncate text-[0.88rem] font-medium text-boss-ivory">{{ $block->title ?: __('Lesson PDF') }}</p>
            </div>
            <a href="{{ $blockFileUrl }}" target="_blank" rel="noopener noreferrer"
               class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-boss-gold/28 bg-boss-gold/[0.10] px-4 py-2 text-[0.75rem] font-medium text-boss-gold transition-colors hover:bg-boss-gold/[0.18]">
                {{ $block->buttonLabel(__('Open PDF')) }}
            </a>
        </div>
        @break

    {{-- ── PRESENTATION ─────────────────────────────────────────────── --}}
    @case('presentation')
        <div class="flex items-center gap-4 rounded-2xl border border-white/[0.05] bg-boss-panel px-5 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.04] text-boss-ivory/40">
                <svg viewBox="0 0 16 16" class="h-5 w-5 fill-none stroke-current stroke-[1.5]">
                    <rect x="1" y="2" width="14" height="10" rx="1.5"/><path d="M8 12v2M5 14h6"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="pd-kicker">{{ __('Presentation') }}</p>
                <p class="mt-0.5 truncate text-[0.88rem] font-medium text-boss-ivory">{{ $block->title ?: __('Lesson Slides') }}</p>
            </div>
            <a href="{{ $blockPresentationOpenUrl }}" target="_blank" rel="noopener noreferrer"
               class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-boss-gold/28 bg-boss-gold/[0.10] px-4 py-2 text-[0.75rem] font-medium text-boss-gold transition-colors hover:bg-boss-gold/[0.18]">
                {{ $block->buttonLabel(__('Open Slides')) }}
            </a>
        </div>
        @break

    {{-- ── TEXT (default) ──────────────────────────────────────────── --}}
    @default
        <div class="space-y-3">
            @if ($block->title)
                <h3 class="font-display text-[1.3rem] font-semibold leading-snug text-boss-ivory">{{ $block->title }}</h3>
            @endif
            @if ($block->content)
                <div class="whitespace-pre-line text-[1rem] leading-[1.9] text-boss-ivory/68">{{ $block->content }}</div>
            @endif
        </div>

@endswitch
