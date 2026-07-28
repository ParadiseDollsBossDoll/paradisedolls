<x-member-layout>
    @php
        $ratingFields = [
            'communication_rating' => __('Communication'),
            'professionalism_rating' => __('Professionalism'),
            'friendliness_rating' => __('Friendliness & Respect'),
            'response_time_rating' => __('Response Time'),
            'reliability_rating' => __('Reliability & Attendance'),
            'customer_engagement_rating' => __('Customer Engagement'),
            'sales_encouragement_rating' => __('Ability to Encourage Tips, Private Shows & Sales'),
            'support_motivation_rating' => __('Support & Motivation'),
            'boundary_understanding_rating' => __('Understanding of Your Boundaries'),
            'overall_performance_rating' => __('Overall Performance'),
        ];

        $choiceGroups = [
            'sounds_like_model' => [
                'label' => __('Does your chatter communicate in a way that genuinely sounds like you and represents your personality?'),
                'options' => [
                    'always' => __('Always'),
                    'most_of_the_time' => __('Most of the time'),
                    'sometimes' => __('Sometimes'),
                    'rarely' => __('Rarely'),
                    'never' => __('Never'),
                ],
            ],
            'understands_personality' => [
                'label' => __('Does your chatter understand your personality, brand and personal goals?'),
                'options' => [
                    'completely' => __('Completely'),
                    'mostly' => __('Mostly'),
                    'somewhat' => __('Somewhat'),
                    'very_little' => __('Very little'),
                    'not_at_all' => __('Not at all'),
                ],
            ],
            'respects_boundaries' => [
                'label' => __('Does your chatter respect your personal boundaries?'),
                'options' => [
                    'always' => __('Always'),
                    'most_of_the_time' => __('Most of the time'),
                    'sometimes' => __('Sometimes'),
                    'rarely' => __('Rarely'),
                    'never' => __('Never'),
                ],
            ],
            'performance_feeling' => [
                'label' => __('Do you personally feel your chatter could be performing better?'),
                'options' => [
                    'happy' => __("No, I'm completely happy"),
                    'small_improvements' => __('A few small improvements could be made'),
                    'several_areas' => __('They could improve in several areas'),
                    'significant_improvement' => __('Significant improvement is needed'),
                ],
            ],
            'wants_to_help' => [
                'label' => __('Do you feel your chatter genuinely wants to help you succeed?'),
                'options' => [
                    'definitely' => __('Definitely'),
                    'mostly' => __('Mostly'),
                    'sometimes' => __('Sometimes'),
                    'rarely' => __('Rarely'),
                    'no' => __('No'),
                ],
            ],
            'confidence_improved' => [
                'label' => __('Has your chatter helped improve your confidence whilst streaming?'),
                'options' => [
                    'yes_significantly' => __('Yes, significantly'),
                    'yes' => __('Yes'),
                    'a_little' => __('A little'),
                    'not_really' => __('Not really'),
                    'no' => __('No'),
                ],
            ],
            'customer_engagement_improved' => [
                'label' => __('Has your chatter helped improve your customer engagement and overall results?'),
                'options' => [
                    'yes_significantly' => __('Yes, significantly'),
                    'yes' => __('Yes'),
                    'a_little' => __('A little'),
                    'not_really' => __('Not really'),
                    'no' => __('No'),
                ],
            ],
            'continue_working' => [
                'label' => __('If you had the opportunity, would you continue working with your current chatter?'),
                'options' => [
                    'definitely' => __('Definitely'),
                    'yes_with_improvements' => __('Yes, but with a few improvements'),
                    'unsure' => __("I'm unsure"),
                    'try_another' => __("I'd prefer to try another chatter"),
                    'change_chatters' => __('I would definitely change chatters'),
                ],
            ],
        ];

        $textQuestions = [
            'does_well' => __('What does your chatter do particularly well?'),
            'could_improve' => __('What areas do you think your chatter could improve?'),
            'missed_opportunities' => __('Do you feel there are any missed opportunities during your streams?'),
            'natural_speech' => __('Does your chatter understand how you would naturally speak to customers?'),
            'one_change' => __('If you could change one thing about your chatter, what would it be?'),
            'recognition' => __('Is there anything your chatter does exceptionally well that deserves recognition?'),
            'anything_else' => __('Is there anything else you would like Paradise Dolls Management to know?'),
        ];
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Confidential Internal Review') }}</p>
                <h1 class="pd-heading pd-text-gradient mt-2 text-[clamp(2rem,4vw,2.7rem)]">{{ __('First Month Chatter Performance Review') }}</h1>
                <p class="mt-2 max-w-3xl text-[0.86rem] leading-relaxed text-boss-ivory/45">{{ __('This private review helps Paradise Dolls recognise excellent chatter support, spot where extra training may be needed, and make sure every model receives the highest standard of service.') }}</p>
            </div>
        </header>

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

        <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
            <form method="POST" action="{{ url()->full() }}" class="pd-panel-strong space-y-6 p-5 md:p-6">
                @csrf

                <section class="space-y-4">
                    <div>
                        <p class="pd-kicker">{{ __('Confidentiality & Anonymous Feedback') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Your identity choice') }}</h2>
                        <p class="mt-2 text-[0.82rem] leading-relaxed text-boss-ivory/45">{{ __('You are welcome to complete this review either anonymously or with your name. Your honest feedback will never affect your position within Paradise Dolls.') }}</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-white/[0.08] bg-white/[0.03] p-4">
                            <input type="radio" name="is_anonymous" value="1" class="mt-1" @checked(old('is_anonymous', '1') === '1') required>
                            <span>
                                <span class="block text-sm font-semibold text-boss-ivory">{{ __('Yes - keep my identity confidential') }}</span>
                                <span class="mt-1 block text-[0.72rem] leading-relaxed text-boss-ivory/35">{{ __('Admins will see the answers without your display name on the review card.') }}</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-white/[0.08] bg-white/[0.03] p-4">
                            <input type="radio" name="is_anonymous" value="0" class="mt-1" @checked(old('is_anonymous') === '0') required>
                            <span>
                                <span class="block text-sm font-semibold text-boss-ivory">{{ __('No - include my name') }}</span>
                                <span class="mt-1 block text-[0.72rem] leading-relaxed text-boss-ivory/35">{{ __('Use this if you are comfortable with management seeing who submitted it.') }}</span>
                            </span>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="model_name" :value="__('Model name')" />
                            <x-text-input id="model_name" name="model_name" class="mt-2" :value="old('model_name', auth()->user()->name)" />
                        </div>
                        <div>
                            <x-input-label for="chatter_user_id" :value="__('Chatter account')" />
                            <select id="chatter_user_id" name="chatter_user_id" class="pd-input mt-2">
                                <option value="">{{ __('Select chatter, or type a name below') }}</option>
                                @foreach ($chatters as $chatter)
                                    <option value="{{ $chatter->id }}" @selected((string) old('chatter_user_id') === (string) $chatter->id)>{{ $chatter->name }} - {{ $chatter->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="chatter_name" :value="__('Chatter name')" />
                            <x-text-input id="chatter_name" name="chatter_name" class="mt-2" :value="old('chatter_name')" placeholder="{{ __('If the chatter is not in the list') }}" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="review_date" :value="__('Review date')" />
                                <x-text-input id="review_date" name="review_date" type="date" class="mt-2" :value="old('review_date', now()->toDateString())" />
                            </div>
                            <div>
                                <x-input-label for="review_period" :value="__('Review period')" />
                                <x-text-input id="review_period" name="review_period" class="mt-2" :value="old('review_period')" placeholder="{{ __('First month') }}" />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-white/[0.06] pt-6">
                    <div>
                        <p class="pd-kicker">{{ __('Performance Ratings') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Rate your chatter') }}</h2>
                        <p class="mt-2 text-[0.78rem] text-boss-ivory/35">{{ __('1 = Poor, 2 = Needs Improvement, 3 = Good, 4 = Very Good, 5 = Excellent') }}</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach ($ratingFields as $field => $label)
                            <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                                <legend class="text-[0.82rem] font-semibold text-boss-ivory">{{ $label }}</legend>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @for ($rating = 1; $rating <= 5; $rating++)
                                        <label class="pd-rating-option cursor-pointer">
                                            <input type="radio" name="{{ $field }}" value="{{ $rating }}" class="sr-only" @checked((string) old($field) === (string) $rating) required>
                                            <span class="pd-rating-pill">{{ $rating }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-4 border-t border-white/[0.06] pt-6">
                    <div>
                        <p class="pd-kicker">{{ __('Your Experience') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('How the support feels') }}</h2>
                    </div>

                    <div class="space-y-4">
                        @foreach ($choiceGroups as $field => $group)
                            <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                                <legend class="text-[0.86rem] font-semibold leading-relaxed text-boss-ivory">{{ $group['label'] }}</legend>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    @foreach ($group['options'] as $value => $option)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-sm border border-white/[0.06] bg-white/[0.025] px-3 py-2 text-[0.78rem] text-boss-ivory/70 has-[:checked]:border-boss-gold/60 has-[:checked]:bg-boss-gold/10 has-[:checked]:text-boss-ivory">
                                            <input type="radio" name="{{ $field }}" value="{{ $value }}" @checked(old($field) === $value) required>
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-4 border-t border-white/[0.06] pt-6">
                    <div>
                        <p class="pd-kicker">{{ __('Additional Feedback') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Written notes') }}</h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($textQuestions as $field => $label)
                            <div>
                                <x-input-label for="{{ $field }}" :value="$label" />
                                <textarea id="{{ $field }}" name="{{ $field }}" rows="4" class="pd-input mt-2">{{ old($field) }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                        <legend class="text-[0.86rem] font-semibold leading-relaxed text-boss-ivory">{{ __('Has your chatter ever said or done anything that did not feel like you, or made you uncomfortable?') }}</legend>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <label class="inline-flex items-center gap-2 text-[0.82rem] text-boss-ivory/70">
                                <input type="radio" name="uncomfortable_incident" value="no" @checked(old('uncomfortable_incident', 'no') === 'no') required>
                                {{ __('No') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-[0.82rem] text-boss-ivory/70">
                                <input type="radio" name="uncomfortable_incident" value="yes" @checked(old('uncomfortable_incident') === 'yes') required>
                                {{ __('Yes') }}
                            </label>
                        </div>
                        <textarea name="uncomfortable_explanation" rows="4" class="pd-input mt-4" placeholder="{{ __('If yes, please explain.') }}">{{ old('uncomfortable_explanation') }}</textarea>
                    </fieldset>
                </section>

                <section class="space-y-4 border-t border-white/[0.06] pt-6">
                    <div>
                        <p class="pd-kicker">{{ __('Overall Satisfaction') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Final questions') }}</h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                            <legend class="text-[0.82rem] font-semibold text-boss-ivory">{{ __('Overall, how satisfied are you with your chatter?') }}</legend>
                            <div class="mt-3 space-y-2">
                                @foreach (['very_satisfied' => __('Very Satisfied'), 'satisfied' => __('Satisfied'), 'neutral' => __('Neutral'), 'unsatisfied' => __('Unsatisfied'), 'very_unsatisfied' => __('Very Unsatisfied')] as $value => $label)
                                    <label class="flex items-center gap-2 text-[0.78rem] text-boss-ivory/70">
                                        <input type="radio" name="overall_satisfaction" value="{{ $value }}" @checked(old('overall_satisfaction') === $value) required>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                            <legend class="text-[0.82rem] font-semibold text-boss-ivory">{{ __('Would you recommend this chatter to another Paradise Dolls model?') }}</legend>
                            <div class="mt-3 space-y-2">
                                @foreach (['yes' => __('Yes'), 'maybe' => __('Maybe'), 'no' => __('No')] as $value => $label)
                                    <label class="flex items-center gap-2 text-[0.78rem] text-boss-ivory/70">
                                        <input type="radio" name="would_recommend" value="{{ $value }}" @checked(old('would_recommend') === $value) required>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                            <legend class="text-[0.82rem] font-semibold text-boss-ivory">{{ __('Would you like management to contact you about this review?') }}</legend>
                            <div class="mt-3 space-y-2">
                                <label class="flex items-center gap-2 text-[0.78rem] text-boss-ivory/70">
                                    <input type="radio" name="contact_requested" value="1" @checked(old('contact_requested') === '1') required>
                                    {{ __('Yes') }}
                                </label>
                                <label class="flex items-center gap-2 text-[0.78rem] text-boss-ivory/70">
                                    <input type="radio" name="contact_requested" value="0" @checked(old('contact_requested', '0') === '0') required>
                                    {{ __('No') }}
                                </label>
                            </div>
                        </fieldset>
                    </div>
                </section>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-white/[0.06] pt-5">
                    <x-primary-button>{{ __('Submit confidential review') }}</x-primary-button>
                </div>
            </form>

            <aside class="pd-panel p-5">
                <p class="pd-kicker text-boss-ivory/35">{{ __('Review Status') }}</p>
                <h2 class="pd-heading mt-2 text-[1.25rem] text-boss-ivory">{{ __('Your submitted reviews') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($reviews as $review)
                        <div class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.62rem] text-boss-gold">{{ $review->statusLabel() }}</span>
                                <span class="text-[0.62rem] text-boss-ivory/25">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mt-3 text-[0.9rem] text-boss-ivory/75">{{ $review->chatter_name ?: __('Chatter not specified') }}</p>
                            <p class="mt-1 text-[0.72rem] text-boss-ivory/35">{{ __('Average score') }}: {{ number_format((float) $review->average_score, 2) }}/5</p>
                        </div>
                    @empty
                        <div class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-5 text-[0.82rem] leading-relaxed text-boss-ivory/35">
                            {{ __('After you submit a chatter review, its management status will appear here.') }}
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>
</x-member-layout>
