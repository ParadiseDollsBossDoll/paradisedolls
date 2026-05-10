<?php

namespace App\Http\Controllers\Community;

use App\Events\CommunityMessageCreated;
use App\Events\CommunityMessageDeleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunityMessageRequest;
use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Models\CommunityMessageRead;
use App\Models\User;
use App\Support\CommunityModeration;
use App\Support\CommunityPresence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityMessageController extends Controller
{
    public function index(Request $request, CommunityChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (! $channel->isAccessibleTo($user)) {
            return response()->json([
                'message' => __('You no longer have access to this channel.'),
            ], 403);
        }

        CommunityPresence::ping($user);

        $request->validate([
            'before_id' => ['nullable', 'integer', 'min:1'],
            'after_id' => ['nullable', 'integer', 'min:1'],
            'q' => ['nullable', 'string', 'min:'.max(1, (int) config('community.performance.search_min_chars', 2)), 'max:100'],
        ]);

        $query = $channel->messages()
            ->select(['id', 'channel_id', 'user_id', 'message', 'attachment', 'reply_to', 'is_pinned', 'created_at'])
            ->with(['user:id,name', 'replyTo.user:id,name', 'reactions']);

        $search = trim((string) $request->input('q'));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->whereFullText('message', $search)
                    ->orWhere('attachment', 'like', '%'.$search.'%');
            });
        }

        $perPage = max(1, (int) config('community.performance.message_page_size', 25));
        $syncLimit = max($perPage, (int) config('community.performance.message_sync_limit', 50));
        $beforeId = $request->integer('before_id');
        $afterId = $request->integer('after_id');

        if ($beforeId > 0) {
            $messages = (clone $query)
                ->where('community_messages.id', '<', $beforeId)
                ->latest()
                ->take($perPage)
                ->get()
                ->reverse()
                ->values();
        } elseif ($afterId > 0) {
            $messages = (clone $query)
                ->where('community_messages.id', '>', $afterId)
                ->oldest()
                ->take($syncLimit)
                ->get()
                ->values();
        } else {
            $messages = (clone $query)
                ->latest()
                ->take($perPage)
                ->get()
                ->reverse()
                ->values();
        }

        $firstUnreadMessageId = $this->firstUnreadMessageId($channel, $user);

        if ($messages->isNotEmpty()) {
            $this->persistReadState($messages->pluck('id')->all(), $user->id);
        }

        $hasMore = false;

        if ($messages->isNotEmpty()) {
            $hasMore = $channel->messages()->where('community_messages.id', '<', $messages->first()->id)->exists();
        }

        return response()->json([
            'channel' => $channel->toFrontendArray($user),
            'messages' => $messages->map(fn (CommunityMessage $message) => $message->toFrontendArray($user))->all(),
            'has_more' => $hasMore,
            'first_unread_message_id' => $search === '' ? $firstUnreadMessageId : null,
            'search' => [
                'query' => $search,
                'result_count' => $messages->count(),
            ],
        ]);
    }

    public function store(StoreCommunityMessageRequest $request, CommunityChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (! $channel->isAccessibleTo($user)) {
            return response()->json([
                'message' => __('You no longer have access to this channel.'),
            ], 403);
        }

        if (! $channel->canPost($user)) {
            $timeout = $user->activeCommunityTimeoutFor($channel);

            if ($timeout) {
                return response()->json([
                    'message' => __('You are currently timed out from posting in community chat.'),
                    'timeout_expires_at' => $timeout->expires_at?->toIso8601String(),
                ], 423);
            }

            return response()->json([
                'message' => __('This channel is locked right now.'),
            ], 423);
        }

        if ($channel->slowmode_seconds > 0 && ! $user->canModerateCommunity()) {
            $latestMessage = $channel->messages()
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            if ($latestMessage && $latestMessage->created_at->diffInSeconds(now()) < $channel->slowmode_seconds) {
                return response()->json([
                    'message' => __('Slowmode is active. Please wait a moment before sending again.'),
                ], 429);
            }
        }

        $replyTo = null;

        if ($request->filled('reply_to')) {
            $replyTo = CommunityMessage::query()
                ->whereKey($request->integer('reply_to'))
                ->where('channel_id', $channel->id)
                ->first();
        }

        $attachment = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            try {
                $path = $file->store('community-attachments', 'public');
            } catch (\Throwable $exception) {
                report($exception);
                Log::channel(config('community.performance.log_channel'))->warning('Community attachment upload failed.', [
                    'user_id' => $user->id,
                    'channel_id' => $channel->id,
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'error' => $exception->getMessage(),
                ]);

                return response()->json([
                    'message' => __('We could not upload that file right now. Please try again.'),
                ], 422);
            }

            $attachment = [
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        }

        $message = CommunityMessage::query()->create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => $request->filled('message') ? trim((string) $request->input('message')) : null,
            'attachment' => $attachment,
            'reply_to' => $replyTo?->id,
        ]);

        $channel->forceFill(['last_message_at' => now()])->save();

        $this->persistReadState([$message->id], $user->id);

        $message->load(['user:id,name', 'replyTo.user:id,name', 'reactions']);

        $this->broadcastSafely(
            fn () => broadcast(new CommunityMessageCreated($message))->toOthers(),
            'community_message_created',
            ['message_id' => $message->id, 'channel_id' => $channel->id, 'user_id' => $user->id]
        );

        CommunityPresence::ping($user);
        CommunityPresence::setTyping($user, $channel->id, false);

        return response()->json([
            'message' => $message->toFrontendArray($user),
        ], 201);
    }

    public function destroy(Request $request, CommunityMessage $message): JsonResponse
    {
        $user = $request->user();
        $channel = $message->channel;

        abort_unless(
            $user && (
                $message->user_id === $user->id
                || ($user->canModerateCommunity() && $channel->isAccessibleTo($user))
            ),
            403
        );

        $channelId = $message->channel_id;
        $deletedId = $message->id;

        if ($user->id !== $message->user_id) {
            CommunityModeration::log($user, 'message_deleted', $channel, $message, $message->user, [
                'reason' => 'moderation',
            ]);
        }

        $message->delete();

        CommunityChannel::query()
            ->whereKey($channelId)
            ->update([
                'last_message_at' => CommunityMessage::query()
                    ->where('channel_id', $channelId)
                    ->latest()
                    ->value('created_at'),
            ]);

        $this->broadcastSafely(
            fn () => broadcast(CommunityMessageDeleted::fromMessage($message))->toOthers(),
            'community_message_deleted',
            ['message_id' => $deletedId, 'channel_id' => $channelId, 'user_id' => $user->id]
        );

        return response()->json([
            'deleted' => true,
            'message_id' => $deletedId,
        ]);
    }

    public function markRead(Request $request, CommunityChannel $channel): JsonResponse
    {
        $user = $request->user();

        abort_unless($channel->isAccessibleTo($user), 403);

        CommunityPresence::ping($user);

        $unreadQuery = $channel->messages()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id));

        $this->persistUnreadQueryInChunks($unreadQuery, $user->id);

        return response()->json([
            'read' => true,
        ]);
    }

    public function pin(Request $request, CommunityMessage $message): JsonResponse
    {
        $user = $request->user();

        abort_unless($user?->canModerateCommunity() && $message->channel->isAccessibleTo($user), 403);

        $message->update([
            'is_pinned' => ! $message->is_pinned,
        ]);

        $message->load(['user:id,name', 'replyTo.user:id,name', 'reactions']);

        CommunityModeration::log($user, $message->is_pinned ? 'message_pinned' : 'message_unpinned', $message->channel, $message, $message->user);

        return response()->json([
            'message' => $message->toFrontendArray($user),
        ]);
    }

    private function persistReadState(array $messageIds, int $userId): void
    {
        if ($messageIds === []) {
            return;
        }

        $now = now();

        CommunityMessageRead::query()->upsert(
            collect($messageIds)->map(fn (int $messageId) => [
                'message_id' => $messageId,
                'user_id' => $userId,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['message_id', 'user_id'],
            ['read_at', 'updated_at']
        );
    }

    private function persistUnreadQueryInChunks(Builder|Relation $query, int $userId): void
    {
        $chunkSize = max(50, (int) config('community.performance.read_mark_chunk_size', 250));

        $query
            ->select('community_messages.id')
            ->chunkById($chunkSize, function (Collection $messages) use ($userId): void {
                $this->persistReadState($messages->pluck('id')->all(), $userId);
            }, 'community_messages.id', 'id');
    }

    private function firstUnreadMessageId(CommunityChannel $channel, User $user): ?int
    {
        return $channel->messages()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id))
            ->orderBy('created_at')
            ->value('community_messages.id');
    }

    private function broadcastSafely(callable $dispatch, string $eventName, array $context = []): void
    {
        try {
            $dispatch();
        } catch (\Throwable $exception) {
            report($exception);

            Log::channel(config('community.performance.log_channel'))->warning('Community broadcast dispatch failed.', [
                'event' => $eventName,
                'error' => $exception->getMessage(),
                ...$context,
            ]);
        }
    }
}
