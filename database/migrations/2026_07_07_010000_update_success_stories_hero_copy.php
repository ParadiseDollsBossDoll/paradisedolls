<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('site_settings')->where('key', 'marketing_content')->first();
        if (! $row) {
            return;
        }

        $content = json_decode((string) $row->value, true);
        if (! is_array($content)) {
            return;
        }

        data_set($content, 'success_stories.hero.eyebrow', 'PARADISE DOLLS COMMUNITY');
        data_set($content, 'success_stories.hero.title', 'Real Stories from Our Paradise Dolls 💎');
        data_set($content, 'success_stories.hero.body', implode("\n\n", [
            'Behind every success is a woman who had the courage to take the first step. Discover the inspiring journeys of our Paradise Dolls as they share how they’ve built confidence, embraced new opportunities, formed lifelong friendships, and transformed their lives with the support of a community that truly believes in their success.',
            'Every Paradise Doll’s journey is unique, but they all began with one decision to believe in themselves. Today, they’re inspiring others to do the same, proving that with the right support, mindset, and determination, incredible things are possible.',
            'At Paradise Dolls, we believe success is about more than reaching your goals. It’s about becoming part of a sisterhood that celebrates every milestone, lifts each other up, and grows stronger together. Here, you’ll find encouragement, friendship, inspiration, and a community that’s genuinely invested in seeing every Doll succeed.',
            'Your story starts with a single step… and this could be the beginning of your own success story. 💖✨',
        ]));

        DB::table('site_settings')
            ->where('key', 'marketing_content')
            ->update([
                'value' => json_encode($content, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Content edits are intentionally preserved when rolling back.
    }
};
