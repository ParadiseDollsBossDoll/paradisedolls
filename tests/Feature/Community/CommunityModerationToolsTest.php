<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityModerationToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderators_can_timeout_members_in_accessible_channels(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $member = User::factory()->create();
        $this->grantCommunityAccess($member);
        $channel = CommunityChannel::query()->create([
            'name' => 'general',
            'description' => 'General discussion',
            'created_by' => $moderator->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $this->actingAs($moderator)
            ->post("/community/members/{$member->id}/timeout", [
                'channel_id' => $channel->id,
                'duration_minutes' => 45,
                'reason' => 'Cooldown',
            ])
            ->assertCreated()
            ->assertJsonPath('timeout.user_id', $member->id);

        $this->actingAs($member)
            ->post("/community/channels/{$channel->slug}/messages", [
                'message' => 'Can I still post?',
            ])
            ->assertStatus(423);
    }

    public function test_moderators_cannot_timeout_other_moderators(): void
    {
        $actor = User::factory()->create(['role' => 'moderator']);
        $target = User::factory()->create(['role' => 'moderator']);
        $channel = CommunityChannel::query()->create([
            'name' => 'support-questions',
            'description' => 'Support',
            'created_by' => $actor->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $this->actingAs($actor)
            ->post("/community/members/{$target->id}/timeout", [
                'channel_id' => $channel->id,
                'duration_minutes' => 30,
            ])
            ->assertForbidden();
    }

    public function test_moderators_can_delete_other_members_messages(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $member = User::factory()->create();
        $channel = CommunityChannel::query()->create([
            'name' => 'wins',
            'description' => 'Celebrate wins',
            'created_by' => $moderator->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);
        $message = CommunityMessage::query()->create([
            'channel_id' => $channel->id,
            'user_id' => $member->id,
            'message' => 'A message that needs moderation.',
        ]);

        $this->actingAs($moderator)
            ->delete("/community/messages/{$message->id}")
            ->assertOk()
            ->assertJsonPath('deleted', true);

        $this->assertSoftDeleted('community_messages', ['id' => $message->id]);
    }
}
