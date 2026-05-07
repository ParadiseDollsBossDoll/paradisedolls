@php($img = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&q=85&w=1920')
<x-layouts.marketing :title="__('Our Story')">
    <section class="relative flex min-h-[72vh] items-end overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $img }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/15"></div>
        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.4em] text-boss-gold">{{ __('Founder Story') }}</p>
            <h1 class="font-display text-[clamp(3rem,7vw,5.5rem)] leading-tight text-white">{{ __('Why I Built Paradise Dolls') }}</h1>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-14 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Meet Kayla') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Support, structure, and a team that wants you to win') }}</h2>
            </div>
            <div class="space-y-6 text-[0.96rem] leading-relaxed text-boss-dark/65">
                <p>{{ __('After more than 15 years in the industry, Kayla saw the same pattern too often: agencies taking large commissions, signing girls, dropping them into a group chat, and leaving them to figure everything out alone.') }}</p>
                <p>{{ __('Paradise Dolls was created because talent does not burn out from lack of potential. Too often, it burns out from lack of guidance, confidence, safety, and proper systems.') }}</p>
                <p>{{ __('Kayla built her own path from no money, qualifications, or safety net into online businesses, global networks, marketing strategy, published modelling opportunities, and a multi-streaming business designed around real financial freedom.') }}</p>
                <p>{{ __('The mission is simple: help women build a completely different life with support, mentorship, structure, confidence, and the Boss Doll Blueprint behind them.') }}</p>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('What Changed') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('This is more than joining an agency') }}</h2>
            </div>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    [__('Guidance'), __('Members are not expected to learn platforms, customers, earnings, and confidence alone.')],
                    [__('Systems'), __('Onboarding, account preparation, walkthroughs, and structured academy modules keep the path clear.')],
                    [__('Confidence'), __('Branding, mindset, consistency, and professionalism are taught as part of the work.')],
                    [__('Lifestyle'), __('The bigger goal is freedom, remote income, travel, and a life that feels genuinely yours.')],
                ] as $item)
                    <div class="bg-white p-6 shadow-luxe">
                        <h3 class="font-display text-[1.25rem] text-boss-dark">{{ $item[0] }}</h3>
                        <p class="mt-3 text-[0.85rem] leading-relaxed text-boss-dark/58">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-20 text-center text-boss-ivory">
        <div class="mx-auto max-w-3xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Paradise Dolls') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Your chance to build the life you deserve') }}</h2>
            <a href="{{ route('home') }}#apply" class="mt-8 inline-flex bg-boss-gold px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:bg-boss-gold-hover">{{ __('Apply Now') }}</a>
        </div>
    </section>
</x-layouts.marketing>
