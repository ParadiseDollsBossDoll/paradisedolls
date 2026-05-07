<x-layouts.marketing :transparentNav="true" :title="__('Home')">
    @php
        $heroImg = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=85&w=1920';
        $workspaceImg = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&q=85&w=1200';
        $villaImg = 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&q=85&w=1200';
        $academyImg = 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&q=85&w=1200';
        $communityImg = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=85&w=1200';
    @endphp

    <section class="relative flex min-h-screen items-center overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-black/40 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pt-24 sm:px-6 lg:px-8">
            <div class="max-w-4xl text-white">
                <p class="mb-6 text-[0.72rem] uppercase tracking-[0.38em] text-boss-gold">{{ config('app.name') }}</p>
                <h1 class="font-display text-[clamp(3.1rem,8vw,7rem)] leading-[0.96] text-white">
                    {{ __('Your Rich Girl Era') }}<br><em>{{ __('starts online') }}</em>
                </h1>
                <p class="mt-7 max-w-2xl text-[1.05rem] leading-relaxed text-white/78">
                    {{ __('A luxury feminine opportunity platform and Boss Doll Blueprint academy for beginners, creators, and ambitious women who want remote income, structure, mentorship, and a supportive community behind them.') }}
                </p>
                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="#apply" class="bg-boss-gold px-9 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:bg-boss-gold-hover">{{ __('Apply Now') }}</a>
                    <a href="{{ route('multistreaming') }}" class="border border-white/40 px-9 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ __('See the System') }}</a>
                </div>
            </div>

            <div class="mt-16 grid max-w-4xl grid-cols-2 gap-3 pb-10 md:grid-cols-4">
                @foreach ([
                    [__('15+'), __('years industry experience')],
                    [__('3'), __('learning formats')],
                    [__('1:1'), __('mentorship structure')],
                    [__('18+'), __('professional onboarding')],
                ] as $stat)
                    <div class="border border-white/20 bg-black/35 px-4 py-4 shadow-lg backdrop-blur-md">
                        <p class="font-display text-[1.65rem] leading-none text-boss-gold-light">{{ $stat[0] }}</p>
                        <p class="mt-2 text-[0.62rem] font-medium uppercase tracking-[0.12em] text-white/75">{{ $stat[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-14 px-4 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Beginner Friendly') }}</p>
                <h2 class="font-display text-[clamp(2.1rem,4vw,3.35rem)] leading-tight text-boss-dark">{{ __('Luxury support without the intimidating agency feeling') }}</h2>
                <p class="mt-6 max-w-2xl text-[0.96rem] leading-relaxed text-boss-dark/62">
                    {{ __('Paradise Dolls is built for women from real backgrounds, not only influencers with huge followings. You bring ambition and consistency. The team brings systems, guidance, onboarding, account preparation, safety standards, and a clear learning path.') }}
                </p>

                <div class="mt-10 grid gap-4 md:grid-cols-2">
                    <div class="border border-boss-pink/60 bg-boss-muted p-6">
                        <h3 class="font-display text-[1.25rem] text-boss-dark">{{ __('The agency handles') }}</h3>
                        <ul class="mt-5 space-y-3 text-[0.86rem] text-boss-dark/62">
                            @foreach ([__('onboarding and account setup'), __('verification preparation'), __('profile guidance'), __('support systems and structure')] as $item)
                                <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-boss-gold"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="border border-boss-pink/60 bg-boss-muted p-6">
                        <h3 class="font-display text-[1.25rem] text-boss-dark">{{ __('You learn') }}</h3>
                        <ul class="mt-5 space-y-3 text-[0.86rem] text-boss-dark/62">
                            @foreach ([__('how to stream professionally'), __('how platforms and tools work'), __('how to engage customers'), __('how to maximise earnings confidently')] as $item)
                                <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-boss-gold"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="aspect-[4/5] overflow-hidden">
                    <img src="{{ $workspaceImg }}" alt="" class="h-full w-full object-cover">
                </div>
                <div class="absolute -bottom-7 -left-4 bg-boss-pink px-7 py-5 shadow-luxe sm:-left-7">
                    <p class="font-display text-[1.8rem] leading-none text-boss-dark">{{ __('Support') }}</p>
                    <p class="mt-1 text-[0.62rem] uppercase tracking-[0.16em] text-boss-dark/55">{{ __('from application to going live') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Freedom & Lifestyle') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Work remotely, build income, and create a life that feels bigger') }}</h2>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ([
                    [$villaImg, __('Paradise living'), __('Tropical locations, villas, cafés, beach clubs, and flexible schedules that make remote income feel tangible.')],
                    [$academyImg, __('Professional systems'), __('Walkthroughs, strategy, equipment guidance, and platform education designed to make the work practical.')],
                    [$communityImg, __('Feminine community'), __('A supportive movement with mentorship, motivation, and structure so members are not left alone.')],
                ] as $card)
                    <div class="group overflow-hidden bg-white shadow-luxe">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="{{ $card[0] }}" alt="" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                        <div class="p-6">
                            <h3 class="font-display text-[1.35rem] text-boss-dark">{{ $card[1] }}</h3>
                            <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $card[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-24 text-boss-ivory">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-14 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div class="border border-white/[0.07] bg-white/[0.035] p-7">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Meet Kayla') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Why Paradise Dolls exists') }}</h2>
                <p class="mt-6 text-[0.92rem] leading-relaxed text-boss-ivory/55">
                    {{ __('Kayla built Paradise Dolls after more than 15 years in the industry, seeing too many women dropped into agencies without the support, confidence, structure, or business education they needed to succeed.') }}
                </p>
                <a href="{{ route('our-story') }}" class="mt-8 inline-flex text-[0.72rem] uppercase tracking-[0.16em] text-boss-gold hover:text-boss-gold-light">{{ __('Read the Founder Story') }} -></a>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ([
                    [__('Survival became strategy'), __('From no safety net to building online businesses, brands, and networks that created real financial freedom.')],
                    [__('Experience became education'), __('The hard lessons became a blueprint for confidence, consistency, branding, mindset, and platform strategy.')],
                    [__('Agency became community'), __('The goal is not to leave girls figuring it out alone. It is support, mentorship, and a team that wants members to win.')],
                    [__('Opportunity became the mission'), __('Paradise Dolls exists to help women step into income, travel, flexibility, and the most successful version of themselves.')],
                ] as $item)
                    <div class="border border-white/[0.07] bg-boss-panel p-6">
                        <h3 class="font-display text-[1.15rem] text-boss-gold-light">{{ $item[0] }}</h3>
                        <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-ivory/48">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('The Core System') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('One stream. Multiple platforms. More visibility.') }}</h2>
                </div>
                <p class="text-[0.95rem] leading-relaxed text-boss-dark/60">
                    {{ __('Paradise Dolls positions multistreaming as the main advantage: simultaneous visibility across platforms, diversified income, stronger traffic, and a smarter system for turning one live session into multiple opportunities.') }}
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ([
                    [__('Traffic'), __('Reach audiences across multiple platforms without multiplying your workload.')],
                    [__('Monetisation'), __('Understand rankings, customer value systems, earnings tools, and retention.')],
                    [__('Confidence'), __('Use walkthroughs to navigate controls, messages, platform tools, and live-stream flow.')],
                ] as $item)
                    <div class="border border-boss-pink/60 bg-boss-muted p-7">
                        <span class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('Multistreaming') }}</span>
                        <h3 class="mt-4 font-display text-[1.35rem] text-boss-dark">{{ $item[0] }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-muted py-24">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Private LMS') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Boss Doll Blueprint') }}</h2>
                <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/62">
                    {{ __('The members area is designed as a luxury feminine streaming operating system, not a generic course library. The core is the walkthrough system: platform navigation, monetisation tools, stream controls, customer interaction, rankings, earnings systems, and customer retention.') }}
                </p>
                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    @foreach ([__('PDF guides with screenshots'), __('Canva-style presentations'), __('Screen-recorded walkthroughs')] as $format)
                        <div class="border border-boss-pink/70 bg-white p-4 text-[0.78rem] leading-relaxed text-boss-dark/62">{{ $format }}</div>
                    @endforeach
                </div>
            </div>

            <div class="bg-boss-dark p-6 text-boss-ivory shadow-luxe">
                <p class="mb-5 text-[0.66rem] uppercase tracking-[0.2em] text-boss-gold">{{ __('Academy order') }}</p>
                <div class="space-y-3">
                    @foreach ([
                        __('Introduction to Kayla & Paradise Dolls'),
                        __('Safety & professionalism'),
                        __('Stream preparation'),
                        __('Equipment & setup guidance'),
                        __('Platform walkthrough systems'),
                        __('Customer psychology and conversion strategy'),
                        __('Passive income, content, and messaging income'),
                    ] as $step)
                        <div class="flex gap-4 border border-white/[0.07] bg-white/[0.035] p-4">
                            <span class="font-display text-boss-gold">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <p class="text-[0.86rem] text-boss-ivory/60">{{ $step }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-pink py-20">
        <div class="mx-auto max-w-5xl px-4 text-center">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Community, Safety, Professionalism') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Glamorous, but grounded') }}</h2>
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                @foreach ([
                    [__('Safety guidance'), __('Clear standards around age, verification, privacy, and professional conduct.')],
                    [__('Structured support'), __('Onboarding, checklists, mentorship, and admin review before training begins.')],
                    [__('All-girl energy'), __('A motivating community that feels aspirational, feminine, and achievable.')],
                ] as $item)
                    <div class="bg-white p-6 text-left shadow-luxe">
                        <h3 class="font-display text-[1.2rem] text-boss-dark">{{ $item[0] }}</h3>
                        <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div class="max-w-3xl">
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Testimonials & Success Stories') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Community wins make the opportunity feel real') }}</h2>
                </div>
                <a href="{{ route('success-stories') }}" class="text-[0.72rem] uppercase tracking-[0.16em] text-boss-gold hover:text-boss-dark">{{ __('View stories') }} -></a>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @forelse ($testimonials as $testimonial)
                    <article class="overflow-hidden bg-boss-muted shadow-luxe">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="{{ $testimonial->displayImage() }}" alt="" class="h-full w-full object-cover">
                        </div>
                        <div class="p-6">
                            @if ($testimonial->result_label)
                                <p class="mb-3 text-[0.66rem] uppercase tracking-[0.18em] text-boss-gold">{{ $testimonial->result_label }}</p>
                            @endif
                            <h3 class="font-display text-[1.3rem] text-boss-dark">{{ $testimonial->headline }}</h3>
                            <p class="mt-4 line-clamp-4 text-[0.88rem] leading-relaxed text-boss-dark/62">{{ $testimonial->quote }}</p>
                            <p class="mt-5 text-[0.72rem] uppercase tracking-[0.14em] text-boss-dark/42">{{ $testimonial->name }}{{ $testimonial->location ? ' - '.$testimonial->location : '' }}</p>
                        </div>
                    </article>
                @empty
                    @foreach ([
                        [__('Beginner confidence'), __('I had no idea where to start, but the structure made everything feel possible instead of overwhelming.'), __('New member')],
                        [__('Remote freedom'), __('The biggest change was feeling like I had support while building something flexible around my life.'), __('Paradise Dolls community')],
                        [__('Professional guidance'), __('The walkthrough approach helped me understand the platforms instead of guessing my way through.'), __('Boss Doll Blueprint')],
                    ] as $story)
                        <article class="bg-boss-muted p-6 shadow-luxe">
                            <p class="mb-3 text-[0.66rem] uppercase tracking-[0.18em] text-boss-gold">{{ $story[0] }}</p>
                            <p class="font-display text-[1.35rem] leading-snug text-boss-dark">"{{ $story[1] }}"</p>
                            <p class="mt-5 text-[0.72rem] uppercase tracking-[0.14em] text-boss-dark/42">{{ $story[2] }}</p>
                        </article>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section id="apply" class="scroll-mt-24 bg-white py-24">
        <div class="mx-auto max-w-2xl px-4">
            <div class="mb-12 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Application') }}</p>
                <h2 class="font-display text-[clamp(1.9rem,4vw,2.75rem)] text-boss-dark">{{ __('Apply to Paradise Dolls') }}</h2>
                <p class="mx-auto mt-4 max-w-xl text-[0.9rem] leading-relaxed text-boss-dark/56">{{ __('No experience is required. The onboarding team reviews every application privately and will guide approved members through the next steps.') }}</p>
            </div>

            @if (session('application_sent'))
                <div class="py-14 text-center">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-boss-pink">
                        <span class="font-display text-[2rem] text-boss-gold">OK</span>
                    </div>
                    <h3 class="mb-4 font-display text-[2rem] text-boss-dark">{{ __('Application Received') }}</h3>
                    <p class="text-[0.95rem] leading-relaxed text-boss-dark/60">{{ __('Thank you for applying. The onboarding team will review your details and contact you with the next step.') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('apply.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="pd-label-light">{{ __('Full Name') }} *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('Your full name') }}" class="pd-input-light mt-2">
                            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <label class="pd-label-light">{{ __('Email Address') }} *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="your@email.com" class="pd-input-light mt-2">
                            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="pd-label-light">{{ __('Phone') }}</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+44..." class="pd-input-light mt-2">
                            <x-input-error class="mt-1.5" :messages="$errors->get('phone')" />
                        </div>
                        <div>
                            <label class="pd-label-light">{{ __('Experience Level') }} *</label>
                            <select name="experience_level" required class="pd-input-light mt-2">
                                <option value="">{{ __('Select your experience level') }}</option>
                                <option value="none" {{ old('experience_level') === 'none' ? 'selected' : '' }}>{{ __('No Experience') }}</option>
                                <option value="beginner" {{ old('experience_level') === 'beginner' ? 'selected' : '' }}>{{ __('Beginner') }}</option>
                                <option value="1-2" {{ old('experience_level') === '1-2' ? 'selected' : '' }}>{{ __('1-2 Years') }}</option>
                                <option value="3+" {{ old('experience_level') === '3+' ? 'selected' : '' }}>{{ __('3+ Years') }}</option>
                            </select>
                            <x-input-error class="mt-1.5" :messages="$errors->get('experience_level')" />
                        </div>
                    </div>

                    <div>
                        <label class="pd-label-light">{{ __('Instagram / TikTok Handle') }}</label>
                        <input type="text" name="social_handle" value="{{ old('social_handle') }}" placeholder="@yourhandle" class="pd-input-light mt-2">
                        <x-input-error class="mt-1.5" :messages="$errors->get('social_handle')" />
                    </div>

                    <div>
                        <label class="pd-label-light">{{ __('Application photos') }}</label>
                        <input type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp" class="pd-input-light mt-2">
                        <p class="mt-2 text-[0.72rem] text-boss-dark/38">{{ __('Upload up to 6 clear photos. JPG, PNG, or WEBP.') }}</p>
                        <x-input-error class="mt-1.5" :messages="$errors->get('photos')" />
                        <x-input-error class="mt-1.5" :messages="$errors->get('photos.*')" />
                    </div>

                    <div>
                        <label class="pd-label-light">{{ __('Message') }}</label>
                        <textarea name="message" rows="4" class="pd-input-light mt-2" placeholder="{{ __('Tell us what kind of freedom, income, or lifestyle you want to build.') }}">{{ old('message') }}</textarea>
                        <x-input-error class="mt-1.5" :messages="$errors->get('message')" />
                    </div>

                    <div class="flex items-start gap-3 bg-boss-cream p-4">
                        <input type="checkbox" name="age_confirmed" id="age-check" value="1" class="mt-1 h-4 w-4 shrink-0 accent-boss-gold" @checked(old('age_confirmed'))>
                        <label for="age-check" class="text-[0.8rem] leading-relaxed text-boss-dark/70">{{ __('I confirm that I am 18 years of age or older and agree to be contacted about my application and onboarding.') }}</label>
                    </div>
                    <x-input-error class="-mt-2" :messages="$errors->get('age_confirmed')" />

                    <button type="submit" class="w-full bg-boss-gold py-4 text-[0.75rem] uppercase tracking-[0.2em] text-white transition-colors hover:bg-boss-gold-hover">{{ __('Submit Application') }}</button>
                    <p class="text-center text-[0.75rem] text-boss-dark/40">{{ __('Approved applicants receive account instructions and the Model Information Form next.') }}</p>
                </form>
            @endif
        </div>
    </section>
</x-layouts.marketing>
