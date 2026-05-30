<x-layouts.marketing :transparentNav="true" :title="__('Home')">
    @php
        $heroImg = marketing_image('home.hero.image');
        $workspaceImg = marketing_image('home.intro.image');
        $heroParagraphs = marketing_paragraphs('home.hero.body');
        $lifestyleCards = marketing_items('home.lifestyle.cards');
        $founderCards = marketing_items('home.founder.cards');
        $systemCards = marketing_items('home.system.cards');
        $blueprintFormats = marketing_items('home.blueprint.formats');
        $blueprintOrder = marketing_items('home.blueprint.order_items');
        $groundedCards = marketing_items('home.grounded.cards');
        $countryCallingCodes = config('country_calling_codes', []);
        $selectedPhoneCountry = old('phone_country', 'GB');
        $phoneCountries = collect($countryCallingCodes)
            ->map(fn (array $country, string $countryCode) => [
                'value'   => $countryCode,
                'name'    => $country['name'],
                'code'    => $country['code'],
                'dialNum' => (int) ltrim($country['code'], '+'),
                'flag'    => 'https://flagcdn.com/w40/'.strtolower($countryCode).'.png',
            ])
            ->sortBy('dialNum')
            ->values();
    @endphp

    <section class="relative flex min-h-screen items-center overflow-hidden">
        <img src="{{ $heroImg }}" alt="" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-black/40 to-boss-dark/95"></div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pt-24 sm:px-6 lg:px-8">
            <div class="max-w-4xl text-white">
                <h1 class="font-display text-[clamp(3.1rem,8vw,7rem)] leading-[0.96] text-white">
                    {{ marketing_content('home.hero.title') }}
                </h1>
                <p class="mt-7 max-w-2xl text-[1.05rem] leading-relaxed text-white/78">
                    @foreach ($heroParagraphs as $paragraph)
                        {{ $paragraph }}@if (! $loop->last)<br><br>@endif
                    @endforeach
                </p>
                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="{{ marketing_link('home.hero.primary_url', '#apply') }}" class="rounded-md bg-[#EEB4C3] px-9 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:bg-[#e0a0b5]">{{ marketing_content('home.hero.primary_label') }}</a>
                    <a href="{{ marketing_link('home.hero.secondary_url', route('multistreaming')) }}" class="rounded-md border border-white/40 px-9 py-3.5 text-[0.72rem] uppercase tracking-[0.16em] text-white transition-colors hover:border-white hover:bg-white hover:text-boss-dark">{{ marketing_content('home.hero.secondary_label') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-14 px-4 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('home.intro.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(2.1rem,4vw,3.35rem)] leading-tight text-boss-dark">{{ marketing_content('home.intro.title') }}</h2>
                <p class="mt-6 max-w-2xl text-[0.96rem] leading-relaxed text-boss-dark/62">
                    {{ marketing_content('home.intro.body') }}
                </p>

                <div class="mt-10 grid gap-4 md:grid-cols-2">
                    <div class="border border-boss-pink/60 bg-boss-muted p-6">
                        <h3 class="font-display text-[1.25rem] text-boss-dark">{{ marketing_content('home.intro.agency_title') }}</h3>
                        <ul class="mt-5 space-y-3 text-[0.86rem] text-boss-dark/62">
                            @foreach (marketing_items('home.intro.agency_items') as $item)
                                <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-boss-rose"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="border border-boss-pink/60 bg-boss-muted p-6">
                        <h3 class="font-display text-[1.25rem] text-boss-dark">{{ marketing_content('home.intro.learn_title') }}</h3>
                        <ul class="mt-5 space-y-3 text-[0.86rem] text-boss-dark/62">
                            @foreach (marketing_items('home.intro.learn_items') as $item)
                                <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-boss-rose"></span>{{ $item }}</li>
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
                    <p class="font-display text-[1.8rem] leading-none text-boss-dark">{{ marketing_content('home.intro.badge_title') }}</p>
                    <p class="mt-1 text-[0.62rem] uppercase tracking-[0.16em] text-boss-dark/55">{{ marketing_content('home.intro.badge_text') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('home.lifestyle.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('home.lifestyle.title') }}</h2>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($lifestyleCards as $card)
                    <div class="group overflow-hidden bg-white shadow-luxe">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="{{ \App\Support\MarketingContent::imageUrl($card['image'] ?? '') }}" alt="" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                        <div class="p-6">
                            <h3 class="font-display text-[1.35rem] text-boss-dark">{{ $card['title'] ?? '' }}</h3>
                            <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $card['body'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-dark py-24 text-boss-ivory">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-14 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div class="border border-white/[0.07] bg-white/[0.035] p-7">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('home.founder.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight">{{ marketing_content('home.founder.title') }}</h2>
                <p class="mt-6 text-[0.92rem] leading-relaxed text-boss-ivory/55">
                    {{ marketing_content('home.founder.body') }}
                </p>
                <a href="{{ marketing_link('home.founder.link_url', route('our-story')) }}" class="mt-8 inline-flex text-[0.72rem] uppercase tracking-[0.16em] text-boss-gold hover:text-boss-gold-light">{{ marketing_content('home.founder.link_label') }} -></a>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($founderCards as $item)
                    <div class="border border-white/[0.07] bg-boss-panel p-6">
                        <h3 class="font-display text-[1.15rem] text-boss-gold-light">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-ivory/48">{{ $item['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                <div>
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('home.system.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('home.system.title') }}</h2>
                </div>
                <p class="text-[0.95rem] leading-relaxed text-boss-dark/60">
                    {{ marketing_content('home.system.body') }}
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($systemCards as $item)
                    <div class="border border-boss-pink/60 bg-boss-muted p-7">
                        <span class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-rose">{{ __('Multistreaming') }}</span>
                        <h3 class="mt-4 font-display text-[1.35rem] text-boss-dark">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-3 text-[0.86rem] leading-relaxed text-boss-dark/58">{{ $item['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-boss-muted py-24">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
            <div>
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('home.blueprint.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('home.blueprint.title') }}</h2>
                <p class="mt-6 text-[0.95rem] leading-relaxed text-boss-dark/62">
                    {{ marketing_content('home.blueprint.body') }}
                </p>
                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    @foreach ($blueprintFormats as $format)
                        <div class="border border-boss-pink/70 bg-white p-4 text-[0.78rem] leading-relaxed text-boss-dark/62">{{ $format }}</div>
                    @endforeach
                </div>
            </div>

            <div class="bg-boss-dark p-6 text-boss-ivory shadow-luxe">
                <p class="mb-5 text-[0.66rem] uppercase tracking-[0.2em] text-boss-gold">{{ marketing_content('home.blueprint.order_title') }}</p>
                <div class="space-y-3">
                    @foreach ($blueprintOrder as $step)
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
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-gold">{{ marketing_content('home.grounded.eyebrow') }}</p>
            <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('home.grounded.title') }}</h2>
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                @foreach ($groundedCards as $item)
                    <div class="bg-white p-6 text-left shadow-luxe">
                        <h3 class="font-display text-[1.2rem] text-boss-dark">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-3 text-[0.84rem] leading-relaxed text-boss-dark/58">{{ $item['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#f3f3f5] py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div class="max-w-3xl">
                    <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('home.testimonials.eyebrow') }}</p>
                    <h2 class="font-display text-[clamp(2rem,4vw,3rem)] leading-tight text-boss-dark">{{ marketing_content('home.testimonials.title') }}</h2>
                </div>
                <a href="{{ route('success-stories') }}" class="text-[0.72rem] uppercase tracking-[0.16em] text-boss-rose hover:text-boss-dark">{{ marketing_content('home.testimonials.link_label') }} -></a>
            </div>

            @php
                $testimonialSlides = $testimonials->isNotEmpty()
                    ? $testimonials->map(fn ($testimonial) => [
                        'name' => $testimonial->name,
                        'handle' => $testimonial->displayHandle(),
                        'quote' => $testimonial->quote,
                        'tag' => $testimonial->displayHashtag(),
                        'image' => $testimonial->displayAvatar(),
                    ])
                    : collect([
                        [
                            'name' => __('New Member'),
                            'handle' => '@newmember',
                            'quote' => __('I had no idea where to start, but the structure made everything feel possible instead of overwhelming.'),
                            'tag' => '#BeginnerConfidence',
                            'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=85&w=300',
                        ],
                        [
                            'name' => __('Paradise Doll'),
                            'handle' => '@paradisedoll',
                            'quote' => __('The biggest change was feeling like I had support while building something flexible around my life.'),
                            'tag' => '#RemoteFreedom',
                            'image' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&q=85&w=300',
                        ],
                        [
                            'name' => __('Blueprint Member'),
                            'handle' => '@blueprintmember',
                            'quote' => __('The walkthrough approach helped me understand the platforms instead of guessing my way through.'),
                            'tag' => '#ProfessionalGuidance',
                            'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=85&w=300',
                        ],
                        [
                            'name' => __('Community Member'),
                            'handle' => '@communitymember',
                            'quote' => __('Having a private place to learn, ask questions, and grow made the whole process feel real.'),
                            'tag' => '#SupportSystem',
                            'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=85&w=300',
                        ],
                    ]);

                $baseSlides = $testimonialSlides->values();
                while ($testimonialSlides->count() < 4) {
                    $testimonialSlides = $testimonialSlides->concat($baseSlides)->values();
                }

                $carouselSlides = $testimonialSlides->values()->concat($testimonialSlides->values());
            @endphp

            <div class="pd-testimonial-carousel -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="pd-testimonial-track gap-5">
                    @foreach ($carouselSlides as $story)
                        <article class="h-[17.5rem] w-[17.5rem] shrink-0 rounded-lg bg-white p-5 shadow-[0_18px_45px_rgba(15,15,20,0.08)] sm:w-[19.5rem]">
                            <div class="flex items-center gap-3">
                                <img src="{{ $story['image'] }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover">
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-[0.86rem] font-semibold leading-tight text-[#06070b]">{{ $story['name'] }}</h3>
                                    <p class="mt-1 truncate text-[0.78rem] leading-tight text-[#6f7280]">{{ $story['handle'] }}</p>
                                </div>
                            </div>

                            <p class="mt-6 line-clamp-5 text-[0.96rem] leading-[1.45] text-[#151821]">{{ $story['quote'] }}</p>

                            @if ($story['tag'])
                                <p class="mt-2 truncate text-[0.9rem] leading-tight text-boss-rose">{{ $story['tag'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="apply" class="scroll-mt-24 bg-white py-24">
        <div class="mx-auto max-w-2xl px-4">
            <div class="mb-12 text-center">
                <p class="mb-4 text-[0.7rem] uppercase tracking-[0.3em] text-boss-rose">{{ marketing_content('home.apply.eyebrow') }}</p>
                <h2 class="font-display text-[clamp(1.9rem,4vw,2.75rem)] text-boss-dark">{{ marketing_content('home.apply.title') }}</h2>
                <p class="mx-auto mt-4 max-w-xl text-[0.9rem] leading-relaxed text-boss-dark/56">{{ marketing_content('home.apply.body') }}</p>
            </div>

            @if (session('application_sent'))
                <div class="py-14 text-center">
                    {{-- Gold tick icon with glow --}}
                    <div class="relative mx-auto mb-7 h-20 w-20">
                        <div class="absolute inset-0 scale-125 rounded-full bg-gradient-to-br from-[#C9A96E]/25 to-[#EEB4C3]/20 blur-xl"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-full border border-[#C9A96E]/35 bg-gradient-to-br from-[#fffcf7] to-[#fff5e8] shadow-[0_8px_28px_rgba(201,169,110,0.22)]">
                            <svg viewBox="0 0 24 24" class="h-9 w-9 text-[#C9A96E]" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12l5 5L20 7"/>
                            </svg>
                        </div>
                        {{-- Small sparkle decorations --}}
                        <span class="absolute -right-1 -top-1 text-[0.9rem] leading-none">✨</span>
                    </div>
                    <h3 class="mb-4 font-display text-[2rem] text-boss-dark">{{ marketing_content('home.apply.success_title') }}</h3>
                    <p class="whitespace-pre-line text-[0.95rem] leading-relaxed text-boss-dark/60">{{ marketing_content('home.apply.success_body') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('apply.store') }}" enctype="multipart/form-data" class="space-y-5" data-application-form>
                    @csrf

                    @if ($referralReferrer)
                        <div class="border border-boss-rose/25 bg-boss-pink/30 px-4 py-3 text-[0.82rem] leading-relaxed text-boss-dark/65">
                            {{ __('You were referred by :name. Submit your application below and the onboarding team will review it privately.', ['name' => $referralReferrer->name]) }}
                        </div>
                    @endif

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="application-name" class="pd-label-light">{{ __('Full Name') }} *</label>
                            <input id="application-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="{{ __('Your full name') }}" class="pd-input-light mt-2">
                            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <label for="application-email" class="pd-label-light">{{ __('Email Address') }} *</label>
                            <input id="application-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="your@email.com" class="pd-input-light mt-2" data-email-address aria-describedby="application-email-feedback">
                            <p id="application-email-feedback" class="mt-1.5 hidden text-[0.76rem] leading-relaxed text-red-600" data-email-feedback></p>
                            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <fieldset>
                            <legend class="sr-only">{{ __('Phone') }}</legend>
                            <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-2">
                                <div
                                    class="relative"
                                    x-data="{
                                        open: false,
                                        search: '',
                                        selected: @js($selectedPhoneCountry),
                                        countries: @js($phoneCountries),
                                        get current() {
                                            return this.countries.find((c) => c.value === this.selected) || this.countries[0];
                                        },
                                        get filtered() {
                                            const q = this.search.trim().toLowerCase();
                                            if (!q) return this.countries;
                                            return this.countries.filter(c =>
                                                c.code.replace('+','').startsWith(q) ||
                                                c.code.includes(q) ||
                                                c.name.toLowerCase().includes(q)
                                            );
                                        },
                                        openDropdown() {
                                            this.open = true;
                                            this.$nextTick(() => this.$refs.countrySearch?.focus());
                                        },
                                        selectCountry(value) {
                                            this.selected = value;
                                            this.search = '';
                                            this.open = false;
                                        },
                                    }"
                                    @keydown.escape.window="open = false; search = ''"
                                    @click.outside="open = false; search = ''"
                                >
                                    <label id="application-phone-country-label" class="pd-label-light normal-case tracking-normal leading-[1.5]">{{ __('Country code') }}</label>
                                    <input type="hidden" name="phone_country" x-model="selected" data-phone-country>
                                    <button
                                        type="button"
                                        class="pd-input-light mt-2 flex h-[3.35rem] items-center gap-2 px-3 text-left"
                                        aria-labelledby="application-phone-country-label"
                                        aria-haspopup="listbox"
                                        :aria-expanded="open.toString()"
                                        @click="openDropdown()"
                                    >
                                        <img :src="current.flag" :alt="current.name" class="h-3.5 w-5 rounded-[2px] object-cover shadow-sm">
                                        <span class="min-w-0 flex-1 text-[0.86rem]" x-text="current.code"></span>
                                        <svg class="h-3 w-3 text-boss-dark/35 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="open"
                                        x-transition
                                        class="absolute left-0 top-full z-50 mt-1 w-52 overflow-hidden rounded-md border border-boss-pink bg-white shadow-luxe"
                                        role="listbox"
                                        aria-labelledby="application-phone-country-label"
                                    >
                                        {{-- Search input --}}
                                        <div class="border-b border-boss-pink/30 p-2">
                                            <input
                                                x-ref="countrySearch"
                                                type="text"
                                                x-model="search"
                                                placeholder="{{ __('Type code or country…') }}"
                                                class="w-full rounded-md border border-boss-pink/40 bg-boss-cream/50 px-2.5 py-1.5 text-[0.8rem] text-boss-dark placeholder-boss-dark/35 outline-none focus:border-boss-rose/60 focus:ring-1 focus:ring-boss-rose/30"
                                                @keydown.escape.stop="open = false; search = ''"
                                                autocomplete="off"
                                            >
                                        </div>
                                        {{-- Results list --}}
                                        <div class="max-h-56 overflow-y-auto py-1">
                                            <template x-if="filtered.length === 0">
                                                <p class="px-3 py-3 text-center text-[0.78rem] text-boss-dark/40">{{ __('No results') }}</p>
                                            </template>
                                            <template x-for="country in filtered" :key="country.value">
                                                <button
                                                    type="button"
                                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-[0.84rem] text-boss-dark transition-colors hover:bg-boss-cream"
                                                    :class="selected === country.value ? 'bg-boss-pink/45 text-boss-rose font-medium' : ''"
                                                    role="option"
                                                    :aria-selected="(selected === country.value).toString()"
                                                    :title="country.name"
                                                    @click="selectCountry(country.value)"
                                                >
                                                    <img :src="country.flag" :alt="country.name" class="h-3.5 w-5 shrink-0 rounded-[2px] object-cover shadow-sm">
                                                    <span class="shrink-0 font-medium" x-text="country.code"></span>
                                                    <span class="min-w-0 truncate text-[0.72rem] text-boss-dark/50" x-text="country.name"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="application-phone-number" class="pd-label-light normal-case tracking-normal leading-[1.5]">{{ __('Phone number') }}</label>
                                    <input
                                        id="application-phone-number"
                                        type="tel"
                                        name="phone_number"
                                        value="{{ old('phone_number') }}"
                                        inputmode="tel"
                                        autocomplete="tel-national"
                                        placeholder="201-555-5555"
                                        pattern="[0-9\s().-]{6,24}"
                                        title="{{ __('Use 6 to 15 digits after the country code.') }}"
                                        class="pd-input-light mt-2 h-[3.35rem]"
                                        data-phone-number
                                    >
                                </div>
                            </div>
                            <p class="mt-2 text-[0.72rem] leading-relaxed text-boss-dark/38">{{ __('Choose your country code, then enter the local number without the + sign.') }}</p>
                            <x-input-error class="mt-1.5" :messages="$errors->get('phone_country')" />
                            <x-input-error class="mt-1.5" :messages="$errors->get('phone_number')" />
                        </fieldset>
                        <div>
                            <label for="application-experience" class="pd-label-light">{{ __('Experience Level') }} *</label>
                            <select id="application-experience" name="experience_level" required class="pd-input-light mt-2">
                                <option value="">{{ __('Select your experience level') }}</option>
                                <option value="none" {{ old('experience_level') === 'none' ? 'selected' : '' }}>{{ __('No Experience') }}</option>
                                <option value="beginner" {{ old('experience_level') === 'beginner' ? 'selected' : '' }}>{{ __('Beginner') }}</option>
                                <option value="1-2" {{ old('experience_level') === '1-2' ? 'selected' : '' }}>{{ __('1-2 Years') }}</option>
                                <option value="3+" {{ old('experience_level') === '3+' ? 'selected' : '' }}>{{ __('3+ Years') }}</option>
                            </select>
                            <x-input-error class="mt-1.5" :messages="$errors->get('experience_level')" />
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="application-instagram" class="pd-label-light">{{ __('Instagram Handle') }}</label>
                            <input id="application-instagram" type="text" name="instagram_handle" value="{{ old('instagram_handle') }}" autocomplete="off" placeholder="@yourinstagram" class="pd-input-light mt-2">
                            <x-input-error class="mt-1.5" :messages="$errors->get('instagram_handle')" />
                        </div>
                        <div>
                            <label for="application-tiktok" class="pd-label-light">{{ __('TikTok Handle') }}</label>
                            <input id="application-tiktok" type="text" name="tiktok_handle" value="{{ old('tiktok_handle') }}" autocomplete="off" placeholder="@yourtiktok" class="pd-input-light mt-2">
                            <x-input-error class="mt-1.5" :messages="$errors->get('tiktok_handle')" />
                        </div>
                    </div>

                    <div>
                        <label for="application-referral-code" class="pd-label-light">
                            {{ __('Referral Code') }}
                            <span class="ml-1 text-[0.72rem] normal-case tracking-normal font-normal text-boss-dark/40">({{ __('optional') }})</span>
                        </label>
                        <input id="application-referral-code" type="text" name="referral_code" value="{{ old('referral_code', $referralCode) }}" autocomplete="off" placeholder="{{ __('e.g. ABC123') }}" class="pd-input-light mt-2" data-referral-code>
                        <x-input-error class="mt-1.5" :messages="$errors->get('referral_code')" />
                    </div>

                    <div>
                        <label for="application-photos" class="pd-label-light">{{ __('Application photos') }}</label>
                        <div class="mt-2">
                            <input id="application-photos" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp" class="sr-only" data-photo-input>
                            <label
                                for="application-photos"
                                class="flex min-h-36 cursor-pointer flex-col items-center justify-center border border-dashed border-boss-pink bg-boss-muted px-4 py-8 text-center transition-colors hover:border-boss-rose hover:bg-boss-pink/30"
                                data-photo-dropzone
                            >
                                <span class="text-[0.68rem] uppercase tracking-[0.16em] text-boss-rose">{{ __('Drop photos here') }}</span>
                                <span class="mt-2 text-[0.9rem] font-medium text-boss-dark">{{ __('Drag and drop, or click to browse') }}</span>
                                <span class="mt-1 text-[0.72rem] text-boss-dark/42" data-photo-summary>{{ __('No photos selected') }}</span>
                            </label>

                            <p class="mt-2 hidden text-[0.76rem] leading-relaxed text-red-600" data-photo-error></p>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2" data-photo-preview></div>
                        </div>
                        <p class="mt-2 text-[0.72rem] text-boss-dark/38">{{ __('Upload up to 6 clear photos. JPG, PNG, or WEBP.') }}</p>
                        <x-input-error class="mt-1.5" :messages="$errors->get('photos')" />
                        <x-input-error class="mt-1.5" :messages="$errors->get('photos.*')" />
                    </div>

                    <div>
                        <label for="application-message" class="pd-label-light">{{ __('Message') }}</label>
                        <textarea id="application-message" name="message" rows="4" class="pd-input-light mt-2" placeholder="{{ __('Tell us what kind of freedom, income, or lifestyle you want to build.') }}">{{ old('message') }}</textarea>
                        <x-input-error class="mt-1.5" :messages="$errors->get('message')" />
                    </div>

                    <div class="flex items-start gap-3 bg-boss-cream p-4">
                        <input type="checkbox" name="age_confirmed" id="age-check" value="1" class="mt-1 h-4 w-4 shrink-0 accent-boss-rose" @checked(old('age_confirmed'))>
                        <label for="age-check" class="text-[0.8rem] leading-relaxed text-boss-dark/70">{{ __('I confirm that I am 18 years of age or older and agree to be contacted about my application and onboarding.') }}</label>
                    </div>
                    <x-input-error class="-mt-2" :messages="$errors->get('age_confirmed')" />

                    <button type="submit" class="w-full rounded-md bg-[#EEB4C3] py-4 text-[0.75rem] uppercase tracking-[0.2em] text-white transition-colors hover:bg-[#e0a0b5]">{{ __('Submit Application') }}</button>
                    <p class="text-center text-[0.75rem] text-boss-dark/40">{{ marketing_content('home.apply.footer_note') }}</p>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var input = document.querySelector('[data-referral-code]');
                        if (!input || input.value.trim() !== '') return;
                        var params = new URLSearchParams(window.location.search);
                        var code = params.get('ref') || params.get('referral') || params.get('referralCode') || '';
                        if (code.trim()) input.value = code.trim();
                    });
                </script>
            @endif
        </div>
    </section>
</x-layouts.marketing>
