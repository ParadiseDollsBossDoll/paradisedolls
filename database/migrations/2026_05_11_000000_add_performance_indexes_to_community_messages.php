<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_messages', function (Blueprint $table) {
            // Faster "latest message by this user in channel" look-up (slowmode check)
            if (! $this->indexExists('community_messages', 'community_messages_channel_user_latest_index')) {
                $table->index(['channel_id', 'user_id', 'created_at'], 'community_messages_channel_user_latest_index');
            }
        });

        // Full-text index for message search (only if engine supports it)
        // MariaDB / MySQL InnoDB both support FULLTEXT on text columns.
        if (! $this->fulltextExists('community_messages', 'community_messages_message_fulltext')) {
            DB::statement('ALTER TABLE community_messages ADD FULLTEXT INDEX community_messages_message_fulltext (message)');
        }

        // Faster "all channels a user has access to" look-up
        Schema::table('community_channel_accesses', function (Blueprint $table) {
            if (! $this->indexExists('community_channel_accesses', 'community_channel_accesses_user_id_index')) {
                $table->index('user_id', 'community_channel_accesses_user_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_messages', function (Blueprint $table) {
            $table->dropIndexIfExists('community_messages_channel_user_latest_index');
        });

        DB::statement('ALTER TABLE community_messages DROP INDEX IF EXISTS community_messages_message_fulltext');

        Schema::table('community_channel_accesses', function (Blueprint $table) {
            $table->dropIndexIfExists('community_channel_accesses_user_id_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains(fn ($row) => $row->Key_name === $index);
    }

    private function fulltextExists(string $table, string $index): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains(fn ($row) => $row->Key_name === $index && $row->Index_type === 'FULLTEXT');
    }
};
