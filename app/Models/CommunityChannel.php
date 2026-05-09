<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CommunityChannel extends Model
{
    public const ACCESS_MEMBERS = 'members';

    public const ACCESS_MODERATORS = 'moderators';

    public const ACCESS_ADMINS = 'admins';

    public const ACCESS_ROLES = 'roles';

    public const ACCESS_INVITE = 'invite';

    public const DENIED_HIDDEN = 'hidden';

    public const DENIED_LOCKED = 'locked';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'created_by',
        'is_private',
        'access_mode',
        'denied_behavior',
        'allowed_roles',
        'order',
        'is_archived',
        'is_locked',
        'slowmode_seconds',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'is_archived' => 'boolean',
            'is_locked' => 'boolean',
            'allowed_roles' => 'array',
            'last_message_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CommunityChannel $channel): void {
            if (blank($channel->slug)) {
                $channel->slug = static::makeUniqueSlug($channel->name);
            }

            if ($channel->order === 0) {
                $channel->order = ((int) static::query()->max('order')) + 1;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CommunityMessage::class, 'channel_id')->orderBy('created_at');
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(CommunityChannelAccess::class, 'community_channel_id');
    }

    public function timeouts(): HasMany
    {
        return $this->hasMany(CommunityMemberTimeout::class, 'channel_id');
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(CommunityModerationLog::class, 'channel_id');
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where('is_archived', false);
    }

    public function scopeDiscoverableTo(Builder $query, User $user): Builder
    {
        return $query->where('is_archived', false);
    }

    public function isDiscoverableTo(User $user): bool
    {
        if ($this->is_archived) {
            return false;
        }

        if ($this->isAccessibleTo($user)) {
            return true;
        }

        return $this->denied_behavior === self::DENIED_LOCKED;
    }

    public function isAccessibleTo(User $user): bool
    {
        if ($this->is_archived) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return match ($this->access_mode ?: self::ACCESS_MEMBERS) {
            self::ACCESS_MEMBERS => true,
            self::ACCESS_MODERATORS => $user->canModerateCommunity(),
            self::ACCESS_ADMINS => false,
            self::ACCESS_ROLES => in_array($user->role, $this->allowed_roles ?? [], true),
            self::ACCESS_INVITE => $this->hasAccessGrantFor($user),
            default => ! $this->is_private,
        };
    }

    public function canPost(User $user): bool
    {
        if (! $this->isAccessibleTo($user)) {
            return false;
        }

        if ($this->is_locked && ! $user->canModerateCommunity()) {
            return false;
        }

        return ! $user->activeCommunityTimeoutFor($this);
    }

    public function hasAccessGrantFor(User $user): bool
    {
        if ($this->relationLoaded('accessGrants')) {
            return $this->accessGrants->contains('user_id', $user->id);
        }

        return $this->accessGrants()->where('user_id', $user->id)->exists();
    }

    public function permissionSummary(): string
    {
        return match ($this->access_mode ?: self::ACCESS_MEMBERS) {
            self::ACCESS_MEMBERS => __('All members'),
            self::ACCESS_MODERATORS => __('Moderators only'),
            self::ACCESS_ADMINS => __('Admins only'),
            self::ACCESS_ROLES => __('Role-specific'),
            self::ACCESS_INVITE => __('Invite only'),
            default => __('All members'),
        };
    }

    public function toFrontendArray(User $viewer, int $unreadCount = 0): array
    {
        $canAccess = $this->isAccessibleTo($viewer);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category ?: __('Community'),
            'description' => $this->description,
            'is_private' => $this->is_private,
            'is_locked' => $this->is_locked,
            'is_archived' => $this->is_archived,
            'slowmode_seconds' => $this->slowmode_seconds,
            'order' => $this->order,
            'unread_count' => $canAccess ? $unreadCount : 0,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'can_access' => $canAccess,
            'is_discoverable' => $this->isDiscoverableTo($viewer),
            'access_mode' => $this->access_mode ?: self::ACCESS_MEMBERS,
            'denied_behavior' => $this->denied_behavior ?: self::DENIED_HIDDEN,
            'allowed_roles' => $this->allowed_roles ?? [],
            'permission_summary' => $this->permissionSummary(),
            'can_post' => $this->canPost($viewer),
            'can_manage' => $viewer->canModerateCommunity(),
            'can_assign_permissions' => $viewer->canManageCommunityChannels(),
            'invited_user_ids' => $viewer->canManageCommunityChannels()
                ? $this->accessGrants()->pluck('user_id')->all()
                : [],
        ];
    }

    public static function accessModes(): array
    {
        return [
            self::ACCESS_MEMBERS,
            self::ACCESS_MODERATORS,
            self::ACCESS_ADMINS,
            self::ACCESS_ROLES,
            self::ACCESS_INVITE,
        ];
    }

    public static function deniedBehaviors(): array
    {
        return [
            self::DENIED_HIDDEN,
            self::DENIED_LOCKED,
        ];
    }

    public static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slugBase = $base !== '' ? $base : 'channel';
        $slug = $slugBase;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn (Builder $builder) => $builder->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$slugBase}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
