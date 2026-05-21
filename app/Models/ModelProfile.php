<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelProfile extends Model
{
    public const STAGE_REGISTRATION = 'registration';

    public const STAGE_CALLBACK = 'callback';

    public const STAGE_ONBOARDING = 'onboarding';

    public const STAGE_VERIFICATION = 'verification';

    public const STAGE_ACTIVE = 'active';

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
        'nationality',
        'spoken_languages',
        'social_handles',
        'with_other_agency',
        'hear_about_us',
        'height',
        'weight',
        'hair_color',
        'eye_color',
        'body_type',
        'has_tattoos_piercings',
        'platforms',
        'current_platforms',
        'fetishes_checklist',
        'work_interests',
        'comfort_levels',
        'custom_content_ok',
        'worn_items_ok',
        'weekly_availability',
        'availability_preference',
        'has_private_space',
        'payout_methods',
        'payout_method_other',
        'payout_country',
        'model_vibe',
        'anything_else',
        'equipment',
        'availability',
        'goals',
        'experience_notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'discord_username',
        'discord_user_id',
        'onboarding_stage',
        'information_submitted_at',
        'verification_status',
        'id_document_path',
        'selfie_with_id_path',
        'platform_codes_path',
        'verification_submitted_at',
        'verification_reviewed_by',
        'verification_reviewed_at',
        'verification_notes',
        'verification_request_instructions',
        'community_invited_at',
        'community_role_assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'platforms'          => 'array',
            'fetishes_checklist' => 'array',
            'work_interests'     => 'array',
            'comfort_levels'     => 'array',
            'payout_methods'     => 'array',
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

    public function hasCommunityChatAccess(): bool
    {
        return $this->isVerified() && $this->isCommunityRoleAssigned();
    }

    public static function onboardingStages(): array
    {
        return [
            self::STAGE_REGISTRATION,
            self::STAGE_CALLBACK,
            self::STAGE_ONBOARDING,
            self::STAGE_VERIFICATION,
            self::STAGE_ACTIVE,
        ];
    }

    public static function onboardingStageOptions(): array
    {
        return [
            self::STAGE_REGISTRATION => __('Registration'),
            self::STAGE_CALLBACK => __('Callback'),
            self::STAGE_ONBOARDING => __('Onboarding'),
            self::STAGE_VERIFICATION => __('Website verification'),
            self::STAGE_ACTIVE => __('Active'),
        ];
    }

    public function onboardingStageLabel(): string
    {
        return self::onboardingStageOptions()[$this->onboarding_stage ?: self::STAGE_REGISTRATION]
            ?? __('Registration');
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

    public function onboardingStatusLabel(): string
    {
        if (! $this->hasInformationForm()) {
            return __('Complete your model information to start onboarding.');
        }

        if ($this->verification_status === self::VERIFICATION_REJECTED) {
            return __('Verification needs resubmission. Review the admin note and upload updated documents.');
        }

        if (! $this->hasVerificationSubmission()) {
            return __('Next step: upload your valid ID and verification documents.');
        }

        if ($this->verification_status === self::VERIFICATION_SUBMITTED) {
            return __('Submitted for review. The admin team is reviewing your onboarding details and verification IDs.');
        }

        if ($this->isVerified() && ! $this->isCommunityInvited()) {
            return __('Verified. Waiting for your Discord Community invitation.');
        }

        if ($this->isCommunityInvited() && ! $this->isCommunityRoleAssigned()) {
            return __('Discord Community invitation sent. Join Discord and wait for your role access.');
        }

        if ($this->isCommunityRoleAssigned()) {
            return __('Fully onboarded. Your Discord Community role is assigned.');
        }

        return $this->verificationStatusLabel();
    }
};
