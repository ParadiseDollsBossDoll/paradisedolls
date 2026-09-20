@props([
    'title',
    'description',
    'keywords' => [],
    'eyebrow',
    'headline',
    'intro',
    'heroImage',
    'primaryKeyword',
    'secondaryKeywords' => [],
    'sections' => [],
    'faq' => [],
])

<x-layouts.marketing
    :transparentNav="true"
    :title="$title"
    :description="$description"
    :keywords="$keywords"
>
    <section class="pd-marketing-image-hero relative flex min-h-[68vh] items-center overflow-hidden pt-24">
        <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/45 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl text-white">
                <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ $eyebrow }}</p>
                <h1 class="pd-marketing-image-hero-title font-display text-[clamp(2.85rem,7vw,5.6rem)] leading-[0.98] text-[#FFF8F6]">{{ $headline }}</h1>
                <p class="pd-marketing-image-hero-body mt-7 max-w-2xl text-[1.05rem] leading-relaxed text-[#FFF8F6]/90">{{ $intro }}</p>
                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="{{ route('apply') }}" class="rounded-md bg-[#EEB4C3] px-9 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:bg-[#e0a0b5]">{{ __('Apply Now') }}</a>
                    <a href="{{ route('multistreaming') }}" class="rounded-md border border-white/40 px-9 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ __('Learn The System') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Search Focus') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ $primaryKeyword }}</h2>
                <p class="mt-6 text-[0.96rem] leading-relaxed text-boss-dark/62">
                    {{ __('Paradise Dolls uses dedicated, useful pages for important searches instead of hiding keywords in a list. This page explains the opportunity clearly for applicants while helping search engines understand the topic.') }}
                </p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($secondaryKeywords as $keyword)
                    <div class="border border-boss-pink/60 bg-boss-muted p-5">
                        <p class="text-[0.68rem] uppercase tracking-[0.2em] text-boss-rose">{{ __('Related search') }}</p>
                        <h3 class="mt-3 font-display text-[1.25rem] text-boss-dark">{{ $keyword }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($sections as $section)
                    <article class="border border-boss-pink/60 bg-white p-7 shadow-luxe">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.22em] text-boss-rose">{{ $section['eyebrow'] ?? __('Paradise Dolls') }}</p>
                        <h2 class="font-display text-[1.55rem] leading-tight text-boss-dark">{{ $section['title'] ?? '' }}</h2>
                        <p class="mt-4 text-[0.9rem] leading-relaxed text-boss-dark/60">{{ $section['body'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Questions') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('What applicants usually want to know') }}</h2>
            </div>
            <div class="divide-y divide-boss-pink/50 border-y border-boss-pink/50">
                @foreach ($faq as $item)
                    <div class="py-6">
                        <h3 class="font-display text-[1.25rem] text-boss-dark">{{ $item['question'] ?? '' }}</h3>
                        <p class="mt-3 text-[0.92rem] leading-relaxed text-boss-dark/62">{{ $item['answer'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-20 text-center text-boss-ivory">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Ready to start?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)]">{{ __('Apply to Paradise Dolls') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-ivory/55">{{ __('No experience is required. The team reviews applications privately and guides approved applicants through onboarding, setup, and training.') }}</p>
            <a href="{{ route('apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ __('Apply Now') }}</a>
        </div>
    </section>
</x-layouts.marketing>
