<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Models\CommunityModerationLog;
use App\Models\Course;
use App\Models\User;
use App\Support\CommunityPresence;
use App\Support\DefaultCommunityChannels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function show(Request $request, ?CommunityChannel $channel = null): View
    {
        $user = $request->user();
        $channelNotice = null;

        CommunityPresence::ping($user);
        $this->ensureDefaultChannels($user);

        $allChannels = CommunityChannel::query()
            ->with('accessGrants')
            ->where('is_archived', false)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $channels = $allChannels
            ->filter(fn (CommunityChannel $item) => $item->isDiscoverableTo($user))
            ->values();

        $accessibleChannels = $channels
            ->filter(fn (CommunityChannel $item) => $item->isAccessibleTo($user))
            ->values();

        if ($channel && ! $channel->isAccessibleTo($user)) {
            $channelNotice = [
                'tone' => 'warning',
                'message' => $channel->isDiscoverableTo($user)
                    ? __('That channel is visible, but you do not currently have access to open it.')
                    : __('That channel is private or unavailable right now.'),
            ];
            $channel = null;
        }

        $selectedChannel = $channel
            ? $accessibleChannels->firstWhere('id', $channel->id)
            : $accessibleChannels->first();

        $unreadCounts = $this->unreadCountsFor($accessibleChannels, $user);
        $firstUnreadMessageId = $selectedChannel ? $this->firstUnreadMessageId($selectedChannel, $user) : null;
        $selectedChannelUnreadCount = $selectedChannel ? ($unreadCounts[$selectedChannel->id] ?? 0) : 0;

        if ($selectedChannel && $selectedChannelUnreadCount > 0) {
            $this->markChannelAsRead($selectedChannel, $user);
        }

        $initialPageSize = max(1, (int) config('community.performance.initial_message_page_size', 15));
        $rawMessages = $selectedChannel
            ? $selectedChannel->messages()
                ->select(['id', 'channel_id', 'user_id', 'message', 'attachment', 'reply_to', 'is_pinned', 'created_at'])
                ->with(['user:id,name,profile_photo_path', 'replyTo.user:id,name', 'reactions'])
                ->reorder()
                ->latest()
                ->take($initialPageSize)
                ->get()
                ->reverse()
                ->values()
            : collect([]);

        $initialHasMore = $rawMessages->isNotEmpty()
            && $selectedChannel->messages()->where('id', '<', $rawMessages->first()->id)->exists();

        $messages = $rawMessages->map(fn (CommunityMessage $message) => $message->toFrontendArray($user))->all();
        $pinnedMessages = $selectedChannel ? $this->pinnedMessagesFor($selectedChannel, $user) : [];

        $courses = $user->isModel()
            ? Course::query()
                ->where('is_published', true)
                ->with('lessons:id,course_id,is_published')
                ->orderBy('sort_order')
                ->get()
            : collect();

        $overallProgress = $user->isModel() && $courses->isNotEmpty()
            ? (int) round(array_sum(Course::batchProgressPercentsForUser($user, $courses)) / $courses->count())
            : 100;

        $archivedChannels = $user->canModerateCommunity()
            ? CommunityChannel::query()
                ->where('is_archived', true)
                ->latest('updated_at')
                ->get()
                ->map(fn (CommunityChannel $item) => $item->toFrontendArray($user))
                ->all()
            : [];

        $moderationLogs = $user->canModerateCommunity()
            ? CommunityModerationLog::query()
                ->with(['actor:id,name', 'targetUser:id,name', 'channel:id,name', 'message:id,message'])
                ->latest()
                ->take(40)
                ->get()
                ->map(fn (CommunityModerationLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor_name' => $log->actor?->name ?? __('System'),
                    'target_name' => $log->targetUser?->name,
                    'channel_name' => $log->channel?->name,
                    'message_excerpt' => Str::limit($log->message?->message ?? '', 80),
                    'details' => $log->details ?? [],
                    'created_at' => $log->created_at?->toIso8601String(),
                ])
                ->all()
            : [];

        $memberDirectory = $user->canManageCommunityChannels()
            ? User::query()
                ->whereIn('role', ['admin', 'moderator', 'model'])
                ->orderBy('name')
                ->get(['id', 'name', 'role'])
                ->map(fn (User $member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->role,
                ])
                ->all()
            : [];

        return view('community.show', [
            'communityState' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'initials' => $user->initials(),
                    'accent' => $user->communityAccent(),
                    'profile_photo_url' => $user->profilePhotoUrl(),
                ],
                'server' => [
                    'name' => __('Paradise Dolls Foundation'),
                    'subtitle' => __('Foundation'),
                ],
                'progress' => [
                    'percent' => $overallProgress,
                    'label' => $user->canModerateCommunity() ? __('Community Chat access') : __('Overall progress'),
                    'subtext' => $user->canModerateCommunity() ? __('Moderation tools enabled') : __('Academy completion across published lessons'),
                ],
                'channels' => $channels->map(fn (CommunityChannel $item) => $item->toFrontendArray($user, $unreadCounts[$item->id] ?? 0))->all(),
                'selected_channel' => $selectedChannel?->toFrontendArray($user, $unreadCounts[$selectedChannel->id] ?? 0),
                'messages' => $messages,
                'pinned_messages' => $pinnedMessages,
                'has_more' => $initialHasMore,
                'members' => CommunityPresence::payloadFor($user, $selectedChannel?->id),
                'channel_notice' => $channelNotice,
                'first_unread_message_id' => $firstUnreadMessageId,
                'archived_channels' => $archivedChannels,
                'moderation_logs' => $moderationLogs,
                'member_directory' => $memberDirectory,
                'channel_access_options' => [
                    'access_modes' => [
                        ['value' => CommunityChannel::ACCESS_MEMBERS, 'label' => __('Members')],
                        ['value' => CommunityChannel::ACCESS_MODERATORS, 'label' => __('Moderators')],
                        ['value' => CommunityChannel::ACCESS_ADMINS, 'label' => __('Admins')],
                        ['value' => CommunityChannel::ACCESS_ROLES, 'label' => __('Role specific')],
                        ['value' => CommunityChannel::ACCESS_INVITE, 'label' => __('Invite only')],
                    ],
                    'denied_behaviors' => [
                        ['value' => CommunityChannel::DENIED_HIDDEN, 'label' => __('Hide entirely')],
                        ['value' => CommunityChannel::DENIED_LOCKED, 'label' => __('Show as locked')],
                    ],
                    'roles' => [
                        ['value' => 'admin', 'label' => __('Admins')],
                        ['value' => 'moderator', 'label' => __('Moderators')],
                        ['value' => 'model', 'label' => __('Members')],
                    ],
                ],
                'routes' => [
                    'home' => route('community.show'),
                    'channels_index' => route('community.channels.index'),
                    'channel' => route('community.channels.show', ['channel' => '__slug__']),
                    'messages' => route('community.channels.messages.index', ['channel' => '__slug__']),
                    'send_message' => route('community.channels.messages.store', ['channel' => '__slug__']),
                    'mark_read' => route('community.channels.read', ['channel' => '__slug__']),
                    'toggle_reaction' => route('community.messages.reactions.toggle', ['message' => '__id__']),
                    'delete_message' => route('community.messages.destroy', ['message' => '__id__']),
                    'toggle_pin' => route('community.messages.pin', ['message' => '__id__']),
                    'presence' => route('community.presence.index'),
                    'presence_ping' => route('community.presence.ping'),
                    'presence_typing' => route('community.presence.typing'),
                    'create_channel' => route('community.channels.store'),
                    'archive_channel' => route('community.channels.archive', ['channel' => '__slug__']),
                    'update_channel' => route('community.channels.update', ['channel' => '__slug__']),
                    'delete_channel' => route('community.channels.destroy', ['channel' => '__slug__']),
                    'restore_channel' => route('community.channels.restore', ['channel' => '__slug__']),
                    'reorder_channels' => route('community.channels.reorder'),
                    'member_timeout' => route('community.members.timeout', ['user' => '__id__']),
                    'revoke_timeout' => route('community.timeouts.revoke', ['timeout' => '__id__']),
                    'moderation_logs' => route('community.moderation.history'),
                ],
                'features' => [
                    'can_manage_channels' => $user->canManageCommunityChannels(),
                    'can_moderate_messages' => $user->canModerateCommunity(),
                    'performance' => [
                        'search_min_chars' => max(1, (int) config('community.performance.search_min_chars', 2)),
                        'search_preview_limit' => max(1, (int) config('community.performance.search_preview_limit', 8)),
                        'image_preview_max_bytes' => max(1, (int) config('community.performance.image_preview_max_bytes', 4194304)),
                    ],
                    'realtime_mode' => 'echo-compatible',
                ],
            ],
        ]);
    }

    private function ensureDefaultChannels(User $user): void
    {
        if (CommunityChannel::query()->where('is_archived', false)->exists()) {
            return;
        }

        $creatorId = $user->id;

        $channels = collect(DefaultCommunityChannels::definitions())->map(function (array $channel, int $index) use ($creatorId) {
            return CommunityChannel::query()->updateOrCreate(
                ['slug' => Str::slug($channel['name'])],
                [
                    'name' => $channel['name'],
                    'category' => $channel['category'],
                    'description' => $channel['description'],
                    'created_by' => $creatorId,
                    'is_private' => false,
                    'access_mode' => $channel['access_mode'],
                    'denied_behavior' => $channel['denied_behavior'],
                    'allowed_roles' => $channel['allowed_roles'] ?? null,
                    'order' => $index + 1,
                    'is_archived' => false,
                    'is_locked' => $channel['is_locked'] ?? false,
                    'slowmode_seconds' => 0,
                ]
            );
        });

        $general = $channels->first();

        if (! $general) {
            return;
        }

        CommunityMessage::query()->firstOrCreate([
            'channel_id' => $general->id,
            'user_id' => $creatorId,
            'message' => 'Welcome to #general. This is the start of the Paradise Dolls Foundation community.',
        ]);

        $general->update(['last_message_at' => now()]);
    }

    private function unreadCountsFor(Collection $channels, User $user): array
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

    private function markChannelAsRead(CommunityChannel $channel, User $user): void
    {
        $unreadQuery = $channel->messages()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id));

        $this->persistUnreadQueryInChunks($unreadQuery, $user->id);
    }

    private function firstUnreadMessageId(CommunityChannel $channel, User $user): ?int
    {
        return $channel->messages()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id))
            ->orderBy('created_at')
            ->value('community_messages.id');
    }

    private function pinnedMessagesFor(CommunityChannel $channel, User $user): array
    {
        return $channel->messages()
            ->select(['id', 'channel_id', 'user_id', 'message', 'attachment', 'reply_to', 'is_pinned', 'created_at'])
            ->with(['user:id,name,profile_photo_path', 'replyTo.user:id,name', 'reactions'])
            ->where('is_pinned', true)
            ->reorder()
            ->latest()
            ->take(50)
            ->get()
            ->map(fn (CommunityMessage $message) => $message->toFrontendArray($user))
            ->all();
    }

    private function persistUnreadQueryInChunks(Builder|Relation $query, int $userId): void
    {
        $chunkSize = max(50, (int) config('community.performance.read_mark_chunk_size', 250));

        $query
            ->select('community_messages.id')
            ->chunkById($chunkSize, function (Collection $messages) use ($userId): void {
                $this->persistReadRows($messages->pluck('id')->all(), $userId);
            }, 'community_messages.id', 'id');
    }

    private function persistReadRows(array $messageIds, int $userId): void
    {
        if ($messageIds === []) {
            return;
        }

        $now = now();

        \App\Models\CommunityMessageRead::query()->upsert(
            array_map(fn (int $messageId) => [
                'message_id' => $messageId,
                'user_id' => $userId,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ], $messageIds),
            ['message_id', 'user_id'],
            ['read_at', 'updated_at']
        );
    }
}
