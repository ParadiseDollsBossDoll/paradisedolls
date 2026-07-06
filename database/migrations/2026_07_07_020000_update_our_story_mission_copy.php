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

        $body = data_get($content, 'our_story.mission.body');
        if (is_array($body)) {
            $body = array_map(fn (mixed $paragraph) => is_string($paragraph)
                ? str_replace('With me guiding you', 'With me and my team guiding you', $paragraph)
                : $paragraph, $body);
        } elseif (is_string($body)) {
            $body = str_replace('With me guiding you', 'With me and my team guiding you', $body);
        } else {
            return;
        }

        data_set($content, 'our_story.mission.body', $body);

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
