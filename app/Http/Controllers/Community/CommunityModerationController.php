<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityChannel;
use App\Models\CommunityMemberTimeout;
use App\Models\CommunityModerationLog;
use App\Models\User;
use App\Support\CommunityModeration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityModerationController extends Controller
{
    public function timeout(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor?->canModerateCommunity(), 403);
        abort_unless(! $user->isAdmin(), 403);
        abort_unless($actor->isAdmin() || ! $user->isModerator(), 403);

        $validated = $request->validate([
            'channel_id' => ['nullable', 'integer', 'exists:community_channels,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
        ]);

        $channel = null;

        if (! empty($validated['channel_id'])) {
            $channel = CommunityChannel::query()->findOrFail($validated['channel_id']);
            abort_unless($channel->isAccessibleTo($actor), 403);
        }

        $timeout = CommunityModeration::applyTimeout(
            $actor,
            $user,
            $channel,
            $validated['reason'] ?? null,
            (int) $validated['duration_minutes']
        );

        return response()->json([
            'timeout' => [
                'id' => $timeout->id,
                'user_id' => $timeout->user_id,
                'channel_id' => $timeout->channel_id,
                'expires_at' => $timeout->expires_at?->toIso8601String(),
                'reason' => $timeout->reason,
            ],
        ], 201);
    }

    public function revoke(Request $request, CommunityMemberTimeout $timeout): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor?->canModerateCommunity(), 403);
        abort_unless($actor->isAdmin() || ! $timeout->user?->isModerator(), 403);

        CommunityModeration::revokeTimeout($actor, $timeout);

        return response()->json([
            'revoked' => true,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canModerateCommunity(), 403);

        $logs = CommunityModerationLog::query()
            ->with(['actor:id,name', 'targetUser:id,name', 'channel:id,name'])
            ->latest()
            ->take(80)
            ->get()
            ->map(fn (CommunityModerationLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'actor_name' => $log->actor?->name ?? __('System'),
                'target_name' => $log->targetUser?->name,
                'channel_name' => $log->channel?->name,
                'details' => $log->details ?? [],
                'created_at' => $log->created_at?->toIso8601String(),
            ])
            ->all();

        return response()->json([
            'logs' => $logs,
        ]);
    }
}
