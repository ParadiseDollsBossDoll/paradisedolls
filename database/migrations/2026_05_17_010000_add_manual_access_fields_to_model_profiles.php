<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->string('onboarding_stage', 32)->default('registration')->after('discord_user_id')->index();
            $table->text('verification_request_instructions')->nullable()->after('verification_notes');
        });
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropColumn(['onboarding_stage', 'verification_request_instructions']);
        });
    }
};
