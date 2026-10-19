@php($hero = marketing_image('success_stories.hero.image'))
<x-layouts.marketing :transparentNav="true" :title="marketing_content('success_stories.hero.title')">
    <section class="pd-marketing-image-hero relative flex min-h-[68vh] items-center justify-center overflow-hidden pb-14 pt-28">
        <img src="{{ $hero }}" alt="" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="relative z-10 mx-auto max-w-5xl px-4 text-center text-white">
            <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ marketing_content('success_stories.hero.eyebrow') }}</p>
            <h1 class="pd-marketing-image-hero-title font-display text-[clamp(2.15rem,5vw,4rem)] leading-tight text-[#FFFFFF]">{{ marketing_content('success_stories.hero.title') }}</h1>
            <div class="pd-marketing-image-hero-body mx-auto mt-5 max-w-3xl space-y-3 text-[0.78rem] leading-relaxed text-[#FFFFFF]/90 sm:text-[0.84rem]">
                @foreach (marketing_paragraphs('success_stories.hero.body') as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#f3f3f5] py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Success Stories') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('success_stories.testimonials.title') }}</h2>
            </div>

            @if (collect(marketing_items('success_stories.review_photos'))->contains(fn ($photo) => !empty($photo['image'])))
                <x-review-photo-gallery />
            @else
                <div class="rounded-lg bg-white px-6 py-16 text-center shadow-[0_18px_45px_rgba(15,15,20,0.08)]">
                    <p class="font-display text-[1.5rem] text-boss-dark">{{ marketing_content('success_stories.empty.title') }}</p>
                    <p class="mx-auto mt-3 max-w-lg text-[0.9rem] leading-relaxed text-boss-dark/58">{{ marketing_content('success_stories.empty.body') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.marketing>
