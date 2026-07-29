<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatterModelReview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminChatterModelReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $weekEnding = (string) $request->query('week_ending', '');

        $reviews = ChatterModelReview::query()
            ->with(['chatter:id,name,email', 'model:id,name,email'])
            ->when($weekEnding !== '', fn ($query) => $query->whereDate('week_ending', $weekEnding))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->whereHas('chatter', fn ($chatterQuery) => $chatterQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('model', fn ($modelQuery) => $modelQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.chatter-model-reviews.index', compact('reviews', 'search', 'weekEnding'));
    }

    public function show(ChatterModelReview $review): View
    {
        $review->load(['chatter:id,name,email', 'model:id,name,email']);

        if ($review->reviewed_at === null) {
            $review->forceFill([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ])->save();

            \Illuminate\Support\Facades\Cache::forget('admin_sidebar_counts_v2');
        }

        return view('admin.chatter-model-reviews.show', compact('review'));
    }
}
