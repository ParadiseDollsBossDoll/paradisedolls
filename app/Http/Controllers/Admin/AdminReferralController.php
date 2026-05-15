<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReferralController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $onlyWithReferrals = $request->boolean('with_referrals');

        $modelsQuery = User::query()
            ->where('role', 'model')
            ->withCount([
                'modelReferrals as referrals_count',
                'modelReferrals as lead_referrals_count' => fn ($query) => $query->where('status', ModelReferral::STATUS_REFERRED),
                'modelReferrals as pending_referrals_count' => fn ($query) => $query->where('status', ModelReferral::STATUS_PENDING),
                'modelReferrals as joined_referrals_count' => fn ($query) => $query->where('status', ModelReferral::STATUS_JOINED),
                'modelReferrals as rejected_referrals_count' => fn ($query) => $query->where('status', ModelReferral::STATUS_REJECTED),
                'modelReferrals as eligible_rewards_count' => fn ($query) => $query->where('reward_status', ModelReferral::REWARD_ELIGIBLE),
                'modelReferrals as paid_rewards_count' => fn ($query) => $query->where('reward_status', ModelReferral::REWARD_PAID),
            ])
            ->withMax(['modelReferrals as latest_referral_at'], 'created_at');

        if ($search !== '') {
            $modelsQuery->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('referral_code', 'like', '%'.$search.'%');
            });
        }

        if ($onlyWithReferrals) {
            $modelsQuery->has('modelReferrals');
        }

        $referrers = $modelsQuery
            ->orderByDesc('referrals_count')
            ->orderBy('name')
            ->paginate(15, ['*'], 'models_page')
            ->withQueryString();

        $recentReferrals = ModelReferral::query()
            ->with([
                'referrer:id,name,email,profile_photo_path,referral_code',
                'application:id,name,email,status,created_at',
            ])
            ->latest()
            ->paginate(10, ['*'], 'referrals_page')
            ->withQueryString();

        $summary = [
            'total' => ModelReferral::query()->count(),
            'active_referrers' => ModelReferral::query()->select('referrer_id')->distinct()->count('referrer_id'),
            'leads' => ModelReferral::query()->where('status', ModelReferral::STATUS_REFERRED)->count(),
            'pending' => ModelReferral::query()->where('status', ModelReferral::STATUS_PENDING)->count(),
            'joined' => ModelReferral::query()->where('status', ModelReferral::STATUS_JOINED)->count(),
            'eligible_rewards' => ModelReferral::query()->where('reward_status', ModelReferral::REWARD_ELIGIBLE)->count(),
            'paid_rewards' => ModelReferral::query()->where('reward_status', ModelReferral::REWARD_PAID)->count(),
        ];

        return view('admin.referrals.index', [
            'onlyWithReferrals' => $onlyWithReferrals,
            'recentReferrals' => $recentReferrals,
            'referrers' => $referrers,
            'search' => $search,
            'summary' => $summary,
        ]);
    }
}
