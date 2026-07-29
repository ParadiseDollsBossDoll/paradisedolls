<x-admin-layout>
    @php
        $coreAnswers = [
            __('Energy & Motivation') => [
                'answer' => $review->optionLabel('energy_motivation'),
                'comments' => $review->energy_comments,
            ],
            __('Effort Towards Earnings') => [
                'answer' => $review->optionLabel('effort_towards_earnings'),
                'comments' => $review->effort_comments,
            ],
            __('Communication & Teamwork') => [
                'answer' => $review->optionLabel('communication_teamwork'),
                'comments' => $review->communication_comments,
            ],
            __('Followed Guidance') => [
                'answer' => $review->optionLabel('followed_guidance'),
                'comments' => $review->guidance_examples,
            ],
        ];

        $writtenAnswers = [
            __('What Went Well') => $review->went_well,
            __('What Could Be Improved') => $review->could_improve,
            __('Authorised Actions') => $review->authorised_actions,
            __('Performance Strategies') => $review->performance_strategies,
            __('Customer Feedback') => $review->customer_feedback,
            __('Coaching Recommendations') => $review->coaching_recommendations,
            __('Additional Comments') => $review->additional_comments,
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.chatter-model-reviews.index') }}" class="text-[0.82rem] text-boss-gold hover:text-boss-gold-light">{{ __('Back to model reviews') }}</a>
                <p class="pd-kicker mt-5">{{ __('Confidential Internal Review') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.7rem)]">{{ __('Weekly Chatter Model Review') }}</h1>
                <p class="mt-2 text-[0.85rem] text-boss-ivory/40">{{ __('Submitted :date', ['date' => $review->submitted_at?->format('M j, Y g:i A')]) }}</p>
            </div>
            <span class="self-start rounded-full bg-boss-gold/10 px-4 py-2 text-[0.78rem] text-boss-gold sm:self-auto">{{ $review->overall_rating }}/5 - {{ $review->overallRatingLabel() }}</span>
        </header>

        <section class="pd-panel grid gap-4 p-5 md:grid-cols-4">
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Chatter') }}</p>
                <p class="mt-2 font-semibold">{{ $review->chatter?->name ?? __('Deleted chatter') }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ $review->chatter?->email }}</p>
            </div>
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Model') }}</p>
                <p class="mt-2 font-semibold">{{ $review->model?->name ?? __('Deleted model') }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ $review->model?->email }}</p>
            </div>
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Week Ending') }}</p>
                <p class="mt-2 font-semibold">{{ $review->week_ending?->format('M j, Y') }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ __('UK payroll week') }}</p>
            </div>
            <div>
                <p class="pd-kicker text-boss-ivory/30">{{ __('Declaration') }}</p>
                <p class="mt-2 font-semibold">{{ $review->declaration_accepted ? __('Accepted') : __('Not accepted') }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ $review->signature }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <section class="pd-panel overflow-hidden">
                    <div class="border-b border-white/[0.06] p-5">
                        <p class="pd-kicker text-boss-ivory/35">{{ __('Overall Performance') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $review->overall_rating }}/5 - {{ $review->overallRatingLabel() }}</h2>
                    </div>
                    <div class="p-5">
                        <p class="whitespace-pre-line text-sm leading-6 text-boss-ivory/65">{{ $review->overall_rating_explanation }}</p>
                    </div>
                </section>

                <section class="pd-panel overflow-hidden">
                    <div class="border-b border-white/[0.06] p-5">
                        <p class="pd-kicker text-boss-ivory/35">{{ __('Core Areas') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Weekly performance answers') }}</h2>
                    </div>
                    <div class="grid gap-px bg-white/[0.06] md:grid-cols-2">
                        @foreach ($coreAnswers as $label => $answer)
                            <div class="bg-[#141419] p-5">
                                <p class="pd-kicker text-boss-ivory/25">{{ $label }}</p>
                                <p class="mt-2 font-semibold text-boss-gold">{{ $answer['answer'] }}</p>
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-boss-ivory/60">{{ filled($answer['comments']) ? $answer['comments'] : __('No comments provided.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="pd-panel overflow-hidden">
                    <div class="border-b border-white/[0.06] p-5">
                        <p class="pd-kicker text-boss-ivory/35">{{ __('Written Feedback') }}</p>
                        <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Detailed responses') }}</h2>
                    </div>
                    <div class="divide-y divide-white/[0.06]">
                        @foreach ($writtenAnswers as $label => $answer)
                            <div class="p-5">
                                <p class="pd-kicker text-boss-ivory/25">{{ $label }}</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-boss-ivory/65">{{ filled($answer) ? $answer : __('No answer provided.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="pd-panel p-5">
                    <p class="pd-kicker text-boss-ivory/35">{{ __('Shift Issues') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $review->shift_issues ? __('Yes') : __('No') }}</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-boss-ivory/60">{{ filled($review->shift_issues_explanation) ? $review->shift_issues_explanation : __('No shift issues reported.') }}</p>
                </section>

                <section class="pd-panel p-5">
                    <p class="pd-kicker text-boss-ivory/35">{{ __('Security & Compliance') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $review->security_concerns ? __('Needs attention') : __('No concerns') }}</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-boss-ivory/60">{{ filled($review->security_concerns_explanation) ? $review->security_concerns_explanation : __('No security concerns reported.') }}</p>
                </section>

                <section class="pd-panel p-5">
                    <p class="pd-kicker text-boss-ivory/35">{{ __('Recognition') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ $review->recognition ? __('Recommended') : __('Not requested') }}</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-boss-ivory/60">{{ filled($review->recognition_reason) ? $review->recognition_reason : __('No recognition note provided.') }}</p>
                </section>
            </aside>
        </div>
    </div>
</x-admin-layout>
