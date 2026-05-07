@php($hero = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=85&w=1920')
<x-layouts.marketing :title="__('Success Stories')">
    <section class="relative flex min-h-[58vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Community Wins') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,6vw,4.5rem)] leading-tight">{{ __('Success Stories') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1rem] leading-relaxed text-white/78">{{ __('Real stories, confidence shifts, and lifestyle wins from the Paradise Dolls community.') }}</p>
        </div>
    </section>

    <section class="bg-boss-muted py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 lg:grid-cols-3">
                @forelse ($testimonials as $testimonial)
                    <article class="overflow-hidden bg-white shadow-luxe">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="{{ $testimonial->displayImage() }}" alt="" class="h-full w-full object-cover">
                        </div>
                        <div class="p-6">
                            @if ($testimonial->result_label)
                                <p class="mb-3 text-[0.66rem] uppercase tracking-[0.18em] text-boss-gold">{{ $testimonial->result_label }}</p>
                            @endif
                            <h2 class="font-display text-[1.35rem] text-boss-dark">{{ $testimonial->headline }}</h2>
                            <p class="mt-4 text-[0.9rem] leading-relaxed text-boss-dark/62">{{ $testimonial->quote }}</p>
                            <p class="mt-5 text-[0.76rem] uppercase tracking-[0.14em] text-boss-dark/42">{{ $testimonial->name }}{{ $testimonial->location ? ' - '.$testimonial->location : '' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-white px-6 py-16 text-center shadow-luxe">
                        <p class="font-display text-[1.5rem] text-boss-dark">{{ __('Success stories are coming soon') }}</p>
                        <p class="mx-auto mt-3 max-w-lg text-[0.9rem] leading-relaxed text-boss-dark/58">{{ __('The team can add approved member testimonials from the admin dashboard as the community grows.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.marketing>
