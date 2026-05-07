<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
            $table->index(['user_id', 'course_id']);
        });

        $now = now();

        DB::table('lesson_progress')
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->select('lessons.course_id', 'lesson_progress.user_id')
            ->distinct()
            ->orderBy('lessons.course_id')
            ->get()
            ->each(function ($row) use ($now): void {
                DB::table('course_enrollments')->insertOrIgnore([
                    'course_id' => $row->course_id,
                    'user_id' => $row->user_id,
                    'enrolled_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

        DB::table('course_chat_messages')
            ->select('course_id', 'user_id')
            ->distinct()
            ->orderBy('course_id')
            ->get()
            ->each(function ($row) use ($now): void {
                DB::table('course_enrollments')->insertOrIgnore([
                    'course_id' => $row->course_id,
                    'user_id' => $row->user_id,
                    'enrolled_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
