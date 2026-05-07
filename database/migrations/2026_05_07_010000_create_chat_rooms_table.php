<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('courses')
            ->select(['id', 'title'])
            ->orderBy('id')
            ->get()
            ->each(function ($course) use ($now): void {
                DB::table('chat_rooms')->insert([
                    'course_id' => $course->id,
                    'name' => $course->title.' Community',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};
