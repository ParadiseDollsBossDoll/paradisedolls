@php
    $description = 'Boss Doll Creators is an online model agency for women, offering remote applications, private onboarding, platform preparation, structured training, and ongoing creator management.';
    $heroImage = asset('images/18.jpeg');

    $services = [
        ['title' => 'Application and onboarding', 'body' => 'Every application is reviewed privately. Approved applicants complete identity checks, agreements, and a guided onboarding process.'],
        ['title' => 'Profile and platform setup', 'body' => 'The team helps models prepare profiles, understand verification requirements, learn platform controls, and present themselves professionally online.'],
        ['title' => 'The Boss Doll Blueprint', 'body' => 'Structured education covers streaming foundations, communication, platform earning tools, privacy, boundaries, and consistent working habits.'],
        ['title' => 'Multistreaming education', 'body' => 'Models learn how different live platforms operate and how to manage multiple audiences, controls, and earning systems responsibly.'],
        ['title' => 'Management and mentoring', 'body' => 'Approved models receive account management, marketing guidance, technical support, mentoring, and help interpreting platform requirements.'],
        ['title' => 'Remote member community', 'body' => 'The member area brings training and support together so models can learn from wherever they are approved to work.'],
    ];

    $steps = [
        ['title' => 'Submit an application', 'body' => 'Tell the team about yourself, your experience, your country details, and the best way to contact you.'],
        ['title' => 'Private review', 'body' => 'The team checks basic suitability, age requirements, and whether the agency opportunity can support your circumstances.'],
        ['title' => 'Verification and agreement', 'body' => 'Approved applicants complete identity verification and review the agency, training, privacy, and self-employed terms.'],
        ['title' => 'Preparation before launch', 'body' => 'Complete platform setup and training before beginning work as a self-employed online creator.'],
    ];

    $faqs = [
        [
            'question' => 'Is Boss Doll Creators a traditional fashion model agency?',
            'answer' => 'No. Boss Doll Creators is an online model management agency for adult creators working remotely on digital live-streaming platforms. It does not offer runway, editorial, commercial casting, or fashion-model placement.',
        ],
        [
            'question' => 'Can women apply from outside the United Kingdom?',
            'answer' => 'The application form supports international contact details and remote applications. Acceptance depends on age, identity verification, local legal eligibility, platform availability, payment compatibility, and the agency review. Not every platform or arrangement is available in every country.',
        ],
        [
            'question' => 'What does an online model agency do?',
            'answer' => 'An online model agency can provide onboarding, platform preparation, training, account management, marketing guidance, technical support, and professional development for digital creators. Services and financial terms should be understood before joining.',
        ],
        [
            'question' => 'Do I need previous online modelling experience?',
            'answer' => 'No. Beginners and experienced creators can apply. Approved beginners receive structured onboarding and training before they begin working online.',
        ],
        [
            'question' => 'Will I be an employee of Boss Doll Creators?',
            'answer' => 'No. Models work as self-employed online creators and remain responsible for their own tax and legal obligations. Boss Doll Creators provides agency management, training, and support.',
        ],
        [
            'question' => 'Can an online model agency guarantee income?',
            'answer' => 'No. Boss Doll Creators does not guarantee earnings or success. Results vary according to effort, consistency, hours, audience engagement, platform performance, and market conditions.',
        ],
        [
            'question' => 'What does the Boss Doll Creators training cost?',
            'answer' => 'There is no fee to submit an application. Approved agency models receive the training programme without an upfront charge subject to the agency agreement and a minimum six-month term. Leaving early to continue independently or with another agency may require reimbursement of the programme\'s stated £1,799 value.',
        ],
        [
            'question' => 'How should I compare online model agencies?',
            'answer' => 'Ask what work is involved, who owns and controls accounts, what support is provided, what fees or commitments apply, how privacy and payments are handled, and what happens if you leave. Read every agreement and avoid agencies that promise guaranteed earnings.',
        ],
    ];

    $structuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => route('home').'#organization',
            'name' => 'Boss Doll Creators',
            'url' => route('home'),
            'logo' => asset(config('brand.logos.horizontal_pink')),
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => route('model-agency').'#webpage',
            'url' => route('model-agency'),
            'name' => 'Online Model Agency for Women | Boss Doll Creators',
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Boss Doll Creators',
                'url' => route('home'),
            ],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Online model agency services',
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Model Agency', 'item' => route('model-agency')],
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
    title="Online Model Agency for Women"
    :description="$description"
    :keywords="['model agency', 'online model agency', 'webcam model agency', 'remote model agency', 'adult model agency']"
    :image="$heroImage"
    :structuredData="$structuredData"
>
    <section class="pd-marketing-image-hero relative flex min-h-[clamp(680px,84svh,820px)] items-center overflow-hidden pb-16 pt-28 sm:pb-20 sm:pt-32">
        <img src="{{ $heroImage }}" alt="Woman represented by the Boss Doll Creators online model agency" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/45 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl text-white">
                <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ __('Online model agency for women 18+') }}</p>
                <h1 class="pd-marketing-image-hero-title font-display text-[clamp(2.85rem,7vw,5.6rem)] leading-[0.98] text-[#FFFFFF]">{{ __('A model agency built for online creators') }}</h1>
                <p class="pd-marketing-image-hero-body mt-7 max-w-2xl text-[1.05rem] leading-relaxed text-[#FFFFFF]/90">{{ __('Boss Doll Creators supports women working remotely on digital live-streaming platforms through private onboarding, structured training, platform preparation, and ongoing management.') }}</p>
                <div class="mt-10 grid w-full max-w-[27rem] grid-cols-1 gap-3 sm:w-auto sm:grid-cols-[minmax(9.5rem,auto)_minmax(13.5rem,auto)]">
                    <a href="{{ route('apply') }}" class="pd-cta-primary px-7 py-3">{{ __('Apply Privately') }}</a>
                    <a href="#agency-type" class="inline-flex min-h-12 items-center justify-center rounded-md border border-white/40 px-7 py-3 text-center text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ __('Understand The Agency') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="Model agency summary" class="border-b border-boss-pink/50 bg-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ([
                ['label' => 'Agency type', 'value' => 'Online management'],
                ['label' => 'Eligibility', 'value' => 'Women 18+'],
                ['label' => 'Application', 'value' => 'Remote and private'],
                ['label' => 'Experience', 'value' => 'Beginners welcome'],
            ] as $fact)
                <div class="border-b border-boss-pink/50 px-3 py-6 odd:border-r sm:px-6 lg:border-b-0 lg:border-r lg:last:border-r-0">
                    <p class="text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-boss-rose">{{ __($fact['label']) }}</p>
                    <p class="mt-2 font-display text-[1.2rem] leading-snug text-boss-dark">{{ __($fact['value']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="agency-type" class="scroll-mt-24 bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.82fr_1.18fr] lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('The important distinction') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('Online model management, not fashion casting') }}</h2>
            </div>
            <div class="space-y-5 text-[0.98rem] leading-8 text-boss-dark/68">
                <p>{{ __('The phrase “model agency” can describe very different businesses. Traditional agencies represent fashion, runway, editorial, commercial, or photographic talent and arrange castings with outside clients.') }}</p>
                <p>{{ __('Boss Doll Creators focuses on adult online creators who work remotely on live-streaming platforms. The agency provides education, account and platform support, management, marketing guidance, and technical help rather than runway or fashion placements.') }}</p>
                <p class="font-semibold text-boss-dark">{{ __('Applicants should choose the agency category that matches the work they actually want. Boss Doll Creators does not advertise traditional fashion-modelling jobs.') }}</p>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Model development') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('What the Boss Doll Creators agency provides') }}</h2>
                <p class="mt-5 text-[0.96rem] leading-7 text-boss-dark/65">{{ __('The system is designed around preparation before launch and practical support after an approved model begins working online.') }}</p>
            </div>

            <div class="mt-12 grid border-y border-boss-pink/60 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <article class="border-b border-boss-pink/60 py-7 md:px-7 md:odd:border-r lg:border-r lg:odd:border-r lg:[&:nth-child(3n)]:border-r-0">
                        <p class="text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-boss-rose">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                        <h3 class="mt-4 font-display text-[1.35rem] text-boss-dark">{{ __($service['title']) }}</h3>
                        <p class="mt-3 text-[0.88rem] leading-6 text-boss-dark/62">{{ __($service['body']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.72fr_1.28fr] lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Remote applications') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('An international application does not mean automatic acceptance') }}</h2>
                <p class="mt-5 text-[0.94rem] leading-7 text-boss-dark/65">{{ __('Boss Doll Creators accepts remote applications and supports international contact details, but online creator work is not identical in every country.') }}</p>
            </div>

            <div class="overflow-hidden border border-boss-pink/60">
                <div class="bg-boss-dark px-6 py-4 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-white sm:px-8">{{ __('What the agency checks') }}</div>
                @foreach ([
                    ['Age and identity', 'Every applicant must be at least 18 and able to complete identity verification.'],
                    ['Local eligibility', 'Applicants must be legally permitted to perform the work in the country where they are based.'],
                    ['Platform access', 'Platform availability, verification rules, and payment methods can differ by country.'],
                    ['Agency suitability', 'The team reviews whether the opportunity and support structure are suitable for each applicant.'],
                ] as $check)
                    <article class="grid gap-2 border-t border-boss-pink/50 px-6 py-5 sm:grid-cols-[0.7fr_1.3fr] sm:gap-8 sm:px-8">
                        <h3 class="font-display text-[1.12rem] text-boss-dark">{{ __($check[0]) }}</h3>
                        <p class="text-[0.84rem] leading-6 text-boss-dark/62">{{ __($check[1]) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-20 text-boss-ivory sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Agency experience') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight">{{ __('A remote agency still needs real people and real experience') }}</h2>
                <p class="mt-6 max-w-xl text-[0.95rem] leading-7 text-boss-ivory/65">{{ __('Founder Kayla built Boss Doll Creators from more than 15 years of industry experience, including online platforms, agencies, studios, model development, and the systems behind remote creator businesses.') }}</p>
                <a href="{{ route('our-story') }}" class="mt-8 inline-flex min-h-11 items-center gap-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-boss-gold transition-colors hover:text-white">
                    {{ __('Read the founder story') }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="M5 12h14m-5-5 5 5-5 5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <div class="border-y border-white/[0.12]">
                @foreach ([
                    ['title' => 'Clear category', 'body' => 'Applicants are told that this is adult online creator work, not fashion or runway representation.'],
                    ['title' => 'Clear working status', 'body' => 'Models work as self-employed creators and retain responsibility for their own tax and legal obligations.'],
                    ['title' => 'Clear limitations', 'body' => 'Applications and earnings are never guaranteed, and availability can vary by country and platform.'],
                ] as $principle)
                    <article class="grid gap-2 border-b border-white/[0.12] py-6 last:border-b-0 sm:grid-cols-[0.7fr_1.3fr] sm:gap-8">
                        <h3 class="font-display text-[1.2rem] text-white">{{ __($principle['title']) }}</h3>
                        <p class="text-[0.84rem] leading-6 text-boss-ivory/55">{{ __($principle['body']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Application process') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('How to apply to the online model agency') }}</h2>
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

    <section class="bg-boss-cream py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.82fr_1.18fr] lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Before joining') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Questions every model should ask an agency') }}</h2>
                <p class="mt-5 text-[0.94rem] leading-7 text-boss-dark/65">{{ __('Good representation starts with clarity. Compare the work, support, financial terms, privacy practices, and exit conditions before making a decision.') }}</p>
            </div>
            <ul class="divide-y divide-boss-pink/60 border-y border-boss-pink/60">
                @foreach ([
                    'What work will I actually perform, and on which platforms?',
                    'What training, management, and technical support will I receive?',
                    'What fees, deductions, minimum terms, or repayment conditions apply?',
                    'Who controls my accounts, personal information, content, and payment records?',
                    'What happens to my accounts and obligations if I leave the agency?',
                ] as $question)
                    <li class="flex gap-4 py-4 text-[0.9rem] leading-6 text-boss-dark/70">
                        <svg class="mt-1 h-4 w-4 shrink-0 text-boss-rose" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="M12 3v18M3 12h18" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>{{ __($question) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Online model agency FAQ') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Questions about representation, remote work, and joining') }}</h2>
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

    <section class="bg-boss-dark py-20 text-boss-ivory sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Continue your research') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Understand the opportunity before applying') }}</h2>
                <p class="mt-5 max-w-xl text-[0.94rem] leading-7 text-boss-ivory/60">{{ __('Explore the actual work, agency structure, and member experiences before deciding whether Boss Doll Creators is right for you.') }}</p>
            </div>
            <nav aria-label="Online model agency guidance" class="divide-y divide-white/10 border-y border-white/10">
                @foreach ([
                    ['route' => 'webcam-jobs', 'label' => 'Webcam jobs explained', 'detail' => 'Role, requirements, self-employed status, and application guidance'],
                    ['route' => 'webcam-agency', 'label' => 'How the webcam agency works', 'detail' => 'Services, responsibilities, training terms, and management support'],
                    ['route' => 'guides', 'label' => 'Boss Doll Creators guides', 'detail' => 'Research safety, remote work, agency choices, earnings, and multistreaming'],
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

    <section class="bg-boss-pink py-16 text-center sm:py-20">
        <div class="mx-auto max-w-2xl px-4 sm:px-6">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-dark/55">{{ __('Ready for a private review?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __('Apply to the Boss Doll Creators online model agency') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-7 text-boss-dark/65">{{ __('You must be at least 18. Applications, platform availability, acceptance, and earnings are not guaranteed.') }}</p>
            <a href="{{ route('apply') }}" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-md bg-boss-dark px-10 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:bg-black">{{ __('Apply Now') }}</a>
        </div>
    </section>
</x-layouts.marketing>
