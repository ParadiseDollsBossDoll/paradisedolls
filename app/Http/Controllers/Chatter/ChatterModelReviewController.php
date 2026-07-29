<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Models\ChatterModelReview;
use App\Services\AdminActivityNotifier;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChatterModelReviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $weekEnding = $this->currentWeekEnding();
        $models = $this->availableModels($user);
        $submittedModelIds = ChatterModelReview::query()
            ->where('chatter_id', $user->id)
            ->whereDate('week_ending', $weekEnding->toDateString())
            ->pluck('model_id')
            ->all();

        $reviews = ChatterModelReview::query()
            ->with(['model:id,name,email'])
            ->where('chatter_id', $user->id)
            ->latest('submitted_at')
            ->paginate(10);

        return view('chatter.model-reviews.index', [
            'reviews' => $reviews,
            'models' => $models,
            'weekEnding' => $weekEnding,
            'submittedModelIds' => $submittedModelIds,
            'submittedThisWeek' => count($submittedModelIds),
            'remainingThisWeek' => max(0, $models->count() - count($submittedModelIds)),
        ]);
    }

    public function create(Request $request): View
    {
        $models = $this->availableModels($request->user());
        $selectedModelId = (string) $request->query('model_id', '');

        if ($selectedModelId === '' && $models->count() === 1) {
            $selectedModelId = (string) $models->first()->id;
        }

        return view('chatter.model-reviews.create', [
            'models' => $models,
            'weekEnding' => $this->currentWeekEnding(),
            'selectedModelId' => $selectedModelId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $assignedModelIds = $this->availableModels($request->user())->pluck('id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'model_id' => ['required', Rule::in($assignedModelIds)],
            'week_ending' => ['required', 'date'],
            'overall_rating' => ['required', 'integer', 'between:1,5'],
            'overall_rating_explanation' => ['required', 'string', 'max:3000'],
            'energy_motivation' => ['required', Rule::in(array_keys(ChatterModelReview::ENERGY_OPTIONS))],
            'energy_comments' => ['nullable', 'string', 'max:3000'],
            'effort_towards_earnings' => ['required', Rule::in(array_keys(ChatterModelReview::EFFORT_OPTIONS))],
            'effort_comments' => ['nullable', 'string', 'max:3000'],
            'communication_teamwork' => ['required', Rule::in(array_keys(ChatterModelReview::COMMUNICATION_OPTIONS))],
            'communication_comments' => ['nullable', 'string', 'max:3000'],
            'followed_guidance' => ['required', Rule::in(array_keys(ChatterModelReview::GUIDANCE_OPTIONS))],
            'guidance_examples' => ['nullable', 'string', 'max:3000'],
            'went_well' => ['required', 'string', 'max:3000'],
            'could_improve' => ['required', 'string', 'max:3000'],
            'shift_issues' => ['required', 'boolean'],
            'shift_issues_explanation' => ['nullable', 'string', 'max:3000'],
            'security_concerns' => ['required', 'boolean'],
            'security_concerns_explanation' => ['nullable', 'string', 'max:3000'],
            'authorised_actions' => ['nullable', 'string', 'max:3000'],
            'performance_strategies' => ['nullable', 'string', 'max:3000'],
            'customer_feedback' => ['nullable', 'string', 'max:3000'],
            'coaching_recommendations' => ['nullable', 'string', 'max:3000'],
            'recognition' => ['required', 'boolean'],
            'recognition_reason' => ['nullable', 'string', 'max:3000'],
            'additional_comments' => ['nullable', 'string', 'max:3000'],
            'declaration_accepted' => ['accepted'],
            'signature' => ['required', 'string', 'max:255'],
        ]);

        $weekEnding = CarbonImmutable::parse($validated['week_ending'], 'Europe/London')->startOfDay();

        $alreadySubmitted = ChatterModelReview::query()
            ->where('chatter_id', $request->user()->id)
            ->where('model_id', $validated['model_id'])
            ->whereDate('week_ending', $weekEnding->toDateString())
            ->exists();

        if ($alreadySubmitted) {
            return back()
                ->withInput()
                ->withErrors(['model_id' => __('You have already submitted a review for this model for the selected week.')]);
        }

        $review = ChatterModelReview::create([
            ...$validated,
            'chatter_id' => $request->user()->id,
            'week_ending' => $weekEnding->toDateString(),
            'submitted_at' => now(),
            'shift_issues' => (bool) $validated['shift_issues'],
            'security_concerns' => (bool) $validated['security_concerns'],
            'recognition' => (bool) $validated['recognition'],
            'declaration_accepted' => true,
        ]);

        \Illuminate\Support\Facades\Cache::forget('admin_sidebar_counts_v2');

        $review->load(['model:id,name,email', 'chatter:id,name,email']);

        app(AdminActivityNotifier::class)->notify(
            title: __('New weekly model review'),
            body: __('A chatter submitted a weekly review for a model.'),
            actionUrl: route('admin.chatter-model-reviews.show', $review, absolute: false),
            category: 'chatter_model_review',
            emailSubject: __('New weekly chatter model review submitted'),
            details: [
                __('Chatter') => $review->chatter?->name,
                __('Model') => $review->model?->name,
                __('Week ending') => $review->week_ending?->format('M j, Y'),
                __('Overall rating') => $review->overall_rating.'/5',
            ],
            actionLabel: __('Review submission'),
        );

        return redirect()
            ->route('chatter.model-reviews.index')
            ->with('status', __('Weekly model review submitted. Thank you.'));
    }

    private function currentWeekEnding(): CarbonImmutable
    {
        $today = CarbonImmutable::now('Europe/London')->startOfDay();

        return $today->addDays((7 - $today->dayOfWeek) % 7);
    }

    private function availableModels($chatter): Collection
    {
        return $chatter->activeAssignedModels()
            ->whereHas('modelProfile')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);
    }
}
