<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_hidden_invite_only_channels_are_not_listed_for_uninvited_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $channel = CommunityChannel::query()->create([
            'name' => 'lead-circle',
            'description' => 'Invite only leaders channel',
            'created_by' => $admin->id,
            'is_private' => true,
            'access_mode' => CommunityChannel::ACCESS_INVITE,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        CommunityMessage::query()->create([
            'channel_id' => $channel->id,
            'user_id' => $admin->id,
            'message' => 'Internal planning only.',
        ]);

        $response = $this->actingAs($member)->get('/community');

        $response->assertOk();
        $response->assertDontSeeText('lead-circle');
        $this->actingAs($member)
            ->get("/community/channels/{$channel->slug}")
            ->assertOk()
            ->assertDontSeeText('Internal planning only.');

        $this->actingAs($member)
            ->get("/community/channels/{$channel->slug}/messages")
            ->assertForbidden();
    }

    public function test_locked_visibility_channels_can_be_seen_but_not_opened_by_unauthorized_members(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $member = User::factory()->create();
        $channel = CommunityChannel::query()->create([
            'name' => 'mods-huddle',
            'description' => 'Moderator planning room',
            'created_by' => $moderator->id,
            'is_private' => true,
            'access_mode' => CommunityChannel::ACCESS_MODERATORS,
            'denied_behavior' => CommunityChannel::DENIED_LOCKED,
        ]);

        $response = $this->actingAs($member)->get('/community');

        $response->assertOk();
        $response->assertSeeText('mods-huddle');

        $this->actingAs($member)
            ->get("/community/channels/{$channel->slug}/messages")
            ->assertForbidden();
    }

    public function test_presence_refresh_rejects_inaccessible_channel_ids(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $channel = CommunityChannel::query()->create([
            'name' => 'leaders',
            'description' => 'Admins only',
            'created_by' => $admin->id,
            'is_private' => true,
            'access_mode' => CommunityChannel::ACCESS_ADMINS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $this->actingAs($member)
            ->get("/community/presence?channel_id={$channel->id}")
            ->assertForbidden();
    }
}
