<x-admin-layout>
    <div
        class="mx-auto max-w-full space-y-6 text-boss-ivory"
        x-data="{
            open: false,
            selected: null,
            showReject: false,
            stageOptions: @js($stageOptions),
            async selectModel(model) {
                this.selected = model;
                this.open = true;
                this.showReject = false;

                if (!model.profile || !model.profile.details_url || model.profile.course_access_loaded) {
                    return;
                }

                this.selected.profile.course_access_loading = true;

                try {
                    const response = await fetch(model.profile.details_url, { headers: { Accept: 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    this.selected.profile.course_access = data.course_access || [];
                    this.selected.profile.course_access_loaded = true;
                } finally {
                    this.selected.profile.course_access_loading = false;
                }
            }
        }"
        @keydown.escape.window="open = false"
    >

        {{-- ── Backdrop ──────────────────────────────────────────── --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm"
            @click="open = false"
        ></div>

        {{-- ── Slide-over panel ──────────────────────────────────── --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-50 flex w-full flex-col border-l border-white/[0.06] bg-[#0d0e14] shadow-2xl sm:max-w-2xl"
        >
            <template x-if="selected">
                <div class="flex h-full flex-col">

                    {{-- Panel header --}}
                    <div class="flex shrink-0 items-center justify-between border-b border-white/[0.06] px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-boss-gold/25 bg-boss-gold/10 font-display text-base text-boss-gold"
                                x-text="selected.name.charAt(0).toUpperCase()"
                            ></div>
                            <div>
                                <h2 class="font-display text-lg font-semibold text-boss-ivory" x-text="selected.name"></h2>
                                <p class="text-sm text-boss-ivory/45" x-text="selected.email"></p>
                                <p x-show="selected.profile && selected.profile.stage_name" class="mt-0.5 text-sm text-boss-gold/70" x-text="selected.profile && selected.profile.stage_name ? '« ' + selected.profile.stage_name + ' »' : ''"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="selected.profile">
                                <a
                                    :href="'{{ url('admin/onboarding') }}/' + selected.profile.id"
                                    class="rounded-lg border border-boss-gold/20 bg-boss-gold/[0.07] px-3 py-1.5 text-[0.72rem] font-medium text-boss-gold transition hover:bg-boss-gold/[0.13]"
                                >
                                    Full Profile
                                </a>
                            </template>
                            <button
                                @click="open = false"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/[0.06] text-boss-ivory/50 transition hover:border-white/[0.12] hover:text-boss-ivory"
                                aria-label="Close"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Scrollable body --}}
                    <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">

                        {{-- Onboarding progress bar --}}
                        <template x-if="selected.profile">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Onboarding Progress</p>
                                    <span class="text-xs font-semibold text-boss-gold" x-text="selected.profile.onboarding_percent + '%'"></span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-white/[0.06]">
                                    <div
                                        class="h-full rounded-full bg-boss-gold transition-all duration-500"
                                        :style="'width: ' + selected.profile.onboarding_percent + '%'"
                                    ></div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2 text-[0.65rem]">
                                    <span :class="selected.profile.has_information_form ? 'text-green-300' : 'text-boss-ivory/30'">
                                        <span x-text="selected.profile.has_information_form ? '✓' : '○'"></span> Info Form
                                    </span>
                                    <span :class="selected.profile.has_verification_submission ? 'text-green-300' : 'text-boss-ivory/30'">
                                        <span x-text="selected.profile.has_verification_submission ? '✓' : '○'"></span> Docs Submitted
                                    </span>
                                    <span :class="selected.profile.is_verified ? 'text-green-300' : 'text-boss-ivory/30'">
                                        <span x-text="selected.profile.is_verified ? '✓' : '○'"></span> Verified
                                    </span>
                                    <span :class="selected.profile.community_invited_at ? 'text-green-300' : 'text-boss-ivory/30'">
                                        <span x-text="selected.profile.community_invited_at ? '✓' : '○'"></span> Discord Invited
                                    </span>
                                    <span :class="selected.profile.community_role_assigned_at ? 'text-green-300' : 'text-boss-ivory/30'">
                                        <span x-text="selected.profile.community_role_assigned_at ? '✓' : '○'"></span> Discord Role Assigned
                                    </span>
                                </div>
                            </div>
                        </template>

                        {{-- Manual onboarding controls --}}
                        <template x-if="selected.profile">
                            <div class="space-y-4 rounded-xl border border-white/[0.06] bg-white/[0.025] p-5">
                                <div>
                                    <p class="text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Manual Access Controls</p>
                                    <p class="mt-1 text-[0.72rem] leading-relaxed text-boss-ivory/35">Move this model through Kayla's manual onboarding phases. Course-specific verification requirements are managed from each course.</p>
                                </div>

                                <form :action="selected.profile.stage_url" method="POST" class="space-y-2">
                                    @csrf
                                    <label class="block text-[0.62rem] uppercase tracking-[0.14em] text-boss-ivory/35">Current phase</label>
                                    <select name="onboarding_stage" x-model="selected.profile.onboarding_stage" class="pd-input w-full text-sm">
                                        <template x-for="option in stageOptions" :key="option.value">
                                            <option :value="option.value" x-text="option.label"></option>
                                        </template>
                                    </select>
                                    <button type="submit" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2 text-sm font-semibold text-boss-ivory/70 transition hover:bg-white/[0.07] hover:text-boss-ivory">
                                        Save Phase
                                    </button>
                                </form>
                            </div>
                        </template>

                        {{-- No profile yet --}}
                        <template x-if="!selected.profile">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-5 text-center text-sm text-boss-ivory/35">
                                No profile created yet for this member.
                            </div>
                        </template>

                        {{-- Information Form --}}
                        <template x-if="selected.profile && selected.profile.has_information_form">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Information Form</p>
                                    <span class="text-[0.65rem] text-boss-ivory/30" x-text="'Submitted ' + selected.profile.information_submitted_at"></span>
                                </div>

                                {{-- Personal details grid --}}
                                <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                    <template x-if="selected.profile.legal_name">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Legal Name</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.legal_name"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.stage_name">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Stage Name</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.stage_name"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.date_of_birth">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Date of Birth</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.date_of_birth"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.phone">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Phone</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.phone"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.city || selected.profile.country">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Location</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="[selected.profile.city, selected.profile.country].filter(Boolean).join(', ')"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.nationality">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Nationality</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.nationality"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.spoken_languages">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Spoken Languages</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.spoken_languages"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.social_handles">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Social Handles</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.social_handles"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.with_other_agency">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">With Another Agency?</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.with_other_agency"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.hear_about_us">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">How They Found Us</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/80" x-text="selected.profile.hear_about_us"></p>
                                        </div>
                                    </template>
                                </div>

                                {{-- Platforms they want to be on --}}
                                <template x-if="selected.profile.platforms && selected.profile.platforms.length > 0">
                                    <div>
                                        <p class="mb-2 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ __('Platforms they\'d like to be on') }}</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="platform in selected.profile.platforms" :key="platform">
                                                <span class="rounded-full bg-boss-gold/10 px-2.5 py-0.5 text-[0.7rem] text-boss-gold" x-text="platform"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Current platforms and usernames --}}
                                <template x-if="selected.profile.current_platforms">
                                    <div>
                                        <p class="mb-1.5 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ __('Current platforms and usernames') }}</p>
                                        <p class="text-[0.82rem] leading-relaxed text-boss-ivory/70" x-text="selected.profile.current_platforms"></p>
                                    </div>
                                </template>

                                {{-- Fetishes & Kinks Checklist --}}
                                <template x-if="selected.profile.fetishes_checklist && Object.keys(selected.profile.fetishes_checklist).length > 0">
                                    <div>
                                        <p class="mb-2 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ __('Fetishes & Kinks Checklist') }}</p>
                                        <div class="space-y-1 max-h-72 overflow-y-auto pr-1">
                                            <template x-for="[item, answer] in Object.entries(selected.profile.fetishes_checklist)" :key="item">
                                                <div class="flex items-center justify-between gap-2 rounded-md px-2 py-1 text-[0.74rem]"
                                                    :class="answer === 'Yes' ? 'bg-emerald-400/8 text-boss-ivory/70' : (answer === 'No' ? 'bg-red-400/8 text-boss-ivory/50' : 'bg-boss-gold/8 text-boss-ivory/65')">
                                                    <span x-text="item"></span>
                                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[0.6rem] font-semibold"
                                                        :class="answer === 'Yes' ? 'bg-emerald-400/15 text-emerald-300' : (answer === 'No' ? 'bg-red-400/15 text-red-300' : 'bg-boss-gold/15 text-boss-gold')"
                                                        x-text="answer"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Equipment --}}
                                <template x-if="selected.profile.equipment && selected.profile.equipment.length > 0">
                                    <div>
                                        <p class="mb-2 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Equipment</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="item in selected.profile.equipment" :key="item">
                                                <span class="rounded-full bg-white/[0.05] px-2.5 py-0.5 text-[0.7rem] text-boss-ivory/60" x-text="item"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Text fields --}}
                                <template x-if="selected.profile.availability">
                                    <div>
                                        <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Availability</p>
                                        <p class="whitespace-pre-line text-sm leading-relaxed text-boss-ivory/65" x-text="selected.profile.availability"></p>
                                    </div>
                                </template>
                                <template x-if="selected.profile.goals">
                                    <div>
                                        <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Goals</p>
                                        <p class="whitespace-pre-line text-sm leading-relaxed text-boss-ivory/65" x-text="selected.profile.goals"></p>
                                    </div>
                                </template>
                                <template x-if="selected.profile.experience_notes">
                                    <div>
                                        <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Experience Notes</p>
                                        <p class="whitespace-pre-line text-sm leading-relaxed text-boss-ivory/65" x-text="selected.profile.experience_notes"></p>
                                    </div>
                                </template>

                                {{-- Appearance --}}
                                <template x-if="selected.profile.height || selected.profile.weight || selected.profile.hair_color || selected.profile.eye_color || selected.profile.body_type || selected.profile.has_tattoos_piercings">
                                    <div class="border-t border-white/[0.04] pt-3">
                                        <p class="mb-2 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Appearance & Style</p>
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[0.78rem]">
                                            <template x-if="selected.profile.height">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Height</span><span class="text-boss-ivory/70" x-text="selected.profile.height"></span></div>
                                            </template>
                                            <template x-if="selected.profile.weight">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Weight</span><span class="text-boss-ivory/70" x-text="selected.profile.weight"></span></div>
                                            </template>
                                            <template x-if="selected.profile.hair_color">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Hair</span><span class="text-boss-ivory/70" x-text="selected.profile.hair_color"></span></div>
                                            </template>
                                            <template x-if="selected.profile.eye_color">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Eyes</span><span class="text-boss-ivory/70" x-text="selected.profile.eye_color"></span></div>
                                            </template>
                                            <template x-if="selected.profile.body_type">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Body</span><span class="text-boss-ivory/70" x-text="selected.profile.body_type"></span></div>
                                            </template>
                                            <template x-if="selected.profile.has_tattoos_piercings">
                                                <div class="col-span-2 flex gap-2"><span class="text-boss-ivory/38">Tattoos/Piercings</span><span class="text-boss-ivory/70" x-text="selected.profile.has_tattoos_piercings"></span></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Work Preferences --}}
                                <template x-if="(selected.profile.work_interests && selected.profile.work_interests.length > 0) || (selected.profile.comfort_levels && selected.profile.comfort_levels.length > 0) || selected.profile.custom_content_ok || selected.profile.worn_items_ok">
                                    <div class="border-t border-white/[0.04] pt-3">
                                        <p class="mb-2 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Work Preferences</p>
                                        <div class="space-y-2">
                                            <template x-if="selected.profile.work_interests && selected.profile.work_interests.length > 0">
                                                <div>
                                                    <p class="mb-1 text-[0.6rem] text-boss-ivory/30">Interests</p>
                                                    <div class="flex flex-wrap gap-1">
                                                        <template x-for="item in selected.profile.work_interests" :key="item">
                                                            <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.65rem] text-boss-gold" x-text="item"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="selected.profile.comfort_levels && selected.profile.comfort_levels.length > 0">
                                                <div>
                                                    <p class="mb-1 text-[0.6rem] text-boss-ivory/30">Comfort levels</p>
                                                    <div class="flex flex-wrap gap-1">
                                                        <template x-for="item in selected.profile.comfort_levels" :key="item">
                                                            <span class="rounded-full bg-white/[0.05] px-2 py-0.5 text-[0.65rem] text-boss-ivory/60" x-text="item"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <div class="flex gap-4 text-[0.78rem]">
                                                <template x-if="selected.profile.custom_content_ok">
                                                    <div class="flex gap-1.5"><span class="text-boss-ivory/38">Custom content</span><span class="text-boss-ivory/70" x-text="selected.profile.custom_content_ok"></span></div>
                                                </template>
                                                <template x-if="selected.profile.worn_items_ok">
                                                    <div class="flex gap-1.5"><span class="text-boss-ivory/38">Worn items</span><span class="text-boss-ivory/70" x-text="selected.profile.worn_items_ok"></span></div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Availability extras --}}
                                <template x-if="selected.profile.weekly_availability || selected.profile.availability_preference || selected.profile.has_private_space">
                                    <div>
                                        <p class="mb-1.5 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Schedule Details</p>
                                        <div class="space-y-1 text-[0.78rem]">
                                            <template x-if="selected.profile.weekly_availability">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Weekly hours</span><span class="text-boss-ivory/70" x-text="selected.profile.weekly_availability"></span></div>
                                            </template>
                                            <template x-if="selected.profile.availability_preference">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Preferred schedule</span><span class="text-boss-ivory/70" x-text="selected.profile.availability_preference"></span></div>
                                            </template>
                                            <template x-if="selected.profile.has_private_space">
                                                <div class="flex gap-2"><span class="text-boss-ivory/38">Private space</span><span class="text-boss-ivory/70" x-text="selected.profile.has_private_space"></span></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Payout --}}
                                <template x-if="(selected.profile.payout_methods && selected.profile.payout_methods.length > 0) || selected.profile.payout_country">
                                    <div>
                                        <p class="mb-2 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Payout Information</p>
                                        <div class="space-y-1.5">
                                            <template x-if="selected.profile.payout_methods && selected.profile.payout_methods.length > 0">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="method in selected.profile.payout_methods" :key="method">
                                                        <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.65rem] text-boss-gold" x-text="method"></span>
                                                    </template>
                                                    <template x-if="selected.profile.payout_method_other">
                                                        <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.65rem] text-boss-gold" x-text="selected.profile.payout_method_other"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="selected.profile.payout_country">
                                                <div class="flex gap-2 text-[0.78rem]"><span class="text-boss-ivory/38">Country</span><span class="text-boss-ivory/70" x-text="selected.profile.payout_country"></span></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Extra details --}}
                                <template x-if="selected.profile.model_vibe">
                                    <div>
                                        <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Vibe / Niche</p>
                                        <p class="whitespace-pre-line text-sm leading-relaxed text-boss-ivory/65" x-text="selected.profile.model_vibe"></p>
                                    </div>
                                </template>
                                <template x-if="selected.profile.anything_else">
                                    <div>
                                        <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Anything Else</p>
                                        <p class="whitespace-pre-line text-sm leading-relaxed text-boss-ivory/65" x-text="selected.profile.anything_else"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Verification documents --}}
                        <template x-if="selected.profile && (selected.profile.doc_id_view || selected.profile.doc_selfie_view || selected.profile.doc_codes_view)">
                            <div>
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Verification Documents</p>
                                    <template x-if="selected.profile.verification_submitted_at">
                                        <span class="text-[0.65rem] text-boss-ivory/30" x-text="'Submitted ' + selected.profile.verification_submitted_at"></span>
                                    </template>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                                    {{-- ID Document --}}
                                    <template x-if="selected.profile.doc_id_view">
                                        <div class="overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.025]" x-data="{ imgFailed: false }">
                                            <div class="relative">
                                                <img
                                                    x-show="!imgFailed"
                                                    :src="selected.profile.doc_id_view"
                                                    x-on:error="imgFailed = true"
                                                    alt="Valid ID"
                                                    class="h-36 w-full object-cover"
                                                    loading="lazy"
                                                />
                                                <div x-show="imgFailed" class="flex h-36 w-full flex-col items-center justify-center gap-2 bg-white/[0.02]">
                                                    <svg class="h-8 w-8 text-boss-ivory/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span class="text-[0.65rem] text-boss-ivory/30">PDF Document</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between px-3 py-2">
                                                <span class="text-[0.65rem] font-medium text-boss-ivory/50">Valid ID</span>
                                                <div class="flex items-center gap-2">
                                                    <a :href="selected.profile.doc_id_view" target="_blank" rel="noopener" class="text-[0.62rem] text-boss-gold transition hover:text-boss-gold-light">View</a>
                                                    <a :href="selected.profile.doc_id_download" class="text-[0.62rem] text-boss-ivory/30 transition hover:text-boss-ivory/60" download>Download</a>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Selfie with ID --}}
                                    <template x-if="selected.profile.doc_selfie_view">
                                        <div class="overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.025]" x-data="{ imgFailed: false }">
                                            <a :href="selected.profile.doc_selfie_view" target="_blank" rel="noopener" class="block">
                                                <img
                                                    :src="selected.profile.doc_selfie_view"
                                                    x-on:error="imgFailed = true"
                                                    alt="Selfie with ID"
                                                    class="h-36 w-full object-cover transition hover:opacity-90"
                                                    loading="lazy"
                                                />
                                            </a>
                                            <div class="flex items-center justify-between px-3 py-2">
                                                <span class="text-[0.65rem] font-medium text-boss-ivory/50">Selfie + ID</span>
                                                <a :href="selected.profile.doc_selfie_download" class="text-[0.62rem] text-boss-ivory/30 transition hover:text-boss-ivory/60" download>Download</a>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Platform codes --}}
                                    <template x-if="selected.profile.doc_codes_view">
                                        <div class="overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.025]" x-data="{ imgFailed: false }">
                                            <div class="relative">
                                                <img
                                                    x-show="!imgFailed"
                                                    :src="selected.profile.doc_codes_view"
                                                    x-on:error="imgFailed = true"
                                                    alt="Platform Codes"
                                                    class="h-36 w-full object-cover"
                                                    loading="lazy"
                                                />
                                                <div x-show="imgFailed" class="flex h-36 w-full flex-col items-center justify-center gap-2 bg-white/[0.02]">
                                                    <svg class="h-8 w-8 text-boss-ivory/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span class="text-[0.65rem] text-boss-ivory/30">PDF Document</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between px-3 py-2">
                                                <span class="text-[0.65rem] font-medium text-boss-ivory/50">Platform Codes</span>
                                                <div class="flex items-center gap-2">
                                                    <a :href="selected.profile.doc_codes_view" target="_blank" rel="noopener" class="text-[0.62rem] text-boss-gold transition hover:text-boss-gold-light">View</a>
                                                    <a :href="selected.profile.doc_codes_download" class="text-[0.62rem] text-boss-ivory/30 transition hover:text-boss-ivory/60" download>Download</a>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </div>
                        </template>

                        {{-- Verification notes (if any) --}}
                        <template x-if="selected.profile && selected.profile.verification_notes">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-4">
                                <p class="mb-2 text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Verification Notes</p>
                                <p class="whitespace-pre-line text-sm leading-relaxed text-boss-ivory/65" x-text="selected.profile.verification_notes"></p>
                            </div>
                        </template>

                        {{-- Discord Community --}}
                        <template x-if="selected.profile && (selected.profile.community_invited_at || selected.profile.discord_username || selected.profile.discord_user_id || selected.profile.community_invite_sent_url)">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-5 space-y-3">
                                <p class="text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Discord Community</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-if="selected.profile.discord_username">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Discord Username</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/70" x-text="selected.profile.discord_username"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.discord_user_id">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Discord ID</p>
                                            <p class="mt-0.5 text-sm text-boss-ivory/70" x-text="selected.profile.discord_user_id"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.community_invited_at">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Discord Invited</p>
                                            <p class="mt-0.5 text-sm text-green-300" x-text="selected.profile.community_invited_at"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.community_invite_sent_url">
                                        <div class="col-span-2">
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Last Invite Link</p>
                                            <p class="mt-0.5 break-all text-sm text-boss-ivory/70" x-text="selected.profile.community_invite_sent_url"></p>
                                        </div>
                                    </template>
                                    <template x-if="selected.profile.community_role_assigned_at">
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Discord Role Assigned</p>
                                            <p class="mt-0.5 text-sm text-boss-gold" x-text="selected.profile.community_role_assigned_at"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Website walkthrough access --}}
                        <template x-if="selected.profile && selected.profile.course_access_loading">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-5 text-sm text-boss-ivory/40">
                                Loading website walkthrough access...
                            </div>
                        </template>

                        <template x-if="selected.profile && selected.profile.course_access && selected.profile.course_access.length">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-5">
                                <div class="mb-4">
                                    <p class="text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Website Walkthrough Access</p>
                                    <p class="mt-1 text-[0.72rem] leading-relaxed text-boss-ivory/35">Unlock only the website walkthroughs this model is cleared to access.</p>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="course in selected.profile.course_access" :key="course.id">
                                        <div class="rounded-xl border border-white/[0.05] bg-white/[0.02] px-3 py-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-medium text-boss-ivory/75" x-text="course.title"></p>
                                                    <p class="mt-0.5 text-[0.62rem] uppercase tracking-[0.12em]" :class="course.is_unlocked ? 'text-green-300/70' : (course.access_request_status === 'pending' ? 'text-boss-gold/75' : (course.access_request_status === 'rejected' ? 'text-red-300/70' : 'text-boss-ivory/28'))" x-text="course.is_unlocked ? 'Unlocked' : (course.access_request_label || 'Locked pending Kayla approval')"></p>
                                                </div>
                                                <template x-if="course.is_unlocked">
                                                    <form :action="course.lock_url" method="POST" class="shrink-0">
                                                        @csrf
                                                        <button type="submit" class="rounded-lg border border-red-400/20 bg-red-400/[0.07] px-3 py-1.5 text-[0.68rem] font-semibold text-red-300/80 transition hover:bg-red-400/[0.12]">
                                                            Lock
                                                        </button>
                                                    </form>
                                                </template>
                                                <template x-if="!course.is_unlocked">
                                                    <form :action="course.unlock_url" method="POST" class="shrink-0">
                                                        @csrf
                                                        <button
                                                            type="submit"
                                                            :disabled="!selected.profile.is_verified"
                                                            class="rounded-lg px-3 py-1.5 text-[0.68rem] font-semibold transition disabled:cursor-not-allowed disabled:border disabled:border-white/[0.06] disabled:bg-white/[0.03] disabled:text-boss-ivory/24"
                                                            :class="selected.profile.is_verified ? 'bg-boss-gold text-boss-ink hover:opacity-90' : ''"
                                                            x-text="selected.profile.is_verified ? (course.access_request_status === 'pending' ? 'Approve & Unlock' : 'Unlock') : 'Verify first'"
                                                        ></button>
                                                    </form>
                                                </template>
                                            </div>
                                            <template x-if="course.access_requirements">
                                                <p class="mt-2 whitespace-pre-line rounded-lg border border-white/[0.04] bg-black/10 px-3 py-2 text-[0.68rem] leading-relaxed text-boss-ivory/34" x-text="course.access_requirements"></p>
                                            </template>
                                            <template x-if="course.access_phases && course.access_phases.length">
                                                <div class="mt-2 space-y-1.5">
                                                    <template x-for="phase in course.access_phases" :key="phase.key">
                                                        <div class="rounded-lg border border-boss-gold/10 bg-boss-gold/[0.035] px-3 py-2">
                                                            <p class="text-[0.58rem] uppercase tracking-[0.12em] text-boss-gold/55" x-text="phase.label"></p>
                                                            <p class="mt-1 whitespace-pre-line text-[0.68rem] leading-relaxed text-boss-ivory/40" x-text="phase.instructions"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="course.access_request_notes">
                                                <div class="mt-2 rounded-lg border border-boss-gold/10 bg-boss-gold/[0.04] px-3 py-2">
                                                    <p class="text-[0.58rem] uppercase tracking-[0.12em] text-boss-gold/55">Model access note</p>
                                                    <p class="mt-1 whitespace-pre-line text-[0.72rem] leading-relaxed text-boss-ivory/55" x-text="course.access_request_notes"></p>
                                                </div>
                                            </template>
                                            <template x-if="course.proof_files && course.proof_files.length">
                                                <div class="mt-2 rounded-lg border border-boss-gold/10 bg-boss-gold/[0.04] px-3 py-2">
                                                    <p class="text-[0.58rem] uppercase tracking-[0.12em] text-boss-gold/55">Course proof files</p>
                                                    <div class="mt-2 space-y-1.5">
                                                        <template x-for="file in course.proof_files" :key="file.id">
                                                            <div class="flex items-center justify-between gap-2 rounded-lg border border-white/[0.04] bg-black/10 px-2.5 py-2">
                                                                <div class="min-w-0">
                                                                    <p class="truncate text-[0.72rem] text-boss-ivory/65" x-text="file.name"></p>
                                                                    <p class="text-[0.6rem] text-boss-ivory/30" x-text="file.size || file.mime_type || 'Uploaded proof'"></p>
                                                                </div>
                                                                <div class="flex shrink-0 gap-2 text-[0.62rem]">
                                                                    <a :href="file.view_url" target="_blank" rel="noopener" class="text-boss-gold transition hover:text-boss-gold-light">View</a>
                                                                    <a :href="file.download_url" class="text-boss-ivory/35 transition hover:text-boss-ivory/65">Download</a>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="course.access_admin_notes">
                                                <div class="mt-2 rounded-lg border border-red-400/10 bg-red-400/[0.04] px-3 py-2">
                                                    <p class="text-[0.58rem] uppercase tracking-[0.12em] text-red-200/55">Kayla resubmission note</p>
                                                    <p class="mt-1 whitespace-pre-line text-[0.72rem] leading-relaxed text-red-100/60" x-text="course.access_admin_notes"></p>
                                                </div>
                                            </template>
                                            <template x-if="!course.is_unlocked && course.access_request_status">
                                                <form :action="course.resubmission_url" method="POST" class="mt-2 space-y-2 rounded-lg border border-white/[0.04] bg-black/10 p-3">
                                                    @csrf
                                                    <label class="block text-[0.58rem] uppercase tracking-[0.12em] text-boss-ivory/30">Request resubmission</label>
                                                    <textarea
                                                        name="admin_notes"
                                                        rows="3"
                                                        required
                                                        class="pd-input min-h-[84px] text-[0.72rem]"
                                                        :placeholder="'Tell ' + selected.name + ' what to fix, upload, or explain before this course can be unlocked.'"
                                                        x-text="course.access_admin_notes || ''"
                                                    ></textarea>
                                                    <button type="submit" class="w-full rounded-lg border border-red-400/20 bg-red-400/[0.08] px-3 py-2 text-[0.68rem] font-semibold text-red-200 transition hover:bg-red-400/[0.13]">
                                                        Request Resubmission
                                                    </button>
                                                </form>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Member since --}}
                        <p class="text-center text-[0.65rem] text-boss-ivory/22">
                            Member since <span x-text="selected.joined"></span>
                        </p>

                    </div>{{-- end scrollable body --}}

                    {{-- ── Footer actions ──────────────────────────────── --}}
                    <template x-if="selected.profile">
                        <div class="shrink-0 space-y-3 border-t border-white/[0.06] px-6 py-4">

                            {{-- Request Verification --}}
                            <template x-if="selected.profile.can_request_verification">
                                <form :action="selected.profile.request_verification_url" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2.5 text-sm font-medium text-boss-ivory/70 transition hover:bg-white/[0.07] hover:text-boss-ivory">
                                        Send Verification Request Email
                                    </button>
                                </form>
                            </template>

                            {{-- Approve + Reject verification --}}
                            <template x-if="selected.profile.can_verify">
                                <div class="space-y-3">
                                    <form :action="selected.profile.verify_url" method="POST">
                                        @csrf
                                        <button type="submit" class="pd-btn-primary w-full">
                                            Approve Account &amp; Send Approval Email
                                        </button>
                                    </form>

                                    {{-- Reject toggle --}}
                                    <div>
                                        <button
                                            type="button"
                                            @click="showReject = !showReject"
                                            class="w-full rounded-xl border border-red-400/25 bg-red-400/[0.07] px-4 py-2.5 text-sm font-medium text-red-300/80 transition hover:bg-red-400/[0.12]"
                                            x-text="showReject ? 'Cancel' : 'Request Resubmission'"
                                        ></button>
                                        <div x-show="showReject" x-cloak class="mt-2 space-y-2">
                                            <form :action="selected.profile.reject_verification_url" method="POST">
                                                @csrf
                                                <label class="block text-[0.62rem] uppercase tracking-[0.14em] text-red-200/65">
                                                    Resubmission instructions for the model
                                                </label>
                                                <p class="text-[0.72rem] leading-relaxed text-red-100/45">
                                                    Tell them exactly which file or detail needs to be updated. This note will appear in their dashboard and email.
                                                </p>
                                                <textarea
                                                    name="verification_notes"
                                                    rows="3"
                                                    placeholder="Example: Please upload a clearer valid ID photo and make sure all corners are visible."
                                                    required
                                                    class="pd-input w-full text-sm"
                                                ></textarea>
                                                <button type="submit" class="mt-2 w-full rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-400/20">
                                                    Send Resubmission Request
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Discord Community invite --}}
                            <template x-if="selected.profile.can_community_invite">
                                <form :action="selected.profile.community_invite_url" method="POST" class="space-y-2">
                                    @csrf
                                    <label class="block text-[0.62rem] uppercase tracking-[0.14em] text-boss-ivory/35">Discord invite link</label>
                                    <input
                                        type="url"
                                        name="community_url"
                                        :value="selected.profile.community_invite_sent_url || selected.profile.default_community_url || ''"
                                        placeholder="https://discord.gg/example"
                                        class="pd-input w-full text-sm"
                                        required
                                    >
                                    <p class="text-[0.72rem] leading-relaxed text-boss-ivory/35">Paste the current Discord invite link before sending. If an old invite expires, paste a fresh one and resend it here.</p>
                                    <button type="submit" class="pd-btn-primary w-full">
                                        <span x-text="selected.profile.community_invited_at ? 'Resend Discord Community Access Email' : 'Send Discord Community Access Email'"></span>
                                    </button>
                                </form>
                            </template>

                            {{-- Mark role assigned --}}
                            <template x-if="selected.profile.can_role_assign">
                                <form :action="selected.profile.community_role_url" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2.5 text-sm font-medium text-boss-ivory/70 transition hover:bg-white/[0.07] hover:text-boss-ivory">
                                        Mark Discord Role Assigned
                                    </button>
                                </form>
                            </template>

                            {{-- All done --}}
                            <template x-if="!selected.profile.can_request_verification && !selected.profile.can_verify && !selected.profile.can_community_invite && !selected.profile.can_role_assign">
                                <p class="text-center text-sm text-boss-ivory/30">
                                    <span x-text="selected.profile.community_role_assigned_at ? 'Fully onboarded ✓' : 'No actions available at this stage.'"></span>
                                </p>
                            </template>

                        </div>
                    </template>

                </div>
            </template>
        </div>

        {{-- ── Page header ───────────────────────────────────────── --}}
        <header class="flex flex-col justify-between gap-4 xl:flex-row xl:items-end">
            <div>
                <p class="pd-kicker">{{ __('Onboarding') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Model Onboarding') }}</h1>
            </div>
            <a href="{{ route('admin.onboarding.export') }}" class="pd-btn-secondary h-11 w-fit gap-2">
                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M8 2v8m0 0 3-3m-3 3L5 7"/>
                    <path d="M3 12.5h10"/>
                </svg>
                {{ __('Download CRM Excel') }}
            </a>
        </header>

        {{-- ── Flash messages ────────────────────────────────────── --}}
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- ── Stats ─────────────────────────────────────────────── --}}
        <section class="grid grid-cols-2 gap-3 xl:grid-cols-6">
            @foreach ([
                [__('Members'), $stats['members']],
                [__('Info Forms'), $stats['information_submitted']],
                [__('Verification Review'), $stats['verification_submitted']],
                [__('Verified'), $stats['verified']],
                [__('Discord Invites'), $stats['community_invited']],
                [__('Discord Roles'), $stats['role_assigned']],
            ] as $stat)
                <div class="pd-stat">
                    <p class="font-display text-[2rem] leading-none text-boss-gold">{{ $stat[1] }}</p>
                    <p class="mt-3 text-[0.68rem] uppercase tracking-[0.08em] text-boss-ivory/50">{{ $stat[0] }}</p>
                </div>
            @endforeach
        </section>

        {{-- ── Members table ─────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel-strong">
            <div class="overflow-x-auto">
                <table class="pd-table min-w-[640px]">
                    <thead>
                        <tr>
                            <th class="text-left">{{ __('Member') }}</th>
                            <th class="text-left">{{ __('Steps') }}</th>
                            <th class="text-left">{{ __('Verification') }}</th>
                            <th class="text-right text-boss-ivory/30">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($models as $model)
                            @php
                                $profile = $model->modelProfile;
                                $modelData = [
                                    'id'      => $model->id,
                                    'name'    => $model->name,
                                    'email'   => $model->email,
                                    'joined'  => $model->created_at->toFormattedDateString(),
                                    'profile' => $profile ? [
                                        'id'                          => $profile->id,
                                        'legal_name'                  => $profile->legal_name,
                                        'stage_name'                  => $profile->stage_name,
                                        'date_of_birth'               => $profile->date_of_birth?->format('M d, Y'),
                                        'phone'                       => $profile->phone,
                                        'country'                     => $profile->country,
                                        'city'                        => $profile->city,
                                        'timezone'                    => $profile->timezone,
                                        'platforms'                   => $profile->platforms ?? [],
                                        'current_platforms'           => $profile->current_platforms,
                                        'fetishes_checklist'          => $profile->fetishes_checklist ?? [],
                                        'nationality'                 => $profile->nationality,
                                        'spoken_languages'            => $profile->spoken_languages,
                                        'social_handles'              => $profile->social_handles,
                                        'with_other_agency'           => $profile->with_other_agency,
                                        'hear_about_us'               => $profile->hear_about_us,
                                        'height'                      => $profile->height,
                                        'weight'                      => $profile->weight,
                                        'hair_color'                  => $profile->hair_color,
                                        'eye_color'                   => $profile->eye_color,
                                        'body_type'                   => $profile->body_type,
                                        'has_tattoos_piercings'       => $profile->has_tattoos_piercings,
                                        'work_interests'              => $profile->work_interests ?? [],
                                        'comfort_levels'              => $profile->comfort_levels ?? [],
                                        'custom_content_ok'           => $profile->custom_content_ok,
                                        'worn_items_ok'               => $profile->worn_items_ok,
                                        'weekly_availability'         => $profile->weekly_availability,
                                        'availability_preference'     => $profile->availability_preference,
                                        'has_private_space'           => $profile->has_private_space,
                                        'payout_methods'              => $profile->payout_methods ?? [],
                                        'payout_method_other'         => $profile->payout_method_other,
                                        'payout_country'              => $profile->payout_country,
                                        'model_vibe'                  => $profile->model_vibe,
                                        'anything_else'               => $profile->anything_else,
                                        'equipment'                   => $profile->equipment ?? [],
                                        'availability'                => $profile->availability,
                                        'goals'                       => $profile->goals,
                                        'experience_notes'            => $profile->experience_notes,
                                        'emergency_contact_name'      => $profile->emergency_contact_name,
                                        'emergency_contact_phone'     => $profile->emergency_contact_phone,
                                        'discord_username'            => $profile->discord_username,
                                        'discord_user_id'             => $profile->discord_user_id,
                                        'onboarding_stage'            => $profile->onboarding_stage ?: \App\Models\ModelProfile::STAGE_REGISTRATION,
                                        'onboarding_stage_label'      => $profile->onboardingStageLabel(),
                                        'stage_url'                   => route('admin.onboarding.stage', $profile),
                                        'verification_request_instructions' => $profile->verification_request_instructions,
                                        'verification_instructions_url' => route('admin.onboarding.verification-instructions', $profile),
                                        'details_url'                  => route('admin.onboarding.details', $profile),
                                        'has_information_form'        => $profile->hasInformationForm(),
                                        'information_submitted_at'    => $profile->information_submitted_at?->toFormattedDateString(),
                                        'verification_status'         => $profile->verification_status,
                                        'verification_status_label'   => $profile->verificationStatusLabel(),
                                        'verification_submitted_at'   => $profile->verification_submitted_at?->toFormattedDateString(),
                                        'verification_notes'          => $profile->verification_notes,
                                        'is_verified'                 => $profile->isVerified(),
                                        'has_verification_submission' => $profile->hasVerificationSubmission(),
                                        'doc_id_view'       => $profile->id_document_path       ? route('admin.onboarding.documents.view', [$profile, 'id'])     : null,
                                        'doc_id_download'   => $profile->id_document_path       ? route('admin.onboarding.documents.show', [$profile, 'id'])     : null,
                                        'doc_selfie_view'   => $profile->selfie_with_id_path    ? route('admin.onboarding.documents.view', [$profile, 'selfie']) : null,
                                        'doc_selfie_download' => $profile->selfie_with_id_path  ? route('admin.onboarding.documents.show', [$profile, 'selfie']) : null,
                                        'doc_codes_view'    => $profile->platform_codes_path    ? route('admin.onboarding.documents.view', [$profile, 'codes'])  : null,
                                        'doc_codes_download'=> $profile->platform_codes_path    ? route('admin.onboarding.documents.show', [$profile, 'codes'])  : null,
                                        'community_invited_at'        => $profile->community_invited_at?->toFormattedDateString(),
                                        'community_invite_sent_url'   => $profile->community_invite_url,
                                        'default_community_url'       => config('paradise.community_url'),
                                        'community_role_assigned_at'  => $profile->community_role_assigned_at?->toFormattedDateString(),
                                        'onboarding_percent'          => $profile->onboardingPercent(),
                                        'course_access'               => [],
                                        'course_access_loaded'        => false,
                                        'course_access_loading'       => false,
                                        'request_verification_url'    => route('admin.onboarding.request-verification', $profile),
                                        'verify_url'                  => route('admin.onboarding.verify', $profile),
                                        'reject_verification_url'     => route('admin.onboarding.reject-verification', $profile),
                                        'community_invite_url'        => route('admin.onboarding.community-invite', $profile),
                                        'community_role_url'          => route('admin.onboarding.community-role-assigned', $profile),
                                        'can_request_verification'    => $profile->hasInformationForm() && ! $profile->isVerified() && $profile->verification_status !== \App\Models\ModelProfile::VERIFICATION_SUBMITTED,
                                        'can_verify'                  => $profile->verification_status === \App\Models\ModelProfile::VERIFICATION_SUBMITTED,
                                        'can_community_invite'        => $profile->isVerified() && ! $profile->community_role_assigned_at,
                                        'can_role_assign'             => $profile->isVerified() && (bool) $profile->community_invited_at && ! $profile->community_role_assigned_at,
                                    ] : null,
                                ];
                            @endphp
                            <tr
                                class="cursor-pointer transition hover:bg-white/[0.025]"
                                @click="selectModel({{ Js::from($modelData) }})"
                            >
                                <td class="align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/10 font-display text-[0.72rem] text-boss-gold">
                                            {{ strtoupper(substr($model->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-boss-ivory">{{ $model->name }}</div>
                                            <div class="text-[0.74rem] text-boss-ivory/35">{{ $model->email }}</div>
                                            @if ($profile?->stage_name)
                                                <div class="text-[0.72rem] text-boss-gold/70">{{ $profile->stage_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="flex flex-wrap gap-1.5">
                                        {{-- Info form step --}}
                                        @if ($profile?->hasInformationForm())
                                            <span class="rounded-full bg-green-400/10 px-2 py-0.5 text-[0.62rem] text-green-300">Info ✓</span>
                                        @else
                                            <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.62rem] text-boss-ivory/28">Info</span>
                                        @endif

                                        {{-- Verification step --}}
                                        @if ($profile?->isVerified())
                                            <span class="rounded-full bg-green-400/10 px-2 py-0.5 text-[0.62rem] text-green-300">Verified ✓</span>
                                        @elseif ($profile?->verification_status === \App\Models\ModelProfile::VERIFICATION_SUBMITTED)
                                            <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.62rem] text-boss-gold">Review</span>
                                        @elseif ($profile?->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED)
                                            <span class="rounded-full bg-red-400/10 px-2 py-0.5 text-[0.62rem] text-red-300">Resubmit</span>
                                        @else
                                            <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.62rem] text-boss-ivory/28">Verify</span>
                                        @endif

                                        {{-- Discord Community step --}}
                                        @if ($profile?->community_role_assigned_at)
                                            <span class="rounded-full bg-green-400/10 px-2 py-0.5 text-[0.62rem] text-green-300">Active ✓</span>
                                        @elseif ($profile?->community_invited_at)
                                            <span class="rounded-full bg-green-400/[0.07] px-2 py-0.5 text-[0.62rem] text-green-400/60">Discord invited</span>
                                        @else
                                            <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.62rem] text-boss-ivory/28">Discord</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle">
                                    @if ($profile)
                                        <span class="rounded-full px-2.5 py-1 text-[0.65rem] {{ $profile->isVerified() ? 'bg-green-400/10 text-green-300' : ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_SUBMITTED ? 'bg-boss-gold/10 text-boss-gold' : ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED ? 'bg-red-400/10 text-red-300' : 'bg-white/[0.04] text-boss-ivory/35')) }}">
                                            {{ $profile->verificationStatusLabel() }}
                                        </span>
                                    @else
                                        <span class="text-[0.72rem] text-boss-ivory/24">No profile</span>
                                    @endif
                                </td>
                                <td class="align-middle text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="inline-flex items-center gap-1 text-[0.72rem] text-boss-ivory/30">
                                            Quick view
                                        </span>
                                        @if ($profile)
                                            <a href="{{ route('admin.onboarding.show', $profile) }}"
                                               class="rounded-lg border border-boss-gold/20 bg-boss-gold/[0.07] px-2.5 py-1 text-[0.7rem] text-boss-gold transition hover:bg-boss-gold/[0.13]">
                                                Full Profile
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-boss-ivory/35">{{ __('No member accounts yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-2">{{ $models->links() }}</div>

    </div>
</x-admin-layout>
