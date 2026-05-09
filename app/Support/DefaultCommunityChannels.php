<?php

namespace App\Support;

class DefaultCommunityChannels
{
    public static function definitions(): array
    {
        return [
            [
                'name' => 'general',
                'description' => 'General community chat and discussion.',
                'category' => 'Channels',
                'access_mode' => 'members',
                'denied_behavior' => 'hidden',
            ],
            [
                'name' => 'announcements',
                'description' => 'Important updates, launches, and notices.',
                'category' => 'Channels',
                'is_locked' => true,
                'access_mode' => 'members',
                'denied_behavior' => 'hidden',
            ],
            [
                'name' => 'support-questions',
                'description' => 'Ask for platform help, workflow advice, or troubleshooting.',
                'category' => 'Channels',
                'access_mode' => 'members',
                'denied_behavior' => 'hidden',
            ],
            [
                'name' => 'new-member-intros',
                'description' => 'Introduce yourself and say hello to the community.',
                'category' => 'Channels',
                'access_mode' => 'members',
                'denied_behavior' => 'hidden',
            ],
            [
                'name' => 'wins',
                'description' => 'Share your progress, breakthroughs, and celebrations.',
                'category' => 'Channels',
                'access_mode' => 'members',
                'denied_behavior' => 'hidden',
            ],
            [
                'name' => 'resources',
                'description' => 'Links, PDFs, and helpful materials.',
                'category' => 'Channels',
                'is_locked' => true,
                'access_mode' => 'members',
                'denied_behavior' => 'hidden',
            ],
            [
                'name' => 'off-topic',
                'description' => 'Casual conversation beyond training and streaming.',
                'category' => 'Channels',
                'access_mode' => 'members',
                'denied_behavior' => 'hidden',
            ],
        ];
    }
}
