@php
    $heroImg   = 'https://images.unsplash.com/photo-1679931992295-a8d77544a807?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920';
    $storyImg  = asset('images/16.jpeg');
    $luxeImg   = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=85&w=900';
    $myStoryImages = [
        asset('images/our-story/my-story-laptop-beach-1.jpeg'),
        asset('images/our-story/my-story-laptop-beach-2.jpeg'),
        asset('images/our-story/my-story-laptop-beach-3.jpeg'),
    ];
@endphp
<x-layouts.marketing :transparentNav="true" :title="__('Our Story')">

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
            <div class="grid gap-14 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('My Story') }}</p>
                    <h2 class="font-display text-[clamp(1.8rem,3.5vw,2.75rem)] leading-tight text-boss-dark">{{ __('From nothing... to building a global business') }}</h2>
                    <div class="mt-10 space-y-6 text-[0.95rem] leading-relaxed text-boss-dark/70">
                        <p>{{ __("I didn't come from money, connections, or a perfect start.") }}</p>
                        <p>{{ __("I left school early and had to figure life out for myself at a young age. Everything I've built came from learning as I went, trusting my instincts, staying resilient, and refusing to give up on creating a bigger life for myself.") }}</p>
                        <p>{{ __("Over the years, I built networks, learned this industry from the ground up, and turned my passion into multiple successful businesses focused on branding, online marketing, and multi-streaming. Of course there were setbacks along the way, but every challenge taught me something valuable and pushed me to grow even more.") }}</p>
                        <p>{{ __("What started as simply wanting more for myself eventually became something much bigger.") }}</p>
                        <p>{{ __("Today, Paradise Dolls and the Boss Doll Blueprint are built to help women create confidence, freedom, friendships, opportunities, and online success from anywhere in the world.") }}</p>
                        <p>
                            {{ __("I also turned my personal brand into opportunities I once only dreamed about. Including becoming an official Playboy cover model and internationally published feature model, which you can read more about here:") }}
                            <a href="https://playboy.co.za/2026/04/30/sets-the-tone/" target="_blank" rel="noopener noreferrer" class="font-medium text-boss-gold underline decoration-boss-gold/40 underline-offset-4 transition-colors hover:text-boss-gold-hover">{{ __('Playboy feature') }}</a>
                        </p>
                        <p class="font-medium text-boss-dark text-[1rem]">{{ __("But the biggest achievement for me has never been the features, followers, or lifestyle. It's being able to inspire other women to realise they are capable of building a bigger life too.") }}</p>
                        <p>{{ __("No matter your background, your past, your age, your body, or where you're starting from, you are still allowed to dream bigger. You are still allowed to become confident, successful, feminine, independent, powerful, and completely yourself all at once.") }}</p>
                        <p>{{ __("Paradise Dolls was created to remind women that they don't have to fit into one box to be successful. You can be soft and strong. Feminine and ambitious. Beautiful and business-minded. This is more than just a platform. It's a movement built to empower women to believe in themselves again.") }}</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:sticky lg:top-28">
                    <div class="aspect-[4/5] overflow-hidden sm:col-span-2">
                        <img src="{{ $myStoryImages[0] }}" alt="{{ __('Kayla working from the beach at sunset') }}" class="h-full w-full object-cover">
                    </div>
                    <div class="aspect-[4/3] overflow-hidden">
                        <img src="{{ $myStoryImages[1] }}" alt="{{ __('Kayla on the beach with a laptop at sunset') }}" class="h-full w-full object-cover">
                    </div>
                    <div class="aspect-[4/3] overflow-hidden">
                        <img src="{{ $myStoryImages[2] }}" alt="{{ __('Kayla building her online business by the ocean') }}" class="h-full w-full object-cover">
                    </div>
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
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['2012', __('The Beginning'), __('Left home early and started building my own path through music, modelling, dancing, and live TV work. I learned quickly how powerful confidence, performance, and personal branding could be.')],
                    ['2016', __('Going Online'), __('As the world shifted online, I started exploring streaming, content creation, and digital platforms. I realised women could create income and freedom from anywhere in the world with just a phone, consistency, and confidence.')],
                    ['2018', __('Building Businesses'), __('I opened studios, created agencies, and developed systems that helped models grow online. I also invested into multiple businesses including clubs, beauty, photography, and online ventures across different countries.')],
                    ['2019-2021', __('Adapting & Evolving'), __('The industry changed fast. Covid, social media growth, and content creation completely transformed the online world. I spent years testing platforms, learning what actually worked, adapting to the changes, and rebuilding stronger.')],
                    ['2025-2026', __('The Boss Doll Blueprint Era'), __('After stepping away for a few years, I came back with a whole new vision, combining multi-streaming, content creation, social media growth, mentorship, and online education into one supportive girls-girl community and Academy.')],
                ] as $t)
                    <div class="border border-boss-pink/40 bg-boss-muted p-6">
                        <p class="font-display text-[2rem] leading-none text-boss-gold">{{ $t[0] }}</p>
                        <h3 class="mt-3 font-display text-[1.1rem] text-boss-dark">{{ $t[1] }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/60">{{ $t[2] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mx-auto mt-14 max-w-3xl space-y-5 text-center text-[0.95rem] leading-relaxed text-boss-dark/65">
                <p>{{ __("This journey hasn't been easy, but it's taught me everything.") }}</p>
                <p>{{ __("For over 15 years I've tested platforms, studied the industry, built businesses, failed, rebuilt, learned the systems myself, and discovered what truly works online.") }}</p>
                <p class="font-medium text-boss-dark">{{ __("Now I'm passing that knowledge, structure, and experience onto other women, so they can build confidence, freedom, income and a life bigger than they ever imagined.") }}</p>
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
