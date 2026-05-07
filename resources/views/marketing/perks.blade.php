@php($hero = 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&q=85&w=1920')
<x-layouts.marketing :title="__('Perks')">
    <section class="relative flex min-h-[58vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('VIP Perks & Rewards') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,6vw,4.5rem)] leading-tight">{{ __('Perks that grow with you') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1rem] leading-relaxed text-white/78">{{ __('Rewards, guidance, and support designed to make success feel exciting, feminine, structured, and achievable.') }}</p>
        </div>
    </section>

    <section class="bg-boss-muted py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    [__('Priority mentoring'), __('Focused check-ins for strategy, confidence, consistency, and growth.')],
                    [__('Creative direction'), __('Portfolio, profile, and content guidance to sharpen your brand.')],
                    [__('Community moments'), __('Events, celebrations, and group support where available.')],
                    [__('Upgrade pathways'), __('Equipment, setup, and workflow recommendations as your income grows.')],
                    [__('Lifestyle rewards'), __('Perks built around travel, spa energy, luxury content spaces, and feminine success.')],
                    [__('Professional support'), __('Safety, boundaries, platform guidance, and onboarding review from the team.')],
                ] as $perk)
                    <div class="bg-white p-6 shadow-luxe">
                        <p class="mb-3 text-[0.66rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('Paradise Dolls') }}</p>
                        <h2 class="font-display text-[1.25rem] text-boss-dark">{{ $perk[0] }}</h2>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $perk[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.marketing>
