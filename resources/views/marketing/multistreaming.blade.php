@php
    $heroImg = marketing_image('multistreaming.hero.image');
    $differentCards = marketing_items('multistreaming.different.cards');
    $strategyCards = marketing_items('multistreaming.strategy.cards');
    $builtCards = marketing_items('multistreaming.built.cards');
@endphp
<x-layouts.marketing :transparentNav="true" :title="marketing_content('multistreaming.hero.title')">
    <section class="relative flex min-h-[60vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('multistreaming.hero.eyebrow') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,7vw,5rem)] leading-tight">{{ marketing_content('multistreaming.hero.title') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1.05rem] leading-relaxed text-white/80">{{ marketing_content('multistreaming.hero.body') }}</p>
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
                    <a href="{{ marketing_link('multistreaming.different.cta_url', route('home').'#apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('multistreaming.different.cta_label') }}</a>
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

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('multistreaming.industry.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('multistreaming.industry.title') }}</h2>
                <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ marketing_content('multistreaming.industry.body') }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white p-8 shadow-luxe">
                    <div class="mb-6 inline-block bg-boss-pink px-4 py-1.5">
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-boss-dark">{{ marketing_content('multistreaming.freemium.label') }}</p>
                    </div>
                    <h3 class="font-display text-[1.45rem] text-boss-dark">{{ marketing_content('multistreaming.freemium.title') }}</h3>
                    <p class="mt-4 text-[0.9rem] leading-relaxed text-boss-dark/62">{{ marketing_content('multistreaming.freemium.body') }}</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-rose">{{ marketing_content('multistreaming.freemium.platform_label') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-dark/65">
                                @foreach (marketing_items('multistreaming.freemium.platforms') as $p)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-rose shrink-0"></span>{{ $p }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-rose">{{ marketing_content('multistreaming.freemium.best_label') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-dark/65">
                                @foreach (marketing_items('multistreaming.freemium.best_for') as $b)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-rose shrink-0"></span>{{ $b }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-boss-dark p-8 shadow-luxe text-boss-ivory">
                    <div class="mb-6 inline-block bg-boss-gold/20 border border-boss-gold/30 px-4 py-1.5">
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-boss-gold">{{ marketing_content('multistreaming.premium.label') }}</p>
                    </div>
                    <h3 class="font-display text-[1.45rem] text-boss-ivory">{{ marketing_content('multistreaming.premium.title') }}</h3>
                    <p class="mt-4 text-[0.9rem] leading-relaxed text-boss-ivory/55">{{ marketing_content('multistreaming.premium.body') }}</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ marketing_content('multistreaming.premium.platform_label') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-ivory/60">
                                @foreach (marketing_items('multistreaming.premium.platforms') as $p)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-gold shrink-0"></span>{{ $p }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ marketing_content('multistreaming.premium.best_label') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-ivory/60">
                                @foreach (marketing_items('multistreaming.premium.best_for') as $b)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-gold shrink-0"></span>{{ $b }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
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
                            <div class="flex gap-5">
                                <span class="font-display text-[2rem] leading-none text-boss-pink shrink-0">{{ sprintf('%02d', $loop->iteration) }}</span>
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
            <a href="{{ marketing_link('multistreaming.cta.url', route('home').'#apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('multistreaming.cta.label') }}</a>
        </div>
    </section>
</x-layouts.marketing>
