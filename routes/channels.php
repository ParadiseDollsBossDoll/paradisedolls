<?php

use App\Models\CommunityChannel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('community.channel.{channelId}', function (User $user, int $channelId) {
    $channel = CommunityChannel::query()
        ->with('accessGrants')
        ->find($channelId);

    if (! $channel || ! $channel->isAccessibleTo($user)) {
        return false;
    }

    return true;
});

Broadcast::channel('community.presence', function (User $user) {
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
