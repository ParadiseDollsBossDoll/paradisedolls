@props([
    'routeName',
    'title',
    'description',
    'keywords' => [],
    'eyebrow' => 'Paradise Dolls guide',
    'heading',
    'lead',
    'heroImage',
    'heroAlt',
    'answer',
    'facts' => [],
    'sections' => [],
    'faqs' => [],
    'related' => [],
    'ctaTitle' => 'Decide whether Paradise Dolls is right for you',
    'ctaBody' => 'Applications are private, reviewed individually, and open only to adults aged 18 or older. Applying does not guarantee acceptance or earnings.',
])
@php
    $structuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => route('home').'#organization',
            'name' => 'Paradise Dolls',
            'url' => route('home'),
            'logo' => asset('images/brand/get-rich-with-paradise-dolls-logo.png'),
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => route($routeName).'#webpage',
            'url' => route($routeName),
            'name' => $title.' | Paradise Dolls',
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Paradise Dolls',
                'url' => route('home'),
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Guides', 'item' => route('guides')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => route($routeName)],
            ],
        ],
    ];

    if ($faqs !== []) {
        $structuredData[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ])->all(),
        ];
    }
@endphp

<x-layouts.marketing
    :transparentNav="true"
    :title="$title"
    :description="$description"
    :keywords="$keywords"
    :image="$heroImage"
    :structuredData="$structuredData"
>
    <section class="pd-marketing-image-hero relative flex min-h-[clamp(620px,78svh,760px)] items-end overflow-hidden pb-16 pt-28 sm:pb-20 sm:pt-32">
        <img src="{{ $heroImage }}" alt="{{ $heroAlt }}" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-black/40 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl text-white">
                <nav aria-label="Breadcrumb" class="mb-6 flex flex-wrap items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-white/70">
                    <a href="{{ route('guides') }}" class="transition-colors hover:text-boss-gold-light">{{ __('Guides') }}</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">{{ __($title) }}</span>
                </nav>
                <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ __($eyebrow) }}</p>
                <h1 class="pd-marketing-image-hero-title max-w-4xl break-words font-display text-[clamp(2.3rem,7vw,5.35rem)] leading-[0.98] text-[#FFF8F6]">{{ __($heading) }}</h1>
                <p class="pd-marketing-image-hero-body mt-7 max-w-3xl text-[1.02rem] leading-8 text-[#FFF8F6]/90">{{ __($lead) }}</p>
                <a href="#guide-start" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-md border border-white/45 px-7 py-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ __('Read the guide') }}</a>
            </div>
        </div>
    </section>

    <section id="guide-start" class="scroll-mt-24 border-b border-boss-pink/50 bg-white">
        <div class="mx-auto grid max-w-7xl lg:grid-cols-[1.1fr_0.9fr]">
            <div class="px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-boss-rose">{{ __('The short answer') }}</p>
                <p class="mt-4 max-w-3xl font-display text-[clamp(1.45rem,2.5vw,2.1rem)] leading-snug text-boss-dark">{{ __($answer) }}</p>
            </div>
            <dl class="grid grid-cols-2 border-t border-boss-pink/50 bg-boss-muted lg:border-l lg:border-t-0">
                @foreach ($facts as $fact)
                    <div class="border-b border-r border-boss-pink/50 px-5 py-7 last:border-b-0 sm:px-6 lg:[&:nth-last-child(-n+2)]:border-b-0">
                        <dt class="text-[0.6rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __($fact['label']) }}</dt>
                        <dd class="mt-2 font-display text-[1.1rem] leading-snug text-boss-dark">{{ __($fact['value']) }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    @foreach ($sections as $section)
        <section id="{{ $section['id'] }}" @class(['scroll-mt-24 py-20 sm:py-24', 'bg-white' => $loop->odd, 'bg-boss-cream' => $loop->even])>
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.78fr_1.22fr] lg:gap-20 lg:px-8">
                <header>
                    <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-boss-rose">{{ __($section['eyebrow']) }}</p>
                    <h2 class="font-display text-[clamp(1.9rem,4vw,3rem)] leading-tight text-boss-dark">{{ __($section['title']) }}</h2>
                </header>

                <div>
                    @if (filled($section['intro'] ?? null))
                        <p class="text-[1rem] leading-8 text-boss-dark/72">{{ __($section['intro']) }}</p>
                    @endif

                    @if (($section['paragraphs'] ?? []) !== [])
                        <div class="mt-6 space-y-5 text-[0.94rem] leading-8 text-boss-dark/68">
                            @foreach ($section['paragraphs'] as $paragraph)
                                <p>{{ __($paragraph) }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (($section['items'] ?? []) !== [])
                        <ul class="mt-8 divide-y divide-boss-pink/60 border-y border-boss-pink/60">
                            @foreach ($section['items'] as $item)
                                <li class="flex gap-4 py-5">
                                    <svg class="mt-1 h-4 w-4 shrink-0 text-boss-rose" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path d="m5 12 4 4L19 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span>
                                        @if (is_array($item))
                                            <strong class="block text-[0.92rem] font-semibold text-boss-dark">{{ __($item['title']) }}</strong>
                                            <span class="mt-1 block text-[0.86rem] leading-6 text-boss-dark/62">{{ __($item['body']) }}</span>
                                        @else
                                            <span class="text-[0.9rem] leading-6 text-boss-dark/68">{{ __($item) }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if (filled($section['note'] ?? null))
                        <aside class="mt-8 border-l-2 border-boss-gold bg-white/70 px-6 py-5 text-[0.88rem] leading-7 text-boss-dark/68">
                            <strong class="block font-semibold text-boss-dark">{{ __($section['note_title'] ?? 'Important') }}</strong>
                            <span class="mt-1 block">{{ __($section['note']) }}</span>
                        </aside>
                    @endif
                </div>
            </div>
        </section>
    @endforeach

    @if ($faqs !== [])
        <section class="bg-white py-20 sm:py-24">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-boss-rose">{{ __('Frequently asked questions') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Clear answers before you make a decision') }}</h2>
                <div class="mt-10 divide-y divide-boss-pink/60 border-y border-boss-pink/60">
                    @foreach ($faqs as $faq)
                        <article class="grid gap-3 py-7 md:grid-cols-[0.8fr_1.2fr] md:gap-10">
                            <h3 class="font-display text-[1.22rem] leading-snug text-boss-dark">{{ __($faq['question']) }}</h3>
                            <p class="text-[0.9rem] leading-7 text-boss-dark/65">{{ __($faq['answer']) }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($related !== [])
        <section class="bg-boss-dark py-20 text-boss-ivory">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.72fr_1.28fr] lg:gap-20 lg:px-8">
                <div>
                    <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-boss-gold">{{ __('Continue learning') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Related Paradise Dolls guides') }}</h2>
                </div>
                <nav aria-label="Related guides" class="divide-y divide-white/10 border-y border-white/10">
                    @foreach ($related as $link)
                        <a href="{{ route($link['route']) }}" class="group flex min-h-20 items-center justify-between gap-6 py-4 transition-colors hover:text-boss-gold">
                            <span>
                                <span class="block font-display text-[1.2rem]">{{ __($link['label']) }}</span>
                                <span class="mt-1 block text-[0.78rem] leading-5 text-boss-ivory/50">{{ __($link['detail']) }}</span>
                            </span>
                            <svg class="h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path d="M5 12h14m-5-5 5 5-5 5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endforeach
                </nav>
            </div>
        </section>
    @endif

    <section class="bg-boss-pink py-16 text-center sm:py-20">
        <div class="mx-auto max-w-2xl px-4 sm:px-6">
            <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-boss-dark/55">{{ __('Ready for the next step?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __($ctaTitle) }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.94rem] leading-7 text-boss-dark/68">{{ __($ctaBody) }}</p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('apply') }}" class="inline-flex min-h-12 items-center justify-center rounded-md bg-boss-dark px-9 py-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:bg-black">{{ __('Apply privately') }}</a>
                <a href="{{ route('webcam-jobs') }}" class="inline-flex min-h-12 items-center justify-center rounded-md border border-boss-dark/30 px-9 py-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-boss-dark transition-colors hover:border-boss-dark hover:bg-white">{{ __('View webcam jobs') }}</a>
            </div>
        </div>
    </section>
</x-layouts.marketing>
