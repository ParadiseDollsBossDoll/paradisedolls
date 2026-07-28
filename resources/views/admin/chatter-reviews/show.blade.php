<x-admin-layout>
    @php
        $ratingLabels = [
            'communication_rating' => __('Communication'),
            'professionalism_rating' => __('Professionalism'),
            'friendliness_rating' => __('Friendliness & Respect'),
            'response_time_rating' => __('Response Time'),
            'reliability_rating' => __('Reliability & Attendance'),
            'customer_engagement_rating' => __('Customer Engagement'),
            'sales_encouragement_rating' => __('Tips, Private Shows & Sales'),
            'support_motivation_rating' => __('Support & Motivation'),
            'boundary_understanding_rating' => __('Understanding Boundaries'),
            'overall_performance_rating' => __('Overall Performance'),
        ];

        $answerLabels = [
            'sounds_like_model' => [
                'label' => __('Sounds like the model'),
                'values' => ['always' => __('Always'), 'most_of_the_time' => __('Most of the time'), 'sometimes' => __('Sometimes'), 'rarely' => __('Rarely'), 'never' => __('Never')],
            ],
            'understands_personality' => [
                'label' => __('Understands personality and goals'),
                'values' => ['completely' => __('Completely'), 'mostly' => __('Mostly'), 'somewhat' => __('Somewhat'), 'very_little' => __('Very little'), 'not_at_all' => __('Not at all')],
            ],
            'respects_boundaries' => [
                'label' => __('Respects personal boundaries'),
                'values' => ['always' => __('Always'), 'most_of_the_time' => __('Most of the time'), 'sometimes' => __('Sometimes'), 'rarely' => __('Rarely'), 'never' => __('Never')],
            ],
            'performance_feeling' => [
                'label' => __('Performance feeling'),
                'values' => ['happy' => __("No, completely happy"), 'small_improvements' => __('Small improvements'), 'several_areas' => __('Several areas'), 'significant_improvement' => __('Significant improvement needed')],
            ],
            'wants_to_help' => [
                'label' => __('Wants to help model succeed'),
                'values' => ['definitely' => __('Definitely'), 'mostly' => __('Mostly'), 'sometimes' => __('Sometimes'), 'rarely' => __('Rarely'), 'no' => __('No')],
            ],
            'confidence_improved' => [
                'label' => __('Improved confidence'),
                'values' => ['yes_significantly' => __('Yes, significantly'), 'yes' => __('Yes'), 'a_little' => __('A little'), 'not_really' => __('Not really'), 'no' => __('No')],
            ],
            'customer_engagement_improved' => [
                'label' => __('Improved customer engagement'),
                'values' => ['yes_significantly' => __('Yes, significantly'), 'yes' => __('Yes'), 'a_little' => __('A little'), 'not_really' => __('Not really'), 'no' => __('No')],
            ],
            'continue_working' => [
                'label' => __('Would continue with chatter'),
                'values' => ['definitely' => __('Definitely'), 'yes_with_improvements' => __('Yes, with improvements'), 'unsure' => __('Unsure'), 'try_another' => __('Prefer another chatter'), 'change_chatters' => __('Would change chatters')],
            ],
            'overall_satisfaction' => [
                'label' => __('Overall satisfaction'),
                'values' => ['very_satisfied' => __('Very Satisfied'), 'satisfied' => __('Satisfied'), 'neutral' => __('Neutral'), 'unsatisfied' => __('Unsatisfied'), 'very_unsatisfied' => __('Very Unsatisfied')],
            ],
            'would_recommend' => [
                'label' => __('Would recommend chatter'),
                'values' => ['yes' => __('Yes'), 'maybe' => __('Maybe'), 'no' => __('No')],
            ],
        ];

        $writtenFields = [
            'does_well' => __('What the chatter does well'),
            'could_improve' => __('Areas to improve'),
            'missed_opportunities' => __('Missed opportunities'),
            'natural_speech' => __('Natural speech / model voice'),
            'uncomfortable_explanation' => __('Uncomfortable incident explanation'),
            'one_change' => __('One thing to change'),
            'recognition' => __('Recognition'),
            'anything_else' => __('Anything else'),
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.chatter-reviews.index') }}" class="text-[0.82rem] text-boss-gold hover:text-boss-gold-light">{{ __('Back to chatter reviews') }}</a>
                <p class="pd-kicker mt-5">{{ __('Confidential Internal Review') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.7rem)]">{{ __('First Month Chatter Performance Review') }}</h1>
                <p class="mt-2 text-[0.85rem] text-boss-ivory/40">{{ $review->created_at->format('M j, Y g:i A') }} - {{ $review->statusLabel() }}</p>
            </div>
            <span class="self-start rounded-full bg-boss-gold/10 px-4 py-2 text-[0.78rem] text-boss-gold sm:self-auto">{{ number_format((float) $review->average_score, 2) }}/5 {{ __('average') }}</span>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <section class="pd-panel grid gap-4 p-5 md:grid-cols-4">
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Model') }}</p>
                <p class="mt-2 font-semibold">{{ $review->is_anonymous ? __('Anonymous model') : ($review->model_name ?: $review->member?->name) }}</p>
                <p class="mt-1 text-[0.72rem] text-boss-ivory/35">{{ $review->is_anonymous ? __('Identity hidden') : $review->member?->email }}</p>
            </div>
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Chatter') }}</p>
                <p class="mt-2 font-semibold">{{ $review->chatter_name ?: $review->chatter?->name ?: __('Not specified') }}</p>
                <p class="mt-1 text-[0.72rem] text-boss-ivory/35">{{ $review->chatter?->email }}</p>
            </div>
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Review date') }}</p>
                <p class="mt-2 font-semibold">{{ $review->review_date?->format('M j, Y') ?: __('Not provided') }}</p>
                <p class="mt-1 text-[0.72rem] text-boss-ivory/35">{{ $review->review_period ?: __('No review period') }}</p>
            </div>
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Contact requested') }}</p>
                <p class="mt-2 font-semibold">{{ $review->contact_requested ? __('Yes') : __('No') }}</p>
                <p class="mt-1 text-[0.72rem] text-boss-ivory/35">{{ $review->uncomfortable_incident === 'yes' ? __('Incident noted') : __('No incident noted') }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
            <div class="space-y-6">
                <section class="pd-panel overflow-hidden">
                    <div class="border-b border-white/[0.06] p-5">
                        <p class="pd-kicker text-boss-ivory/35">{{ __('Performance Ratings') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Score breakdown') }}</h2>
                    </div>
                    <div class="grid gap-px bg-white/[0.06] sm:grid-cols-2">
                        @foreach ($ratingLabels as $field => $label)
                            <div class="bg-[#141419] p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-[0.82rem] text-boss-ivory/65">{{ $label }}</p>
                                    <p class="text-[1rem] font-semibold text-boss-gold">{{ $review->{$field} }}/5</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="pd-panel overflow-hidden">
                    <div class="border-b border-white/[0.06] p-5">
                        <p class="pd-kicker text-boss-ivory/35">{{ __('Model Experience') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Selected answers') }}</h2>
                    </div>
                    <div class="grid gap-px bg-white/[0.06] md:grid-cols-2">
                        @foreach ($answerLabels as $field => $group)
                            <div class="bg-[#141419] p-4">
                                <p class="pd-kicker text-boss-ivory/25">{{ $group['label'] }}</p>
                                <p class="mt-2 text-[0.88rem] text-boss-ivory/75">{{ $group['values'][$review->{$field}] ?? $review->{$field} }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="pd-panel overflow-hidden">
                    <div class="border-b border-white/[0.06] p-5">
                        <p class="pd-kicker text-boss-ivory/35">{{ __('Additional Feedback') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Written responses') }}</h2>
                    </div>
                    <div class="divide-y divide-white/[0.06]">
                        @foreach ($writtenFields as $field => $label)
                            <div class="p-5">
                                <p class="pd-kicker text-boss-ivory/25">{{ $label }}</p>
                                <p class="mt-2 whitespace-pre-line text-[0.86rem] leading-relaxed text-boss-ivory/65">{{ filled($review->{$field}) ? $review->{$field} : __('No answer provided.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <form method="POST" action="{{ route('admin.chatter-reviews.update', $review) }}" class="pd-panel space-y-5 p-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <p class="pd-kicker text-boss-ivory/35">{{ __('Management Use Only') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Internal decision') }}</h2>
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Review status')" />
                        <select id="status" name="status" class="pd-input mt-2" required>
                            @foreach ([
                                \App\Models\ChatterPerformanceReview::STATUS_SUBMITTED => __('Submitted'),
                                \App\Models\ChatterPerformanceReview::STATUS_REVIEWED => __('Reviewed'),
                                \App\Models\ChatterPerformanceReview::STATUS_FOLLOW_UP => __('Follow-up needed'),
                                \App\Models\ChatterPerformanceReview::STATUS_CLOSED => __('Closed'),
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $review->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="management_performance" :value="__('Overall performance')" />
                        <select id="management_performance" name="management_performance" class="pd-input mt-2">
                            <option value="">{{ __('Not selected') }}</option>
                            @foreach ([
                                'outstanding' => __('Outstanding'),
                                'excellent' => __('Excellent'),
                                'meets_expectations' => __('Meets Expectations'),
                                'requires_improvement' => __('Requires Improvement'),
                                'immediate_coaching_required' => __('Immediate Coaching Required'),
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(old('management_performance', $review->management_performance) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="model_wishes_to_remain" :value="__('Model wishes to remain with this chatter')" />
                        <select id="model_wishes_to_remain" name="model_wishes_to_remain" class="pd-input mt-2">
                            <option value="">{{ __('Not selected') }}</option>
                            @foreach ([
                                'yes' => __('Yes'),
                                'yes_with_improvements' => __('Yes, with improvements'),
                                'unsure' => __('Unsure'),
                                'no' => __('No'),
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(old('model_wishes_to_remain', $review->model_wishes_to_remain) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <fieldset>
                        <legend class="pd-kicker text-boss-ivory/35">{{ __('Additional training required') }}</legend>
                        <div class="mt-3 flex gap-4">
                            <label class="inline-flex items-center gap-2 text-[0.82rem] text-boss-ivory/70">
                                <input type="radio" name="additional_training_required" value="1" @checked(old('additional_training_required', $review->additional_training_required) === true || old('additional_training_required') === '1')>
                                {{ __('Yes') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-[0.82rem] text-boss-ivory/70">
                                <input type="radio" name="additional_training_required" value="0" @checked(old('additional_training_required', $review->additional_training_required) === false || old('additional_training_required') === '0')>
                                {{ __('No') }}
                            </label>
                        </div>
                    </fieldset>

                    <div>
                        <x-input-label for="manager_notes" :value="__('Manager notes')" />
                        <textarea id="manager_notes" name="manager_notes" rows="6" class="pd-input mt-2">{{ old('manager_notes', $review->manager_notes) }}</textarea>
                    </div>

                    @if ($review->reviewer)
                        <p class="text-[0.72rem] leading-relaxed text-boss-ivory/35">{{ __('Last reviewed by :name on :date', ['name' => $review->reviewer->name, 'date' => $review->reviewed_at?->format('M j, Y g:i A')]) }}</p>
                    @endif

                    <x-primary-button class="w-full justify-center">{{ __('Save management notes') }}</x-primary-button>
                </form>
            </aside>
        </div>
    </div>
</x-admin-layout>
