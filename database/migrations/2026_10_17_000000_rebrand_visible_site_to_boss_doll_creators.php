<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings')) {
            $row = DB::table('site_settings')->where('key', 'marketing_content')->first();

            if ($row) {
                $content = json_decode((string) $row->value, true);

                if (is_array($content)) {
                    DB::table('site_settings')
                        ->where('key', 'marketing_content')
                        ->update([
                            'value' => json_encode($this->replaceVisibleBranding($content), JSON_UNESCAPED_UNICODE),
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::table('site_settings')->updateOrInsert(
                ['key' => 'theme'],
                [
                    'value' => json_encode([
                        'preset' => 'boss-light',
                        'mode' => 'light',
                        'primary' => '#FF0080',
                        'primaryLight' => '#FFE6F0',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            Cache::forget('site_setting.marketing_content');
            Cache::forget('site_setting.theme');
        }

        $this->replaceBrandingInTable('courses', [
            'title', 'description', 'short_description', 'what_you_will_learn', 'requirements',
            'intro_title', 'intro_body', 'course_access_requirements', 'access_registration_instructions',
            'access_callback_instructions', 'access_onboarding_instructions', 'access_verification_instructions',
        ]);
        $this->replaceBrandingInTable('course_modules', ['title', 'description']);
        $this->replaceBrandingInTable('lessons', [
            'title', 'body', 'overview', 'steps', 'tips', 'safety_notes', 'bunny_video_title',
        ]);
        $this->replaceBrandingInTable('lesson_content_blocks', ['title', 'content', 'bunny_video_title']);
    }

    public function down(): void
    {
        // The retired brand and any admin-edited marketing copy are not restored automatically.
    }

    private function replaceVisibleBranding(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->replaceVisibleBranding($item), $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        return str_replace(
            ["Paradise Doll\u{2019}s", 'PARADISE DOLLS', 'Paradise Dolls', "Paradise Doll's", 'Paradise Doll'],
            ["Boss Doll Creator\u{2019}s", 'BOSS DOLL CREATORS', 'Boss Doll Creators', "Boss Doll Creator's", 'Boss Doll Creator'],
            $value,
        );
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function replaceBrandingInTable(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($columns === []) {
            return;
        }

        DB::table($table)
            ->select(array_merge(['id'], $columns))
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table, $columns): void {
                foreach ($rows as $row) {
                    $changes = [];

                    foreach ($columns as $column) {
                        $current = $row->{$column};
                        $updated = $this->replaceVisibleBranding($current);

                        if ($updated !== $current) {
                            $changes[$column] = $updated;
                        }
                    }

                    if ($changes !== []) {
                        DB::table($table)->where('id', $row->id)->update($changes);
                    }
                }
            });
    }
};
