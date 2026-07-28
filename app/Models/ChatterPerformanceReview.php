<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterPerformanceReview extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_FOLLOW_UP = 'follow_up';
    public const STATUS_CLOSED = 'closed';

    public const RATING_FIELDS = [
        'communication_rating',
        'professionalism_rating',
        'friendliness_rating',
        'response_time_rating',
        'reliability_rating',
        'customer_engagement_rating',
        'sales_encouragement_rating',
        'support_motivation_rating',
        'boundary_understanding_rating',
        'overall_performance_rating',
    ];

    protected $fillable = [
        'user_id',
        'chatter_user_id',
        'is_anonymous',
        'model_name',
        'chatter_name',
        'review_date',
        'review_period',
        'communication_rating',
        'professionalism_rating',
        'friendliness_rating',
        'response_time_rating',
        'reliability_rating',
        'customer_engagement_rating',
        'sales_encouragement_rating',
        'support_motivation_rating',
        'boundary_understanding_rating',
        'overall_performance_rating',
        'sounds_like_model',
        'understands_personality',
        'respects_boundaries',
        'performance_feeling',
        'wants_to_help',
        'confidence_improved',
        'customer_engagement_improved',
        'continue_working',
        'does_well',
        'could_improve',
        'missed_opportunities',
        'natural_speech',
        'uncomfortable_incident',
        'uncomfortable_explanation',
        'one_change',
        'recognition',
        'anything_else',
        'overall_satisfaction',
        'would_recommend',
        'contact_requested',
        'average_score',
        'status',
        'management_performance',
        'model_wishes_to_remain',
        'additional_training_required',
        'manager_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'contact_requested' => 'boolean',
            'additional_training_required' => 'boolean',
            'review_date' => 'date',
            'reviewed_at' => 'datetime',
            'average_score' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function chatter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chatter_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_FOLLOW_UP => 'Follow-up needed',
            self::STATUS_CLOSED => 'Closed',
            default => 'Submitted',
        };
    }

    public static function averageFromRatings(array $values): float
    {
        $ratings = collect(self::RATING_FIELDS)
            ->map(fn (string $field) => (int) ($values[$field] ?? 0))
            ->filter(fn (int $rating) => $rating >= 1 && $rating <= 5);

        return round($ratings->avg() ?: 0, 2);
    }
}
