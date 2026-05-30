<x-member-layout>
    @php
        $selectedPlatforms = old('platforms', $profile->platforms ?? []);
        $selectedPlatforms = is_array($selectedPlatforms) ? $selectedPlatforms : [];
        $selectedEquipment = old('equipment', $profile->equipment ?? []);
        $selectedEquipment = is_array($selectedEquipment) ? $selectedEquipment : [];
        $selectedPhoneCountry = old('phone_country', $selectedPhoneCountry);
        $phoneNumber = old('phone_number', $phoneNumber);
        $currentCountry = old('country', $profile->country);
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <header>
            <p class="pd-kicker">{{ __('Onboarding') }}</p>
            <h1 class="pd-heading pd-text-gradient mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Model Information Form') }}</h1>
        </header>

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('member.onboarding.update') }}" class="space-y-6" data-phone-form>
            @csrf
            @method('PUT')

            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 1') }}</p>
                        <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Identity & Contact') }}</h2>
                    </div>
                    @if ($profile->information_submitted_at)
                        <span class="pd-badge">{{ __('Submitted') }}</span>
                    @endif
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="legal_name" class="pd-label">{{ __('Legal name') }}</label>
                        <input id="legal_name" name="legal_name" value="{{ old('legal_name', $profile->legal_name) }}" class="pd-input mt-2" required>
                    </div>
                    <div>
                        <label for="stage_name" class="pd-label">{{ __('Stage name') }}</label>
                        <input id="stage_name" name="stage_name" value="{{ old('stage_name', $profile->stage_name) }}" class="pd-input mt-2" required>
                    </div>
                    <div>
                        <label for="date_of_birth" class="pd-label">{{ __('Date of birth') }}</label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth?->format('Y-m-d')) }}" class="pd-input mt-2" required>
                    </div>
                    <fieldset data-phone-field>
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
                                <label id="onboarding-phone-country-label" class="pd-label normal-case tracking-normal leading-[1.5]">{{ __('Country code') }}</label>
                                <input type="hidden" name="phone_country" x-model="selected" data-phone-country>
                                <button
                                    type="button"
                                    class="pd-input mt-2 flex h-[2.8rem] items-center gap-2 px-3 py-0 text-left"
                                    aria-labelledby="onboarding-phone-country-label"
                                    aria-haspopup="listbox"
                                    :aria-expanded="open.toString()"
                                    @click="openDropdown()"
                                >
                                    <img :src="current.flag" :alt="current.name" class="h-3.5 w-5 rounded-[2px] object-cover shadow-sm">
                                    <span class="min-w-0 flex-1 text-[0.82rem]" x-text="current.code"></span>
                                    <svg class="h-3 w-3 shrink-0 text-boss-ivory/35 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                                </button>

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-transition
                                    class="absolute left-0 top-full z-50 mt-1 w-56 overflow-hidden rounded-md border border-white/[0.08] bg-[#161114] shadow-luxe"
                                    role="listbox"
                                    aria-labelledby="onboarding-phone-country-label"
                                >
                                    {{-- Search input --}}
                                    <div class="border-b border-white/[0.06] p-2">
                                        <input
                                            x-ref="countrySearch"
                                            type="text"
                                            x-model="search"
                                            placeholder="{{ __('Type code or country…') }}"
                                            class="w-full rounded-md border border-white/[0.08] bg-white/[0.05] px-2.5 py-1.5 text-[0.78rem] text-boss-ivory placeholder-boss-ivory/30 outline-none focus:border-boss-gold/40 focus:ring-1 focus:ring-boss-gold/20"
                                            @keydown.escape.stop="open = false; search = ''"
                                            autocomplete="off"
                                        >
                                    </div>
                                    {{-- Results --}}
                                    <div class="max-h-56 overflow-y-auto py-1">
                                        <template x-if="filtered.length === 0">
                                            <p class="px-3 py-3 text-center text-[0.75rem] text-boss-ivory/30">{{ __('No results') }}</p>
                                        </template>
                                        <template x-for="country in filtered" :key="country.value">
                                            <button
                                                type="button"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-[0.82rem] text-boss-ivory/68 transition-colors hover:bg-white/[0.05] hover:text-boss-ivory"
                                                :class="selected === country.value ? 'bg-boss-gold/12 text-boss-gold font-medium' : ''"
                                                role="option"
                                                :aria-selected="(selected === country.value).toString()"
                                                :title="country.name"
                                                @click="selectCountry(country.value)"
                                            >
                                                <img :src="country.flag" :alt="country.name" class="h-3.5 w-5 shrink-0 rounded-[2px] object-cover shadow-sm">
                                                <span class="shrink-0 font-medium" x-text="country.code"></span>
                                                <span class="min-w-0 truncate text-[0.7rem] text-boss-ivory/40" x-text="country.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="onboarding-phone-number" class="pd-label normal-case tracking-normal leading-[1.5]">{{ __('Phone number') }}</label>
                                <input
                                    id="onboarding-phone-number"
                                    type="tel"
                                    name="phone_number"
                                    value="{{ $phoneNumber }}"
                                    inputmode="tel"
                                    autocomplete="tel-national"
                                    placeholder="201-555-5555"
                                    pattern="[0-9\s().-]{6,24}"
                                    title="{{ __('Use 6 to 15 digits after the country code.') }}"
                                    class="pd-input mt-2 h-[2.8rem]"
                                    data-phone-number
                                    required
                                >
                            </div>
                        </div>
                        <p class="mt-2 text-[0.72rem] leading-relaxed text-boss-ivory/35">{{ __('Choose your country code, then enter the local number without the + sign.') }}</p>
                        <x-input-error class="mt-1.5" :messages="$errors->get('phone_country')" />
                        <x-input-error class="mt-1.5" :messages="$errors->get('phone_number')" />
                    </fieldset>
                    <div>
                        <label for="country" class="pd-label">{{ __('Country') }}</label>
                        <select id="country" name="country" class="pd-input mt-2" autocomplete="country-name" required>
                            <option value="">{{ __('Select country') }}</option>
                            @if ($currentCountry && ! in_array($currentCountry, $countryOptions, true))
                                <option value="{{ $currentCountry }}" selected>{{ $currentCountry }}</option>
                            @endif
                            @foreach ($countryOptions as $countryOption)
                                <option value="{{ $countryOption }}" @selected($currentCountry === $countryOption)>{{ $countryOption }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-1.5" :messages="$errors->get('country')" />
                    </div>
                    <div>
                        <label for="city" class="pd-label">{{ __('City') }}</label>
                        <input id="city" name="city" value="{{ old('city', $profile->city) }}" autocomplete="address-level2" placeholder="{{ __('City') }}" class="pd-input mt-2">
                        <x-input-error class="mt-1.5" :messages="$errors->get('city')" />
                    </div>
                    {{-- Basic Info extras --}}
                    <div>
                        <label for="nationality" class="pd-label">{{ __('Nationality') }}</label>
                        <input id="nationality" name="nationality" value="{{ old('nationality', $profile->nationality) }}" class="pd-input mt-2" placeholder="{{ __('e.g. British') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('nationality')" />
                    </div>
                    <div>
                        <label for="spoken_languages" class="pd-label">{{ __('Spoken languages') }}</label>
                        <input id="spoken_languages" name="spoken_languages" value="{{ old('spoken_languages', $profile->spoken_languages) }}" class="pd-input mt-2" placeholder="{{ __('e.g. English') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('spoken_languages')" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="social_handles" class="pd-label">{{ __('Social media handles') }}</label>
                        <input id="social_handles" name="social_handles" value="{{ old('social_handles', $profile->social_handles) }}" class="pd-input mt-2" placeholder="{{ __('e.g. @username on Instagram, TikTok') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('social_handles')" />
                    </div>
                    <div>
                        <label for="with_other_agency" class="pd-label">{{ __('Are you with another agency?') }}</label>
                        <input id="with_other_agency" name="with_other_agency" value="{{ old('with_other_agency', $profile->with_other_agency) }}" maxlength="255" class="pd-input mt-2" placeholder="{{ __('e.g. No / Yes, agency name') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('with_other_agency')" />
                    </div>
                    <div>
                        <label for="hear_about_us" class="pd-label">{{ __('How did you hear about us?') }}</label>
                        <input id="hear_about_us" name="hear_about_us" value="{{ old('hear_about_us', $profile->hear_about_us) }}" maxlength="255" class="pd-input mt-2" placeholder="{{ __('e.g. Referral, social media, search…') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('hear_about_us')" />
                    </div>
                </div>
            </section>

            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 2') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Platforms & Setup') }}</h2>
                </div>

                <div class="space-y-5">
                    <div class="space-y-4">
                        <p class="pd-label">{{ __('Platforms you\'d like to be on') }}</p>
                        <p class="-mt-3 text-[0.72rem] text-boss-ivory/38">{{ __('Tick all that apply.') }}</p>

                        {{-- Streaming platforms --}}
                        <div>
                            <p class="mb-2 text-[0.62rem] uppercase tracking-[0.16em] text-boss-ivory/40">{{ __('Streaming Platforms') }}</p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach ($platformOptions as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2.5 text-[0.78rem] text-boss-ivory/58 transition-colors hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                        <input type="checkbox" name="platforms[]" value="{{ $option }}" class="h-4 w-4 rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold focus:ring-offset-0" @checked(in_array($option, $selectedPlatforms, true))>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Fan sites --}}
                        <div>
                            <p class="mb-2 text-[0.62rem] uppercase tracking-[0.16em] text-boss-ivory/40">{{ __('Fan Sites') }}</p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach ($fanSiteOptions as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2.5 text-[0.78rem] text-boss-ivory/58 transition-colors hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                        <input type="checkbox" name="platforms[]" value="{{ $option }}" class="h-4 w-4 rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold focus:ring-offset-0" @checked(in_array($option, $selectedPlatforms, true))>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Social media --}}
                        <div>
                            <p class="mb-2 text-[0.62rem] uppercase tracking-[0.16em] text-boss-ivory/40">{{ __('Social Media Platforms') }}</p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach ($socialMediaOptions as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2.5 text-[0.78rem] text-boss-ivory/58 transition-colors hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                        <input type="checkbox" name="platforms[]" value="{{ $option }}" class="h-4 w-4 rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold focus:ring-offset-0" @checked(in_array($option, $selectedPlatforms, true))>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="current_platforms" class="pd-label">{{ __('Current platforms and usernames') }}</label>
                        <p class="mt-1 text-[0.72rem] text-boss-ivory/38">{{ __('List each website you are currently active on and the username you use there.') }}</p>
                        <textarea
                            id="current_platforms"
                            name="current_platforms"
                            rows="3"
                            class="pd-input mt-2"
                            placeholder="{{ __('e.g. Chaturbate: @username; OnlyFans: @username') }}"
                        >{{ old('current_platforms', $profile->current_platforms) }}</textarea>
                        <x-input-error class="mt-1.5" :messages="$errors->get('current_platforms')" />
                    </div>

                    {{-- ── General Fetishes & Kinks Checklist ──────────────────── --}}
                    @php
                        $savedChecklist = old('fetishes_checklist', $profile->fetishes_checklist ?? []);
                        $savedChecklist = is_array($savedChecklist) ? $savedChecklist : [];
                    @endphp
                    <div x-data="{ open: true }">
                        <button type="button"
                            @click="open = !open"
                            class="flex w-full items-center justify-between rounded-xl border border-white/[0.07] bg-white/[0.03] px-4 py-3 text-left transition hover:border-boss-gold/20 hover:bg-boss-gold/[0.04]">
                            <div>
                                <p class="pd-label mb-0">{{ __('General Fetishes & Kinks Checklist') }}</p>
                                <p class="mt-0.5 text-[0.7rem] text-boss-ivory/38">{{ __('Please answer Yes / No / Sometimes for each item.') }}</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-boss-ivory/40 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                        </button>

                        <div x-show="open" x-cloak x-transition class="mt-3 space-y-5">
                            @foreach ($fetishSections as $section)
                                <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] overflow-hidden">
                                    <div class="border-b border-white/[0.06] bg-white/[0.03] px-4 py-2.5">
                                        <p class="text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-boss-gold/80">{{ $section['title'] }}</p>
                                        @if (!empty($section['note']))
                                            <p class="mt-0.5 text-[0.65rem] text-boss-ivory/38">{{ $section['note'] }}</p>
                                        @endif
                                    </div>
                                    {{-- Header row --}}
                                    <div class="hidden grid-cols-[1fr_auto] gap-2 border-b border-white/[0.04] px-4 py-2 sm:grid">
                                        <span></span>
                                        <div class="flex w-[18rem] justify-around text-[0.6rem] uppercase tracking-[0.12em] text-boss-ivory/30">
                                            <span class="w-16 text-center">{{ __('Yes') }}</span>
                                            <span class="w-16 text-center">{{ __('No') }}</span>
                                            <span class="w-20 text-center">{{ __('Sometimes') }}</span>
                                        </div>
                                    </div>
                                    {{-- Items --}}
                                    @foreach ($section['items'] as $item)
                                        @php $key = $item; $val = $savedChecklist[$key] ?? ''; @endphp
                                        <div class="grid grid-cols-1 gap-2 border-b border-white/[0.03] px-4 py-2.5 last:border-0 sm:grid-cols-[1fr_auto] sm:items-center">
                                            <span class="text-[0.8rem] text-boss-ivory/70">{{ $item }}</span>
                                            <div class="flex gap-2 sm:w-[18rem] sm:justify-around">
                                                {{-- Yes --}}
                                                <label class="flex w-16 cursor-pointer items-center justify-center rounded-lg border px-2 py-1.5 text-[0.65rem] transition
                                                    border-white/[0.06] bg-white/[0.025] text-boss-ivory/40
                                                    hover:border-emerald-400/25 hover:text-emerald-300/70
                                                    has-[:checked]:border-emerald-400/40 has-[:checked]:bg-emerald-400/10 has-[:checked]:text-emerald-300">
                                                    <input type="radio" name="fetishes_checklist[{{ $key }}]" value="Yes" class="sr-only" @checked($val === 'Yes')>
                                                    <span>{{ __('Yes') }}</span>
                                                </label>
                                                {{-- No --}}
                                                <label class="flex w-16 cursor-pointer items-center justify-center rounded-lg border px-2 py-1.5 text-[0.65rem] transition
                                                    border-white/[0.06] bg-white/[0.025] text-boss-ivory/40
                                                    hover:border-red-400/25 hover:text-red-300/70
                                                    has-[:checked]:border-red-400/40 has-[:checked]:bg-red-400/10 has-[:checked]:text-red-300">
                                                    <input type="radio" name="fetishes_checklist[{{ $key }}]" value="No" class="sr-only" @checked($val === 'No')>
                                                    <span>{{ __('No') }}</span>
                                                </label>
                                                {{-- Sometimes --}}
                                                <label class="flex w-20 cursor-pointer items-center justify-center rounded-lg border px-2 py-1.5 text-[0.65rem] transition
                                                    border-white/[0.06] bg-white/[0.025] text-boss-ivory/40
                                                    hover:border-boss-gold/25 hover:text-boss-gold/70
                                                    has-[:checked]:border-boss-gold/40 has-[:checked]:bg-boss-gold/10 has-[:checked]:text-boss-gold">
                                                    <input type="radio" name="fetishes_checklist[{{ $key }}]" value="Sometimes" class="sr-only" @checked($val === 'Sometimes')>
                                                    <span>{{ __('Sometimes') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── Work Preferences ──────────────────────────────────────── --}}
                    <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4 space-y-5">
                        <p class="pd-label">{{ __('Work Preferences') }}</p>

                        <div class="space-y-3">
                            <p class="text-[0.72rem] uppercase tracking-[0.14em] text-boss-ivory/40">{{ __('Work interests') }}</p>
                            <p class="text-[0.7rem] text-boss-ivory/38">{{ __('What type of content are you interested in?') }}</p>
                            @php
                                $workInterestOptions = ['OnlyFans Content', 'Webcam Premium Shows', 'Freemium Webcam', 'All Types'];
                                $selectedWorkInterests = old('work_interests', $profile->work_interests ?? []);
                                $selectedWorkInterests = is_array($selectedWorkInterests) ? $selectedWorkInterests : [];
                            @endphp
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @foreach ($workInterestOptions as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2.5 text-[0.78rem] text-boss-ivory/58 transition-colors hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                        <input type="checkbox" name="work_interests[]" value="{{ $option }}" class="h-4 w-4 rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold focus:ring-offset-0" @checked(in_array($option, $selectedWorkInterests, true))>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-1.5" :messages="$errors->get('work_interests')" />
                        </div>

                        <div class="space-y-3">
                            <p class="text-[0.72rem] uppercase tracking-[0.14em] text-boss-ivory/40">{{ __('Comfort levels') }}</p>
                            <p class="text-[0.7rem] text-boss-ivory/38">{{ __('What are you comfortable performing?') }}</p>
                            @php
                                $comfortLevelOptions = ['Lingerie', 'Topless', 'Nude', 'Toys (Solo)', 'Girl/Girl', 'Fetish', 'Anal (Solo)', 'Domination / Roleplay'];
                                $selectedComfortLevels = old('comfort_levels', $profile->comfort_levels ?? []);
                                $selectedComfortLevels = is_array($selectedComfortLevels) ? $selectedComfortLevels : [];
                            @endphp
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @foreach ($comfortLevelOptions as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2.5 text-[0.78rem] text-boss-ivory/58 transition-colors hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                        <input type="checkbox" name="comfort_levels[]" value="{{ $option }}" class="h-4 w-4 rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold focus:ring-offset-0" @checked(in_array($option, $selectedComfortLevels, true))>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-1.5" :messages="$errors->get('comfort_levels')" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <p class="pd-label">{{ __('Custom content requests') }}</p>
                                <p class="text-[0.7rem] text-boss-ivory/38">{{ __('Do you accept custom content requests?') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['Yes', 'No', 'Maybe'] as $choice)
                                        <label class="flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2 text-[0.78rem] transition border-white/[0.07] bg-white/[0.035] text-boss-ivory/58 hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                            <input type="radio" name="custom_content_ok" value="{{ $choice }}" class="sr-only" @checked(old('custom_content_ok', $profile->custom_content_ok) === $choice)>
                                            <span>{{ $choice }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('custom_content_ok')" />
                            </div>
                            <div class="space-y-2">
                                <p class="pd-label">{{ __('Worn items requests') }}</p>
                                <p class="text-[0.7rem] text-boss-ivory/38">{{ __('Are you OK with selling worn items?') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['Yes', 'No', 'Maybe'] as $choice)
                                        <label class="flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2 text-[0.78rem] transition border-white/[0.07] bg-white/[0.035] text-boss-ivory/58 hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                            <input type="radio" name="worn_items_ok" value="{{ $choice }}" class="sr-only" @checked(old('worn_items_ok', $profile->worn_items_ok) === $choice)>
                                            <span>{{ $choice }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('worn_items_ok')" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="pd-label">{{ __('Available equipment') }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($equipmentOptions as $option)
                                <label class="flex items-center gap-2 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2 text-[0.78rem] text-boss-ivory/58">
                                    <input type="checkbox" name="equipment[]" value="{{ $option }}" class="rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold" @checked(in_array($option, $selectedEquipment, true))>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="availability" class="pd-label">{{ __('Availability') }}</label>
                        <textarea id="availability" name="availability" rows="4" class="pd-input mt-2" required>{{ old('availability', $profile->availability) }}</textarea>
                    </div>

                    <div>
                        <label for="goals" class="pd-label">{{ __('Goals') }}</label>
                        <textarea id="goals" name="goals" rows="4" class="pd-input mt-2" required>{{ old('goals', $profile->goals) }}</textarea>
                    </div>

                    <div>
                        <label for="experience_notes" class="pd-label">{{ __('Experience notes') }}</label>
                        <textarea id="experience_notes" name="experience_notes" rows="4" class="pd-input mt-2">{{ old('experience_notes', $profile->experience_notes) }}</textarea>
                    </div>

                    {{-- ── Schedule & Availability extras ────────────────────────── --}}
                    <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4 space-y-4">
                        <p class="pd-label">{{ __('Schedule Details') }}</p>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="weekly_availability" class="pd-label">{{ __('Weekly availability') }}</label>
                                <input id="weekly_availability" name="weekly_availability" value="{{ old('weekly_availability', $profile->weekly_availability) }}" class="pd-input mt-2" placeholder="{{ __('e.g. 20 hours/week, Mon–Fri') }}">
                                <x-input-error class="mt-1.5" :messages="$errors->get('weekly_availability')" />
                            </div>
                            <div>
                                <label for="availability_preference" class="pd-label">{{ __('Preferred schedule') }}</label>
                                <input id="availability_preference" name="availability_preference" value="{{ old('availability_preference', $profile->availability_preference) }}" class="pd-input mt-2" placeholder="{{ __('e.g. Evenings and weekends') }}">
                                <x-input-error class="mt-1.5" :messages="$errors->get('availability_preference')" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <p class="pd-label">{{ __('Do you have a private space to work from?') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['Yes', 'No', 'Working on it'] as $choice)
                                    <label class="flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2 text-[0.78rem] transition border-white/[0.07] bg-white/[0.035] text-boss-ivory/58 hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                        <input type="radio" name="has_private_space" value="{{ $choice }}" class="sr-only" @checked(old('has_private_space', $profile->has_private_space) === $choice)>
                                        <span>{{ $choice }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-1.5" :messages="$errors->get('has_private_space')" />
                        </div>
                    </div>
                </div>
            </section>

            {{-- ── Step 3: Appearance & Style ──────────────────────────────── --}}
            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 3') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Appearance & Style') }}</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="height" class="pd-label">{{ __('Height') }}</label>
                        <input id="height" name="height" value="{{ old('height', $profile->height) }}" class="pd-input mt-2" placeholder="{{ __('e.g. 5\'5" / 165cm') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('height')" />
                    </div>
                    <div>
                        <label for="weight" class="pd-label">{{ __('Weight') }}</label>
                        <input id="weight" name="weight" value="{{ old('weight', $profile->weight) }}" class="pd-input mt-2" placeholder="{{ __('e.g. 55kg / 121 lbs') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('weight')" />
                    </div>
                    <div>
                        <label for="hair_color" class="pd-label">{{ __('Hair color') }}</label>
                        <input id="hair_color" name="hair_color" value="{{ old('hair_color', $profile->hair_color) }}" class="pd-input mt-2" placeholder="{{ __('e.g. Black, Brown, Blonde…') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('hair_color')" />
                    </div>
                    <div>
                        <label for="eye_color" class="pd-label">{{ __('Eye color') }}</label>
                        <input id="eye_color" name="eye_color" value="{{ old('eye_color', $profile->eye_color) }}" class="pd-input mt-2" placeholder="{{ __('e.g. Brown, Hazel, Blue…') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('eye_color')" />
                    </div>
                    <div>
                        <label for="body_type" class="pd-label">{{ __('Body type') }}</label>
                        <input id="body_type" name="body_type" value="{{ old('body_type', $profile->body_type) }}" class="pd-input mt-2" placeholder="{{ __('e.g. Slim, Athletic, Curvy…') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('body_type')" />
                    </div>
                    <div>
                        <label for="has_tattoos_piercings" class="pd-label">{{ __('Tattoos & piercings') }}</label>
                        <input id="has_tattoos_piercings" name="has_tattoos_piercings" value="{{ old('has_tattoos_piercings', $profile->has_tattoos_piercings) }}" class="pd-input mt-2" placeholder="{{ __('e.g. Small tattoo on wrist, ear piercings') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('has_tattoos_piercings')" />
                    </div>
                </div>
            </section>

            {{-- ── Step 4: Payout Information ──────────────────────────────── --}}
            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 4') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Payout Information') }}</h2>
                </div>

                @php
                    $payoutMethodOptions = ['Revolut', 'Bank Transfer', 'Crypto', 'Other'];
                    $selectedPayoutMethods = old('payout_methods', $profile->payout_methods ?? []);
                    $selectedPayoutMethods = is_array($selectedPayoutMethods) ? $selectedPayoutMethods : [];
                @endphp
                <div
                    class="space-y-5"
                    x-data="{ otherPayout: @js(in_array('Other', $selectedPayoutMethods)) }"
                >
                    <div class="space-y-3">
                        <p class="pd-label">{{ __('Preferred payout methods') }}</p>
                        <p class="text-[0.72rem] text-boss-ivory/38">{{ __('Tick all methods you are able to receive payment via.') }}</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($payoutMethodOptions as $option)
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2.5 text-[0.78rem] text-boss-ivory/58 transition-colors hover:border-boss-gold/25 hover:bg-boss-gold/[0.06] hover:text-boss-ivory has-[:checked]:border-boss-gold/35 has-[:checked]:bg-boss-gold/[0.09] has-[:checked]:text-boss-ivory">
                                    <input
                                        type="checkbox"
                                        name="payout_methods[]"
                                        value="{{ $option }}"
                                        class="h-4 w-4 rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold focus:ring-offset-0"
                                        @checked(in_array($option, $selectedPayoutMethods, true))
                                        @if($option === 'Other') x-on:change="otherPayout = $el.checked" @endif
                                    >
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error class="mt-1.5" :messages="$errors->get('payout_methods')" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="payout_method_other" class="pd-label" :class="otherPayout ? '' : 'opacity-40'">{{ __('Other payout method') }}</label>
                            <input
                                id="payout_method_other"
                                name="payout_method_other"
                                value="{{ old('payout_method_other', $profile->payout_method_other) }}"
                                class="pd-input mt-2 transition-opacity"
                                :class="otherPayout ? '' : 'opacity-40 cursor-not-allowed'"
                                :disabled="!otherPayout"
                                placeholder="{{ __('Specify if you selected Other') }}"
                            >
                            <x-input-error class="mt-1.5" :messages="$errors->get('payout_method_other')" />
                        </div>
                        <div>
                            <label for="payout_country" class="pd-label">{{ __('Payout country / region') }}</label>
                            <input id="payout_country" name="payout_country" value="{{ old('payout_country', $profile->payout_country) }}" class="pd-input mt-2" placeholder="{{ __('Country where you receive payments') }}">
                            <x-input-error class="mt-1.5" :messages="$errors->get('payout_country')" />
                        </div>
                    </div>
                </div>
            </section>

            {{-- ── Step 5: Extra Details ────────────────────────────────────── --}}
            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 5') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Extra Details') }}</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="model_vibe" class="pd-label">{{ __('Your vibe / niche') }}</label>
                        <p class="mt-1 text-[0.72rem] text-boss-ivory/38">{{ __('Describe your personal brand, niche, or content style.') }}</p>
                        <textarea id="model_vibe" name="model_vibe" rows="3" class="pd-input mt-2" placeholder="{{ __('e.g. Girlfriend experience, dominant vibes, cosplay…') }}">{{ old('model_vibe', $profile->model_vibe) }}</textarea>
                        <x-input-error class="mt-1.5" :messages="$errors->get('model_vibe')" />
                    </div>
                    <div>
                        <label for="anything_else" class="pd-label">{{ __('Anything else we should know?') }}</label>
                        <p class="mt-1 text-[0.72rem] text-boss-ivory/38">{{ __('Any other details, questions, or notes you\'d like to share.') }}</p>
                        <textarea id="anything_else" name="anything_else" rows="4" class="pd-input mt-2" placeholder="{{ __('Share anything else…') }}">{{ old('anything_else', $profile->anything_else) }}</textarea>
                        <x-input-error class="mt-1.5" :messages="$errors->get('anything_else')" />
                    </div>
                </div>
            </section>

            {{-- ── Step 6: Discord ─────────────────────────────────────────── --}}
            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 6') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Discord') }}</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="discord_username" class="pd-label">{{ __('Discord username') }}</label>
                        <input id="discord_username" name="discord_username" value="{{ old('discord_username', $profile->discord_username) }}" placeholder="username or username#0000" class="pd-input mt-2">
                    </div>
                    <div>
                        <label for="discord_user_id" class="pd-label">{{ __('Discord user ID') }}</label>
                        <input id="discord_user_id" name="discord_user_id" value="{{ old('discord_user_id', $profile->discord_user_id) }}" placeholder="{{ __('Optional') }}" class="pd-input mt-2">
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('member.dashboard') }}" class="pd-btn-secondary">{{ __('Back to dashboard') }}</a>
                <button type="submit" class="pd-btn-primary">{{ __('Submit Model Information') }}</button>
            </div>
        </form>
    </div>
</x-member-layout>
