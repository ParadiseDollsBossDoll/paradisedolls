<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_channels', function (Blueprint $table): void {
            $table->foreignId('course_id')
                ->nullable()
                ->after('last_message_at')
                ->constrained('courses')
                ->nullOnDelete();

            $table->string('course_name')->nullable()->after('course_id');

            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('community_channels', function (Blueprint $table): void {
            $table->dropForeign(['course_id']);
            $table->dropIndex(['course_id']);
            $table->dropColumn(['course_id', 'course_name']);
        });
    }
};
