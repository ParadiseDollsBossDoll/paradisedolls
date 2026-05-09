<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityChannel;
use App\Support\CommunityPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityPresenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        CommunityPresence::ping($request->user());

        $channelId = $request->integer('channel_id');
        $summary = $request->boolean('summary');

        if ($channelId > 0) {
            $channel = CommunityChannel::query()->findOrFail($channelId);

            abort_unless($channel->isAccessibleTo($request->user()), 403);
        }

        return response()->json([
            'members' => CommunityPresence::payloadFor($request->user(), $channelId > 0 ? $channelId : null, $summary),
        ]);
    }

    public function ping(Request $request): JsonResponse
    {
        CommunityPresence::ping($request->user());

        return response()->json([
            'ok' => true,
        ]);
    }

    public function typing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'integer', 'exists:community_channels,id'],
            'typing' => ['required', 'boolean'],
        ]);

        $channel = CommunityChannel::query()->findOrFail($validated['channel_id']);

        abort_unless($channel->canPost($request->user()), 403);

        CommunityPresence::ping($request->user());
        CommunityPresence::setTyping($request->user(), $channel->id, (bool) $validated['typing']);

        return response()->json([
            'ok' => true,
            'typing' => CommunityPresence::typingUsersFor($request->user(), $channel->id),
        ]);
    }
}
