<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class BroadcastingAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => 'test-app',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
        ]);

        Broadcast::forgetDrivers();

        require base_path('routes/channels.php');
    }

    public function test_accessible_members_can_authorize_the_private_community_channel(): void
    {
        $user = User::factory()->create();
        $this->grantCommunityAccess($user);
        $channel = CommunityChannel::query()->create([
            'name' => 'general',
            'description' => 'General chat',
            'created_by' => $user->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => "private-community.channel.{$channel->id}",
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['auth']);
    }

    public function test_members_cannot_authorize_private_admin_only_channels(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $this->grantCommunityAccess($member);
        $channel = CommunityChannel::query()->create([
            'name' => 'leaders',
            'description' => 'Admin only',
            'created_by' => $owner->id,
            'is_private' => true,
            'access_mode' => CommunityChannel::ACCESS_ADMINS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $response = $this->actingAs($member)->post('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => "private-community.channel.{$channel->id}",
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertForbidden();
    }

    public function test_admins_can_authorize_private_admin_only_channels(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $channel = CommunityChannel::query()->create([
            'name' => 'leaders',
            'description' => 'Admin only',
            'created_by' => $admin->id,
            'is_private' => true,
            'access_mode' => CommunityChannel::ACCESS_ADMINS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $response = $this->actingAs($admin)->post('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => "private-community.channel.{$channel->id}",
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['auth']);
    }

    public function test_presence_auth_returns_the_member_payload_used_by_the_community_sidebar(): void
    {
        $user = User::factory()->create([
            'name' => 'Roel Descartin',
        ]);
        $this->grantCommunityAccess($user);

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'presence-community.presence',
        ], [
            'Accept' => 'application/json',
        ]);

        $payload = $response
            ->assertOk()
            ->assertJsonStructure(['auth', 'channel_data'])
            ->json();

        $channelData = json_decode($payload['channel_data'], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame((string) $user->id, $channelData['user_id']);
        $this->assertSame($user->id, $channelData['user_info']['id']);
        $this->assertSame('Roel Descartin', $channelData['user_info']['name']);
        $this->assertSame('RD', $channelData['user_info']['initials']);
        $this->assertTrue($channelData['user_info']['online']);
    }

    public function test_only_invited_members_can_authorize_invite_only_channels(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invited = User::factory()->create();
        $uninvited = User::factory()->create();
        $this->grantCommunityAccess($invited);
        $this->grantCommunityAccess($uninvited);
        $channel = CommunityChannel::query()->create([
            'name' => 'quiet-room',
            'description' => 'Invite only',
            'created_by' => $admin->id,
            'is_private' => true,
            'access_mode' => CommunityChannel::ACCESS_INVITE,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);

        $channel->accessGrants()->create([
            'user_id' => $invited->id,
            'invited_by' => $admin->id,
        ]);

        $this->actingAs($invited)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => "private-community.channel.{$channel->id}",
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this->actingAs($uninvited)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => "private-community.channel.{$channel->id}",
            ], ['Accept' => 'application/json'])
            ->assertForbidden();
    }
}
