@php
    $heroImg   = 'https://images.unsplash.com/photo-1679931992295-a8d77544a807?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920';
    $storyImg  = asset('images/16.jpeg');
    $luxeImg   = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=85&w=900';
@endphp
<x-layouts.marketing :title="__('Our Story')">

    {{-- Hero --}}
    <section class="relative flex min-h-[72vh] items-end overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center bg-top" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>
        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pb-18 sm:px-6 lg:px-8 pb-16">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.38em] text-boss-gold">{{ __('About Us') }}</p>
            <h1 class="font-display text-[clamp(3rem,7vw,5.5rem)] leading-tight text-white">{{ __('Our Story') }}</h1>
            <p class="mt-5 max-w-xl text-[1rem] leading-relaxed text-white/72">{{ __('How one woman turned survival into a global mission for feminine success.') }}</p>
        </div>
    </section>

    {{-- Why Paradise Dolls exists --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-[1fr_1fr] lg:items-start">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Why Paradise Dolls Exists') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('The industry needed something different.') }}</h2>
                    <div class="mt-8 space-y-6 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        <p>{{ __("I've been in this industry for over 15 years, and I've seen it all.") }}</p>
                        <p>{{ __("I've watched agencies take huge commissions while offering very little in return.") }}</p>
                        <p>{{ __("I've seen girls signed, dropped into a group chat, and left to figure everything out alone.") }}</p>
                        <p>{{ __("I've seen talent burn out, confidence break, and potential wasted — not because the girls weren't capable, but because the support simply wasn't there.") }}</p>
                        <p class="font-medium text-boss-dark">{{ __("That's why Paradise Dolls exists.") }}</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="aspect-[4/5] overflow-hidden">
                        <img src="{{ $storyImg }}" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="absolute -bottom-7 -right-4 bg-boss-gold px-7 py-5 shadow-luxe sm:-right-7">
                        <p class="font-display text-[1.8rem] leading-none text-white">15+</p>
                        <p class="mt-1 text-[0.62rem] uppercase tracking-[0.16em] text-white/80">{{ __('years in the industry') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Kayla's personal story --}}
    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('My Story') }}</p>
                <h2 class="font-display text-[clamp(1.8rem,3.5vw,2.75rem)] leading-tight text-boss-dark">{{ __('From nothing… to building a global business') }}</h2>
                <div class="mt-10 space-y-6 text-[0.95rem] leading-relaxed text-boss-dark/70">
                    <p>{{ __("I didn't come from money, connections, or qualifications.") }}</p>
                    <p>{{ __("I left home early, carrying my own struggles and trauma like many of us do. I left school and college with no GCSEs, no certificates, and no safety net. All I had was hustle, instinct, resilience, and a fire inside me that refused to accept struggle as a permanent state.") }}</p>
                    <p>{{ __("So I learned this industry fast. I built networks. I connected with major names behind the scenes. And yes… I failed many times too.") }}</p>
                    <p>{{ __("But every setback taught me something valuable.") }}</p>
                    <p>{{ __("Over the years, I successfully built multiple businesses generating millions, creating a strong foundation in growth, branding, online marketing, and strategy. I also navigated major economic shifts and the impact of COVID, which sharpened my ability to adapt, rebuild, and scale even stronger than before.") }}</p>
                    <p>{{ __("What started as survival eventually became something much bigger.") }}</p>
                    <p>{{ __("I went from having nothing… to building my own global multi-streaming business, helping models earn across multiple platforms at once and create real financial freedom for themselves.") }}</p>
                    <p>{{ __("I turned my personal brand into opportunities I once only dreamed about — including becoming an official Playboy cover model and internationally published feature model.") }}</p>
                    <p class="font-medium text-boss-dark text-[1rem]">{{ __("But the biggest achievement for me isn't the features, the money, the followers, or the lifestyle.") }}</p>
                    <p class="font-medium text-boss-dark text-[1rem]">{{ __("It's being able to help other girls realise they are capable of building a completely different life too.") }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- The Mission --}}
    <section class="bg-boss-dark py-24 text-boss-ivory">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('The Mission') }}</p>
                    <h2 class="font-display text-[clamp(1.9rem,3.5vw,3rem)] leading-tight">{{ __('This is more than just an agency.') }}</h2>
                    <p class="mt-7 text-[0.95rem] leading-relaxed text-boss-ivory/60">
                        {{ __("Because this industry can change your life when you're guided properly. With me guiding you, you'll learn how to handle everything this industry throws at you — from confidence, branding, mindset, and consistency, to the behind-the-scenes business strategies most girls never get taught.") }}
                    </p>
                    <p class="mt-5 text-[0.95rem] leading-relaxed text-boss-ivory/60">
                        {{ __("You won't be left alone trying to figure it out. You'll have support, structure, mentorship, and a team behind you that genuinely wants to see you win.") }}
                    </p>
                    <p class="mt-7 text-[1rem] font-medium text-boss-ivory">{{ __("This is your chance to build the life you deserve.") }}</p>
                    <a href="{{ route('home') }}#apply" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ __('Become A Doll') }}</a>
                </div>
                <div class="grid gap-4">
                    @foreach ([
                        [__('Confidence'), __('You\'ll learn everything needed — from platform strategy to personal branding and the mindset to stay consistent.')],
                        [__('Structure'), __('Walkthroughs, checklists, onboarding, and a clear training path. Nothing left to guesswork.')],
                        [__('Community'), __('An all-girl environment that\'s motivating, supportive, and ambitious without being competitive or intimidating.')],
                        [__('Mentorship'), __('Real guidance from someone who has built, failed, rebuilt, and succeeded across multiple income streams and platforms.')],
                    ] as $item)
                        <div class="border border-white/[0.07] bg-white/[0.03] p-5">
                            <h3 class="font-display text-[1.1rem] text-boss-gold-light">{{ $item[0] }}</h3>
                            <p class="mt-2 text-[0.84rem] leading-relaxed text-boss-ivory/45">{{ $item[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Timeline --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('The Journey') }}</p>
                <h2 class="font-display text-[clamp(1.8rem,3vw,2.5rem)] text-boss-dark">{{ __('From ambition to agency') }}</h2>
            </div>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['2018', __('The Beginning'), __('Started with ambition and the belief that performers deserve more. Built early networks and tested what really worked.')],
                    ['2020', __('Going Digital'), __('Scaled audiences online during a global shift. Earned from anywhere — and proved the model worked.')],
                    ['2022', __('Building the Agency'), __('Formalised training, mentorship, and the Boss Doll Blueprint. The system that turned experience into education.')],
                    ['2024', __('Global Vision'), __('International growth, a worldwide sisterhood, and a mission to reach women everywhere who deserve better guidance.')],
                ] as $t)
                    <div class="border border-boss-pink/40 bg-boss-muted p-6">
                        <p class="font-display text-[2rem] leading-none text-boss-gold">{{ $t[0] }}</p>
                        <h3 class="mt-3 font-display text-[1.1rem] text-boss-dark">{{ $t[1] }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/60">{{ $t[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-boss-pink py-20 text-center">
        <div class="mx-auto max-w-3xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Ready?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __('Step into your highest level') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-relaxed text-boss-dark/65">{{ __('No experience necessary. The team handles onboarding, verification, and setup. You bring consistency and ambition.') }}</p>
            <a href="{{ route('home') }}#apply" class="mt-10 inline-block rounded-md bg-[#EEB4C3] px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-[#e0a0b5]">{{ __('Become A Doll') }}</a>
        </div>
    </section>

</x-layouts.marketing>
