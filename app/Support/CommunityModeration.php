<?php

namespace App\Support;

use App\Models\CommunityChannel;
use App\Models\CommunityMemberTimeout;
use App\Models\CommunityMessage;
use App\Models\CommunityModerationLog;
use App\Models\User;

class CommunityModeration
{
    public static function log(
        User $actor,
        string $action,
        ?CommunityChannel $channel = null,
        ?CommunityMessage $message = null,
        ?User $targetUser = null,
        array $details = []
    ): void {
        CommunityModerationLog::query()->create([
            'actor_id' => $actor->id,
            'target_user_id' => $targetUser?->id,
            'channel_id' => $channel?->id,
            'message_id' => $message?->id,
            'action' => $action,
            'details' => $details,
        ]);
    }

    public static function applyTimeout(
        User $actor,
        User $target,
        ?CommunityChannel $channel,
        ?string $reason,
        int $durationMinutes
    ): CommunityMemberTimeout {
        $timeout = CommunityMemberTimeout::query()
            ->active()
            ->where('user_id', $target->id)
            ->where('channel_id', $channel?->id)
            ->first();

        if ($timeout) {
            $timeout->forceFill([
                'created_by' => $actor->id,
                'reason' => $reason,
                'expires_at' => now()->addMinutes($durationMinutes),
                'revoked_at' => null,
            ])->save();
        } else {
            $timeout = CommunityMemberTimeout::query()->create([
                'user_id' => $target->id,
                'channel_id' => $channel?->id,
                'created_by' => $actor->id,
                'reason' => $reason,
                'expires_at' => now()->addMinutes($durationMinutes),
            ]);
        }

        self::log($actor, 'member_timeout', $channel, null, $target, [
            'reason' => $reason,
            'duration_minutes' => $durationMinutes,
            'expires_at' => $timeout->expires_at?->toIso8601String(),
        ]);

        return $timeout;
    }

    public static function revokeTimeout(User $actor, CommunityMemberTimeout $timeout): void
    {
        $timeout->forceFill([
            'revoked_at' => now(),
        ])->save();

        self::log($actor, 'member_timeout_revoked', $timeout->channel, null, $timeout->user, [
            'timeout_id' => $timeout->id,
        ]);
    }
}
