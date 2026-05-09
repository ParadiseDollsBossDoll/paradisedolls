<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_channels', function (Blueprint $table) {
            $table->string('access_mode', 24)->default('members')->after('is_private');
            $table->string('denied_behavior', 24)->default('hidden')->after('access_mode');
            $table->json('allowed_roles')->nullable()->after('denied_behavior');
        });

        Schema::create('community_channel_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_channel_id')->constrained('community_channels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['community_channel_id', 'user_id'], 'community_channel_access_unique');
        });

        Schema::create('community_member_timeouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('community_channels')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 255)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'channel_id', 'expires_at'], 'community_member_timeouts_lookup_index');
        });

        Schema::create('community_moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('community_channels')->nullOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('community_messages')->nullOnDelete();
            $table->string('action', 80);
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'created_at'], 'community_moderation_logs_channel_created_index');
            $table->index(['target_user_id', 'created_at'], 'community_moderation_logs_target_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_moderation_logs');
        Schema::dropIfExists('community_member_timeouts');
        Schema::dropIfExists('community_channel_accesses');

        Schema::table('community_channels', function (Blueprint $table) {
            $table->dropColumn(['access_mode', 'denied_behavior', 'allowed_roles']);
        });
    }
};
