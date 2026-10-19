<?php

namespace App\Services;

use App\Models\ChatterModelReview;
use App\Models\ChatterPerformanceReview;
use App\Models\ChatterRequest;
use App\Models\ChatterTimesheet;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminSidebarBadges
{
    public const ROUTES = [
        'admin.applications.index' => 'applications',
        'admin.referrals.index' => 'referrals',
        'admin.onboarding.index' => 'onboarding',
        'admin.chatter-hours.index' => 'chatters',
        'admin.chatter-reviews.index' => 'chatter_reviews',
        'admin.chatter-model-reviews.index' => 'model_reviews',
    ];

    public function counts(User $admin, ?string $viewing = null): array
    {
        $seen = DB::table('admin_sidebar_visits')->where('user_id', $admin->id)->pluck('seen_at', 'section');
        $queries = [
            'applications' => [ModelApplication::query()->where('status', ModelApplication::STATUS_PENDING)],
            'onboarding' => [ModelProfile::query()->where('verification_status', ModelProfile::VERIFICATION_SUBMITTED)],
            'referrals' => [ModelReferral::query()->where(function (Builder $query): void {
                $query->where(function (Builder $lead): void {
                    $lead->where('status', ModelReferral::STATUS_REFERRED)->whereNull('model_application_id');
                })->orWhere('reward_status', ModelReferral::REWARD_ELIGIBLE);
            })],
            'chatters' => [
                ChatterRequest::query()->where('status', ChatterRequest::STATUS_PENDING),
                ChatterTimesheet::query()->whereWorkflowStatus(ChatterTimesheet::WORKFLOW_READY_FOR_REVIEW),
            ],
            'chatter_reviews' => [ChatterPerformanceReview::query()->where('status', ChatterPerformanceReview::STATUS_SUBMITTED)],
            'model_reviews' => [ChatterModelReview::query()->whereNull('reviewed_at')],
        ];

        $counts = [];
        foreach ($queries as $section => $sources) {
            $counts[$section] = 0;
            if ($viewing === $section) {
                continue;
            }
            foreach ($sources as $query) {
                if (isset($seen[$section])) {
                    $cutoff = Carbon::parse($seen[$section]);
                    $query->where(function (Builder $unseen) use ($cutoff): void {
                        $unseen->where('updated_at', '>', $cutoff);
                        // Timesheets become ready as the payroll week ends, without a row update.
                        if ($unseen->getModel() instanceof ChatterTimesheet) {
                            $unseen->orWhereDate('period_end', '>=', $cutoff->copy()->timezone(ChatterTimesheet::WORKFLOW_TIMEZONE)->toDateString());
                        }
                    });
                }
                $counts[$section] += $query->count();
            }
        }

        return $counts;
    }

    public function markViewed(User $admin, string $section, Carbon $startedAt): void
    {
        if (! $admin->isAdmin() || ! in_array($section, self::ROUTES, true)) {
            return;
        }

        $visit = [
            'user_id' => $admin->id,
            'section' => $section,
            'seen_at' => $startedAt->format('Y-m-d H:i:s.u'),
        ];
        DB::table('admin_sidebar_visits')->insertOrIgnore($visit);
        // An older tab finishing later must not move the watermark backwards.
        DB::table('admin_sidebar_visits')
            ->where('user_id', $admin->id)
            ->where('section', $section)
            ->where('seen_at', '<', $visit['seen_at'])
            ->update(['seen_at' => $visit['seen_at']]);
    }
}
