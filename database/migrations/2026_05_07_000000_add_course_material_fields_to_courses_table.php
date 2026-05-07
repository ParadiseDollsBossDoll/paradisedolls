<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('has_course_outline')->default(false)->after('description');
            $table->string('course_outline_url')->nullable()->after('has_course_outline');
            $table->boolean('has_intro')->default(false)->after('course_outline_url');
            $table->string('intro_title')->nullable()->after('has_intro');
            $table->string('intro_video_url')->nullable()->after('intro_title');
            $table->string('intro_duration')->nullable()->after('intro_video_url');
            $table->text('intro_body')->nullable()->after('intro_duration');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'has_course_outline',
                'course_outline_url',
                'has_intro',
                'intro_title',
                'intro_video_url',
                'intro_duration',
                'intro_body',
            ]);
        });
    }
};
