@php
    $blockType = $block->flowType();
    $blockImageUrl = $block->imageUrl();
    $blockFileUrl = $block->fileUrl();
    $blockVideoUrl = $block->videoEmbedUrl();
    $blockPresentationOpenUrl = $block->presentationOpenUrl();
@endphp

@switch($blockType)
    @case('image')
        <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
            <img src="{{ $blockImageUrl }}" alt="{{ $block->title ?: $selectedLesson->title }}" class="max-h-[620px] w-full object-cover">
            @if ($block->title)
                <div class="border-t border-white/[0.05] bg-boss-panel px-5 py-4 md:px-6">
                    <h3 class="pd-heading text-[1.25rem] text-boss-ivory">{{ $block->title }}</h3>
                </div>
            @endif
        </section>
        @break

    @case('video')
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
            @if ($block->title)
                <div class="mb-4 max-w-3xl">
                    <p class="pd-kicker">{{ __('Video') }}</p>
                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $block->title }}</h3>
                </div>
            @endif
            <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f] shadow-glow">
                <div class="aspect-video w-full">
                    <iframe class="h-full w-full" src="{{ $blockVideoUrl }}" title="{{ $block->title ?: $selectedLesson->title }}" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
        </section>
        @break

    @case('pdf_resource')
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="max-w-3xl">
                    <p class="pd-kicker">{{ __('PDF') }}</p>
                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $block->title ?: __('Lesson PDF') }}</h3>
                </div>
                <a href="{{ $blockFileUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ __('Open PDF') }}</a>
            </div>
        </section>
        @break

    @case('presentation')
        <section class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="max-w-3xl">
                    <p class="pd-kicker">{{ __('Presentation') }}</p>
                    <h3 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $block->title ?: __('Lesson Presentation') }}</h3>
                </div>
                <a href="{{ $blockPresentationOpenUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn-primary shrink-0">{{ __('Open Presentation') }}</a>
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
