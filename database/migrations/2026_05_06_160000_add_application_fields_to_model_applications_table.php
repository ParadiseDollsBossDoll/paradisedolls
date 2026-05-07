<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_applications', function (Blueprint $table) {
            $table->string('experience_level')->nullable()->after('message');
            $table->string('social_handle')->nullable()->after('experience_level');
            $table->boolean('age_confirmed')->default(false)->after('social_handle');
        });
    }

    public function down(): void
    {
        Schema::table('model_applications', function (Blueprint $table) {
            $table->dropColumn(['experience_level', 'social_handle', 'age_confirmed']);
        });
    }
};
