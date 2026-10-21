<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('site_settings')->where('key', 'marketing_content')->first();
        if (! $row) {
            return;
        }

        $content = json_decode((string) $row->value, true, 512, JSON_THROW_ON_ERROR);
        $photos = data_get($content, 'success_stories.review_photos');
        if (! is_array($photos) || $photos === []) {
            return;
        }

        $additions = [
            ['image' => 'images/reviews/ruby.jpeg', 'title' => 'Ruby - Model'],
            ['image' => 'images/reviews/precious.jpeg', 'title' => 'Precious - Chatter'],
            ['image' => 'images/reviews/jhon-paul.jpeg', 'title' => 'Jhon Paul - Chatter'],
            ['image' => 'images/reviews/alina.jpeg', 'title' => 'Alina - Model'],
        ];

        foreach ($additions as $photo) {
            if (! in_array($photo['image'], array_column($photos, 'image'), true)) {
                $photos[] = $photo;
            }
        }

        data_set($content, 'success_stories.review_photos', $photos);
        DB::table('site_settings')->where('key', 'marketing_content')->update([
            'value' => json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
        Cache::forget('site_setting.marketing_content');
    }

    public function down(): void
    {
        // Keep approved reviews and later Site Editor changes when rolling code back.
    }
};
