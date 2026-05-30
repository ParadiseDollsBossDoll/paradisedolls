<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ModelApplication extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'experience_level',
        'social_handle',
        'age_confirmed',
        'photo_paths',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'age_confirmed' => 'boolean',
            'photo_paths' => 'array',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ModelProfile::class, 'model_application_id');
    }

    public function referral(): HasOne
    {
        return $this->hasOne(ModelReferral::class, 'model_application_id');
    }

    public function canResendApprovalEmail(): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $user = $this->relationLoaded('user')
            ? $this->user
            : $this->user()->select(['id', 'last_login_at'])->first();

        if (! $user || $user->last_login_at) {
            return false;
        }

        $profile = $this->relationLoaded('profile')
            ? $this->profile
            : $this->profile()->select(['id', 'model_application_id', 'information_submitted_at'])->first();

        return ! $profile?->hasInformationForm();
    }
}
