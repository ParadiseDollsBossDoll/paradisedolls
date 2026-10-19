<x-layouts.marketing :title="marketing_content($page.'.title')" :description="marketing_content($page.'.description')">
    <main class="min-h-screen bg-white pt-24 text-boss-dark md:pt-28">
        @if (marketing_content($page.'.hero.image') !== '')
            <img src="{{ marketing_image($page.'.hero.image') }}" alt="" class="block h-auto w-full">
        @endif
        <div class="mx-auto max-w-7xl space-y-10 px-4 py-12 sm:px-6 lg:px-8">
            @if (marketing_content($page.'.logo') !== '')
                <img src="{{ marketing_image($page.'.logo') }}" alt="" class="h-auto max-h-32 max-w-full object-contain">
            @endif
            @if (marketing_content($page.'.title') !== '')
                <h1 class="text-3xl font-bold md:text-4xl">{{ marketing_content($page.'.title') }}</h1>
            @endif
            @foreach (marketing_paragraphs($page.'.body') as $paragraph)
                <p class="max-w-3xl whitespace-pre-line leading-relaxed">{{ $paragraph }}</p>
            @endforeach
            @foreach (marketing_items($page.'.sections') as $section)
                <section class="grid items-start gap-8 border-t border-boss-pink/20 py-10 md:grid-cols-2">
                    @if (!empty($section['image']))
                        <img src="{{ \App\Support\MarketingContent::imageUrl($section['image']) }}" alt="" class="h-auto w-full">
                    @endif
                    <div class="space-y-4">
                        @if (!empty($section['title']))
                            <h2 class="text-2xl font-bold">{{ $section['title'] }}</h2>
                        @endif
                        @if (!empty($section['body']))
                            <p class="whitespace-pre-line leading-relaxed">{{ $section['body'] }}</p>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </main>
</x-layouts.marketing>
