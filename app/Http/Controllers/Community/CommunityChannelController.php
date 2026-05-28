<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunityChannelRequest;
use App\Http\Requests\UpdateCommunityChannelRequest;
use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Support\CommunityModeration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class CommunityChannelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->channelRosterPayload($request->user()));
    }

    public function store(StoreCommunityChannelRequest $request): JsonResponse
    {
        $channel = CommunityChannel::query()->create([
            'name' => $request->string('name')->toString(),
            'slug' => CommunityChannel::makeUniqueSlug($request->string('name')->toString()),
            'category' => $request->string('category')->toString() ?: 'Community',
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
            'created_by' => $request->user()->id,
            'is_private' => $request->boolean('is_private'),
            'access_mode' => $request->string('access_mode')->toString(),
            'denied_behavior' => $request->string('denied_behavior')->toString(),
            'allowed_roles' => $this->normalizeAllowedRoles($request->input('allowed_roles', [])),
            'is_locked' => $request->boolean('is_locked'),
            'slowmode_seconds' => (int) $request->integer('slowmode_seconds', 0),
            'order' => ((int) CommunityChannel::query()->max('order')) + 1,
        ]);

        $this->syncInvites($channel, $request->input('invited_user_ids', []), $request->user()->id);

        CommunityModeration::log($request->user(), 'channel_created', $channel, null, null, [
            'access_mode' => $channel->access_mode,
            'denied_behavior' => $channel->denied_behavior,
            'allowed_roles' => $channel->allowed_roles,
        ]);

        return response()->json([
            'channel' => $channel->fresh()->toFrontendArray($request->user()),
        ], 201);
    }

    public function update(UpdateCommunityChannelRequest $request, CommunityChannel $channel): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->canModerateCommunity(), 403);

        $name = $request->string('name')->toString();

        $channel->fill([
            'name' => $name,
            'slug' => $name !== $channel->name ? CommunityChannel::makeUniqueSlug($name, $channel->id) : $channel->slug,
            'category' => $request->string('category')->toString() ?: 'Community',
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
            'is_locked' => $request->boolean('is_locked'),
            'slowmode_seconds' => (int) $request->integer('slowmode_seconds', 0),
        ]);

        if ($user->canManageCommunityChannels()) {
            $channel->fill([
                'is_private' => $request->boolean('is_private'),
                'access_mode' => $request->string('access_mode')->toString(),
                'denied_behavior' => $request->string('denied_behavior')->toString(),
                'allowed_roles' => $this->normalizeAllowedRoles($request->input('allowed_roles', [])),
            ]);
        }

        $channel->save();

        if ($user->canManageCommunityChannels()) {
            $this->syncInvites($channel, $request->input('invited_user_ids', []), $user->id);
        }

        CommunityModeration::log($user, 'channel_updated', $channel, null, null, [
            'is_locked' => $channel->is_locked,
            'slowmode_seconds' => $channel->slowmode_seconds,
            'access_mode' => $channel->access_mode,
        ]);

        return response()->json([
            'channel' => $channel->fresh()->toFrontendArray($user),
        ]);
    }

    public function archive(Request $request, CommunityChannel $channel): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->canModerateCommunity(), 403);

        $activeChannels = CommunityChannel::query()
            ->where('is_archived', false)
            ->count();

        if ($activeChannels <= 1) {
            return response()->json([
                'message' => __('At least one active channel must remain.'),
            ], 422);
        }

        $channel->update(['is_archived' => true]);

        CommunityModeration::log($user, 'channel_archived', $channel);

        return response()->json([
            'archived' => true,
        ]);
    }

    public function destroy(Request $request, CommunityChannel $channel): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->canManageCommunityChannels(), 403);

        $activeChannels = CommunityChannel::query()
            ->where('is_archived', false)
            ->count();

        if (! $channel->is_archived && $activeChannels <= 1) {
            return response()->json([
                'message' => __('At least one active channel must remain.'),
            ], 422);
        }

        $channelName = $channel->name;
        $messageCount = $channel->messages()->count();
        $wasArchived = $channel->is_archived;

        CommunityModeration::log($user, 'channel_deleted', $channel, null, null, [
            'message_count' => $messageCount,
            'was_archived' => $wasArchived,
        ]);

        $channel->delete();

        return response()->json([
            'deleted' => true,
            'channel_name' => $channelName,
        ]);
    }

    public function restore(Request $request, CommunityChannel $channel): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->canModerateCommunity(), 403);

        $channel->update(['is_archived' => false]);

        CommunityModeration::log($user, 'channel_restored', $channel);

        return response()->json([
            'channel' => $channel->fresh()->toFrontendArray($user),
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canManageCommunityChannels(), 403);

        $validated = $request->validate([
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['integer', Rule::exists('community_channels', 'id')->where('is_archived', false)],
        ]);

        foreach ($validated['channels'] as $index => $channelId) {
            CommunityChannel::query()
                ->whereKey($channelId)
                ->update(['order' => $index + 1]);
        }

        $channels = CommunityChannel::query()
            ->where('is_archived', false)
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->filter(fn (CommunityChannel $channel) => $channel->isDiscoverableTo($request->user()))
            ->values()
            ->map(fn (CommunityChannel $channel) => $channel->toFrontendArray($request->user()))
            ->all();

        CommunityModeration::log($request->user(), 'channels_reordered', null, null, null, [
            'channel_ids' => $validated['channels'],
        ]);

        return response()->json([
            'channels' => $channels,
        ]);
    }

    private function normalizeAllowedRoles(array $roles): array
    {
        return collect($roles)
            ->filter(fn ($role) => in_array($role, ['admin', 'moderator', 'model'], true))
            ->values()
            ->all();
    }

    private function syncInvites(CommunityChannel $channel, array $userIds, int $actorId): void
    {
        $ids = collect($userIds)
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        $channel->accessGrants()->whereNotIn('user_id', $ids->all())->delete();

        foreach ($ids as $userId) {
            $channel->accessGrants()->updateOrCreate(
                ['user_id' => $userId],
                ['invited_by' => $actorId],
            );
        }
    }

    private function channelRosterPayload($user): array
    {
        $allChannels = CommunityChannel::query()
            ->with('accessGrants')
            ->where('is_archived', false)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $channels = $allChannels
            ->filter(fn (CommunityChannel $channel) => $channel->isDiscoverableTo($user))
            ->values();

        $unreadCounts = $this->unreadCountsFor($channels, $user);

        return [
            'channels' => $channels
                ->map(fn (CommunityChannel $channel) => $channel->toFrontendArray($user, $unreadCounts[$channel->id] ?? 0))
                ->all(),
            'archived_channels' => $user->canModerateCommunity()
                ? CommunityChannel::query()
                    ->where('is_archived', true)
                    ->latest('updated_at')
                    ->get()
                    ->map(fn (CommunityChannel $channel) => $channel->toFrontendArray($user))
                    ->all()
                : [],
        ];
    }

    private function unreadCountsFor(Collection $channels, $user): array
    {
        $accessibleChannelIds = $channels
            ->filter(fn (CommunityChannel $channel) => $channel->isAccessibleTo($user))
            ->pluck('id')
            ->all();

        if ($accessibleChannelIds === []) {
            return [];
        }

        return CommunityMessage::query()
            ->selectRaw('channel_id, count(*) as unread_count')
            ->whereIn('channel_id', $accessibleChannelIds)
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id))
            ->groupBy('channel_id')
            ->pluck('unread_count', 'channel_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
