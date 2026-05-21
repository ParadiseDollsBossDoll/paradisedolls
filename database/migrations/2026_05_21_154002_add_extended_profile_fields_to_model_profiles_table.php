<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            // Basic Info extras
            $table->string('nationality')->nullable()->after('country');
            $table->string('spoken_languages')->nullable()->after('nationality');
            $table->string('social_handles')->nullable()->after('spoken_languages');
            $table->string('with_other_agency')->nullable()->after('social_handles');
            $table->string('hear_about_us')->nullable()->after('with_other_agency');

            // Appearance & Style
            $table->string('height')->nullable()->after('hear_about_us');
            $table->string('weight')->nullable()->after('height');
            $table->string('hair_color')->nullable()->after('weight');
            $table->string('eye_color')->nullable()->after('hair_color');
            $table->string('body_type')->nullable()->after('eye_color');
            $table->text('has_tattoos_piercings')->nullable()->after('body_type');

            // Work Preferences
            $table->json('work_interests')->nullable()->after('has_tattoos_piercings');
            $table->json('comfort_levels')->nullable()->after('work_interests');
            $table->string('custom_content_ok')->nullable()->after('comfort_levels');
            $table->string('worn_items_ok')->nullable()->after('custom_content_ok');

            // Availability
            $table->string('weekly_availability')->nullable()->after('worn_items_ok');
            $table->string('availability_preference')->nullable()->after('weekly_availability');
            $table->string('has_private_space')->nullable()->after('availability_preference');

            // Payout Info
            $table->json('payout_methods')->nullable()->after('has_private_space');
            $table->string('payout_method_other')->nullable()->after('payout_methods');
            $table->string('payout_country')->nullable()->after('payout_method_other');

            // Extra Details
            $table->text('model_vibe')->nullable()->after('payout_country');
            $table->text('anything_else')->nullable()->after('model_vibe');
        });
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'nationality', 'spoken_languages', 'social_handles', 'with_other_agency', 'hear_about_us',
                'height', 'weight', 'hair_color', 'eye_color', 'body_type', 'has_tattoos_piercings',
                'work_interests', 'comfort_levels', 'custom_content_ok', 'worn_items_ok',
                'weekly_availability', 'availability_preference', 'has_private_space',
                'payout_methods', 'payout_method_other', 'payout_country',
                'model_vibe', 'anything_else',
            ]);
        });
    }
};
