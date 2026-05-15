<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_messages', function (Blueprint $table) {
            $table->index(
                ['channel_id', 'is_pinned', 'created_at'],
                'community_messages_channel_pinned_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('community_messages', function (Blueprint $table) {
            $table->dropIndex('community_messages_channel_pinned_index');
        });
    }
};
