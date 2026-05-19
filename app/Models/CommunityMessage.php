<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CommunityMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'channel_id',
        'user_id',
        'message',
        'attachment',
        'reply_to',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'attachment' => 'array',
            'is_pinned' => 'boolean',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(CommunityChannel::class, 'channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'reply_to');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class, 'message_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(CommunityMessageRead::class, 'message_id');
    }

    public function excerpt(): string
    {
        if (filled($this->message)) {
            return Str::limit(trim(preg_replace('/\s+/', ' ', $this->message) ?? ''), 90);
        }

        return $this->attachment['original_name'] ?? __('Attachment');
    }

    public function attachmentPayload(): ?array
    {
        if (! is_array($this->attachment) || blank($this->attachment['path'] ?? null)) {
            return null;
        }

        $mime = $this->attachment['mime_type'] ?? 'application/octet-stream';

        $url = route('community.messages.attachment', ['message' => $this->id]);

        return [
            'url' => $url,
            'preview_url' => Str::startsWith($mime, 'image/') ? $url : null,
            'name' => $this->attachment['original_name'] ?? basename($this->attachment['path']),
            'size' => $this->attachment['size'] ?? null,
            'mime_type' => $mime,
            'is_image' => Str::startsWith($mime, 'image/'),
        ];
    }

    public function reactionsFor(?User $viewer = null): array
    {
        $viewerId = $viewer?->id;

        return $this->reactions
            ->groupBy('emoji')
            ->map(function ($group, $emoji) use ($viewerId) {
                return [
                    'emoji' => $emoji,
                    'count' => $group->count(),
                    'reacted' => $viewerId ? $group->contains('user_id', $viewerId) : false,
                ];
            })
            ->values()
            ->all();
    }

    public function toFrontendArray(?User $viewer = null): array
    {
        $reply = $this->replyTo;

        return [
            'id' => $this->id,
            'channel_id' => $this->channel_id,
            'message' => $this->message,
            'attachment' => $this->attachmentPayload(),
            'reply_to' => $reply ? [
                'id' => $reply->id,
                'message' => $reply->excerpt(),
                'user_name' => $reply->user?->name,
            ] : null,
            'is_pinned' => (bool) $this->is_pinned,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'initials' => $this->user->initials(),
                'accent' => $this->user->communityAccent(),
                'profile_photo_url' => $this->user->profilePhotoUrl(),
                'is_current_user' => $viewer?->id === $this->user_id,
            ],
            'reactions' => $this->reactionsFor($viewer),
            'can_delete' => $viewer ? ($viewer->id === $this->user_id || $viewer->canModerateCommunity()) : false,
            'can_pin' => $viewer?->canModerateCommunity() ?? false,
        ];
    }
}
