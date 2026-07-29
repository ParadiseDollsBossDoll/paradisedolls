<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatterPerformanceReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminChatterPerformanceReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $reviews = ChatterPerformanceReview::query()
            ->with(['member:id,name,email', 'chatter:id,name,email'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('model_name', 'like', "%{$search}%")
                        ->orWhere('chatter_name', 'like', "%{$search}%")
                        ->orWhereHas('member', fn ($memberQuery) => $memberQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('chatter', fn ($chatterQuery) => $chatterQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $memberReviewLink = URL::temporarySignedRoute('member.chatter-reviews.create', now()->addDays(60));

        return view('admin.chatter-reviews.index', compact('reviews', 'search', 'status', 'memberReviewLink'));
    }

    public function show(ChatterPerformanceReview $review): View
    {
        $review->load(['member:id,name,email', 'chatter:id,name,email', 'reviewer:id,name,email']);

        return view('admin.chatter-reviews.show', compact('review'));
    }

    public function update(Request $request, ChatterPerformanceReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                ChatterPerformanceReview::STATUS_SUBMITTED,
                ChatterPerformanceReview::STATUS_REVIEWED,
                ChatterPerformanceReview::STATUS_FOLLOW_UP,
                ChatterPerformanceReview::STATUS_CLOSED,
            ])],
            'management_performance' => ['nullable', Rule::in([
                'outstanding',
                'excellent',
                'meets_expectations',
                'requires_improvement',
                'immediate_coaching_required',
            ])],
            'model_wishes_to_remain' => ['nullable', Rule::in(['yes', 'yes_with_improvements', 'unsure', 'no'])],
            'additional_training_required' => ['nullable', 'boolean'],
            'manager_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $review->forceFill([
            ...$validated,
            'additional_training_required' => $request->has('additional_training_required')
                ? (bool) $validated['additional_training_required']
                : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        \Illuminate\Support\Facades\Cache::forget('admin_sidebar_counts_v2');

        return redirect()
            ->route('admin.chatter-reviews.show', $review)
            ->with('status', __('Chatter review notes were saved.'));
    }
}
