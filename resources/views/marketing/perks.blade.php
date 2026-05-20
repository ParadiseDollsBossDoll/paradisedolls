@php
    $heroImg = marketing_image('perks.hero.image');
    $rewardCards = marketing_items('perks.rewards.cards');
    $supportCards = marketing_items('perks.support.cards');
@endphp
<x-layouts.marketing :transparentNav="true" :title="marketing_content('perks.hero.title')">
    <section class="relative flex min-h-[62vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/50 to-boss-dark/90"></div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('perks.hero.eyebrow') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,6vw,5rem)] leading-tight">{{ marketing_content('perks.hero.title') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1rem] leading-relaxed text-white/80">{{ marketing_content('perks.hero.body') }}</p>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('perks.rewards.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('perks.rewards.title') }}</h2>
                <p class="mt-5 text-[0.95rem] leading-relaxed text-boss-dark/62">{{ marketing_content('perks.rewards.body') }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($rewardCards as $perk)
                    <article class="group overflow-hidden bg-boss-muted shadow-luxe">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="{{ \App\Support\MarketingContent::imageUrl($perk['image'] ?? '') }}" alt="" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                        <div class="p-6">
                            <p class="mb-2 text-[0.65rem] uppercase tracking-[0.18em] text-boss-rose">{{ config('app.name') }}</p>
                            <h3 class="font-display text-[1.25rem] text-boss-dark">{{ $perk['title'] ?? '' }}</h3>
                            <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $perk['body'] ?? '' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('perks.support.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(1.8rem,3vw,2.5rem)] text-boss-dark">{{ marketing_content('perks.support.title') }}</h2>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($supportCards as $item)
                    <div class="bg-white p-6 shadow-luxe">
                        <h3 class="font-display text-[1.2rem] text-boss-dark">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $item['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-20 text-center text-boss-ivory">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('perks.cta.eyebrow') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)]">{{ marketing_content('perks.cta.title') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-ivory/55">{{ marketing_content('perks.cta.body') }}</p>
            <a href="{{ marketing_link('perks.cta.url', route('home').'#apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('perks.cta.label') }}</a>
        </div>
    </section>
</x-layouts.marketing>
