@php($hero = 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&q=85&w=1920')
<x-layouts.marketing :title="__('Work From Home')">
    <section class="relative flex min-h-[60vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/52"></div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.4em] text-boss-gold">{{ __('Remote Income') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,6vw,4.5rem)] leading-tight">{{ __('Work From Home') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1rem] leading-relaxed text-white/80">{{ __('Turn your own space into a polished online workspace with setup guidance, preparation, platform walkthroughs, and flexible schedules.') }}</p>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
            @foreach ([
                [__('Private setup'), __('Lighting, camera, audio, framing, and environment checklists help your home setup feel professional.')],
                [__('Flexible schedule'), __('Build around your lifestyle while learning consistency, boundaries, and sustainable routines.')],
                [__('Guided systems'), __('The team handles account preparation while the academy teaches platform use and live workflow.')],
            ] as $item)
                <div class="border border-boss-pink/60 bg-boss-muted p-7">
                    <h2 class="font-display text-[1.35rem] text-boss-dark">{{ $item[0] }}</h2>
                    <p class="mt-3 text-[0.88rem] leading-relaxed text-boss-dark/60">{{ $item[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.marketing>
