@php
    $heroImg      = asset('images/6.jpeg');
    $studioImg    = asset('images/4.jpeg');
    $communityImg = asset('images/5.jpeg');
    $beachImg     = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=85&w=900';
@endphp
<x-layouts.marketing :title="__('Work From Paradise')">

    {{-- Hero --}}
    <section class="relative flex min-h-[72vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/45 to-boss-dark/90"></div>
        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Travel & Income') }}</p>
            <h1 class="font-display text-[clamp(2.8rem,7vw,5.5rem)] leading-tight">{{ __('Work From Paradise') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1.05rem] leading-relaxed text-white/80">{{ __('Portable rigs, flexible schedules, and an all-girl expat community so your office can be anywhere on the planet.') }}</p>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}#apply" class="bg-boss-gold px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:bg-boss-gold-hover">{{ __('Apply Now') }}</a>
                <a href="{{ route('our-story') }}" class="border border-white/40 px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white/10">{{ __('Our Story') }}</a>
            </div>
        </div>
    </section>

    {{-- Key messaging --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[1fr_1fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('The Lifestyle') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Luxury for less. Freedom for more.') }}</h2>
                    <div class="mt-8 space-y-6 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        <p>{{ __('Some of the world\'s most exciting locations offer luxury living at a fraction of the cost of major cities — and Paradise Dolls models know how to find them.') }}</p>
                        <p>{{ __('When you\'re earning online, your location is your choice. Tropical destinations, iconic nightlife, incredible food, and a growing expat girl boss community await.') }}</p>
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-3">
                        @foreach ([
                            __('Tropical paradise'),
                            __('Luxury for less'),
                            __('Iconic nightlife'),
                            __('Amazing food'),
                            __('Expat girl boss hotspot'),
                            __('Remote-friendly infrastructure'),
                        ] as $item)
                            <div class="flex items-center gap-3 border border-boss-pink/60 bg-boss-muted px-4 py-3">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-boss-gold"></span>
                                <span class="text-[0.84rem] text-boss-dark/70">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="relative">
                    <div class="aspect-[4/5] overflow-hidden">
                        <img src="{{ $beachImg }}" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="absolute -bottom-6 -left-5 bg-boss-pink px-7 py-5 shadow-luxe">
                        <p class="font-display text-[1.7rem] leading-none text-boss-dark">{{ __('Any location') }}</p>
                        <p class="mt-1 text-[0.62rem] uppercase tracking-[0.16em] text-boss-dark/55">{{ __('your schedule, your paradise') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Studio Living --}}
    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div class="overflow-hidden shadow-luxe">
                    <img src="{{ $studioImg }}" alt="" class="h-full w-full object-cover">
                </div>
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Studio Living') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Exclusive studio spaces worldwide') }}</h2>
                    <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ __('Paradise Dolls coordinates access to exclusive studio spaces designed specifically for professional streaming. These are not generic co-working spaces — they are purpose-built, fully equipped, and aesthetically stunning.') }}</p>
                    <div class="mt-8 space-y-4">
                        @foreach ([
                            [__('Luxury private spaces'), __('Purpose-built streaming rooms with full privacy, professional aesthetic, and premium finishes.')],
                            [__('Fully equipped webcam studios'), __('High-quality lighting, stable connectivity, gorgeous backdrops — everything optimised for professional streaming.')],
                            [__('Professional glam setup'), __('The environment reflects the brand: polished, aspirational, and built for the highest-quality content.')],
                        ] as $item)
                            <div class="flex gap-5 border border-boss-pink/50 bg-white p-5">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-boss-gold"></span>
                                <div>
                                    <p class="font-medium text-boss-dark text-[0.9rem]">{{ $item[0] }}</p>
                                    <p class="mt-1 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item[1] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Community & Security --}}
    <section class="bg-boss-dark py-24 text-boss-ivory">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2">
                {{-- Community --}}
                <div>
                    <div class="relative overflow-hidden shadow-luxe mb-8">
                        <div class="aspect-[16/9]">
                            <img src="{{ $communityImg }}" alt="" class="h-full w-full object-cover opacity-80">
                        </div>
                    </div>
                    <p class="mb-3 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Community') }}</p>
                    <h3 class="font-display text-[clamp(1.5rem,3vw,2rem)] text-boss-ivory">{{ __('An all-girl environment built for growth') }}</h3>
                    <div class="mt-5 space-y-4 text-[0.9rem] leading-relaxed text-boss-ivory/55">
                        <p>{{ __('When you work from a Paradise Dolls studio, you\'re not alone. You\'re surrounded by a supportive all-girl community where everyone shares tips, encourages consistency, and motivates each other daily.') }}</p>
                        <ul class="mt-4 space-y-2">
                            @foreach ([
                                __('All-girl supportive environment'),
                                __('Share tips and strategies as you grow'),
                                __('Motivating, ambitious atmosphere'),
                                __('Community events and group moments'),
                            ] as $item)
                                <li class="flex items-center gap-3">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-boss-gold"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Security & Lifestyle --}}
                <div class="flex flex-col justify-start">
                    <p class="mb-3 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Security & Lifestyle') }}</p>
                    <h3 class="font-display text-[clamp(1.5rem,3vw,2rem)] text-boss-ivory">{{ __('Private. Safe. Yours.') }}</h3>
                    <p class="mt-5 text-[0.9rem] leading-relaxed text-boss-ivory/55">{{ __('Every studio and location recommendation from Paradise Dolls is vetted for safety, privacy, and professional suitability. Your discretion and security are non-negotiable.') }}</p>
                    <div class="mt-8 grid gap-4">
                        @foreach ([
                            [__('Total privacy & discretion'), __('Your work, your identity, and your content are protected at every location.')],
                            [__('Safe, secure environments'), __('Every recommended space is vetted for security, reliability, and professional standard.')],
                            [__('Make it your home base'), __('Studio living means routine, community, and structure — not just a room with a camera.')],
                        ] as $item)
                            <div class="border border-white/[0.07] bg-white/[0.03] p-5">
                                <h4 class="font-display text-[1rem] text-boss-gold-light">{{ $item[0] }}</h4>
                                <p class="mt-2 text-[0.84rem] leading-relaxed text-boss-ivory/45">{{ $item[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-boss-pink py-20 text-center">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Ready to explore?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __('Your paradise is waiting') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-dark/65">{{ __('Apply today. The team handles onboarding, verification, and setup — then you decide where in the world you want to work.') }}</p>
            <a href="{{ route('home') }}#apply" class="mt-10 inline-block bg-boss-dark px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-boss-gold">{{ __('Join Us') }}</a>
        </div>
    </section>

</x-layouts.marketing>
