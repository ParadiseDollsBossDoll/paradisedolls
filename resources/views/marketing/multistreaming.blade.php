@php
    $heroImg    = asset('images/14.jpeg');
    $streamImg  = 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&q=85&w=900';
@endphp
<x-layouts.marketing :title="__('Multistreaming')">

    {{-- Hero --}}
    <section class="relative flex min-h-[60vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $heroImg }}');"></div>
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('The System') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,7vw,5rem)] leading-tight">{{ __('The Power of Multistreaming') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1.05rem] leading-relaxed text-white/80">{{ __('One show. Multiple platforms. Multiple incomes.') }}</p>
        </div>
    </section>

    {{-- What makes Paradise Dolls different --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('What Makes Us Different') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('True simultaneous multistreaming') }}</h2>
                    <div class="mt-8 space-y-5 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        <p>{{ __('Paradise Dolls is built around true simultaneous multistreaming. Not switching platforms. Not taking turns. Streaming everywhere at once.') }}</p>
                        <p>{{ __('The highest-earning models today are no longer choosing one side only — they are learning how to combine both systems together intelligently.') }}</p>
                        <p>{{ __("You go live once… the system works everywhere.") }}</p>
                    </div>
                    <a href="{{ route('home') }}#apply" class="mt-10 inline-block bg-boss-gold px-10 py-3.5 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-boss-gold-hover">{{ __('Get Set Up') }}</a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
                    @foreach ([
                        [__('Increased Visibility'), __('Reach multiple audiences simultaneously without multiplying your workload.')],
                        [__('Higher Earnings Per Hour'), __('More viewers across more platforms means more tips, more privates, more income.')],
                        [__('Multiple Income Streams'), __('Earn from several platforms at once — diversify so no single site defines your month.')],
                        [__('Less Risk'), __('If one site is slow or down, the others still perform. Your income stays protected.')],
                        [__('More Private Opportunities'), __('Higher-paying clients exist across premium platforms — multistreaming puts you in front of them.')],
                        [__('Faster Growth'), __('Build your fanbase everywhere at once rather than growing one audience at a time.')],
                    ] as $item)
                        <div class="border border-boss-pink/60 bg-boss-muted p-5">
                            <p class="font-display text-[1.1rem] text-boss-dark">{{ $item[0] }}</p>
                            <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Freemium vs Premium education --}}
    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Understanding the Industry') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('Premium and freemium — both sides working together') }}</h2>
                <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/65">{{ __('Many new models only understand one side of the industry. The highest earners know how to use both systems strategically. This is what Paradise Dolls teaches.') }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Freemium --}}
                <div class="bg-white p-8 shadow-luxe">
                    <div class="mb-6 inline-block bg-boss-pink px-4 py-1.5">
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-boss-dark">{{ __('Freemium / Token Sites') }}</p>
                    </div>
                    <h3 class="font-display text-[1.45rem] text-boss-dark">{{ __('Public rooms. Token tipping. Massive traffic.') }}</h3>
                    <p class="mt-4 text-[0.9rem] leading-relaxed text-boss-dark/62">{{ __('Public chat-based platforms where viewers enter your room for free. Your goal is to entertain, attract attention, build excitement, encourage tipping, and upsell private or exclusive shows.') }}</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('Platforms') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-dark/65">
                                @foreach (['Chaturbate', 'Stripchat', 'BongaCams', 'Cam4', 'CamSoda'] as $p)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-gold shrink-0"></span>{{ $p }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('Best for') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-dark/65">
                                @foreach ([__('Massive traffic'), __('Fan building'), __('Audience growth'), __('Upselling privates'), __('Going viral')] as $b)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-gold shrink-0"></span>{{ $b }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Premium --}}
                <div class="bg-boss-dark p-8 shadow-luxe text-boss-ivory">
                    <div class="mb-6 inline-block bg-boss-gold/20 border border-boss-gold/30 px-4 py-1.5">
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-boss-gold">{{ __('Premium Sites') }}</p>
                    </div>
                    <h3 class="font-display text-[1.45rem] text-boss-ivory">{{ __('Pay-per-minute. Private sessions. Quality spenders.') }}</h3>
                    <p class="mt-4 text-[0.9rem] leading-relaxed text-boss-ivory/55">{{ __('Premium websites charge customers per minute for private access to you. Fewer viewers — but the customers are often far more serious spenders looking for direct attention and one-on-one experiences.') }}</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('Platforms') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-ivory/60">
                                @foreach (['Streamate', 'AdultWork', 'Babestation', 'Xpanded / XXXpanded'] as $p)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-gold shrink-0"></span>{{ $p }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <p class="mb-3 text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold">{{ __('Best for') }}</p>
                            <ul class="space-y-1.5 text-[0.86rem] text-boss-ivory/60">
                                @foreach ([__('Higher quality spenders'), __('Private income'), __('Loyal regulars'), __('Longer sessions'), __('Direct earnings')] as $b)
                                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-boss-gold shrink-0"></span>{{ $b }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How the industry is changing --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('How the Industry is Changing') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ __('The lines are blurring. Smart models are ahead.') }}</h2>
                    <div class="mt-8 space-y-5 text-[0.95rem] leading-relaxed text-boss-dark/65">
                        <p>{{ __('Premium websites are beginning to adopt freemium-style features because they realise modern customers enjoy tipping, interacting publicly, games, menus, and live engagement.') }}</p>
                        <p>{{ __('Sites like Streamate and AdultWork now allow models to use tip menus, receive tips publicly, and create interactive rooms — blending token culture with premium earning.') }}</p>
                        <p>{{ __("This means modern creators can now combine BOTH sectors together. Freemium helps you build fans. Premium helps you maximise earnings. One side feeds the other.") }}</p>
                    </div>
                </div>
                <div class="border border-boss-pink/40 bg-boss-muted p-8">
                    <p class="mb-6 text-[0.7rem] uppercase tracking-[0.2em] text-boss-gold">{{ __('The Smart Strategy') }}</p>
                    <div class="space-y-5">
                        @foreach ([
                            [__('Stream publicly on'), __('Freemium platforms (Chaturbate, Stripchat, CamSoda) to build traffic, visibility, and fans.')],
                            [__('Convert and earn on'), __('Premium platforms (Streamate, AdultWork) for higher-paying private clients and serious spenders.')],
                            [__('Compound your income'), __('Both sides working simultaneously — the system earns across every platform at once.')],
                        ] as $step)
                            <div class="flex gap-5">
                                <span class="font-display text-[2rem] leading-none text-boss-pink shrink-0">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <div>
                                    <p class="font-medium text-boss-dark text-[0.9rem]">{{ $step[0] }}</p>
                                    <p class="mt-1 text-[0.86rem] leading-relaxed text-boss-dark/60">{{ $step[1] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Built for maximum earnings --}}
    <section class="bg-boss-dark py-24 text-boss-ivory">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Built For Maximum Earnings') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ __('Paradise Dolls teaches both sectors') }}</h2>
                <p class="mx-auto mt-5 max-w-2xl text-[0.95rem] leading-relaxed text-boss-ivory/55">{{ __('We are not teaching girls to rely on only one website or one income source. We teach you how to build traffic, build fans, convert viewers, maximise private earnings, diversify your income, and create long-term online success.') }}</p>
            </div>
            <div class="mx-auto grid max-w-4xl gap-4 sm:grid-cols-3">
                @foreach ([
                    [__('Full setup'), __('Multi-platform onboarding and optimised stream quality guidance.')],
                    [__('Smart scheduling'), __('Platform-aware session timing to reach the right viewers at the right moments.')],
                    [__('Monetisation strategy'), __('Tips, privates, goals, games, exclusive content — all taught inside the Boss Doll Blueprint.')],
                ] as $item)
                    <div class="border border-white/[0.07] bg-white/[0.03] p-6">
                        <h3 class="font-display text-[1.1rem] text-boss-gold-light">{{ $item[0] }}</h3>
                        <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-ivory/45">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-boss-pink py-20 text-center">
        <div class="mx-auto max-w-2xl px-4">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ __('Start the System') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] text-boss-dark">{{ __('You go live once. The system works everywhere.') }}</h2>
            <a href="{{ route('home') }}#apply" class="mt-10 inline-block bg-boss-dark px-12 py-4 text-[0.72rem] uppercase tracking-[0.18em] text-white transition-colors hover:bg-boss-gold">{{ __('Apply to Paradise Dolls') }}</a>
        </div>
    </section>

</x-layouts.marketing>
