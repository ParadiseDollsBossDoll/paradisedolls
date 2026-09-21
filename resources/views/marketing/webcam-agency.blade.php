@php
    $description = 'Paradise Dolls is a webcam agency for women, providing private onboarding, platform setup, structured training, multistreaming education, and ongoing model support.';
    $heroImage = asset('images/17.jpeg');

    $agencySupport = [
        ['title' => 'Private onboarding', 'body' => 'Applications are reviewed individually. Approved models complete identity checks, agreements, platform preparation, and a guided setup process.'],
        ['title' => 'Platform preparation', 'body' => 'The team helps with profile preparation, account setup, verification requirements, platform controls, and professional presentation.'],
        ['title' => 'Structured training', 'body' => 'The Boss Doll Blueprint covers streaming foundations, customer communication, earning tools, safety, boundaries, and consistent working habits.'],
        ['title' => 'Multistreaming education', 'body' => 'Models learn how different platforms work and how multistreaming can broaden visibility without losing control of the live experience.'],
        ['title' => 'Ongoing management', 'body' => 'Approved models receive mentoring, account management, marketing guidance, technical support, and help understanding platform requirements.'],
        ['title' => 'Private community', 'body' => 'Members can learn alongside other Paradise Dolls models in a supportive environment built around professionalism and shared progress.'],
    ];

    $steps = [
        ['title' => 'Apply privately', 'body' => 'Complete the application form and confirm that you are at least 18 years old. Applying does not guarantee acceptance.'],
        ['title' => 'Speak with the team', 'body' => 'The team reviews your application and explains the opportunity, expectations, and next steps before you commit.'],
        ['title' => 'Verify and prepare', 'body' => 'Approved applicants complete identity verification, required agreements, profile preparation, and platform setup.'],
        ['title' => 'Train before going live', 'body' => 'Learn the systems, safety standards, and professional working methods before beginning as a self-employed online creator.'],
    ];

    $faqs = [
        [
            'question' => 'What does a webcam agency do?',
            'answer' => 'A webcam agency supports online creators with services such as onboarding, platform setup, training, account management, technical guidance, marketing support, and professional development. The exact services and terms should be clear before a model joins.',
        ],
        [
            'question' => 'What does Paradise Dolls help models with?',
            'answer' => 'Paradise Dolls provides private onboarding, profile and platform preparation, the Boss Doll Blueprint training programme, multistreaming education, mentoring, account management, marketing guidance, technical support, and an approved-model community.',
        ],
        [
            'question' => 'Do I need webcam modelling experience?',
            'answer' => 'No. Paradise Dolls considers applications from beginners and experienced models. Approved beginners receive structured preparation and training before going live.',
        ],
        [
            'question' => 'Am I employed by the agency?',
            'answer' => 'No. Models work as self-employed online creators and remain responsible for their own tax and legal obligations. Paradise Dolls provides agency management, education, and support.',
        ],
        [
            'question' => 'Does Paradise Dolls support working from home?',
            'answer' => 'Yes. Models can work remotely when they have a private workspace, reliable internet, suitable equipment, and can meet platform, verification, safety, and professional requirements.',
        ],
        [
            'question' => 'Does Paradise Dolls support more than one webcam platform?',
            'answer' => 'Yes. Multistreaming education is part of the Paradise Dolls system. Approved models learn how platform tools and earning systems differ and how to manage more than one live platform professionally.',
        ],
        [
            'question' => 'Is the training free?',
            'answer' => 'There is no fee to submit an application. For approved agency models, the training programme is provided without an upfront charge subject to the agency agreement, including a six-month minimum term. If a model leaves early to continue independently or with another agency, the agreement may require reimbursement of the programme\'s £1,799 value. Applicants should read the full terms before submitting.',
        ],
        [
            'question' => 'Does a webcam agency guarantee earnings?',
            'answer' => 'No. Paradise Dolls does not guarantee income or success. Results vary with individual effort, consistency, hours, audience engagement, platform performance, and market conditions.',
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
            '@id' => route('webcam-agency').'#webpage',
            'url' => route('webcam-agency'),
            'name' => 'Webcam Agency for Women | Paradise Dolls',
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Paradise Dolls',
                'url' => route('home'),
            ],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Webcam model agency services',
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Webcam Agency', 'item' => route('webcam-agency')],
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
    title="Webcam Agency for Women"
    :description="$description"
    :keywords="['webcam agency', 'webcam model agency', 'cam model agency', 'adult webcam agency', 'streaming agency']"
    :image="$heroImage"
    :structuredData="$structuredData"
>
    <section class="pd-marketing-image-hero relative flex min-h-[clamp(680px,84svh,820px)] items-center overflow-hidden pb-16 pt-28 sm:pb-20 sm:pt-32">
        <img src="{{ $heroImage }}" alt="Paradise Dolls webcam agency model working from a private home setup" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/45 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl text-white">
                <p class="pd-marketing-image-hero-eyebrow mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-[#F2C7D2]">{{ __('Webcam agency for women 18+') }}</p>
                <h1 class="pd-marketing-image-hero-title font-display text-[clamp(2.85rem,7vw,5.6rem)] leading-[0.98] text-[#FFF8F6]">{{ __('A webcam agency that prepares you for the real work') }}</h1>
                <p class="pd-marketing-image-hero-body mt-7 max-w-2xl text-[1.05rem] leading-relaxed text-[#FFF8F6]/90">{{ __('Paradise Dolls combines private onboarding, practical training, platform setup, multistreaming education, and ongoing management for self-employed online creators.') }}</p>
                <div class="mt-10 grid w-full max-w-[27rem] grid-cols-1 gap-3 sm:w-auto sm:grid-cols-[minmax(9.5rem,auto)_minmax(13.5rem,auto)]">
                    <a href="{{ route('apply') }}" class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#EEB4C3] px-7 py-3 text-center text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-boss-dark transition-colors hover:bg-[#e0a0b5]">{{ __('Apply Privately') }}</a>
                    <a href="#agency-explained" class="inline-flex min-h-12 items-center justify-center rounded-md border border-white/40 px-7 py-3 text-center text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ __('How The Agency Works') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="Agency summary" class="border-b border-boss-pink/50 bg-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ([
                ['label' => 'Eligibility', 'value' => 'Women 18+'],
                ['label' => 'Work status', 'value' => 'Self-employed'],
                ['label' => 'Experience', 'value' => 'Beginners welcome'],
                ['label' => 'Support', 'value' => 'Before and after launch'],
            ] as $fact)
                <div class="border-b border-boss-pink/50 px-3 py-6 odd:border-r sm:px-6 lg:border-b-0 lg:border-r lg:last:border-r-0">
                    <p class="text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-boss-rose">{{ __($fact['label']) }}</p>
                    <p class="mt-2 font-display text-[1.2rem] leading-snug text-boss-dark">{{ __($fact['value']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="agency-explained" class="scroll-mt-24 bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.88fr_1.12fr] lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('The agency relationship') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('What a webcam agency should actually do') }}</h2>
            </div>
            <div class="space-y-5 text-[0.98rem] leading-8 text-boss-dark/68">
                <p>{{ __('A webcam model agency should provide more than a registration link. Its role is to help creators understand the platforms, prepare accounts correctly, learn professional working methods, protect their privacy, and get informed support when questions arise.') }}</p>
                <p>{{ __('Paradise Dolls provides management and education while the model remains a self-employed online creator. You control whether the opportunity suits you, your working boundaries, and the hours you choose to commit, subject to platform rules and any agreement you accept.') }}</p>
                <p class="font-semibold text-boss-dark">{{ __('The work is adult live-streaming. Every applicant must be at least 18, complete identity verification, and understand the nature of the role before starting.') }}</p>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Agency services') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('What Paradise Dolls provides') }}</h2>
                <p class="mt-5 text-[0.96rem] leading-7 text-boss-dark/65">{{ __('Support begins before an approved model goes live and continues as she learns the platforms and develops her own working routine.') }}</p>
            </div>

            <div class="mt-12 grid border-y border-boss-pink/60 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($agencySupport as $item)
                    <article class="border-b border-boss-pink/60 py-7 md:px-7 md:odd:border-r lg:border-r lg:odd:border-r lg:[&:nth-child(3n)]:border-r-0">
                        <p class="text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-boss-rose">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                        <h3 class="mt-4 font-display text-[1.35rem] text-boss-dark">{{ __($item['title']) }}</h3>
                        <p class="mt-3 text-[0.88rem] leading-6 text-boss-dark/62">{{ __($item['body']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:gap-20">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Clear responsibilities') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Agency support without hiding your responsibilities') }}</h2>
                    <p class="mt-5 text-[0.94rem] leading-7 text-boss-dark/65">{{ __('A useful agency relationship is clear about what the team handles and what remains the model\'s responsibility.') }}</p>
                </div>

                <div class="overflow-hidden border border-boss-pink/60">
                    <div class="grid grid-cols-2 bg-boss-dark px-5 py-4 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-white sm:px-7">
                        <span>{{ __('Paradise Dolls supports') }}</span>
                        <span>{{ __('The model controls') }}</span>
                    </div>
                    @foreach ([
                        ['Onboarding, training, and platform preparation', 'Whether the opportunity is right for her'],
                        ['Account management and technical guidance', 'Her own boundaries and professional conduct'],
                        ['Marketing education and multistreaming support', 'Her working routine, effort, and consistency'],
                        ['Help understanding platform requirements', 'Her tax, legal, and self-employed obligations'],
                    ] as $row)
                        <div class="grid grid-cols-2 gap-5 border-t border-boss-pink/50 px-5 py-5 text-[0.84rem] leading-6 text-boss-dark/68 first:border-t-0 sm:px-7">
                            <p>{{ __($row[0]) }}</p>
                            <p>{{ __($row[1]) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-20 text-boss-ivory sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-start lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Experience behind the system') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight">{{ __('Built from more than 15 years in the industry') }}</h2>
                <p class="mt-6 max-w-xl text-[0.95rem] leading-7 text-boss-ivory/65">{{ __('Founder Kayla created Paradise Dolls after seeing women enter agencies without enough structure, confidence, practical education, or ongoing support. The agency is designed to make those foundations part of the process from the beginning.') }}</p>
                <a href="{{ route('our-story') }}" class="mt-8 inline-flex min-h-11 items-center gap-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-boss-gold transition-colors hover:text-white">
                    {{ __('Read the founder story') }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="M5 12h14m-5-5 5 5-5 5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <div class="border-y border-white/[0.12]">
                @foreach ([
                    ['title' => 'No income promises', 'body' => 'Earnings depend on the model, her consistency, audience engagement, platform performance, and market conditions.'],
                    ['title' => 'Adult work stated clearly', 'body' => 'Applicants are told what the work involves and must be 18 or older before entering the process.'],
                    ['title' => 'Terms before commitment', 'body' => 'Applicants can review the training commitment and self-employed responsibilities before submitting.'],
                ] as $principle)
                    <article class="grid gap-2 border-b border-white/[0.12] py-6 last:border-b-0 sm:grid-cols-[0.75fr_1.25fr] sm:gap-8">
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
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Joining the agency') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3.2rem)] leading-tight text-boss-dark">{{ __('How the Paradise Dolls application process works') }}</h2>
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
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20 lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Before you decide') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Understand the terms, not just the benefits') }}</h2>
                <p class="mt-5 text-[0.94rem] leading-7 text-boss-dark/65">{{ __('There is no charge to submit an application. Approved agency models receive the training programme without an upfront charge subject to a minimum six-month agency term.') }}</p>
            </div>
            <div class="space-y-5 text-[0.92rem] leading-7 text-boss-dark/68">
                <p>{{ __('The training programme has a stated retail value of £1,799. Under the application agreement, a model who leaves before completing the minimum term and continues independently or with another agency may be required to reimburse that value.') }}</p>
                <p>{{ __('Models are self-employed, remain responsible for their own tax and legal obligations, and must follow the rules of each platform they use. Payments may reflect platform earnings, refunds, chargebacks, platform deductions, authorised advances, or other agreed deductions.') }}</p>
                <p class="font-semibold text-boss-dark">{{ __('Read the complete application agreement before accepting it. Ask the team about anything you do not understand before you apply.') }}</p>
                <a href="{{ route('apply') }}" class="inline-flex min-h-11 items-center border-b border-boss-rose text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-boss-rose transition-colors hover:border-boss-dark hover:text-boss-dark">{{ __('Review terms and apply') }}</a>
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ __('Webcam agency FAQ') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Questions to ask before joining a webcam model agency') }}</h2>
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
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Research the opportunity') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Learn more before choosing an agency') }}</h2>
                <p class="mt-5 max-w-xl text-[0.94rem] leading-7 text-boss-ivory/60">{{ __('A good decision starts with understanding the role, the working setup, and how the agency supports you.') }}</p>
            </div>
            <nav aria-label="Webcam agency guidance" class="divide-y divide-white/10 border-y border-white/10">
                @foreach ([
                    ['route' => 'webcam-jobs', 'label' => 'Webcam jobs explained', 'detail' => 'Role, eligibility, self-employed status, and application guidance'],
                    ['route' => 'work-from-home', 'label' => 'Working from home', 'detail' => 'Workspace, privacy, equipment, and remote routines'],
                    ['route' => 'multistreaming', 'label' => 'How multistreaming works', 'detail' => 'Why models may use more than one live platform'],
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
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-dark/55">{{ __('Ready to speak with the team?') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __('Apply privately to Paradise Dolls') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-[0.95rem] leading-7 text-boss-dark/65">{{ __('Applications are reviewed individually. You must be 18 or older, and submitting the form does not guarantee acceptance or earnings.') }}</p>
            <a href="{{ route('apply') }}" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-md bg-boss-dark px-10 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-white transition-colors hover:bg-black">{{ __('Apply Now') }}</a>
        </div>
    </section>
</x-layouts.marketing>
