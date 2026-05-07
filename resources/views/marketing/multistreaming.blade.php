@php
    $hero = 'https://images.unsplash.com/photo-1764664035133-0d2ca12016dd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920';
@endphp
<x-layouts.marketing :title="__('Multistreaming')">
    <section class="relative min-h-[50vh] flex items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="relative z-10 text-center text-white px-4 max-w-3xl mx-auto">
            <p class="text-boss-gold tracking-[0.3em] uppercase mb-4 text-[0.7rem]">{{ __('The stack') }}</p>
            <h1 class="font-display text-[clamp(2.5rem,6vw,4rem)]">{{ __('Multistreaming') }}</h1>
            <p class="mt-6 text-white/80">{{ __('One broadcast. Multiple platforms. Shared analytics mindset.') }}</p>
        </div>
    </section>
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 space-y-10">
            @foreach ([
                [__('Go live'), __('Use one guided setup tuned for your hardware.')],
                [__('Reach everywhere'), __('Syndicate to major platforms without doubling your workload.')],
                [__('Compound earnings'), __('Diversify revenue so no single channel defines your month.')],
            ] as $step)
                <div class="flex gap-6">
                    <div class="w-12 h-12 shrink-0 rounded-full bg-boss-pink flex items-center justify-center text-boss-gold font-display font-semibold">{{ $loop->iteration }}</div>
                    <div>
                        <h2 class="font-display text-xl text-boss-dark mb-2">{{ $step[0] }}</h2>
                        <p class="text-boss-dark/65 leading-relaxed">{{ $step[1] }}</p>
                    </div>
                </div>
            @endforeach
            <a href="{{ route('home') }}#apply" class="inline-block bg-boss-gold hover:bg-boss-gold-hover text-white px-10 py-3 tracking-[0.15em] uppercase text-[0.7rem]">{{ __('Get setup') }}</a>
        </div>
    </section>
</x-layouts.marketing>
