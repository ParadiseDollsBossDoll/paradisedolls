<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->foreignId('model_id')
                ->nullable()
                ->after('platform')
                ->constrained('users')
                ->nullOnDelete();
            $table->index(['model_id', 'clocked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->dropForeign(['model_id']);
            $table->dropIndex(['model_id', 'clocked_in_at']);
            $table->dropColumn('model_id');
        });
    }
};
