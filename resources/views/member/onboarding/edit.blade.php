<x-member-layout>
    @php
        $selectedPlatforms = old('platforms', $profile->platforms ?? []);
        $selectedEquipment = old('equipment', $profile->equipment ?? []);
        $selectedPhoneCountry = old('phone_country', $selectedPhoneCountry);
        $phoneNumber = old('phone_number', $phoneNumber);
        $selectedEmergencyContactPhoneCountry = old('emergency_contact_phone_country', $selectedEmergencyContactPhoneCountry);
        $emergencyContactPhoneNumber = old('emergency_contact_phone_number', $emergencyContactPhoneNumber);
        $currentCountry = old('country', $profile->country);
        $currentTimezone = old('timezone', $profile->timezone);
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
                                    selected: @js($selectedPhoneCountry),
                                    countries: @js($phoneCountries),
                                    get current() {
                                        return this.countries.find((country) => country.value === this.selected) || this.countries[0];
                                    },
                                    selectCountry(value) {
                                        this.selected = value;
                                        this.open = false;
                                    },
                                }"
                                @keydown.escape.window="open = false"
                                @click.outside="open = false"
                            >
                                <label id="onboarding-phone-country-label" class="pd-label normal-case tracking-normal leading-[1.5]">{{ __('Country code') }}</label>
                                <input type="hidden" name="phone_country" x-model="selected" data-phone-country>
                                <button
                                    type="button"
                                    class="pd-input mt-2 flex h-[2.8rem] items-center gap-2 px-3 py-0 text-left"
                                    aria-labelledby="onboarding-phone-country-label"
                                    aria-haspopup="listbox"
                                    :aria-expanded="open.toString()"
                                    @click="open = ! open"
                                >
                                    <img :src="current.flag" :alt="current.name" class="h-3.5 w-5 rounded-[2px] object-cover shadow-sm">
                                    <span class="min-w-0 flex-1 text-[0.82rem]" x-text="current.code"></span>
                                    <span class="text-[0.62rem] text-boss-ivory/35" aria-hidden="true">v</span>
                                </button>

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-transition
                                    class="absolute left-0 top-full z-50 mt-1 max-h-64 w-40 overflow-y-auto rounded-md border border-white/[0.08] bg-[#161114] py-1 shadow-luxe"
                                    role="listbox"
                                    aria-labelledby="onboarding-phone-country-label"
                                >
                                    <template x-for="country in countries" :key="country.value">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-[0.82rem] text-boss-ivory/68 transition-colors hover:bg-white/[0.05] hover:text-boss-ivory"
                                            :class="selected === country.value ? 'bg-[#EEB4C3]/12 text-[#EEB4C3]' : ''"
                                            role="option"
                                            :aria-selected="(selected === country.value).toString()"
                                            :title="country.name"
                                            @click="selectCountry(country.value)"
                                        >
                                            <img :src="country.flag" :alt="country.name" class="h-3.5 w-5 rounded-[2px] object-cover shadow-sm">
                                            <span x-text="country.code"></span>
                                        </button>
                                    </template>
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
                    <div class="md:col-span-2">
                        <label for="timezone" class="pd-label">{{ __('Timezone') }}</label>
                        <select id="timezone" name="timezone" class="pd-input mt-2" autocomplete="off">
                            <option value="">{{ __('Select timezone') }}</option>
                            @if ($currentTimezone && ! in_array($currentTimezone, $timezoneOptions, true))
                                <option value="{{ $currentTimezone }}" selected>{{ $currentTimezone }}</option>
                            @endif
                            @foreach ($timezoneOptions as $timezoneOption)
                                <option value="{{ $timezoneOption }}" @selected($currentTimezone === $timezoneOption)>{{ $timezoneOption }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-1.5" :messages="$errors->get('timezone')" />
                    </div>
                </div>
            </section>

            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 2') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Platforms & Setup') }}</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <p class="pd-label">{{ __('Current or preferred platforms') }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach ($platformOptions as $option)
                                <label class="flex items-center gap-2 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2 text-[0.78rem] text-boss-ivory/58">
                                    <input type="checkbox" name="platforms[]" value="{{ $option }}" class="rounded border-white/20 bg-boss-ink text-[#EEB4C3] focus:ring-[#EEB4C3]" @checked(in_array($option, $selectedPlatforms, true))>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="pd-label">{{ __('Available equipment') }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($equipmentOptions as $option)
                                <label class="flex items-center gap-2 rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2 text-[0.78rem] text-boss-ivory/58">
                                    <input type="checkbox" name="equipment[]" value="{{ $option }}" class="rounded border-white/20 bg-boss-ink text-[#EEB4C3] focus:ring-[#EEB4C3]" @checked(in_array($option, $selectedEquipment, true))>
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
                </div>
            </section>

            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 3') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Emergency Contact & Discord') }}</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="emergency_contact_name" class="pd-label">{{ __('Contact name') }}</label>
                        <input id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}" class="pd-input mt-2">
                    </div>
                    <fieldset data-phone-field>
                        <legend class="sr-only">{{ __('Contact phone') }}</legend>
                        <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-2">
                            <div
                                class="relative"
                                x-data="{
                                    open: false,
                                    selected: @js($selectedEmergencyContactPhoneCountry),
                                    countries: @js($phoneCountries),
                                    get current() {
                                        return this.countries.find((country) => country.value === this.selected) || this.countries[0];
                                    },
                                    selectCountry(value) {
                                        this.selected = value;
                                        this.open = false;
                                    },
                                }"
                                @keydown.escape.window="open = false"
                                @click.outside="open = false"
                            >
                                <label id="emergency-contact-phone-country-label" class="pd-label normal-case tracking-normal leading-[1.5]">{{ __('Country code') }}</label>
                                <input type="hidden" name="emergency_contact_phone_country" x-model="selected" data-phone-country>
                                <button
                                    type="button"
                                    class="pd-input mt-2 flex h-[2.8rem] items-center gap-2 px-3 py-0 text-left"
                                    aria-labelledby="emergency-contact-phone-country-label"
                                    aria-haspopup="listbox"
                                    :aria-expanded="open.toString()"
                                    @click="open = ! open"
                                >
                                    <img :src="current.flag" :alt="current.name" class="h-3.5 w-5 rounded-[2px] object-cover shadow-sm">
                                    <span class="min-w-0 flex-1 text-[0.82rem]" x-text="current.code"></span>
                                    <span class="text-[0.62rem] text-boss-ivory/35" aria-hidden="true">v</span>
                                </button>

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-transition
                                    class="absolute left-0 top-full z-50 mt-1 max-h-64 w-40 overflow-y-auto rounded-md border border-white/[0.08] bg-[#161114] py-1 shadow-luxe"
                                    role="listbox"
                                    aria-labelledby="emergency-contact-phone-country-label"
                                >
                                    <template x-for="country in countries" :key="country.value">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-[0.82rem] text-boss-ivory/68 transition-colors hover:bg-white/[0.05] hover:text-boss-ivory"
                                            :class="selected === country.value ? 'bg-[#EEB4C3]/12 text-[#EEB4C3]' : ''"
                                            role="option"
                                            :aria-selected="(selected === country.value).toString()"
                                            :title="country.name"
                                            @click="selectCountry(country.value)"
                                        >
                                            <img :src="country.flag" :alt="country.name" class="h-3.5 w-5 rounded-[2px] object-cover shadow-sm">
                                            <span x-text="country.code"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label for="emergency-contact-phone-number" class="pd-label normal-case tracking-normal leading-[1.5]">{{ __('Contact phone') }}</label>
                                <input
                                    id="emergency-contact-phone-number"
                                    type="tel"
                                    name="emergency_contact_phone_number"
                                    value="{{ $emergencyContactPhoneNumber }}"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    placeholder="201-555-5555"
                                    pattern="[0-9\s().-]{6,24}"
                                    title="{{ __('Use 6 to 15 digits after the country code.') }}"
                                    class="pd-input mt-2 h-[2.8rem]"
                                    data-phone-number
                                >
                            </div>
                        </div>
                        <x-input-error class="mt-1.5" :messages="$errors->get('emergency_contact_phone_country')" />
                        <x-input-error class="mt-1.5" :messages="$errors->get('emergency_contact_phone_number')" />
                    </fieldset>
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

