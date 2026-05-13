<?php

namespace Tests\Feature\Community;

use App\Models\CommunityChannel;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityPinnedMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_channel_messages_include_pinned_messages_outside_current_page(): void
    {
        config(['community.performance.message_page_size' => 5]);

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $channel = $this->createChannel($admin);
        $pinned = $this->createMessage($channel, $member, 'Older pinned message', now()->subHour(), true);

        foreach (range(1, 8) as $index) {
            $this->createMessage($channel, $member, "Recent message {$index}", now()->addMinutes($index));
        }

        $response = $this->actingAs($admin)
            ->getJson("/community/channels/{$channel->slug}/messages")
            ->assertOk()
            ->assertJsonPath('pinned_messages.0.id', $pinned->id)
            ->assertJsonPath('pinned_messages.0.is_pinned', true);

        $this->assertNotContains($pinned->id, collect($response->json('messages'))->pluck('id'));
    }

    public function test_channel_messages_can_load_context_around_a_pinned_message(): void
    {
        config(['community.performance.message_page_size' => 5]);

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $channel = $this->createChannel($admin);
        $messages = collect(range(1, 9))->map(fn (int $index) => $this->createMessage(
            $channel,
            $member,
            "Message {$index}",
            now()->addMinutes($index),
            $index === 5,
        ));
        $target = $messages[4];

        $this->actingAs($admin)
            ->getJson("/community/channels/{$channel->slug}/messages?around_id={$target->id}")
            ->assertOk()
            ->assertJsonFragment([
                'id' => $target->id,
                'message' => 'Message 5',
                'is_pinned' => true,
            ])
            ->assertJsonPath('pinned_messages.0.id', $target->id);
    }

    public function test_community_messages_and_member_roster_include_profile_photo_urls(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/member.jpg', 'avatar');

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'role' => 'model',
            'profile_photo_path' => 'profile-photos/member.jpg',
        ]);
        $channel = $this->createChannel($admin);

        $this->createMessage($channel, $member, 'Photo payload check', now());

        $this->actingAs($member)
            ->getJson("/community/channels/{$channel->slug}/messages")
            ->assertOk()
            ->assertJsonPath('messages.0.user.profile_photo_url', $member->profilePhotoUrl())
            ->assertJsonPath('channel.id', $channel->id);

        $this->actingAs($member)
            ->getJson('/community/presence?summary=1')
            ->assertOk()
            ->assertJsonPath('members.online.0.profile_photo_url', $member->profilePhotoUrl());
    }

    private function createChannel(User $creator): CommunityChannel
    {
        return CommunityChannel::query()->create([
            'name' => 'general',
            'description' => 'General discussion',
            'created_by' => $creator->id,
            'is_private' => false,
            'access_mode' => CommunityChannel::ACCESS_MEMBERS,
            'denied_behavior' => CommunityChannel::DENIED_HIDDEN,
        ]);
    }

    private function createMessage(
        CommunityChannel $channel,
        User $user,
        string $body,
        \DateTimeInterface $createdAt,
        bool $pinned = false,
    ): CommunityMessage {
        $message = CommunityMessage::query()->create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => $body,
            'is_pinned' => $pinned,
        ]);

        $message->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $message;
    }
}
