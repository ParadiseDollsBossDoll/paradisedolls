<x-layouts.marketing :transparentNav="true" :title="marketing_content($page.'.title')" :description="marketing_content($page.'.description')">
    <main class="min-h-screen bg-white text-boss-dark">
        <section class="pd-marketing-image-hero relative flex min-h-[72vh] items-end overflow-hidden bg-boss-dark pt-24">
            @if (marketing_content($page.'.hero.image') !== '')
                <img src="{{ marketing_image($page.'.hero.image') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-top" aria-hidden="true">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent" aria-hidden="true"></div>
            <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                @if (marketing_content($page.'.logo') !== '')
                    <img src="{{ marketing_image($page.'.logo') }}" alt="" class="mb-6 h-auto max-h-32 max-w-full object-contain">
                @endif
                @if (marketing_content($page.'.hero.eyebrow') !== '')
                    <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em]">{{ marketing_content($page.'.hero.eyebrow') }}</p>
                @endif
                @if (marketing_content($page.'.title') !== '')
                    <h1 class="pd-marketing-image-hero-title break-words font-display text-4xl leading-tight md:text-6xl">{{ marketing_content($page.'.title') }}</h1>
                @endif
                @foreach (marketing_paragraphs($page.'.hero.body') as $paragraph)
                    <p class="pd-marketing-image-hero-body mt-5 max-w-xl whitespace-pre-line text-base leading-relaxed">{{ $paragraph }}</p>
                @endforeach
            </div>
        </section>
        <div class="mx-auto max-w-7xl space-y-10 px-4 py-16 sm:px-6 md:py-24 lg:px-8">
            @if (marketing_content($page.'.intro.title') !== '')
                <h2 class="break-words font-display text-3xl md:text-4xl">{{ marketing_content($page.'.intro.title') }}</h2>
            @endif
            @foreach (marketing_paragraphs($page.'.body') as $paragraph)
                <p class="max-w-3xl whitespace-pre-line leading-relaxed">{{ $paragraph }}</p>
            @endforeach
            @foreach (marketing_items($page.'.sections') as $section)
                <section @class(['grid items-start gap-10 border-t border-boss-pink/10 py-12 lg:gap-16', 'md:grid-cols-2' => !empty($section['image'])])>
                    <div class="space-y-4">
                        @if (!empty($section['title']))
                            <h2 class="break-words font-display text-3xl md:text-4xl">{{ $section['title'] }}</h2>
                        @endif
                        @if (!empty($section['body']))
                            <p class="whitespace-pre-line leading-relaxed">{{ $section['body'] }}</p>
                        @endif
                    </div>
                    @if (!empty($section['image']))
                        <img src="{{ \App\Support\MarketingContent::imageUrl($section['image']) }}" alt="" class="h-auto w-full">
                    @endif
                </section>
            @endforeach
        </div>
    </main>
</x-layouts.marketing>
