@php($hero = asset('images/15.jpeg'))
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

    <section class="bg-[#f3f3f5] py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Community Testimonials') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Real words from approved Paradise Dolls members') }}</h2>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($testimonials as $testimonial)
                    <article class="min-h-[17.5rem] rounded-lg bg-white p-5 shadow-[0_18px_45px_rgba(15,15,20,0.08)]">
                        <div class="flex items-center gap-3">
                            <img src="{{ $testimonial->displayAvatar() }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover">
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-[0.86rem] font-semibold leading-tight text-[#06070b]">{{ $testimonial->name }}</h3>
                                <p class="mt-1 truncate text-[0.78rem] leading-tight text-[#6f7280]">{{ $testimonial->displayHandle() }}</p>
                            </div>
                        </div>

                        <p class="mt-6 text-[0.96rem] leading-[1.45] text-[#151821]">{{ $testimonial->quote }}</p>

                        @if ($testimonial->displayHashtag())
                            <p class="mt-3 truncate text-[0.9rem] leading-tight text-[#1d9bf0]">{{ $testimonial->displayHashtag() }}</p>
                        @endif
                    </article>
                @empty
                    <div class="col-span-full rounded-lg bg-white px-6 py-16 text-center shadow-[0_18px_45px_rgba(15,15,20,0.08)]">
                        <p class="font-display text-[1.5rem] text-boss-dark">{{ __('Success stories are coming soon') }}</p>
                        <p class="mx-auto mt-3 max-w-lg text-[0.9rem] leading-relaxed text-boss-dark/58">{{ __('The team can add approved member testimonials from the admin dashboard as the community grows.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.marketing>
