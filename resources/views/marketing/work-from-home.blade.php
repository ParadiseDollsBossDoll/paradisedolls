@php
    $heroImg    = asset('images/2.jpeg');
    $setupImg   = asset('images/1.jpeg');
    $flexImg    = asset('images/3.jpeg');
@endphp
<x-layouts.marketing :transparentNav="true" :title="__('Work From Home')">

    {{-- Hero --}}
    <section class="relative flex min-h-[62vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Flexibility & Freedom') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,7vw,5rem)] leading-tight">{{ __('Work From Home') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1.05rem] leading-relaxed text-white/80">{{ __('Studio-quality setups, full training, and a flexible schedule — without leaving the comfort or privacy of your own space.') }}</p>
        </div>
    </section>

    {{-- 5 core benefits --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Your Space, Your Schedule') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Your home becomes headquarters.') }}</h2>
                    <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ __('Paradise Dolls is built for women who want professional income without having to leave home. Everything you need — training, systems, guidance, and community — is available remotely from day one.') }}</p>

                    <div class="mt-10 space-y-4">
                        @foreach ([
                            [__('Work from your own space'), __('No commute, no office, no dress code outside your stream. Your bedroom, studio corner, or dedicated room becomes your place of business.')],
                            [__('Flexible schedule you control'), __('You decide when you go live. Morning, evening, or night — the platforms work around your lifestyle, not the other way around.')],
                            [__('Full training and walkthroughs provided'), __('The Boss Doll Blueprint gives you everything: platform navigation, monetisation systems, customer interaction, and stream controls.')],
                            [__('Multi-platform earning system'), __('Learn to stream across multiple platforms simultaneously — so your income is never reliant on a single site or audience.')],
                            [__('Privacy & discretion'), __('Your privacy is protected at every step. The team manages verification and account setup before you ever go public.')],
                        ] as $benefit)
                            <div class="flex gap-5 border border-boss-pink/50 bg-boss-muted p-5">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-boss-gold"></span>
                                <div>
                                    <p class="font-medium text-boss-dark text-[0.9rem]">{{ $benefit[0] }}</p>
                                    <p class="mt-1 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $benefit[1] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-5 lg:pt-10">
                    <div class="overflow-hidden shadow-luxe">
                        <img src="{{ $setupImg }}" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="border border-boss-pink/40 bg-boss-muted p-6">
                        <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('What the team handles') }}</p>
                        <ul class="space-y-3 text-[0.86rem] text-boss-dark/65">
                            @foreach ([
                                __('Account setup on every platform'),
                                __('Identity verification and age checks'),
                                __('Profile preparation and review'),
                                __('Onboarding structure and guidance'),
                                __('Support systems throughout'),
                            ] as $item)
                                <li class="flex items-center gap-3">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-boss-gold"></span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Technical setup support --}}
    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Technical Setup') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Professional quality, from wherever you are') }}</h2>
                    <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ __('The Boss Doll Blueprint includes step-by-step guidance on lighting, audio, framing, and connectivity — so your home stream looks premium on every platform from day one.') }}</p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            [__('Lighting guidance'), __('Ring light, softbox, and natural light setups depending on your space and budget.')],
                            [__('Audio quality'), __('Clear, professional sound without expensive studio equipment.')],
                            [__('Camera framing'), __('Optimal positioning, angle, and background styling for every platform.')],
                            [__('Equipment upgrades'), __('Recommended upgrades at every income level — from beginner to advanced.')],
                        ] as $item)
                            <div class="bg-white p-5 shadow-luxe">
                                <h3 class="font-display text-[1.1rem] text-boss-dark">{{ $item[0] }}</h3>
                                <p class="mt-2 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-hidden shadow-luxe">
                    <img src="{{ $flexImg }}" alt="" class="h-full w-full object-cover">
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-boss-dark py-20 text-center text-boss-ivory">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Start from home') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)]">{{ __('Apply and the team will guide you from there') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-ivory/55">{{ __('No experience needed. The onboarding team reviews every application privately and handles setup before training begins.') }}</p>
            <a href="{{ route('home') }}#apply" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ __('Become A Doll') }}</a>
        </div>
    </section>

</x-layouts.marketing>
