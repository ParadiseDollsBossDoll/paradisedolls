<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelProfile extends Model
{
    public const VERIFICATION_NOT_REQUESTED = 'not_requested';

    public const VERIFICATION_REQUESTED = 'requested';

    public const VERIFICATION_SUBMITTED = 'submitted';

    public const VERIFICATION_VERIFIED = 'verified';

    public const VERIFICATION_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'model_application_id',
        'legal_name',
        'stage_name',
        'date_of_birth',
        'phone',
        'country',
        'city',
        'timezone',
        'platforms',
        'equipment',
        'availability',
        'goals',
        'experience_notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'discord_username',
        'discord_user_id',
        'information_submitted_at',
        'verification_status',
        'id_document_path',
        'selfie_with_id_path',
        'platform_codes_path',
        'verification_submitted_at',
        'verification_reviewed_by',
        'verification_reviewed_at',
        'verification_notes',
        'community_invited_at',
        'community_role_assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'platforms' => 'array',
            'equipment' => 'array',
            'information_submitted_at' => 'datetime',
            'verification_submitted_at' => 'datetime',
            'verification_reviewed_at' => 'datetime',
            'community_invited_at' => 'datetime',
            'community_role_assigned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ModelApplication::class, 'model_application_id');
    }

    public function verificationReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verification_reviewed_by');
    }

    public function hasInformationForm(): bool
    {
        return $this->information_submitted_at !== null;
    }

    public function hasVerificationSubmission(): bool
    {
        return $this->verification_submitted_at !== null
            && filled($this->id_document_path)
            && filled($this->selfie_with_id_path);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === self::VERIFICATION_VERIFIED;
    }

    public function isCommunityInvited(): bool
    {
        return $this->community_invited_at !== null;
    }

    public function isCommunityRoleAssigned(): bool
    {
        return $this->community_role_assigned_at !== null;
    }

    public function onboardingPercent(): int
    {
        $complete = 1; // account exists

        if ($this->hasInformationForm()) {
            $complete++;
        }

        if ($this->hasVerificationSubmission()) {
            $complete++;
        }

        if ($this->isVerified()) {
            $complete++;
        }

        if ($this->isCommunityInvited()) {
            $complete++;
        }

        if ($this->isCommunityRoleAssigned()) {
            $complete++;
        }

        return (int) round(($complete / 6) * 100);
    }

    public function verificationStatusLabel(): string
    {
        return match ($this->verification_status) {
            self::VERIFICATION_REQUESTED => __('Verification requested'),
            self::VERIFICATION_SUBMITTED => __('Submitted for review'),
            self::VERIFICATION_VERIFIED => __('Verified'),
            self::VERIFICATION_REJECTED => __('Needs resubmission'),
            default => __('Not requested'),
        };
    }
};
