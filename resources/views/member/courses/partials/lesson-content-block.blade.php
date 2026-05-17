@php
    $blockType                = $block->flowType();
    $blockImageUrl            = $block->imageUrl();
    $blockFileUrl             = $block->fileUrl();
    $blockVideoUrl            = $block->videoEmbedUrl();
    $blockPresentationOpenUrl = $block->presentationOpenUrl();
    $blockPdfEmbedUrl         = null;
    $blockPresentationEmbedUrl = null;
    $blockPresentationSlideUrls = $blockType === 'presentation'
        ? collect($block->settings['slide_images'] ?? [])
            ->map(fn (?string $path) => $path && preg_match('/^(https?:)?\/\//', $path) ? $path : ($path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null))
            ->filter()
            ->values()
            ->all()
        : [];

    // $blockPdfEmbedUrl  → Google Drive iframe (already clean, no browser chrome)
    // $blockPdfCanvasUrl → local/storage PDF rendered via PDF.js (no browser toolbar)
    $blockPdfCanvasUrl = null;

    if ($blockType === 'pdf_resource' && filled($blockFileUrl)) {
        $parts = parse_url($blockFileUrl);
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
                $blockPdfEmbedUrl = 'https://drive.google.com/file/d/'.rawurlencode($fileId).'/preview';
            }
        } elseif (str_ends_with(strtolower($parts['path'] ?? $blockFileUrl), '.pdf')) {
            // Use PDF.js canvas renderer — avoids the browser's native PDF toolbar entirely
            $blockPdfCanvasUrl = $blockFileUrl;
        }
    }

    if ($blockType === 'presentation' && filled($blockPresentationOpenUrl)) {
        $presentationUrl = $blockPresentationOpenUrl;
        $absolutePresentationUrl = preg_match('/^(https?:)?\/\//', $presentationUrl)
            ? $presentationUrl
            : url($presentationUrl);
        $presentationPath = parse_url($presentationUrl, PHP_URL_PATH) ?: $presentationUrl;
        $presentationExtension = strtolower(pathinfo($presentationPath, PATHINFO_EXTENSION));

        // Embed all PDF presentations directly; skip Office Online / Google Docs dependency.
        if ($presentationExtension === 'pdf' || $presentationExtension === '') {
            $blockPresentationEmbedUrl = $absolutePresentationUrl;
        } elseif (str_contains($absolutePresentationUrl, 'docs.google.com') || str_contains($absolutePresentationUrl, 'drive.google.com')) {
            $blockPresentationEmbedUrl = $absolutePresentationUrl;
        }
        // PPT/PPTX: no Office Online embed — admin should re-upload as PDF.
    }
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
        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel">

            {{-- Header --}}
            <div class="flex flex-wrap items-center gap-4 px-5 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.04] text-boss-ivory/40">
                    <svg viewBox="0 0 16 16" class="h-5 w-5 fill-none stroke-current stroke-[1.5]">
                        <path d="M4 2h5.5L12 4.5V14H4V2z"/><path d="M9.5 2v2.5H12"/><path d="M6 7h4M6 9.5h4M6 12h2"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="pd-kicker">{{ __('PDF Resource') }}</p>
                    <p class="mt-0.5 truncate text-[0.88rem] font-medium text-boss-ivory">{{ $block->title ?: __('Lesson PDF') }}</p>
                </div>
                @if ($blockFileUrl)
                    <a href="{{ $blockFileUrl }}" target="_blank" rel="noopener noreferrer"
                       class="shrink-0 inline-flex items-center gap-2 rounded-lg border border-boss-gold/28 bg-boss-gold/[0.10] px-4 py-2 text-[0.75rem] font-medium text-boss-gold transition-colors hover:bg-boss-gold/[0.18]">
                        <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.5]">
                            <path d="M6 2H3a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9M10 2h4m0 0v4m0-4L7 9"/>
                        </svg>
                        {{ $block->buttonLabel(__('Open PDF in New Tab')) }}
                    </a>
                @endif
            </div>

            @if ($blockPdfCanvasUrl)
                {{-- PDF.js canvas viewer — no browser toolbar, full custom UI --}}
                <div
                    x-data="pdfLessonViewer({{ Js::from($blockPdfCanvasUrl) }})"
                    x-init="init()"
                    class="border-t border-white/[0.05]"
                >
                    {{-- Loading --}}
                    <div x-show="loading && !error"
                         class="flex items-center justify-center gap-3 bg-[#060610] py-16">
                        <svg class="h-5 w-5 animate-spin text-boss-gold/50" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-[0.8rem] text-boss-ivory/40">{{ __('Loading PDF…') }}</span>
                    </div>

                    {{-- Error --}}
                    <div x-show="error" class="bg-[#060610] px-5 py-5 text-[0.78rem] text-red-300/70" x-text="error"></div>

                    {{-- Canvas scroll area — no x-cloak so offsetWidth is always readable --}}
                    <div
                        x-show="!loading && !error"
                        x-ref="wrap"
                        class="pd-scroll overflow-y-auto bg-[#060610]"
                        style="height: min(76vh, 860px); min-height: min(520px, 68vh); scrollbar-gutter: stable; scroll-behavior: smooth"
                    >
                        <div
                            x-ref="pages"
                            class="mx-auto flex w-full max-w-[1120px] flex-col items-center gap-5 px-4 py-5 sm:gap-6 sm:px-6"
                        >
                        </div>
                    </div>

                </div>

            @elseif ($blockPdfEmbedUrl)
                {{-- Google Drive PDF — their iframe embed is already clean --}}
                <div class="h-[72vh] min-h-[520px] w-full overflow-hidden border-t border-white/[0.05]">
                    <iframe
                        class="h-full w-full border-0"
                        src="{{ $blockPdfEmbedUrl }}"
                        title="{{ $block->title ?: __('Lesson PDF') }}"
                        loading="lazy"
                        allowfullscreen
                    ></iframe>
                </div>

            @elseif ($blockFileUrl)
                <div class="border-t border-white/[0.05] px-5 py-4 text-[0.78rem] text-boss-ivory/38">
                    {{ __('PDF preview is unavailable for this file type. Use the button above to open it.') }}
                </div>
            @endif

        </div>
        @break

    {{-- ── PRESENTATION ─────────────────────────────────────────────── --}}
    @case('presentation')
        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel">
            <div class="flex flex-wrap items-center gap-4 px-5 py-4">
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
                   class="shrink-0 inline-flex items-center gap-2 rounded-lg border border-boss-gold/28 bg-boss-gold/[0.10] px-4 py-2 text-[0.75rem] font-medium text-boss-gold transition-colors hover:bg-boss-gold/[0.18]">
                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.5]">
                        <path d="M6 2H3a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9M10 2h4m0 0v4m0-4L7 9"/>
                    </svg>
                    {{ $block->buttonLabel(__('Open Presentation PDF')) }}
                </a>
            </div>
            @if ($blockPresentationSlideUrls !== [])
                <div
                    class="bg-[#06060f]"
                    x-data="{ current: 0, slides: @js($blockPresentationSlideUrls) }"
                >
                    <div class="overflow-hidden bg-black">
                        <div class="flex min-h-[260px] items-center justify-center sm:min-h-[520px]">
                            <template x-for="(slide, slideIndex) in slides" :key="slide">
                                <img
                                    x-show="current === slideIndex"
                                    x-bind:src="slide"
                                    x-bind:alt="`{{ __('Slide') }} ${slideIndex + 1}`"
                                    class="max-h-[72vh] w-full object-contain"
                                >
                            </template>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/[0.05] bg-boss-panel px-4 py-3">
                            <button type="button" @click="current = Math.max(0, current - 1)" x-bind:disabled="current === 0" class="rounded-lg border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-[0.72rem] text-boss-ivory/50 transition-colors hover:border-boss-gold/25 hover:text-boss-gold disabled:opacity-35">
                                {{ __('Previous') }}
                            </button>
                            <span class="text-[0.72rem] text-boss-ivory/38" x-text="`{{ __('Slide') }} ${current + 1} / ${slides.length}`"></span>
                            <button type="button" @click="current = Math.min(slides.length - 1, current + 1)" x-bind:disabled="current === slides.length - 1" class="rounded-lg border border-boss-gold/24 bg-boss-gold/[0.10] px-3 py-2 text-[0.72rem] text-boss-gold transition-colors hover:bg-boss-gold/[0.16] disabled:opacity-35">
                                {{ __('Next') }}
                            </button>
                        </div>
                    </div>
                </div>
            @elseif ($blockPresentationEmbedUrl)
                <div class="h-[70vh] min-h-[480px] w-full pd-scroll overflow-y-auto sm:h-[760px]" style="scrollbar-gutter: stable">
                    <iframe
                        class="h-full w-full border-0"
                        src="{{ $blockPresentationEmbedUrl }}"
                        title="{{ $block->title ?: __('Lesson Slides') }}"
                        allowfullscreen
                        loading="lazy"
                        scrolling="yes"
                    ></iframe>
                </div>
            @else
                <div class="border-t border-white/[0.05] px-5 py-4 text-[0.78rem] text-boss-ivory/38">
                    {{ __('Slide conversion is unavailable on this server. The original presentation PDF was preserved; use the button above to open it.') }}
                </div>
            @endif
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
