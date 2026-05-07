<x-member-layout>
    @php
        $selectedPlatforms = old('platforms', $profile->platforms ?? []);
        $selectedEquipment = old('equipment', $profile->equipment ?? []);
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

        <form method="POST" action="{{ route('member.onboarding.update') }}" class="space-y-6">
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
                    <div>
                        <label for="phone" class="pd-label">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" value="{{ old('phone', $profile->phone) }}" class="pd-input mt-2" required>
                    </div>
                    <div>
                        <label for="country" class="pd-label">{{ __('Country') }}</label>
                        <input id="country" name="country" value="{{ old('country', $profile->country) }}" class="pd-input mt-2" required>
                    </div>
                    <div>
                        <label for="city" class="pd-label">{{ __('City') }}</label>
                        <input id="city" name="city" value="{{ old('city', $profile->city) }}" class="pd-input mt-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="timezone" class="pd-label">{{ __('Timezone') }}</label>
                        <input id="timezone" name="timezone" value="{{ old('timezone', $profile->timezone) }}" placeholder="Europe/London, EST, GMT+1" class="pd-input mt-2">
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
                                    <input type="checkbox" name="platforms[]" value="{{ $option }}" class="rounded border-white/20 bg-boss-ink text-boss-gold focus:ring-boss-gold" @checked(in_array($option, $selectedPlatforms, true))>
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
                </div>
            </section>

            <section class="pd-panel-strong p-5 md:p-6">
                <div class="mb-5">
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Step 3') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ __('Emergency Contact & Community') }}</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="emergency_contact_name" class="pd-label">{{ __('Contact name') }}</label>
                        <input id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}" class="pd-input mt-2">
                    </div>
                    <div>
                        <label for="emergency_contact_phone" class="pd-label">{{ __('Contact phone') }}</label>
                        <input id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $profile->emergency_contact_phone) }}" class="pd-input mt-2">
                    </div>
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
