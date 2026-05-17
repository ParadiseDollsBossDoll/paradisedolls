<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityChannelManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_channel_and_members_see_it_in_the_roster(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $this->grantCommunityAccess($member);

        $this->actingAs($admin)
            ->postJson('/community/channels', [
                'name' => 'wins',
                'description' => 'Celebrate member milestones.',
                'category' => 'Community',
                'access_mode' => CommunityChannel::ACCESS_MEMBERS,
                'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
            ])
            ->assertCreated()
            ->assertJsonPath('channel.name', 'wins');

        $this->actingAs($member)
            ->getJson('/community/channels')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'wins',
                'slug' => 'wins',
            ]);
    }

    public function test_admin_can_delete_a_channel_permanently(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        CommunityChannel::query()->create([
            'name' => 'general',
            'description' => 'General discussion',
            'created_by' => $admin->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);
        $channel = CommunityChannel::query()->create([
            'name' => 'announcements',
            'description' => 'Important updates',
            'created_by' => $admin->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $this->actingAs($admin)
            ->deleteJson("/community/channels/{$channel->slug}")
            ->assertOk()
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('community_channels', [
            'id' => $channel->id,
        ]);
    }

    public function test_admin_can_create_channel_with_slowmode(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $this->grantCommunityAccess($member);

        $response = $this->actingAs($admin)
            ->postJson('/community/channels', [
                'name' => 'slow-chat',
                'description' => 'A slower room.',
                'category' => 'Community',
                'access_mode' => CommunityChannel::ACCESS_MEMBERS,
                'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
                'slowmode_seconds' => 30,
            ])
            ->assertCreated()
            ->assertJsonPath('channel.slowmode_seconds', 30);

        $slug = $response->json('channel.slug');

        $this->assertDatabaseHas('community_channels', [
            'slug' => $slug,
            'slowmode_seconds' => 30,
        ]);

        $this->actingAs($member)
            ->getJson('/community/channels')
            ->assertOk()
            ->assertJsonFragment([
                'slug' => $slug,
                'slowmode_seconds' => 30,
            ]);
    }
}
