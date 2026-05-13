<?php

namespace App\Http\Controllers\Community;

use App\Events\CommunityReactionUpdated;
use App\Http\Controllers\Controller;
use App\Models\CommunityMessage;
use App\Models\MessageReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageReactionController extends Controller
{
    public function toggle(Request $request, CommunityMessage $message): JsonResponse
    {
        $validated = $request->validate([
            'emoji' => ['required', 'string', 'in:❤️,🔥,👍,😂'],
        ]);

        $channel = $message->channel;
        $user = $request->user();

        abort_unless($channel->isAccessibleTo($user), 403);

        if (! $channel->canPost($user) && ! $user->canModerateCommunity()) {
            return response()->json([
                'message' => $user->activeCommunityTimeoutFor($channel)
                    ? __('You are currently timed out from reacting in community chat.')
                    : __('This channel is locked right now.'),
            ], 423);
        }

        $reaction = MessageReaction::query()->where([
            'message_id' => $message->id,
            'user_id' => $user->id,
            'emoji' => $validated['emoji'],
        ])->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ]);
        }

        $message->load(['user:id,name,profile_photo_path', 'replyTo.user:id,name', 'reactions']);

        try {
            broadcast(new CommunityReactionUpdated($message))->toOthers();
        } catch (\Throwable $exception) {
            report($exception);

            Log::channel(config('community.performance.log_channel'))->warning('Community reaction broadcast dispatch failed.', [
                'message_id' => $message->id,
                'channel_id' => $message->channel_id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => $message->toFrontendArray($user),
        ]);
    }
}
