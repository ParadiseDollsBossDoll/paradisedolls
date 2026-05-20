@php
    $heroImg = marketing_image('work_from_home.hero.image');
    $setupImg = marketing_image('work_from_home.main.image');
    $flexImg = marketing_image('work_from_home.technical.image');
    $benefits = marketing_items('work_from_home.main.benefits');
    $teamItems = marketing_items('work_from_home.team.items');
    $technicalCards = marketing_items('work_from_home.technical.cards');
@endphp
<x-layouts.marketing :transparentNav="true" :title="marketing_content('work_from_home.hero.title')">
    <section class="relative flex min-h-[62vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('work_from_home.hero.eyebrow') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,7vw,5rem)] leading-tight">{{ marketing_content('work_from_home.hero.title') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1.05rem] leading-relaxed text-white/80">{{ marketing_content('work_from_home.hero.body') }}</p>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('work_from_home.main.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('work_from_home.main.title') }}</h2>
                    <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ marketing_content('work_from_home.main.body') }}</p>

                    <div class="mt-10 space-y-4">
                        @foreach ($benefits as $benefit)
                            <div class="flex gap-5 border border-boss-pink/50 bg-boss-muted p-5">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-boss-rose"></span>
                                <div>
                                    <p class="font-medium text-boss-dark text-[0.9rem]">{{ $benefit['title'] ?? '' }}</p>
                                    <p class="mt-1 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $benefit['body'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-5 lg:pt-10">
                    <div class="overflow-hidden shadow-luxe">
                        <img src="{{ $setupImg }}" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="border border-boss-pink/40 bg-boss-muted p-6">
                        <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-rose">{{ marketing_content('work_from_home.team.title') }}</p>
                        <ul class="space-y-3 text-[0.86rem] text-boss-dark/65">
                            @foreach ($teamItems as $item)
                                <li class="flex items-center gap-3">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-boss-rose"></span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('work_from_home.technical.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('work_from_home.technical.title') }}</h2>
                    <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ marketing_content('work_from_home.technical.body') }}</p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        @foreach ($technicalCards as $item)
                            <div class="bg-white p-5 shadow-luxe">
                                <h3 class="font-display text-[1.1rem] text-boss-dark">{{ $item['title'] ?? '' }}</h3>
                                <p class="mt-2 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item['body'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-hidden shadow-luxe">
                    <img src="{{ $flexImg }}" alt="" class="h-full w-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-20 text-center text-boss-ivory">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('work_from_home.cta.eyebrow') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)]">{{ marketing_content('work_from_home.cta.title') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-ivory/55">{{ marketing_content('work_from_home.cta.body') }}</p>
            <a href="{{ marketing_link('work_from_home.cta.url', route('home').'#apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('work_from_home.cta.label') }}</a>
        </div>
    </section>
</x-layouts.marketing>
