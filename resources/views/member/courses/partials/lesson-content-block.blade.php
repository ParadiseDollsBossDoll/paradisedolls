@php
    $blockType = $block->flowType();
    $blockImageUrl = $block->imageUrl();
    $blockFileUrl = $block->fileUrl();
    $blockVideoUrl = $block->videoEmbedUrl();
    $blockPresentationEmbedUrl = $block->canvaPresentationEmbedUrl();
    $blockPresentationOpenUrl = $block->presentationOpenUrl();
    $blockLines = $block->contentLines();
    $galleryUrls = $block->galleryImageUrls();
    $galleryCaptions = $block->galleryCaptions();
@endphp

@switch($blockType)
    @case('divider')
        <div class="flex items-center gap-3 py-3">
            <span class="h-px flex-1 bg-white/[0.08]"></span>
            <span class="h-1.5 w-1.5 rounded-full bg-boss-gold/50"></span>
            <span class="h-px flex-1 bg-white/[0.08]"></span>
        </div>
        @break

    @case('heading')
        <section class="px-1 py-2">
            @if ($block->title)
                <h3 class="pd-heading max-w-4xl text-[clamp(1.55rem,3vw,2.35rem)] leading-tight text-boss-ivory">{{ $block->title }}</h3>
            @endif
            @if ($block->content)
                <p class="mt-3 max-w-3xl whitespace-pre-line text-[1rem] leading-8 text-boss-ivory/52">{{ $block->content }}</p>
            @endif
        </section>
        @break

    @case('image')
        <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
            <img src="{{ $blockImageUrl }}" alt="{{ $block->title ?: $selectedLesson->title }}" class="max-h-[620px] w-full object-cover">
            @if ($block->title || $block->content)
                <div class="border-t border-white/[0.05] bg-boss-panel px-5 py-4 md:px-6">
                    @if ($block->title)
                        <h3 class="pd-heading text-[1.25rem] text-boss-ivory">{{ $block->title }}</h3>
                    @endif
                    @if ($block->content)
                        <p class="mt-2 whitespace-pre-line text-[0.88rem] leading-7 text-boss-ivory/48">{{ $block->content }}</p>
                    @endif
                </div>
            @endif
        </section>
        @break

    @case('gallery')
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
            @if ($block->title || $block->content)
                <div class="mb-4 max-w-3xl">
                    @if ($block->title)
                        <h3 class="pd-heading text-[1.35rem] text-boss-ivory">{{ $block->title }}</h3>
                    @endif
                    @if ($block->content)
                        <p class="mt-2 whitespace-pre-line text-[0.9rem] leading-7 text-boss-ivory/48">{{ $block->content }}</p>
                    @endif
                </div>
            @endif
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($galleryUrls as $imageUrl)
                    <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer" class="group overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f] transition-colors hover:border-boss-gold/25">
                        <img src="{{ $imageUrl }}" alt="{{ $selectedLesson->title }} {{ $loop->iteration }}" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105">
                        @if (! blank($galleryCaptions[$loop->index] ?? null))
                            <span class="block border-t border-white/[0.05] px-3 py-2 text-[0.72rem] leading-relaxed text-boss-ivory/42">{{ $galleryCaptions[$loop->index] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
        @break

    @case('video')
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
            @if ($block->title || $block->content)
                <div class="mb-4 max-w-3xl">
                    @if ($block->title)
                        <p class="pd-kicker">{{ __('Video') }}</p>
                        <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $block->title }}</h3>
                    @endif
                    @if ($block->content)
                        <p class="mt-3 whitespace-pre-line text-[0.9rem] leading-7 text-boss-ivory/50">{{ $block->content }}</p>
                    @endif
                </div>
            @endif
            <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f] shadow-glow">
                <div class="aspect-video w-full">
                    <iframe class="h-full w-full" src="{{ $blockVideoUrl }}" title="{{ $block->title ?: $selectedLesson->title }}" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
        </section>
        @break

    @case('canva')
        <section
            class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6"
            @if ($blockPresentationEmbedUrl)
                x-data="{ presentationBlocked: false, presentationLoaded: false, presentationTimer: null }"
                x-init="presentationTimer = window.setTimeout(() => { if (! presentationLoaded) presentationBlocked = true }, 7000)"
            @endif
        >
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="max-w-3xl">
                    <p class="pd-kicker">{{ __('Presentation') }}</p>
                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $block->title ?: __('Lesson Slides') }}</h3>
                    @if ($block->content)
                        <p class="mt-3 whitespace-pre-line text-[0.9rem] leading-7 text-boss-ivory/50">{{ $block->content }}</p>
                    @endif
                </div>
                @if ($blockPresentationOpenUrl)
                    <a href="{{ $blockPresentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-secondary">{{ __('Open Presentation') }}</a>
                @endif
            </div>

            @if ($blockPresentationEmbedUrl)
                <div x-show="! presentationBlocked" class="presentation-wrapper relative aspect-video w-full overflow-hidden rounded-2xl border border-boss-gold/25 bg-[#0f0d0b]">
                    <iframe
                        src="{{ $blockPresentationEmbedUrl }}"
                        title="{{ $block->title ?: $selectedLesson->title }}"
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
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-boss-gold/25 bg-boss-gold/10 font-display text-[1.25rem] text-boss-gold-light">C</div>
                        <div class="min-w-0 flex-1">
                            <h4 class="pd-heading text-[1.25rem] text-boss-ivory">{{ __('Presentation cannot be embedded') }}</h4>
                            <p class="mt-2 text-[0.82rem] leading-relaxed text-boss-ivory/45">{{ __('Open the presentation in a new tab to view it.') }}</p>
                        </div>
                        @if ($blockPresentationOpenUrl)
                            <a href="{{ $blockPresentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ __('Open Presentation') }}</a>
                        @endif
                    </div>
                </div>
            @elseif ($blockPresentationOpenUrl)
                <div class="rounded-2xl border border-boss-gold/20 bg-boss-gold/[0.045] p-5 md:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-boss-gold/25 bg-boss-gold/10 font-display text-[1.25rem] text-boss-gold-light">C</div>
                        <div class="min-w-0 flex-1">
                            <h4 class="pd-heading text-[1.25rem] text-boss-ivory">{{ __('Presentation cannot be embedded') }}</h4>
                            <p class="mt-2 text-[0.82rem] leading-relaxed text-boss-ivory/45">{{ __('Open the presentation in a new tab to view it.') }}</p>
                        </div>
                        <a href="{{ $blockPresentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ __('Open Presentation') }}</a>
                    </div>
                </div>
            @endif
        </section>
        @break

    @case('pdf_resource')
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="max-w-3xl">
                    <p class="pd-kicker">{{ __('Resource') }}</p>
                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $block->title ?: __('Lesson Resource') }}</h3>
                    @if ($block->content)
                        <p class="mt-3 whitespace-pre-line text-[0.9rem] leading-7 text-boss-ivory/50">{{ $block->content }}</p>
                    @endif
                </div>
                <a href="{{ $blockFileUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ $block->buttonLabel(__('Open Resource')) }}</a>
            </div>
            @if (str_ends_with(strtolower(parse_url($blockFileUrl, PHP_URL_PATH) ?? ''), '.pdf'))
                <div class="mt-4 overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f]">
                    <div class="aspect-video w-full">
                        <iframe class="h-full w-full" src="{{ $blockFileUrl }}#page=1&view=FitH" title="{{ $block->title ?: __('PDF Guide') }}" loading="lazy"></iframe>
                    </div>
                </div>
            @endif
        </section>
        @break

    @case('steps')
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
            <p class="pd-kicker">{{ __('Step-by-step') }}</p>
            @if ($block->title)
                <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $block->title }}</h3>
            @endif
            <div class="mt-4 space-y-2">
                @foreach ($blockLines as $step)
                    <div class="flex gap-3 rounded-xl border border-white/[0.06] bg-white/[0.025] p-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-boss-gold text-[0.7rem] font-semibold text-boss-ink">{{ $loop->iteration }}</span>
                        <p class="text-[0.88rem] leading-relaxed text-boss-ivory/58">{{ $step }}</p>
                    </div>
                @endforeach
            </div>
        </section>
        @break

    @case('tips')
        <section class="rounded-2xl border border-boss-gold/15 bg-boss-gold/[0.045] p-5 md:p-6">
            <p class="pd-kicker">{{ __('Important Tips') }}</p>
            @if ($block->title)
                <h3 class="pd-heading mt-2 text-[1.25rem] text-boss-ivory">{{ $block->title }}</h3>
            @endif
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach ($blockLines as $tip)
                    <p class="rounded-xl border border-boss-gold/15 bg-boss-gold/[0.05] px-3 py-2 text-[0.84rem] leading-relaxed text-boss-ivory/58">{{ $tip }}</p>
                @endforeach
            </div>
        </section>
        @break

    @case('safety')
        <section class="rounded-2xl border border-red-300/15 bg-red-300/[0.045] p-5 md:p-6">
            <p class="text-[0.7rem] uppercase tracking-[0.18em] text-red-200/60">{{ __('Safety Notes') }}</p>
            @if ($block->title)
                <h3 class="pd-heading mt-2 text-[1.25rem] text-boss-ivory">{{ $block->title }}</h3>
            @endif
            <div class="mt-4 space-y-2">
                @foreach ($blockLines as $note)
                    <p class="rounded-xl border border-red-200/15 bg-red-200/[0.04] px-3 py-2 text-[0.84rem] leading-relaxed text-boss-ivory/58">{{ $note }}</p>
                @endforeach
            </div>
        </section>
        @break

    @default
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-7">
            @if ($block->title)
                <h3 class="pd-heading text-[1.35rem] text-boss-ivory">{{ $block->title }}</h3>
            @endif
            @if ($block->content)
                <p class="mt-3 max-w-3xl whitespace-pre-line text-[0.95rem] leading-8 text-boss-ivory/60">{{ $block->content }}</p>
            @endif
        </section>
@endswitch
