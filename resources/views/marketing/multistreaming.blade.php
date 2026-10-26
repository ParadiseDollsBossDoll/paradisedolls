@php
    $heroImg = marketing_image('multistreaming.hero.image');
    $differentCards = marketing_items('multistreaming.different.cards');
    $strategyCards = marketing_items('multistreaming.strategy.cards');
    $builtCards = marketing_items('multistreaming.built.cards');
@endphp
<x-layouts.marketing :transparentNav="true" :title="marketing_content('multistreaming.hero.title')">
    <section class="pd-marketing-image-hero relative flex min-h-[60vh] items-center justify-center overflow-hidden pt-24">
        <img src="{{ $heroImg }}" alt="" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ marketing_content('multistreaming.hero.eyebrow') }}</p>
            <h1 class="pd-marketing-image-hero-title font-display text-[clamp(2.7rem,7vw,5rem)] leading-tight text-[#FFFFFF]">{{ marketing_content('multistreaming.hero.title') }}</h1>
            <p class="pd-marketing-image-hero-body mx-auto mt-6 max-w-2xl text-[1.05rem] leading-relaxed text-[#FFFFFF]/90">{{ marketing_content('multistreaming.hero.body') }}</p>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('multistreaming.different.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('multistreaming.different.title') }}</h2>
                    <div class="mt-8 space-y-5 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        @foreach (marketing_paragraphs('multistreaming.different.body') as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <a href="{{ marketing_link('multistreaming.different.cta_url', route('home').'#apply') }}" class="pd-cta-primary mt-10 px-10 py-3.5">{{ marketing_content('multistreaming.different.cta_label') }}</a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
                    @foreach ($differentCards as $item)
                        <div class="border border-boss-pink/60 bg-boss-muted p-5">
                            <p class="font-display text-[1.1rem] text-boss-dark">{{ $item['title'] ?? '' }}</p>
                            <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item['body'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('multistreaming.change.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('multistreaming.change.title') }}</h2>
                    <div class="mt-8 space-y-5 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        @foreach (marketing_paragraphs('multistreaming.change.body') as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>
                <div class="border border-boss-pink/40 bg-boss-muted p-8">
                    <p class="mb-6 text-[0.7rem] uppercase tracking-[0.2em] text-boss-rose">{{ marketing_content('multistreaming.strategy.eyebrow') }}</p>
                    <div class="space-y-5">
                        @foreach ($strategyCards as $step)
                            <div class="grid grid-cols-[2.75rem_minmax(0,1fr)] gap-4">
                                <span class="pd-strategy-index">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <div>
                                    <p class="font-medium text-boss-dark text-[0.9rem]">{{ $step['title'] ?? '' }}</p>
                                    <p class="mt-1 text-[0.86rem] leading-relaxed text-boss-dark/60">{{ $step['body'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-24 text-boss-ivory">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('multistreaming.built.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ marketing_content('multistreaming.built.title') }}</h2>
                <p class="mx-auto mt-5 max-w-2xl text-[0.95rem] leading-relaxed text-boss-ivory/55">{{ marketing_content('multistreaming.built.body') }}</p>
            </div>
            <div class="mx-auto grid max-w-4xl gap-4 sm:grid-cols-3">
                @foreach ($builtCards as $item)
                    <div class="border border-white/[0.07] bg-white/[0.03] p-6">
                        <h3 class="font-display text-[1.1rem] text-boss-gold-light">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-ivory/45">{{ $item['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-pink py-20 text-center">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('multistreaming.cta.eyebrow') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ marketing_content('multistreaming.cta.title') }}</h2>
            <a href="{{ marketing_link('multistreaming.cta.url', route('home').'#apply') }}" class="pd-cta-primary mt-10 px-12 py-4">{{ marketing_content('multistreaming.cta.label') }}</a>
        </div>
    </section>
</x-layouts.marketing>
