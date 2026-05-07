@php
    $img = 'https://images.unsplash.com/photo-1679931992295-a8d77544a807?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800';
@endphp
<x-layouts.marketing :title="__('Our Story')">
    <section class="relative h-[70vh] flex items-end overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center bg-top" style="background-image: url('{{ $img }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 w-full">
            <p class="text-boss-gold tracking-[0.4em] uppercase mb-4 text-[0.7rem]">{{ __('About Us') }}</p>
            <h1 class="text-white font-display text-[clamp(3rem,7vw,5.5rem)] leading-tight">{{ __('Our Story') }}</h1>
        </div>
    </section>

    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <p class="text-boss-gold tracking-[0.3em] uppercase mb-4 text-[0.7rem]">{{ __('The Foundation') }}</p>
                <h2 class="font-display text-[clamp(1.8rem,3vw,2.5rem)] text-boss-dark leading-snug mb-6">{{ __('From ambition to agency') }}</h2>
                <p class="text-boss-dark/65 leading-relaxed text-[0.95rem]">{{ __("Boss Doll began with one model's vision: professional training, real community, and income paths that fit real life. Today we are a global network helping members stream smarter, travel freely, and build brands they own.") }}</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach ([
                    ['2018', __('The Beginning'), __('Started with ambition and the belief that performers deserve more.')],
                    ['2020', __('Going Digital'), __('Scaled audiences online - earning from anywhere.')],
                    ['2022', __('Building the Agency'), __('Formal training, mentorship, and the Boss Doll blueprint.')],
                    ['2024', __('Global Vision'), __('International growth and a worldwide sisterhood.')],
                ] as $t)
                    <div class="border border-boss-pink/40 p-6 bg-boss-muted/50">
                        <p class="text-boss-gold font-display text-2xl mb-2">{{ $t[0] }}</p>
                        <p class="font-display text-boss-dark mb-2">{{ $t[1] }}</p>
                        <p class="text-boss-dark/60 text-sm leading-relaxed">{{ $t[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.marketing>
