<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelReferral extends Model
{
    public const STATUS_REFERRED = 'referred';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_JOINED = 'joined';

    public const REWARD_NOT_ELIGIBLE = 'not_eligible';

    public const REWARD_ELIGIBLE = 'eligible';

    public const REWARD_PAID = 'paid';

    public const SOURCE_MEMBER_FORM = 'member_form';

    public const SOURCE_APPLY_LINK = 'apply_link';

    protected $fillable = [
        'referrer_id',
        'model_application_id',
        'candidate_name',
        'candidate_email',
        'candidate_phone',
        'candidate_social_handle',
        'experience_level',
        'note',
        'photo_paths',
        'consent_confirmed',
        'source',
        'status',
        'reward_status',
        'joined_at',
        'reward_marked_paid_at',
        'reward_marked_paid_by',
    ];

    protected function casts(): array
    {
        return [
            'photo_paths' => 'array',
            'consent_confirmed' => 'boolean',
            'joined_at' => 'datetime',
            'reward_marked_paid_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ModelApplication::class, 'model_application_id');
    }

    public function rewardMarkedPaidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reward_marked_paid_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('Application pending'),
            self::STATUS_REJECTED => __('Rejected'),
            self::STATUS_JOINED => __('Joined'),
            default => __('Referred'),
        };
    }

    public function rewardStatusLabel(): string
    {
        return match ($this->reward_status) {
            self::REWARD_ELIGIBLE => __('Reward eligible'),
            self::REWARD_PAID => __('Reward paid'),
            default => __('Not eligible yet'),
        };
    }

    public function markJoined(): void
    {
        $this->forceFill([
            'status' => self::STATUS_JOINED,
            'reward_status' => self::REWARD_ELIGIBLE,
            'joined_at' => $this->joined_at ?? now(),
        ])->save();
    }

    public function markRejected(): void
    {
        $this->forceFill([
            'status' => self::STATUS_REJECTED,
            'reward_status' => self::REWARD_NOT_ELIGIBLE,
        ])->save();
    }
}
