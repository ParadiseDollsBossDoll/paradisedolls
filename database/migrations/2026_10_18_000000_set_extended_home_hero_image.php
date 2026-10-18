<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $row = DB::table('site_settings')->where('key', 'marketing_content')->lockForUpdate()->first();

            if (! $row) {
                return;
            }

            $content = json_decode($row->value, false, 512, JSON_THROW_ON_ERROR);

            if (! is_object($content)) {
                throw new RuntimeException('Marketing content must be a JSON object.');
            }

            $content->home ??= new stdClass;
            $content->home->hero ??= new stdClass;
            $content->home->hero->image = 'images/home/boss-doll-lounge-extended.png';

            DB::table('site_settings')->where('key', 'marketing_content')->update([
                'value' => json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
            ]);
        });

        Cache::forget('site_setting.marketing_content');
    }

    public function down(): void
    {
        // Keep the current image rather than undoing a later administrator edit.
    }
};
