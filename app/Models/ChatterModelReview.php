<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterModelReview extends Model
{
    public const RATING_LABELS = [
        1 => 'Poor',
        2 => 'Needs Improvement',
        3 => 'Good',
        4 => 'Very Good',
        5 => 'Excellent',
    ];

    public const ENERGY_OPTIONS = [
        'excellent' => 'Excellent',
        'good' => 'Good',
        'average' => 'Average',
        'poor' => 'Poor',
    ];

    public const EFFORT_OPTIONS = [
        'yes' => 'Yes',
        'mostly' => 'Mostly',
        'sometimes' => 'Sometimes',
        'no' => 'No',
    ];

    public const COMMUNICATION_OPTIONS = [
        'excellent' => 'Excellent',
        'good' => 'Good',
        'average' => 'Average',
        'needs_improvement' => 'Needs Improvement',
    ];

    public const GUIDANCE_OPTIONS = [
        'always' => 'Always',
        'mostly' => 'Mostly',
        'sometimes' => 'Sometimes',
        'rarely' => 'Rarely',
    ];

    protected $fillable = [
        'chatter_id',
        'model_id',
        'week_ending',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'overall_rating',
        'overall_rating_explanation',
        'energy_motivation',
        'energy_comments',
        'effort_towards_earnings',
        'effort_comments',
        'communication_teamwork',
        'communication_comments',
        'followed_guidance',
        'guidance_examples',
        'went_well',
        'could_improve',
        'shift_issues',
        'shift_issues_explanation',
        'security_concerns',
        'security_concerns_explanation',
        'authorised_actions',
        'performance_strategies',
        'customer_feedback',
        'coaching_recommendations',
        'recognition',
        'recognition_reason',
        'additional_comments',
        'declaration_accepted',
        'signature',
    ];

    protected function casts(): array
    {
        return [
            'week_ending' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'shift_issues' => 'boolean',
            'security_concerns' => 'boolean',
            'recognition' => 'boolean',
            'declaration_accepted' => 'boolean',
        ];
    }

    public function chatter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chatter_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(User::class, 'model_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function overallRatingLabel(): string
    {
        return self::RATING_LABELS[(int) $this->overall_rating] ?? (string) $this->overall_rating;
    }

    public function optionLabel(string $field): string
    {
        $options = match ($field) {
            'energy_motivation' => self::ENERGY_OPTIONS,
            'effort_towards_earnings' => self::EFFORT_OPTIONS,
            'communication_teamwork' => self::COMMUNICATION_OPTIONS,
            'followed_guidance' => self::GUIDANCE_OPTIONS,
            default => [],
        };

        return $options[$this->{$field}] ?? (string) $this->{$field};
    }
}
