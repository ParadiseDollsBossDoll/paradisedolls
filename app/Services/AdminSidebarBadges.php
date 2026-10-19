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
        $seen = DB::table('admin_sidebar_visits')->where('user_id', $admin->id)->pluck('seen_items', 'section');
        $counts = [];
        foreach ($this->snapshot() as $section => $items) {
            $acknowledged = isset($seen[$section]) ? json_decode($seen[$section], true, 512, JSON_THROW_ON_ERROR) : [];
            $counts[$section] = 0;
            if ($viewing === $section) {
                continue;
            }
            foreach ($items as $key => $version) {
                if (($acknowledged[$key] ?? null) !== $version) {
                    $counts[$section]++;
                }
            }
        }

        return $counts;
    }

    public function snapshot(): array
    {
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

        $snapshot = [];
        foreach ($queries as $section => $sources) {
            $snapshot[$section] = [];
            foreach ($sources as $query) {
                foreach ($query->cursor() as $record) {
                    $attributes = $record->getAttributes();
                    ksort($attributes);
                    $key = class_basename($record).':'.$record->getKey();
                    $snapshot[$section][$key] = hash('sha256', json_encode($attributes, JSON_THROW_ON_ERROR));
                }
            }
        }

        return $snapshot;
    }

    public function markViewed(User $admin, string $section, Carbon $startedAt, ?array $snapshot = null): void
    {
        if (! $admin->isAdmin() || ! in_array($section, self::ROUTES, true)) {
            return;
        }

        $visit = [
            'user_id' => $admin->id,
            'section' => $section,
            'seen_at' => $startedAt->format('Y-m-d H:i:s.u'),
            'seen_items' => json_encode(($snapshot ?? $this->snapshot())[$section], JSON_THROW_ON_ERROR),
        ];
        DB::table('admin_sidebar_visits')->insertOrIgnore($visit);
        // An older tab finishing later must not move the watermark backwards.
        DB::table('admin_sidebar_visits')
            ->where('user_id', $admin->id)
            ->where('section', $section)
            ->where('seen_at', '<', $visit['seen_at'])
            ->update(['seen_at' => $visit['seen_at'], 'seen_items' => $visit['seen_items']]);
    }
}
