<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaignRun extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'email_campaign_id',
        'status',
        'subject',
        'body',
        'action_label',
        'action_url',
        'scheduled_for',
        'started_at',
        'completed_at',
        'recipient_count',
        'sent_count',
        'failed_count',
        'skipped_count',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'recipient_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'skipped_count' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(EmailCampaignDelivery::class);
    }

    public function refreshProgress(): void
    {
        $sent = $this->deliveries()->where('status', EmailCampaignDelivery::STATUS_SENT)->count();
        $failed = $this->deliveries()->where('status', EmailCampaignDelivery::STATUS_FAILED)->count();
        $skipped = $this->deliveries()->where('status', EmailCampaignDelivery::STATUS_SKIPPED)->count();
        $unfinished = $this->deliveries()->whereIn('status', [
            EmailCampaignDelivery::STATUS_PENDING,
            EmailCampaignDelivery::STATUS_PROCESSING,
        ])->exists();

        $attributes = [
            'sent_count' => $sent,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
        ];

        if (! $unfinished) {
            $attributes['status'] = match (true) {
                $failed === 0 => self::STATUS_COMPLETED,
                $sent === 0 => self::STATUS_FAILED,
                default => self::STATUS_PARTIAL,
            };
            $attributes['completed_at'] = now();
        }

        $this->update($attributes);
    }
}
