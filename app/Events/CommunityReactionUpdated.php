<?php

namespace App\Events;

use App\Models\CommunityMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommunityReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public CommunityMessage $message)
    {
        $this->message->loadMissing([
            'user:id,name',
            'replyTo.user:id,name',
            'reactions',
            'reads',
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("community.channel.{$this->message->channel_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'community.message.reactions';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->toFrontendArray(),
        ];
    }
}
