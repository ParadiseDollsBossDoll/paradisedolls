<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->string('discord_username')->nullable()->after('emergency_contact_phone');
            $table->string('discord_user_id')->nullable()->after('discord_username');
            $table->timestamp('community_role_assigned_at')->nullable()->after('community_invited_at');
        });
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table) {
            $table->dropColumn(['discord_username', 'discord_user_id', 'community_role_assigned_at']);
        });
    }
};
