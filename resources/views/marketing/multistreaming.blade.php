@php($hero = 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&q=85&w=1920')
<x-layouts.marketing :title="__('Multistreaming')">
    <section class="relative flex min-h-[62vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/58"></div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('The Core System') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,6vw,4.5rem)] leading-tight">{{ __('Multistreaming') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1rem] leading-relaxed text-white/78">{{ __('A forward-thinking system built around simultaneous visibility, traffic, customer engagement, and diversified earnings across multiple platforms at once.') }}</p>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ([
                    [__('Broadcast smarter'), __('Use one prepared setup and one live workflow across multiple platform audiences.')],
                    [__('Multiply reach'), __('Increase traffic and visibility without rebuilding your schedule around every individual site.')],
                    [__('Learn the tools'), __('Walkthroughs cover controls, rankings, customer value, earnings systems, and platform features.')],
                ] as $step)
                    <div class="border border-boss-pink/60 bg-boss-muted p-7">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-full bg-boss-pink font-display text-boss-gold">{{ $loop->iteration }}</div>
                        <h2 class="font-display text-[1.35rem] text-boss-dark">{{ $step[0] }}</h2>
                        <p class="mt-3 text-[0.88rem] leading-relaxed text-boss-dark/60">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-14 bg-boss-dark p-7 text-boss-ivory md:p-10">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.25em] text-boss-gold">{{ __('Walkthrough focus') }}</p>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([__('Platform navigation'), __('Monetisation systems'), __('Customer retention'), __('Stream controls'), __('Rankings'), __('User value systems'), __('Earnings tools'), __('Customer interaction')] as $item)
                        <div class="border border-white/[0.07] bg-white/[0.035] px-4 py-3 text-[0.8rem] text-boss-ivory/55">{{ $item }}</div>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('home') }}#apply" class="mt-10 inline-block bg-boss-gold px-10 py-3 text-[0.7rem] uppercase tracking-[0.15em] text-white transition-colors hover:bg-boss-gold-hover">{{ __('Get setup') }}</a>
        </div>
    </section>
</x-layouts.marketing>
