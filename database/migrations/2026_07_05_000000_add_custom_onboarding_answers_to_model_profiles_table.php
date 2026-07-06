<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->json('custom_onboarding_answers')->nullable()->after('anything_else');
            $table->string('onboarding_form_version', 32)->nullable()->after('custom_onboarding_answers');
        });
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropColumn(['custom_onboarding_answers', 'onboarding_form_version']);
        });
    }
};
