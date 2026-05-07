@php
    $hero = 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920';
@endphp
<x-layouts.marketing :title="__('Work From Home')">
    <section class="relative min-h-[55vh] flex items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative z-10 text-center text-white px-4 max-w-3xl mx-auto">
            <p class="text-boss-gold tracking-[0.4em] uppercase mb-4 text-[0.7rem]">{{ __('Flexibility') }}</p>
            <h1 class="font-display text-[clamp(2.5rem,6vw,4rem)] leading-tight">{{ __('Work From Home') }}</h1>
            <p class="mt-6 text-white/80 text-[1rem] leading-relaxed">{{ __('Studio-quality setups, structured onboarding, and mentorship - without sacrificing comfort or privacy.') }}</p>
        </div>
    </section>
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 text-boss-dark/70 leading-relaxed space-y-6 text-[0.95rem]">
            <p>{{ __('We help you design lighting, audio, and framing that feel premium on every platform. Your home becomes headquarters for a serious career with schedules you control.') }}</p>
            <p>{{ __('Members get technical checklists, equipment guides, and optional upgrade paths as you grow.') }}</p>
            <a href="{{ route('home') }}#apply" class="inline-block mt-4 bg-boss-gold hover:bg-boss-gold-hover text-white px-10 py-3 tracking-[0.15em] uppercase text-[0.7rem]">{{ __('Apply Now') }}</a>
        </div>
    </section>
</x-layouts.marketing>
