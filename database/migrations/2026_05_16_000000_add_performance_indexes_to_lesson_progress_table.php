<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            // Speeds up admin dashboard stats: whereIn('lesson_id', [...]) + whereNotNull('completed_at')
            // The existing unique(user_id, lesson_id) index cannot serve queries that start with lesson_id.
            $table->index(['lesson_id', 'completed_at'], 'lesson_progress_lesson_completed_idx');

            // Speeds up member progress queries: where('user_id', $id) + whereNotNull('completed_at')
            // The existing unique(user_id, lesson_id) index only covers user_id+lesson_id lookups,
            // not user_id+completed_at filtering.
            $table->index(['user_id', 'completed_at'], 'lesson_progress_user_completed_idx');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            $table->dropIndex('lesson_progress_lesson_completed_idx');
            $table->dropIndex('lesson_progress_user_completed_idx');
        });
    }
};
