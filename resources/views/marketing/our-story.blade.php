@php
    $heroImg = marketing_image('our_story.hero.image');
    $storyImg = marketing_image('our_story.why.image');
    $myStoryImages = marketing_items('our_story.story.images');
    $missionCards = marketing_items('our_story.mission.cards');
    $timelineCards = marketing_items('our_story.timeline.cards');
@endphp
<x-layouts.marketing :transparentNav="true" :title="marketing_content('our_story.hero.title')">
    <section class="relative flex min-h-[72vh] items-end overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center bg-top" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>
        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.38em] text-boss-gold">{{ marketing_content('our_story.hero.eyebrow') }}</p>
            <h1 class="font-display text-[clamp(3rem,7vw,5.5rem)] leading-tight text-white">{{ marketing_content('our_story.hero.title') }}</h1>
            <p class="mt-5 max-w-xl text-[1rem] leading-relaxed text-white/72">{{ marketing_content('our_story.hero.body') }}</p>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-[1fr_1fr] lg:items-start">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('our_story.why.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('our_story.why.title') }}</h2>
                    <div class="mt-8 space-y-6 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        @foreach (marketing_paragraphs('our_story.why.body') as $paragraph)
                            <p @class(['font-medium text-boss-dark' => $loop->last])>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>
                <div class="relative">
                    <div class="aspect-[4/5] overflow-hidden">
                        <img src="{{ $storyImg }}" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="absolute -bottom-7 -right-4 bg-boss-rose px-7 py-5 shadow-luxe sm:-right-7">
                        <p class="font-display text-[1.8rem] leading-none text-white">{{ marketing_content('our_story.why.badge_number') }}</p>
                        <p class="mt-1 text-[0.62rem] uppercase tracking-[0.16em] text-white/80">{{ marketing_content('our_story.why.badge_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('our_story.story.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(1.8rem,3.5vw,2.75rem)] leading-tight text-boss-dark">{{ marketing_content('our_story.story.title') }}</h2>
                    <div class="mt-10 space-y-6 text-[0.95rem] leading-relaxed text-boss-dark/70">
                        @foreach (marketing_paragraphs('our_story.story.body') as $paragraph)
                            <p @class(['font-medium text-boss-dark text-[1rem]' => $loop->iteration === 7])>{{ $paragraph }}</p>
                        @endforeach
                        <p>
                            <a href="{{ marketing_link('our_story.story.link_url', '#') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-boss-rose underline decoration-boss-rose/40 underline-offset-4 transition-colors hover:text-boss-dark">
                                {{ marketing_content('our_story.story.link_label') }}
                            </a>
                        </p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:sticky lg:top-28">
                    @foreach ($myStoryImages as $image)
                        <div @class(['overflow-hidden', 'aspect-[4/5] sm:col-span-2' => $loop->first, 'aspect-[4/3]' => ! $loop->first])>
                            <img src="{{ \App\Support\MarketingContent::imageUrl($image['image'] ?? '') }}" alt="{{ $image['alt'] ?? '' }}" class="h-full w-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-24 text-boss-ivory">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('our_story.mission.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(1.9rem,3.5vw,3rem)] leading-tight">{{ marketing_content('our_story.mission.title') }}</h2>
                    @foreach (marketing_paragraphs('our_story.mission.body') as $paragraph)
                        <p @class([
                            'mt-7 text-[1rem] font-medium text-boss-ivory' => $loop->last,
                            'mt-7 text-[0.95rem] leading-relaxed text-boss-ivory/60' => $loop->first,
                            'mt-5 text-[0.95rem] leading-relaxed text-boss-ivory/60' => ! $loop->first && ! $loop->last,
                        ])>{{ $paragraph }}</p>
                    @endforeach
                    <a href="{{ marketing_link('our_story.mission.cta_url', route('home').'#apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('our_story.mission.cta_label') }}</a>
                </div>
                <div class="grid gap-4">
                    @foreach ($missionCards as $item)
                        <div class="border border-white/[0.07] bg-white/[0.03] p-5">
                            <h3 class="font-display text-[1.1rem] text-boss-gold-light">{{ $item['title'] ?? '' }}</h3>
                            <p class="mt-2 text-[0.84rem] leading-relaxed text-boss-ivory/45">{{ $item['body'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('our_story.timeline.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(1.8rem,3vw,2.5rem)] text-boss-dark">{{ marketing_content('our_story.timeline.title') }}</h2>
            </div>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-5">
                @foreach ($timelineCards as $t)
                    <div class="border border-boss-pink/40 bg-boss-muted p-6">
                        <p class="font-display text-[2rem] leading-none text-boss-rose">{{ $t['year'] ?? '' }}</p>
                        <h3 class="mt-3 font-display text-[1.1rem] text-boss-dark">{{ $t['title'] ?? '' }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/60">{{ $t['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mx-auto mt-14 max-w-3xl space-y-5 text-center text-[0.95rem] leading-relaxed text-boss-dark/65">
                @foreach (marketing_paragraphs('our_story.timeline.closing') as $paragraph)
                    <p @class(['font-medium text-boss-dark' => $loop->last])>{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-pink py-20 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('our_story.cta.eyebrow') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ marketing_content('our_story.cta.title') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-dark/65">{{ marketing_content('our_story.cta.body') }}</p>
            <a href="{{ marketing_link('our_story.cta.url', route('home').'#apply') }}" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('our_story.cta.label') }}</a>
        </div>
    </section>
</x-layouts.marketing>
