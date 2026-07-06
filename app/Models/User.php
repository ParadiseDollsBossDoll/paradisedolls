<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_photo_path',
        'referral_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->referral_code)) {
                $user->referral_code = static::generateReferralCode();
            }
        });
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = 'PD'.Str::upper(Str::random(8));
        } while (static::query()->where('referral_code', $code)->exists());

        return $code;
    }

    public function ensureReferralCode(): string
    {
        if (blank($this->referral_code)) {
            $this->forceFill([
                'referral_code' => static::generateReferralCode(),
            ])->save();
        }

        return $this->referral_code;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModel(): bool
    {
        return $this->role === 'model';
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function canModerateCommunity(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    public function canManageCommunityChannels(): bool
    {
        return $this->isAdmin();
    }

    public function hasCommunityChatAccess(): bool
    {
        if (! $this->isModel()) {
            return false;
        }

        $profile = $this->relationLoaded('modelProfile')
            ? $this->modelProfile
            : $this->modelProfile()
                ->select(['id', 'user_id', 'verification_status', 'community_role_assigned_at'])
                ->first();

        return (bool) $profile?->hasCommunityChatAccess();
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(CourseChatMessage::class);
    }

    public function modelProfile(): HasOne
    {
        return $this->hasOne(ModelProfile::class);
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function courseAccessRequests(): HasMany
    {
        return $this->hasMany(CourseAccessRequest::class);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    public function communityMessages(): HasMany
    {
        return $this->hasMany(CommunityMessage::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'submitted_by');
    }

    public function modelReferrals(): HasMany
    {
        return $this->hasMany(ModelReferral::class, 'referrer_id');
    }

    public function communityMessageReads(): HasMany
    {
        return $this->hasMany(CommunityMessageRead::class);
    }

    public function communityTimeouts(): HasMany
    {
        return $this->hasMany(CommunityMemberTimeout::class);
    }

    public function activeCommunityTimeoutFor(?CommunityChannel $channel = null): ?CommunityMemberTimeout
    {
        return $this->communityTimeouts()
            ->active()
            ->when($channel, function ($query) use ($channel) {
                $query->where(function ($builder) use ($channel) {
                    $builder
                        ->whereNull('channel_id')
                        ->orWhere('channel_id', $channel->id);
                });
            })
            ->orderByDesc('channel_id')
            ->orderByDesc('expires_at')
            ->first();
    }

    public function initials(): string
    {
        $initials = Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $segment) => Str::upper(Str::substr($segment, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'PD';
    }

    public function communityAccent(): string
    {
        $palette = [
            '#C9A96E', '#D7B27F', '#8E5E3B', '#B1704A',
            '#6E90C9', '#A2678A', '#3EAF7C', '#9C7AE3',
        ];

        return $palette[$this->id % count($palette)];
    }

    public function profilePhotoUrl(): ?string
    {
        if (blank($this->profile_photo_path) || ! Storage::disk('public')->exists($this->profile_photo_path)) {
            return null;
        }

        return route('profile-photos.show', $this, absolute: false)
            .'?v='.substr(sha1($this->profile_photo_path), 0, 12);
    }

    public function toCommunityMemberArray(bool $online, bool $isSelf = false): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'initials' => $this->initials(),
            'accent' => $this->communityAccent(),
            'profile_photo_url' => $this->profilePhotoUrl(),
            'role' => $this->role,
            'online' => $online,
            'is_self' => $isSelf,
        ];
    }
}
