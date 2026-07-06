<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const WORK_INTEREST_RENAMES = [
        'Freemium Webcam' => 'Freemium Streaming',
        'Webcam Premium Shows' => 'Premium Streaming',
        'OnlyFans Content' => 'Fan Subscription Platforms',
    ];

    public function up(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->text('payout_account_name')->nullable()->after('payout_country');
            $table->text('payout_bank_name')->nullable()->after('payout_account_name');
            $table->text('payout_sort_code')->nullable()->after('payout_bank_name');
            $table->text('payout_account_number')->nullable()->after('payout_sort_code');
            $table->text('payout_iban')->nullable()->after('payout_account_number');
        });

        $this->updateSavedProfiles();
        $this->updateSavedFormDefinition();

        DB::table('model_profiles')
            ->whereIn('community_invite_url', [
                'https://discord.gg/zfe9HqX',
                'https://discord.gg/zfe9HqXq',
            ])
            ->update(['community_invite_url' => 'https://discord.gg/GvKNFmeRm']);
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'payout_account_name',
                'payout_bank_name',
                'payout_sort_code',
                'payout_account_number',
                'payout_iban',
            ]);
        });
    }

    private function updateSavedProfiles(): void
    {
        DB::table('model_profiles')
            ->select(['id', 'work_interests', 'fetishes_checklist'])
            ->orderBy('id')
            ->each(function (object $profile): void {
                $updates = [];
                $workInterests = $this->decodeJson($profile->work_interests);

                if ($workInterests !== []) {
                    $updates['work_interests'] = json_encode($this->renameWorkInterests($workInterests));
                }

                $fetishes = $this->decodeJson($profile->fetishes_checklist);
                if (array_key_exists('Twerking / Ass Play', $fetishes)) {
                    $fetishes['Twerking'] = $fetishes['Twerking / Ass Play'];
                    unset($fetishes['Twerking / Ass Play']);
                    $updates['fetishes_checklist'] = json_encode($fetishes);
                }

                if ($updates !== []) {
                    DB::table('model_profiles')->where('id', $profile->id)->update($updates);
                }
            });
    }

    private function updateSavedFormDefinition(): void
    {
        $row = DB::table('site_settings')->where('key', 'member_onboarding_form')->first();
        if (! $row) {
            return;
        }

        $definition = $this->decodeJson($row->value);
        if ($definition === []) {
            return;
        }

        $camOptions = data_get($definition, 'option_groups.platforms_cam.options', []);
        if (is_array($camOptions) && ! in_array('XXPANDER', $camOptions, true)) {
            $camOptions[] = 'XXPANDER';
            data_set($definition, 'option_groups.platforms_cam.options', array_values($camOptions));
        }

        foreach (['options', 'archived'] as $key) {
            $workOptions = data_get($definition, "option_groups.work_interests.{$key}", []);
            if (is_array($workOptions)) {
                data_set($definition, "option_groups.work_interests.{$key}", $this->renameWorkInterests($workOptions));
            }
        }

        foreach (($definition['fetish_sections'] ?? []) as $index => $section) {
            if (! is_array($section) || ($section['id'] ?? '') !== 'bodily_sensation') {
                continue;
            }

            foreach (['items', 'archived_items'] as $key) {
                $items = is_array($section[$key] ?? null) ? $section[$key] : [];
                $definition['fetish_sections'][$index][$key] = array_values(array_unique(array_map(
                    fn (string $item) => $item === 'Twerking / Ass Play' ? 'Twerking' : $item,
                    $items,
                )));
            }
        }

        $definition['version'] = now()->format('YmdHis');

        DB::table('site_settings')
            ->where('key', 'member_onboarding_form')
            ->update([
                'value' => json_encode($definition),
                'updated_at' => now(),
            ]);
    }

    private function renameWorkInterests(array $values): array
    {
        $renamed = array_map(
            fn (string $value) => self::WORK_INTEREST_RENAMES[$value] ?? $value,
            array_values(array_filter($values, 'is_string')),
        );

        $priority = ['Freemium Streaming', 'Premium Streaming', 'Fan Subscription Platforms', 'All Types'];

        return array_values(array_unique([
            ...array_values(array_intersect($priority, $renamed)),
            ...array_values(array_diff($renamed, $priority)),
        ]));
    }

    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
};
