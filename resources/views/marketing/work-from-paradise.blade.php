@php
    $heroImg = marketing_image('work_from_paradise.hero.image');
    $studioImg = marketing_image('work_from_paradise.studio.image');
    $communityImg = marketing_image('work_from_paradise.community.image');
    $beachImg = marketing_image('work_from_paradise.lifestyle.image');
    $studioCards = marketing_items('work_from_paradise.studio.cards');
    $communityItems = marketing_items('work_from_paradise.community.items');
    $securityCards = marketing_items('work_from_paradise.security.cards');
@endphp
<x-layouts.marketing :transparentNav="true" :title="marketing_content('work_from_paradise.hero.title')">
    <section class="pd-marketing-image-hero relative flex min-h-[72vh] items-center justify-center overflow-hidden pt-24">
        <img src="{{ $heroImg }}" alt="" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/45 to-boss-dark/90"></div>
        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ marketing_content('work_from_paradise.hero.eyebrow') }}</p>
            <h1 class="pd-marketing-image-hero-title font-display text-[clamp(2.8rem,7vw,5.5rem)] leading-tight text-[#FFF8F6]">{{ marketing_content('work_from_paradise.hero.title') }}</h1>
            <p class="pd-marketing-image-hero-body mx-auto mt-6 max-w-2xl text-[1.05rem] leading-relaxed text-[#FFF8F6]/90">{{ marketing_content('work_from_paradise.hero.body') }}</p>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <a href="{{ marketing_link('work_from_paradise.hero.primary_url', route('home').'#apply') }}" class="rounded-md bg-[#EEB4C3] px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('work_from_paradise.hero.primary_label') }}</a>
                <a href="{{ marketing_link('work_from_paradise.hero.secondary_url', route('our-story')) }}" class="rounded-md border border-white/40 px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white/10">{{ marketing_content('work_from_paradise.hero.secondary_label') }}</a>
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[1fr_1fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('work_from_paradise.lifestyle.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('work_from_paradise.lifestyle.title') }}</h2>
                    <div class="mt-8 space-y-6 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        @foreach (marketing_paragraphs('work_from_paradise.lifestyle.body') as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-3">
                        @foreach (marketing_items('work_from_paradise.lifestyle.items') as $item)
                            <div class="flex items-center gap-3 border border-boss-pink/60 bg-boss-muted px-4 py-3">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-boss-rose"></span>
                                <span class="text-[0.84rem] text-boss-dark/70">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="relative">
                    <div class="aspect-[4/5] overflow-hidden">
                        <img src="{{ $beachImg }}" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="absolute -bottom-6 -left-5 bg-boss-pink px-7 py-5 shadow-luxe">
                        <p class="font-display text-[1.7rem] leading-none text-boss-dark">{{ marketing_content('work_from_paradise.lifestyle.badge_title') }}</p>
                        <p class="mt-1 text-[0.62rem] uppercase tracking-[0.16em] text-boss-dark/55">{{ marketing_content('work_from_paradise.lifestyle.badge_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div class="overflow-hidden shadow-luxe">
                    <img src="{{ $studioImg }}" alt="" class="h-full w-full object-cover">
                </div>
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('work_from_paradise.studio.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('work_from_paradise.studio.title') }}</h2>
                    <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ marketing_content('work_from_paradise.studio.body') }}</p>
                    <div class="mt-8 space-y-4">
                        @foreach ($studioCards as $item)
                            <div class="flex gap-5 border border-boss-pink/50 bg-white p-5">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-boss-rose"></span>
                                <div>
                                    <p class="font-medium text-boss-dark text-[0.9rem]">{{ $item['title'] ?? '' }}</p>
                                    <p class="mt-1 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item['body'] ?? '' }}</p>
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
            <div class="grid gap-12 lg:grid-cols-2">
                <div>
                    <div class="relative mb-8 overflow-hidden shadow-luxe">
                        <div class="aspect-[16/9]">
                            <img src="{{ $communityImg }}" alt="" class="h-full w-full object-cover opacity-80">
                        </div>
                    </div>
                    <p class="mb-3 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('work_from_paradise.community.eyebrow') }}</p>
                    <h3 class="font-display text-[clamp(1.5rem,3vw,2rem)] text-boss-ivory">{{ marketing_content('work_from_paradise.community.title') }}</h3>
                    <div class="mt-5 space-y-4 text-[0.9rem] leading-relaxed text-boss-ivory/55">
                        <p>{{ marketing_content('work_from_paradise.community.body') }}</p>
                        <ul class="mt-4 space-y-2">
                            @foreach ($communityItems as $item)
                                <li class="flex items-center gap-3">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-boss-gold"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="flex flex-col justify-start">
                    <p class="mb-3 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('work_from_paradise.security.eyebrow') }}</p>
                    <h3 class="font-display text-[clamp(1.5rem,3vw,2rem)] text-boss-ivory">{{ marketing_content('work_from_paradise.security.title') }}</h3>
                    <p class="mt-5 text-[0.9rem] leading-relaxed text-boss-ivory/55">{{ marketing_content('work_from_paradise.security.body') }}</p>
                    <div class="mt-8 grid gap-4">
                        @foreach ($securityCards as $item)
                            <div class="border border-white/[0.07] bg-white/[0.03] p-5">
                                <h4 class="font-display text-[1rem] text-boss-gold-light">{{ $item['title'] ?? '' }}</h4>
                                <p class="mt-2 text-[0.84rem] leading-relaxed text-boss-ivory/45">{{ $item['body'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-pink py-20 text-center">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('work_from_paradise.cta.eyebrow') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ marketing_content('work_from_paradise.cta.title') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-dark/65">{{ marketing_content('work_from_paradise.cta.body') }}</p>
            <a href="{{ marketing_link('work_from_paradise.cta.url', route('home').'#apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('work_from_paradise.cta.label') }}</a>
        </div>
    </section>
</x-layouts.marketing>
