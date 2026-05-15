<x-member-layout>
    <div class="mx-auto max-w-6xl space-y-7 text-boss-ivory">
        <header class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Referrals') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2.1rem,4vw,3rem)]">{{ __('Refer a Model') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-boss-ivory/[0.45]">
                    {{ __('Share Paradise Dolls with potential models and track when your referrals become reward eligible.') }}
                </p>
            </div>

            <div class="rounded-full border border-boss-gold/15 bg-boss-gold/[0.07] px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] text-boss-gold">
                {{ __('Rewards activate after approval') }}
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/20 bg-emerald-300/10 p-4 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-5">
                <article
                    x-data="{
                        copied: false,
                        async copyLink() {
                            await navigator.clipboard?.writeText(this.$refs.referralLink.value);
                            this.copied = true;
                            setTimeout(() => this.copied = false, 1800);
                        },
                    }"
                    class="rounded-2xl border border-white/[0.07] bg-boss-panel-strong p-5 shadow-[0_24px_70px_rgba(0,0,0,0.22)]"
                >
                    <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold/70">{{ __('Your Referral Link') }}</p>
                    <h2 class="mt-2 font-display text-2xl text-boss-ivory">{{ __('Share your private link') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-boss-ivory/[0.42]">
                        {{ __('Anyone who applies from this link will be connected to you automatically.') }}
                    </p>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <label for="referral-link" class="sr-only">{{ __('Referral link') }}</label>
                        <input id="referral-link" x-ref="referralLink" readonly value="{{ $referralLink }}" class="pd-input h-12 flex-1 font-mono text-[0.78rem]">
                        <button type="button" class="pd-btn-primary h-12 justify-center" @click="copyLink()">
                            <span x-show="!copied">{{ __('Copy Link') }}</span>
                            <span x-show="copied" x-cloak>{{ __('Copied') }}</span>
                        </button>
                    </div>

                    <div class="mt-4 rounded-xl border border-white/[0.06] bg-white/[0.025] p-4">
                        <p class="text-[0.66rem] uppercase tracking-[0.16em] text-boss-ivory/[0.32]">{{ __('Your code') }}</p>
                        <p class="mt-1 font-display text-2xl text-boss-gold-light">{{ $member->referral_code }}</p>
                    </div>
                </article>

                <article class="rounded-2xl border border-white/[0.07] bg-boss-panel-strong p-5">
                    <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold/70">{{ __('How rewards work') }}</p>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-boss-ivory/[0.46]">
                        <p>{{ __('1. Submit a referral with photos and permission.') }}</p>
                        <p>{{ __('2. Admin reviews or converts the lead into an application.') }}</p>
                        <p>{{ __('3. When the candidate is approved, your reward becomes eligible.') }}</p>
                    </div>
                </article>
            </div>

            <form method="POST" action="{{ route('member.referrals.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-white/[0.07] bg-boss-panel-strong p-5 shadow-[0_24px_70px_rgba(0,0,0,0.22)]">
                @csrf
                <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-gold/70">{{ __('Referral Lead') }}</p>
                <h2 class="mt-2 font-display text-2xl text-boss-ivory">{{ __('Send a candidate to admin') }}</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="candidate_name" class="pd-label">{{ __('Candidate name') }} *</label>
                        <input id="candidate_name" name="candidate_name" value="{{ old('candidate_name') }}" required class="pd-input mt-2" placeholder="{{ __('Full name') }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('candidate_name')" />
                    </div>

                    <div>
                        <label for="candidate_email" class="pd-label">{{ __('Candidate email') }}</label>
                        <input id="candidate_email" type="email" name="candidate_email" value="{{ old('candidate_email') }}" class="pd-input mt-2" placeholder="candidate@email.com">
                        <x-input-error class="mt-1.5" :messages="$errors->get('candidate_email')" />
                    </div>

                    <div>
                        <label for="candidate_phone" class="pd-label">{{ __('Phone') }}</label>
                        <input id="candidate_phone" name="candidate_phone" value="{{ old('candidate_phone') }}" class="pd-input mt-2" placeholder="+63...">
                        <x-input-error class="mt-1.5" :messages="$errors->get('candidate_phone')" />
                    </div>

                    <div>
                        <label for="candidate_social_handle" class="pd-label">{{ __('Instagram / TikTok') }}</label>
                        <input id="candidate_social_handle" name="candidate_social_handle" value="{{ old('candidate_social_handle') }}" class="pd-input mt-2" placeholder="@handle">
                        <x-input-error class="mt-1.5" :messages="$errors->get('candidate_social_handle')" />
                    </div>

                    <p class="sm:col-span-2 text-[0.72rem] text-boss-ivory/[0.35]">
                        {{ __('Provide at least one contact method: email, phone, or social handle.') }}
                    </p>

                    <div class="sm:col-span-2">
                        <label for="experience_level" class="pd-label">{{ __('Experience level') }} *</label>
                        <select id="experience_level" name="experience_level" required class="pd-input mt-2">
                            <option value="">{{ __('Select experience') }}</option>
                            <option value="none" @selected(old('experience_level') === 'none')>{{ __('No Experience') }}</option>
                            <option value="beginner" @selected(old('experience_level') === 'beginner')>{{ __('Beginner') }}</option>
                            <option value="1-2" @selected(old('experience_level') === '1-2')>{{ __('1-2 Years') }}</option>
                            <option value="3+" @selected(old('experience_level') === '3+')>{{ __('3+ Years') }}</option>
                        </select>
                        <x-input-error class="mt-1.5" :messages="$errors->get('experience_level')" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="referral_photos" class="pd-label">{{ __('Candidate photos') }} *</label>
                        <input id="referral_photos" type="file" name="photos[]" multiple required accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="pd-input mt-2">
                        <p class="mt-2 text-[0.72rem] text-boss-ivory/[0.35]">{{ __('Upload 1-6 clear JPG, PNG, or WEBP photos. Max 5MB each.') }}</p>
                        <x-input-error class="mt-1.5" :messages="$errors->get('photos')" />
                        <x-input-error class="mt-1.5" :messages="$errors->get('photos.*')" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="note" class="pd-label">{{ __('Short note') }}</label>
                        <textarea id="note" name="note" rows="4" class="pd-input mt-2" placeholder="{{ __('Why do you think they would be a good fit?') }}">{{ old('note') }}</textarea>
                        <x-input-error class="mt-1.5" :messages="$errors->get('note')" />
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-boss-gold/15 bg-boss-gold/[0.06] p-4">
                    <label for="consent_confirmed" class="flex items-start gap-3 text-sm leading-6 text-boss-ivory/[0.7]">
                        <input id="consent_confirmed" name="consent_confirmed" type="checkbox" value="1" class="mt-1 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" @checked(old('consent_confirmed'))>
                        <span>{{ __("I confirm I have permission to share this candidate's contact details and photos, and I believe they are 18 years of age or older.") }}</span>
                    </label>
                    <x-input-error class="mt-1.5" :messages="$errors->get('consent_confirmed')" />
                </div>

                <button type="submit" class="pd-btn-primary mt-5 w-full justify-center">{{ __('Submit Referral') }}</button>
            </form>
        </section>

        <section class="rounded-2xl border border-white/[0.07] bg-boss-panel-strong p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[0.65rem] uppercase tracking-[0.18em] text-boss-ivory/[0.35]">{{ __('History') }}</p>
                    <h2 class="mt-1 font-display text-2xl text-boss-ivory">{{ __('Your Referrals') }}</h2>
                </div>
                <p class="text-xs text-boss-ivory/[0.36]">{{ __('Reward status updates after admin approval.') }}</p>
            </div>

            <div class="mt-5 overflow-hidden rounded-xl border border-white/[0.06]">
                @forelse ($referrals as $referral)
                    <div class="flex flex-col gap-3 border-t border-white/[0.06] bg-white/[0.02] p-4 first:border-t-0 md:flex-row md:items-center md:justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-boss-ivory">{{ $referral->candidate_name }}</p>
                            <p class="mt-1 truncate text-xs text-boss-ivory/[0.38]">
                                {{ $referral->candidate_email ?? $referral->candidate_phone ?? $referral->candidate_social_handle }}
                            </p>
                            <p class="mt-2 text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/[0.28]">
                                {{ $referral->created_at->toFormattedDateString() }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-white/[0.08] bg-white/[0.03] px-2.5 py-1 text-[0.64rem] text-boss-ivory/[0.55]">
                                {{ $referral->statusLabel() }}
                            </span>
                            <span class="rounded-full border border-boss-gold/15 bg-boss-gold/[0.07] px-2.5 py-1 text-[0.64rem] text-boss-gold-light">
                                {{ $referral->rewardStatusLabel() }}
                            </span>
                            <span class="rounded-full border border-white/[0.08] bg-white/[0.03] px-2.5 py-1 text-[0.64rem] text-boss-ivory/[0.42]">
                                {{ count($referral->photo_paths ?? []) }} {{ __('photos') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-boss-ivory/[0.38]">
                        {{ __('No referrals submitted yet.') }}
                    </div>
                @endforelse
            </div>

            @if ($referrals->hasPages())
                <div class="mt-5">{{ $referrals->links() }}</div>
            @endif
        </section>
    </div>
</x-member-layout>
