<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_channels', function (Blueprint $table) {
            $table->index(['is_archived', 'order', 'name'], 'community_channels_active_order_index');
            $table->index('last_message_at', 'community_channels_last_message_index');
        });

        Schema::table('community_messages', function (Blueprint $table) {
            $table->index(['channel_id', 'created_at'], 'community_messages_channel_created_index');
            $table->index(['channel_id', 'user_id', 'created_at'], 'community_messages_channel_user_created_index');
            $table->index(['reply_to'], 'community_messages_reply_to_index');
            $table->index(['deleted_at'], 'community_messages_deleted_at_index');
        });

        Schema::table('message_reactions', function (Blueprint $table) {
            $table->index(['message_id', 'emoji'], 'message_reactions_message_emoji_index');
        });

        Schema::table('community_message_reads', function (Blueprint $table) {
            $table->index(['user_id', 'message_id'], 'community_message_reads_user_message_index');
            $table->index(['message_id', 'read_at'], 'community_message_reads_message_read_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('community_message_reads', function (Blueprint $table) {
            $table->dropIndex('community_message_reads_user_message_index');
            $table->dropIndex('community_message_reads_message_read_at_index');
        });

        Schema::table('message_reactions', function (Blueprint $table) {
            $table->dropIndex('message_reactions_message_emoji_index');
        });

        Schema::table('community_messages', function (Blueprint $table) {
            $table->dropIndex('community_messages_channel_created_index');
            $table->dropIndex('community_messages_channel_user_created_index');
            $table->dropIndex('community_messages_reply_to_index');
            $table->dropIndex('community_messages_deleted_at_index');
        });

        Schema::table('community_channels', function (Blueprint $table) {
            $table->dropIndex('community_channels_active_order_index');
            $table->dropIndex('community_channels_last_message_index');
        });
    }
};
