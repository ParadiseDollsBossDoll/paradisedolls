@php
    $heroImg        = asset('images/7.jpeg');
    $allInclusiveImg= asset('images/8.jpeg');
    $yachtImg       = asset('images/9.jpeg');
    $spaImg         = asset('images/10.jpeg');
    $diningImg      = asset('images/11.jpeg');
    $vipPartyImg    = asset('images/12.jpeg');
    $photoshootImg  = asset('images/13.jpeg');
@endphp
<x-layouts.marketing :title="__('VIP Perks')">

    {{-- Hero --}}
    <section class="relative flex min-h-[62vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/50 to-boss-dark/90"></div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('VIP Perks & Rewards') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,6vw,5rem)] leading-tight">{{ __('Live the VIP Lifestyle') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1rem] leading-relaxed text-white/80">{{ __("When you're a top earner, you don't just make money… You unlock a VIP lifestyle most people only dream of.") }}</p>
        </div>
    </section>

    {{-- VIP perks grid --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Exclusive Rewards') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Perks that grow with your earnings') }}</h2>
                <p class="mt-5 text-[0.95rem] leading-relaxed text-boss-dark/62">{{ __('Paradise Dolls rewards its top earners with real-world luxury experiences that make the work feel like the lifestyle.') }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ([
                    [
                        __('All-Inclusive Luxury Getaways'),
                        __('Fully paid trips, villas, hotels, and beachfront escapes. The agency rewards top earners with unforgettable travel experiences.'),
                        $allInclusiveImg,
                    ],
                    [
                        __('Luxury Yacht Trips'),
                        __('Private yacht experiences with champagne, food, and island escapes. Access to events most people only see on social media.'),
                        $yachtImg,
                    ],
                    [
                        __('Spa & Beauty Treatments'),
                        __('VIP wellness and beauty access — from premium spas to full beauty treatments that help you look and feel your best on and off camera.'),
                        $spaImg,
                    ],
                    [
                        __('Fine Dining'),
                        __('Exclusive restaurant experiences and high-end dining at world-class venues across luxury destinations.'),
                        $diningImg,
                    ],
                    [
                        __('VIP Parties & DJs'),
                        __('Guest list access to private events, celebrity-style parties, and premium nightlife experiences worldwide.'),
                        $vipPartyImg,
                    ],
                    [
                        __('Photoshoots & Brand Building'),
                        __('Professional shoots, full styling, and brand-building opportunities to grow your image, audience, and personal brand.'),
                        $photoshootImg,
                    ],
                ] as $perk)
                    <article class="group overflow-hidden bg-boss-muted shadow-luxe">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="{{ $perk[2] }}" alt="" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                        <div class="p-6">
                            <p class="mb-2 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('Paradise Dolls') }}</p>
                            <h3 class="font-display text-[1.25rem] text-boss-dark">{{ $perk[0] }}</h3>
                            <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $perk[1] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Support perks --}}
    <section class="bg-boss-cream py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Community & Support') }}</p>
                <h2 class="font-display text-[clamp(1.8rem,3vw,2.5rem)] text-boss-dark">{{ __('Ongoing support, every step of the way') }}</h2>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    [__('Priority Mentoring'), __('Focused check-ins for strategy, confidence, consistency, and growth planning.')],
                    [__('Creative Direction'), __('Portfolio, profile, and content guidance to sharpen your personal brand.')],
                    [__('Community Moments'), __('Events, celebrations, and group support that make the journey feel less solo.')],
                    [__('Upgrade Pathways'), __('Equipment, setup, and workflow recommendations as your income grows.')],
                    [__('Professional Support'), __('Safety guidance, platform advice, and boundaries support from the team.')],
                    [__('Structured Onboarding'), __('Every member is guided through the same professional process — no one is left to figure it out alone.')],
                ] as $item)
                    <div class="bg-white p-6 shadow-luxe">
                        <h3 class="font-display text-[1.2rem] text-boss-dark">{{ $item[0] }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-boss-dark py-20 text-center text-boss-ivory">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Ready to earn your perks?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)]">{{ __('Join Paradise Dolls today') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-ivory/55">{{ __('No experience required. The onboarding team reviews every application privately. Perks are earned — and the path starts here.') }}</p>
            <a href="{{ route('home') }}#apply" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ __('Become A Doll') }}</a>
        </div>
    </section>

</x-layouts.marketing>
