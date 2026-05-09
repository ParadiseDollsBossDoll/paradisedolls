<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityReadTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_community_and_unread_messages_are_marked_as_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $channel = CommunityChannel::query()->create([
            'name' => 'general',
            'description' => 'General discussion',
            'created_by' => $admin->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);
        $message = CommunityMessage::query()->create([
            'channel_id' => $channel->id,
            'user_id' => $member->id,
            'message' => 'Unread note for admin.',
        ]);

        $this->actingAs($admin)
            ->get('/community')
            ->assertOk();

        $this->assertDatabaseHas('community_message_reads', [
            'message_id' => $message->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_mark_read_endpoint_accepts_channel_message_relations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $channel = CommunityChannel::query()->create([
            'name' => 'announcements',
            'description' => 'Team updates',
            'created_by' => $admin->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);
        $message = CommunityMessage::query()->create([
            'channel_id' => $channel->id,
            'user_id' => $member->id,
            'message' => 'Please read this update.',
        ]);

        $this->actingAs($admin)
            ->post("/community/channels/{$channel->slug}/read")
            ->assertOk()
            ->assertJsonPath('read', true);

        $this->assertDatabaseHas('community_message_reads', [
            'message_id' => $message->id,
            'user_id' => $admin->id,
        ]);
    }
}
