@php
    $hero = 'https://images.unsplash.com/photo-1759417006128-d0317a0097f2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920';
@endphp
<x-layouts.marketing :title="__('Work From Paradise')">
    <section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/45"></div>
        <div class="relative z-10 text-center text-white px-4 max-w-3xl mx-auto">
            <p class="text-boss-gold tracking-[0.4em] uppercase mb-4 text-[0.7rem]">{{ __('Travel & streams') }}</p>
            <h1 class="font-display text-[clamp(2.5rem,6vw,4rem)] leading-tight">{{ __('Work From Paradise') }}</h1>
            <p class="mt-6 text-white/85 text-[1rem] leading-relaxed">{{ __('Portable rigs, timezone-friendly scheduling, and lifestyle planning so your office can be anywhere.') }}</p>
        </div>
    </section>
    <section class="py-20 bg-boss-cream">
        <div class="max-w-3xl mx-auto px-4 text-boss-dark/70 leading-relaxed space-y-6 text-[0.95rem]">
            <p>{{ __("We coordinate logistics recommendations, connectivity tips, and brand-safe content strategies when you're on the move.") }}</p>
            <a href="{{ route('home') }}#apply" class="inline-block bg-boss-dark text-white hover:bg-boss-gold px-10 py-3 tracking-[0.15em] uppercase text-[0.7rem] transition-colors">{{ __('Join us') }}</a>
        </div>
    </section>
</x-layouts.marketing>
