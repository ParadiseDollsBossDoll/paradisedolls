<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmailCampaign extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_COMPLETED = 'completed';

    public const AUDIENCE_ALL_MODELS = 'all_models';

    public const AUDIENCE_ONBOARDED_MODELS = 'onboarded_models';

    protected $fillable = [
        'created_by',
        'name',
        'subject',
        'body',
        'action_label',
        'action_url',
        'audience',
        'status',
        'next_send_at',
        'repeat_every_days',
        'last_sent_at',
        'total_runs',
    ];

    protected function casts(): array
    {
        return [
            'next_send_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'repeat_every_days' => 'integer',
            'total_runs' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(EmailCampaignRun::class);
    }

    public function latestRun(): HasOne
    {
        return $this->hasOne(EmailCampaignRun::class)->latestOfMany();
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_ACTIVE])
            ->whereNotNull('next_send_at')
            ->where('next_send_at', '<=', now());
    }

    public static function audienceOptions(): array
    {
        return [
            self::AUDIENCE_ALL_MODELS => __('All models'),
            self::AUDIENCE_ONBOARDED_MODELS => __('Fully onboarded models'),
        ];
    }

    public function audienceLabel(): string
    {
        return self::audienceOptions()[$this->audience] ?? __('All models');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => __('Scheduled'),
            self::STATUS_ACTIVE => __('Recurring'),
            self::STATUS_PAUSED => __('Paused'),
            self::STATUS_COMPLETED => __('Completed'),
            default => __('Draft'),
        };
    }

    public function repeats(): bool
    {
        return $this->repeat_every_days !== null;
    }
}
