<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ChatterPerformanceReview;
use App\Models\User;
use App\Services\AdminActivityNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberChatterPerformanceReviewController extends Controller
{
    public function create(Request $request): View
    {
        $reviews = $request->user()
            ->chatterPerformanceReviews()
            ->latest()
            ->take(8)
            ->get();

        $chatters = User::query()
            ->where('role', 'chatter')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('member.chatter-reviews.create', compact('reviews', 'chatters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_anonymous' => ['required', 'boolean'],
            'chatter_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'chatter')),
            ],
            'model_name' => ['nullable', 'string', 'max:255'],
            'chatter_name' => ['nullable', 'string', 'max:255'],
            'review_date' => ['nullable', 'date'],
            'review_period' => ['nullable', 'string', 'max:120'],
            ...$this->ratingRules(),
            ...$this->experienceRules(),
            'does_well' => ['nullable', 'string', 'max:2000'],
            'could_improve' => ['nullable', 'string', 'max:2000'],
            'missed_opportunities' => ['nullable', 'string', 'max:2000'],
            'natural_speech' => ['nullable', 'string', 'max:2000'],
            'uncomfortable_incident' => ['required', Rule::in(['no', 'yes'])],
            'uncomfortable_explanation' => ['nullable', 'string', 'max:2000'],
            'one_change' => ['nullable', 'string', 'max:2000'],
            'recognition' => ['nullable', 'string', 'max:2000'],
            'anything_else' => ['nullable', 'string', 'max:2000'],
            'overall_satisfaction' => ['required', Rule::in(['very_satisfied', 'satisfied', 'neutral', 'unsatisfied', 'very_unsatisfied'])],
            'would_recommend' => ['required', Rule::in(['yes', 'maybe', 'no'])],
            'contact_requested' => ['required', 'boolean'],
        ]);

        $chatter = isset($validated['chatter_user_id'])
            ? User::query()->where('role', 'chatter')->find($validated['chatter_user_id'])
            : null;

        $isAnonymous = (bool) $validated['is_anonymous'];
        $review = ChatterPerformanceReview::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'model_name' => $isAnonymous ? null : ($validated['model_name'] ?: $request->user()->name),
            'chatter_name' => $chatter?->name ?: $validated['chatter_name'],
            'average_score' => ChatterPerformanceReview::averageFromRatings($validated),
            'status' => ChatterPerformanceReview::STATUS_SUBMITTED,
        ]);

        app(AdminActivityNotifier::class)->notify(
            title: __('New chatter performance review'),
            body: __('A model submitted a confidential chatter performance review.'),
            actionUrl: route('admin.chatter-reviews.show', $review, absolute: false),
            category: 'chatter_performance_review',
            emailSubject: __('New chatter performance review submitted'),
            details: [
                __('Submitted by') => $isAnonymous ? __('Anonymous model') : $request->user()->name,
                __('Chatter') => $review->chatter_name ?: __('Not specified'),
                __('Average score') => number_format((float) $review->average_score, 2).'/5',
                __('Contact requested') => $review->contact_requested ? __('Yes') : __('No'),
            ],
            actionLabel: __('Review feedback'),
        );

        return redirect()
            ->to($request->fullUrl())
            ->with('status', __('Your confidential chatter review has been submitted to Paradise Dolls management.'));
    }

    private function ratingRules(): array
    {
        return collect(ChatterPerformanceReview::RATING_FIELDS)
            ->mapWithKeys(fn (string $field) => [$field => ['required', 'integer', 'between:1,5']])
            ->all();
    }

    private function experienceRules(): array
    {
        return [
            'sounds_like_model' => ['required', Rule::in(['always', 'most_of_the_time', 'sometimes', 'rarely', 'never'])],
            'understands_personality' => ['required', Rule::in(['completely', 'mostly', 'somewhat', 'very_little', 'not_at_all'])],
            'respects_boundaries' => ['required', Rule::in(['always', 'most_of_the_time', 'sometimes', 'rarely', 'never'])],
            'performance_feeling' => ['required', Rule::in(['happy', 'small_improvements', 'several_areas', 'significant_improvement'])],
            'wants_to_help' => ['required', Rule::in(['definitely', 'mostly', 'sometimes', 'rarely', 'no'])],
            'confidence_improved' => ['required', Rule::in(['yes_significantly', 'yes', 'a_little', 'not_really', 'no'])],
            'customer_engagement_improved' => ['required', Rule::in(['yes_significantly', 'yes', 'a_little', 'not_really', 'no'])],
            'continue_working' => ['required', Rule::in(['definitely', 'yes_with_improvements', 'unsure', 'try_another', 'change_chatters'])],
        ];
    }
}
