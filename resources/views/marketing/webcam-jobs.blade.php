@php
    $description = 'Explore remote webcam jobs with Paradise Dolls. Learn what the work involves, who can apply, how onboarding works, and what training and support approved models receive.';
    $heroImage = asset('images/2.jpeg');

    $requirements = [
        'You are at least 18 years old and legally entitled to work as an online creator.',
        'You can complete identity and age verification for the platforms you use.',
        'You have reliable internet and a private, suitable place to stream.',
        'You are willing to follow platform rules, professional standards, and safety guidance.',
        'You can work consistently, communicate professionally, and take part in training.',
    ];

    $support = [
        'Private application review and structured onboarding',
        'Platform setup, profile preparation, and verification guidance',
        'The Boss Doll Blueprint training programme',
        'Multistreaming, equipment, presentation, and customer communication education',
        'Privacy, boundaries, account security, and professional support',
        'Access to the Paradise Dolls model community after approval',
    ];

    $steps = [
        ['title' => 'Send your application', 'body' => 'Tell us about yourself, your experience level, and how the team can contact you. No previous webcam experience is required.'],
        ['title' => 'Private team review', 'body' => 'The Paradise Dolls team reviews each application individually. Applying does not guarantee acceptance.'],
        ['title' => 'Verification and onboarding', 'body' => 'Approved applicants complete identity checks, platform preparation, and any required agreements before starting.'],
        ['title' => 'Training before going live', 'body' => 'You receive structured guidance on setup, safety, platform tools, customer communication, and professional working standards.'],
    ];

    $faqs = [
        [
            'question' => 'What does a webcam model job involve?',
            'answer' => 'The work involves live online video streaming as an adult creator, speaking with viewers, presenting your profile professionally, using platform earning features, and following each platform\'s rules. Paradise Dolls provides management, training, and support to approved models.',
        ],
        [
            'question' => 'Do I need webcam modelling experience?',
            'answer' => 'No. Paradise Dolls accepts applications from complete beginners and experienced models. Approved beginners receive onboarding and training before they begin working online.',
        ],
        [
            'question' => 'Can I work from home?',
            'answer' => 'Yes. The opportunity is remote, provided you have a private workspace, reliable internet, suitable equipment, and can meet the platform, safety, and verification requirements.',
        ],
        [
            'question' => 'Is this employment or self-employed work?',
            'answer' => 'Models work as self-employed online creators. You are responsible for your own tax and legal obligations. Paradise Dolls provides agency management, training, account support, marketing guidance, and technical support.',
        ],
        [
            'question' => 'How much can a webcam model earn?',
            'answer' => 'Earnings are not guaranteed. Results vary according to consistency, hours worked, audience engagement, platform performance, and market conditions. The team focuses on helping approved models understand the platforms and build professional working habits.',
        ],
        [
            'question' => 'Does it cost anything to apply?',
            'answer' => 'Submitting an application does not require payment. Approved agency models receive the training programme without an upfront charge subject to the agency terms, including the minimum commitment explained in the application agreement. Read those terms carefully before submitting.',
        ],
        [
            'question' => 'How do I apply for webcam jobs with Paradise Dolls?',
            'answer' => 'Select Apply Now, complete the private application form, confirm that you are 18 or older, and accept the application terms. The team will contact you if your application is selected for the next stage.',
        ],
    ];

    $structuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => route('home').'#organization',
            'name' => 'Paradise Dolls',
            'url' => route('home'),
            'logo' => asset('images/brand/get-rich-with-paradise-dolls-logo.png'),
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => route('webcam-jobs').'#webpage',
            'url' => route('webcam-jobs'),
            'name' => 'Webcam Jobs for Women | Paradise Dolls',
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Paradise Dolls',
                'url' => route('home'),
            ],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Remote webcam model jobs',
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Webcam Jobs', 'item' => route('webcam-jobs')],
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ])->all(),
        ],
    ];
@endphp

<x-layouts.marketing
    :transparentNav="true"
    title="Webcam Jobs for Women"
    :description="$description"
    :keywords="['webcam jobs', 'webcam model jobs', 'remote webcam jobs', 'online model jobs', 'work from home webcam jobs']"
    :image="$heroImage"
    :structuredData="$structuredData"
>
    <section class="pd-marketing-image-hero relative flex min-h-[clamp(680px,84svh,820px)] items-center overflow-hidden pb-16 pt-28 sm:pb-20 sm:pt-32">
        <img src="{{ $heroImage }}" alt="Woman working online from a private home streaming setup" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/45 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl text-white">
                <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ __('Remote opportunity for women 18+') }}</p>
                <h1 class="pd-marketing-image-hero-title font-display text-[clamp(2.85rem,7vw,5.6rem)] leading-[0.98] text-[#FFF8F6]">{{ __('Webcam jobs with training, privacy, and real support') }}</h1>
                <p class="pd-marketing-image-hero-body mt-7 max-w-2xl text-[1.05rem] leading-relaxed text-[#FFF8F6]/90">{{ __('Paradise Dolls supports women who want to work remotely as self-employed online creators, with private onboarding, platform education, multistreaming training, and ongoing agency guidance.') }}</p>
                <div class="mt-10 grid w-full max-w-[26rem] grid-cols-1 gap-3 sm:w-auto sm:grid-cols-[minmax(9.5rem,auto)_minmax(12.75rem,auto)]">
                    <a href="{{ route('apply') }}" class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#EEB4C3] px-7 py-3 text-center text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-boss-dark transition-colors hover:bg-[#e0a0b5]">{{ __('Apply Now') }}</a>
                    <a href="#role-details" class="inline-flex min-h-12 items-center justify-center rounded-md border border-white/40 px-7 py-3 text-center text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ __('Read Role Details') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="Opportunity summary" class="border-b border-boss-pink/50 bg-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ([
                ['label' => 'Minimum age', 'value' => '18+ only'],
                ['label' => 'Work style', 'value' => 'Remote'],
                ['label' => 'Experience', 'value' => 'Beginners welcome'],
                ['label' => 'Application', 'value' => 'Private review'],
            ] as $fact)
                <div class="border-b border-boss-pink/50 px-3 py-6 odd:border-r sm:px-6 lg:border-b-0 lg:border-r lg:last:border-r-0">
                    <p class="text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-boss-rose">{{ __($fact['label']) }}</p>
                    <p class="mt-2 font-display text-[1.2rem] text-boss-dark">{{ __($fact['value']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="role-details" class="scroll-mt-24 bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.88fr_1.12fr] lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('The opportunity') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('What a webcam job with Paradise Dolls involves') }}</h2>
            </div>
            <div class="space-y-5 text-[0.98rem] leading-8 text-boss-dark/68">
                <p>{{ __('This is adult online creator work performed remotely on live-streaming platforms. Models build an audience through live video, conversation, profile presentation, and platform earning features while maintaining their own boundaries and following platform rules.') }}</p>
                <p>{{ __('Paradise Dolls acts as a management agency. The team helps approved models prepare their accounts, understand the technology, learn professional working methods, protect their privacy, and develop a consistent routine. Models work on a self-employed basis rather than as employees.') }}</p>
                <p class="font-semibold text-boss-dark">{{ __('No agency can honestly guarantee a particular income. Earnings depend on individual effort, consistency, audience engagement, platform performance, and market conditions.') }}</p>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-14 px-4 sm:px-6 lg:grid-cols-2 lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Before you apply') }}</p>
                <h2 class="font-display text-[clamp(1.85rem,3vw,2.7rem)] leading-tight text-boss-dark">{{ __('Who can apply for webcam model jobs?') }}</h2>
                <ul class="mt-8 divide-y divide-boss-pink/60 border-y border-boss-pink/60">
                    @foreach ($requirements as $requirement)
                        <li class="flex gap-4 py-4 text-[0.92rem] leading-6 text-boss-dark/70">
                            <svg class="mt-1 h-4 w-4 shrink-0 text-boss-rose" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path d="m5 12 4 4L19 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>{{ __($requirement) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Agency support') }}</p>
                <h2 class="font-display text-[clamp(1.85rem,3vw,2.7rem)] leading-tight text-boss-dark">{{ __('What approved Paradise Dolls models receive') }}</h2>
                <ul class="mt-8 divide-y divide-boss-pink/60 border-y border-boss-pink/60">
                    @foreach ($support as $item)
                        <li class="flex gap-4 py-4 text-[0.92rem] leading-6 text-boss-dark/70">
                            <svg class="mt-1 h-4 w-4 shrink-0 text-boss-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path d="M12 3v18M3 12h18" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <span>{{ __($item) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Application process') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('How to apply for webcam jobs') }}</h2>
                <p class="mt-5 text-[0.96rem] leading-7 text-boss-dark/65">{{ __('The process is private and designed to make expectations clear before an approved applicant starts working online.') }}</p>
            </div>

            <ol class="mt-12 grid gap-px overflow-hidden border border-boss-pink/60 bg-boss-pink/60 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $step)
                    <li class="bg-boss-muted p-6 sm:p-7">
                        <span class="font-display text-[2rem] leading-none text-boss-rose">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-5 font-display text-[1.35rem] text-boss-dark">{{ __($step['title']) }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-6 text-boss-dark/62">{{ __($step['body']) }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="bg-boss-dark py-20 text-boss-ivory sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Learn before you apply') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Understand the work and the agency') }}</h2>
                <p class="mt-5 max-w-xl text-[0.94rem] leading-7 text-boss-ivory/60">{{ __('Explore the beginner process, remote setup, safety, and professional systems before deciding whether the opportunity is right for you.') }}</p>
            </div>
            <nav aria-label="Webcam job guidance" class="divide-y divide-white/10 border-y border-white/10">
                @foreach ([
                    ['route' => 'how-to-become-a-webcam-model', 'label' => 'How to become a webcam model', 'detail' => 'A careful beginner path from research to a first live session'],
                    ['route' => 'work-from-home', 'label' => 'Working from home', 'detail' => 'Workspace, privacy, equipment, and flexible scheduling'],
                    ['route' => 'webcam-model-safety-privacy', 'label' => 'Safety and privacy', 'detail' => 'Identity separation, account security, boundaries, and incident response'],
                    ['route' => 'guides', 'label' => 'Browse all guides', 'detail' => 'Agency choices, earnings, payments, multistreaming, and more'],
                ] as $link)
                    <a href="{{ route($link['route']) }}" class="group flex min-h-20 items-center justify-between gap-6 py-4 transition-colors hover:text-boss-gold">
                        <span>
                            <span class="block font-display text-[1.2rem]">{{ __($link['label']) }}</span>
                            <span class="mt-1 block text-[0.78rem] text-boss-ivory/45">{{ __($link['detail']) }}</span>
                        </span>
                        <svg class="h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="M5 12h14m-5-5 5 5-5 5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Webcam jobs FAQ') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Questions applicants ask before getting started') }}</h2>
            </div>

            <div class="mt-10 divide-y divide-boss-pink/60 border-y border-boss-pink/60">
                @foreach ($faqs as $faq)
                    <article class="grid gap-3 py-7 md:grid-cols-[0.8fr_1.2fr] md:gap-10">
                        <h3 class="font-display text-[1.25rem] leading-snug text-boss-dark">{{ __($faq['question']) }}</h3>
                        <p class="text-[0.9rem] leading-7 text-boss-dark/65">{{ __($faq['answer']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-pink py-16 text-center sm:py-20">
        <div class="mx-auto max-w-2xl px-4 sm:px-6">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-dark/55">{{ __('Ready for the next step?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __('Apply privately to Paradise Dolls') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-7 text-boss-dark/65">{{ __('Applications are reviewed individually. You must be 18 or older, and submitting the form does not guarantee acceptance.') }}</p>
            <a href="{{ route('apply') }}" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-md bg-boss-dark px-10 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:bg-black">{{ __('Apply Now') }}</a>
        </div>
    </section>
</x-layouts.marketing>
