@php
    $description = 'Explore candid Boss Doll Creators guides about webcam jobs, becoming a webcam model, remote setup, privacy, agency choices, multistreaming, earnings, and payments.';
    $heroImage = asset('images/17.jpeg');

    $groups = [
        [
            'eyebrow' => 'Getting started',
            'title' => 'Understand the work before you apply',
            'body' => 'Start with the adult nature of the role, eligibility, beginner process, home setup, and realistic expectations.',
            'items' => [
                ['route' => 'webcam-jobs', 'label' => 'Webcam jobs for women', 'detail' => 'Role, eligibility, self-employment, support, and application steps', 'time' => '8 min read'],
                ['route' => 'how-to-become-a-webcam-model', 'label' => 'How to become a webcam model', 'detail' => 'A beginner path from initial research to a first live session', 'time' => '10 min read'],
                ['route' => 'work-from-home', 'label' => 'Work-from-home webcam jobs', 'detail' => 'Private workspace, equipment, scheduling, safety, and routine', 'time' => '8 min read'],
            ],
        ],
        [
            'eyebrow' => 'Safety and support',
            'title' => 'Protect yourself and compare your options',
            'body' => 'Review privacy limits, account security, boundaries, agency responsibilities, written terms, fees, and warning signs.',
            'items' => [
                ['route' => 'webcam-model-safety-privacy', 'label' => 'Webcam model safety and privacy', 'detail' => 'Practical privacy layers without promises of complete anonymity', 'time' => '10 min read'],
                ['route' => 'webcam-agency-vs-independent', 'label' => 'Webcam agency vs independent', 'detail' => 'Compare support, control, cost, account ownership, and contracts', 'time' => '9 min read'],
                ['route' => 'webcam-agency', 'label' => 'What a webcam agency should do', 'detail' => 'Boss Doll Creators services, responsibilities, process, and terms', 'time' => '8 min read'],
            ],
        ],
        [
            'eyebrow' => 'Operations and earnings',
            'title' => 'Learn how professional systems work',
            'body' => 'Understand multi-platform streaming, variable income, payment records, deductions, and responsible performance review.',
            'items' => [
                ['route' => 'multistreaming', 'label' => 'Multistreaming for webcam models', 'detail' => 'Technology, platform rules, workflow, stability, and attention', 'time' => '8 min read'],
                ['route' => 'webcam-model-earnings-payments', 'label' => 'Webcam model earnings and payments', 'detail' => 'Tokens, gross and final pay, fees, conversions, and records', 'time' => '9 min read'],
                ['route' => 'model-agency', 'label' => 'Online model agency for women', 'detail' => 'How online creator management differs from fashion casting', 'time' => '7 min read'],
            ],
        ],
    ];

    $allItems = collect($groups)->flatMap(fn ($group) => $group['items'])->values();

    $structuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => route('guides').'#webpage',
            'url' => route('guides'),
            'name' => 'Webcam Model Guides | Boss Doll Creators',
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Boss Doll Creators',
                'url' => route('home'),
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Guides', 'item' => route('guides')],
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $allItems->map(fn ($item, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'url' => route($item['route']),
            ])->all(),
        ],
    ];
@endphp

<x-layouts.marketing
    :transparentNav="true"
    title="Webcam Model Guides"
    :description="$description"
    :keywords="['webcam model guides', 'webcam modelling for beginners', 'webcam model safety', 'webcam model training']"
    :image="$heroImage"
    :structuredData="$structuredData"
>
    <section class="pd-marketing-image-hero relative flex min-h-[clamp(620px,76svh,740px)] items-end overflow-hidden pb-16 pt-28 sm:pb-20 sm:pt-32">
        <img src="{{ $heroImage }}" alt="Women learning together in a professional online creator studio" class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-b from-black/42 via-black/38 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl text-white">
                <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ __('Boss Doll Creators learning centre') }}</p>
                <h1 class="pd-marketing-image-hero-title break-words font-display text-[clamp(2.35rem,7vw,5.5rem)] leading-[0.98] text-[#FFFFFF]">{{ __('Webcam model guides for informed decisions') }}</h1>
                <p class="pd-marketing-image-hero-body mt-7 max-w-3xl text-[1.02rem] leading-8 text-[#FFFFFF]/90">{{ __('Candid guidance for adult women researching webcam work, from beginner eligibility and private home setup to agency terms, multistreaming, earnings, and payment records.') }}</p>
                <a href="#guide-library" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-md border border-white/45 px-7 py-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ __('Browse all guides') }}</a>
            </div>
        </div>
    </section>

    <section class="border-b border-boss-pink/50 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1fr_auto] md:items-center lg:px-8">
            <div>
                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-boss-rose">{{ __('Read before applying') }}</p>
                <p class="mt-3 max-w-3xl font-display text-[clamp(1.4rem,2.5vw,2rem)] leading-snug text-boss-dark">{{ __('Clear information matters more than promises. These guides explain the opportunity, the risks, and the questions worth asking.') }}</p>
            </div>
            <div class="border-l-2 border-boss-gold pl-5 text-[0.82rem] leading-6 text-boss-dark/62">
                <strong class="block text-boss-dark">{{ __('Adults only') }}</strong>
                {{ __('Webcam modelling is adult creator work for people aged 18 or older.') }}
            </div>
        </div>
    </section>

    <div id="guide-library" class="scroll-mt-24">
        @foreach ($groups as $group)
            <section @class(['py-20 sm:py-24', 'bg-boss-cream' => $loop->odd, 'bg-white' => $loop->even])>
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:gap-20">
                        <header>
                            <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-boss-rose">{{ __($group['eyebrow']) }}</p>
                            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __($group['title']) }}</h2>
                            <p class="mt-5 max-w-lg text-[0.92rem] leading-7 text-boss-dark/64">{{ __($group['body']) }}</p>
                        </header>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($group['items'] as $item)
                                <a href="{{ route($item['route']) }}" class="group flex min-h-56 flex-col justify-between rounded-md border border-boss-pink/65 bg-white p-6 transition-all duration-200 hover:-translate-y-0.5 hover:border-boss-rose/45 hover:shadow-luxe focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-boss-rose">
                                    <span>
                                        <span class="text-[0.62rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __($item['time']) }}</span>
                                        <span class="mt-4 block font-display text-[1.4rem] leading-snug text-boss-dark">{{ __($item['label']) }}</span>
                                        <span class="mt-3 block text-[0.85rem] leading-6 text-boss-dark/62">{{ __($item['detail']) }}</span>
                                    </span>
                                    <span class="mt-7 inline-flex items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-boss-dark transition-colors group-hover:text-boss-rose">
                                        {{ __('Read guide') }}
                                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path d="M5 12h14m-5-5 5 5-5 5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>

    <section class="bg-boss-dark py-20 text-boss-ivory">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.82fr_1.18fr] lg:items-center lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-boss-gold">{{ __('About the guidance') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Built from real industry experience, without income promises') }}</h2>
            </div>
            <div class="space-y-5 text-[0.92rem] leading-7 text-boss-ivory/62">
                <p>{{ __('Kayla built Boss Doll Creators after more than 15 years in the industry. The learning centre reflects the agency\'s focus on structured training, platform understanding, privacy, professional standards, and informed applicant decisions.') }}</p>
                <p>{{ __('Platform rules, payment systems, and legal requirements can change. Applicants should verify current terms and use qualified local legal or tax advice where needed.') }}</p>
                <a href="{{ route('our-story') }}" class="inline-flex min-h-11 items-center gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-boss-gold transition-colors hover:text-boss-gold-light">
                    {{ __('Read the founder story') }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="M5 12h14m-5-5 5 5-5 5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <section class="bg-boss-pink py-16 text-center sm:py-20">
        <div class="mx-auto max-w-2xl px-4 sm:px-6">
            <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-boss-dark/55">{{ __('After you have done the research') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __('Apply privately to Boss Doll Creators') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.94rem] leading-7 text-boss-dark/68">{{ __('You must be 18 or older. Every application is reviewed individually, and applying does not guarantee acceptance or earnings.') }}</p>
            <a href="{{ route('apply') }}" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-md bg-boss-dark px-10 py-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:bg-black">{{ __('Apply privately') }}</a>
        </div>
    </section>
</x-layouts.marketing>
