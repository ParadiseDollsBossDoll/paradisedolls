<?php

use App\Models\CommunityChannel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;

$hasCommunityAccess = function (User $user): bool {
    if ($user->canModerateCommunity()) {
        return true;
    }

    return Cache::remember(
        'broadcast_community_access_'.$user->id,
        now()->addSeconds(60),
        fn () => $user->hasCommunityChatAccess()
    );
};

Broadcast::channel('community.channel.{channelId}', function (User $user, int $channelId) use ($hasCommunityAccess) {
    if (! $hasCommunityAccess($user)) {
        return false;
    }

    $channel = CommunityChannel::query()
        ->with('accessGrants')
        ->find($channelId);

    if (! $channel || ! $channel->isAccessibleTo($user)) {
        return false;
    }

    return true;
});

Broadcast::channel('community.presence', function (User $user) use ($hasCommunityAccess) {
    if (! $hasCommunityAccess($user)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'initials' => $user->initials(),
        'accent' => $user->communityAccent(),
        'profile_photo_url' => $user->profilePhotoUrl(),
        'role' => $user->role,
        'online' => true,
    ];
});
