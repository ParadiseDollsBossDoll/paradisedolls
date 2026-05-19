<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CommunitySlowmodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_with_old_history_is_blocked_by_recent_message_slowmode(): void
    {
        $now = Carbon::parse('2026-05-17 12:00:00');
        $this->travelTo($now);

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $this->grantCommunityAccess($member);
        $channel = $this->createChannel($admin, [
            'name' => 'general',
            'slowmode_seconds' => 30,
        ]);

        $this->createMessageAt($channel, $member, 'Old message before slowmode.', $now->copy()->subMinutes(10));
        $this->createMessageAt($channel, $member, 'Recent message inside slowmode.', $now->copy()->subSeconds(10));

        $this->actingAs($member)
            ->postJson("/community/channels/{$channel->slug}/messages", [
                'message' => 'This should be blocked.',
            ])
            ->assertStatus(429)
            ->assertJsonPath('message', __('Slowmode is active. Please wait a moment before sending again.'));

        $this->assertDatabaseMissing('community_messages', [
            'channel_id' => $channel->id,
            'user_id' => $member->id,
            'message' => 'This should be blocked.',
        ]);
    }

    public function test_member_can_post_after_slowmode_window_passes(): void
    {
        $now = Carbon::parse('2026-05-17 12:00:00');
        $this->travelTo($now);

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $this->grantCommunityAccess($member);
        $channel = $this->createChannel($admin, [
            'name' => 'cooldown-complete',
            'slowmode_seconds' => 30,
        ]);

        $this->createMessageAt($channel, $member, 'Message outside slowmode.', $now->copy()->subSeconds(31));

        $this->actingAs($member)
            ->postJson("/community/channels/{$channel->slug}/messages", [
                'message' => 'Posting after cooldown.',
            ])
            ->assertCreated()
            ->assertJsonPath('message.message', 'Posting after cooldown.');
    }

    public function test_admins_and_moderators_bypass_slowmode(): void
    {
        $now = Carbon::parse('2026-05-17 12:00:00');
        $this->travelTo($now);

        $creator = User::factory()->create(['role' => 'admin']);

        foreach (['admin', 'moderator'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $channel = $this->createChannel($creator, [
                'name' => "{$role}-slowmode",
                'slowmode_seconds' => 30,
            ]);

            $this->createMessageAt($channel, $user, "Recent {$role} message.", $now->copy()->subSeconds(5));

            $this->actingAs($user)
                ->postJson("/community/channels/{$channel->slug}/messages", [
                    'message' => "{$role} bypass message.",
                ])
                ->assertCreated()
                ->assertJsonPath('message.message', "{$role} bypass message.");
        }
    }

    private function createChannel(User $creator, array $overrides = []): CommunityChannel
    {
        return CommunityChannel::query()->create(array_merge([
            'name' => 'general',
            'description' => 'General discussion',
            'created_by' => $creator->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
            'slowmode_seconds' => 0,
        ], $overrides));
    }

    private function createMessageAt(CommunityChannel $channel, User $user, string $body, Carbon $createdAt): CommunityMessage
    {
        $message = CommunityMessage::query()->create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => $body,
        ]);

        $message->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $message;
    }
}
