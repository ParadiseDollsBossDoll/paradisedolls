<?php

namespace App\Models;

use Carbon\CarbonInterface;
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

    public const AUDIENCE_NOT_ONBOARDED_MODELS = 'not_onboarded_models';

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
            self::AUDIENCE_NOT_ONBOARDED_MODELS => __('Models not fully onboarded'),
        ];
    }

    public static function repeatPresetOptions(): array
    {
        return [
            'none' => __('Do not repeat'),
            'daily' => __('Every day'),
            'weekly' => __('Every week'),
            'fortnightly' => __('Every 2 weeks'),
            'monthly' => __('Every month'),
            'custom' => __('Custom days'),
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

    public function repeatPreset(): string
    {
        return match ($this->repeat_every_days) {
            null => 'none',
            1 => 'daily',
            7 => 'weekly',
            14 => 'fortnightly',
            30 => 'monthly',
            default => 'custom',
        };
    }

    public function repeatLabel(): string
    {
        return match ($this->repeat_every_days) {
            null => __('One-time send'),
            1 => __('Repeats every day'),
            7 => __('Repeats every week'),
            14 => __('Repeats every 2 weeks'),
            30 => __('Repeats every month'),
            default => __('Repeats every :days days', ['days' => $this->repeat_every_days]),
        };
    }

    public static function schedulingTimezone(): string
    {
        return (string) config('services.email_campaigns.timezone', 'Europe/London');
    }

    public function nextSendAtForAdmin(): ?CarbonInterface
    {
        return $this->next_send_at?->copy()->timezone(self::schedulingTimezone());
    }

    public function lastSentAtForAdmin(): ?CarbonInterface
    {
        return $this->last_sent_at?->copy()->timezone(self::schedulingTimezone());
    }
}
