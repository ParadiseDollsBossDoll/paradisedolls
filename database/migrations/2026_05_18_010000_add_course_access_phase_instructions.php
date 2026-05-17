<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->text('access_registration_instructions')->nullable()->after('course_access_requirements');
            $table->text('access_callback_instructions')->nullable()->after('access_registration_instructions');
            $table->text('access_onboarding_instructions')->nullable()->after('access_callback_instructions');
            $table->text('access_verification_instructions')->nullable()->after('access_onboarding_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'access_registration_instructions',
                'access_callback_instructions',
                'access_onboarding_instructions',
                'access_verification_instructions',
            ]);
        });
    }
};
