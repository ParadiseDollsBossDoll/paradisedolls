<?php

namespace App\Events;

use App\Models\CommunityMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommunityMessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $messageId, public int $channelId)
    {
    }

    public static function fromMessage(CommunityMessage $message): self
    {
        return new self($message->id, $message->channel_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("community.channel.{$this->channelId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'community.message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'channel_id' => $this->channelId,
        ];
    }
}
