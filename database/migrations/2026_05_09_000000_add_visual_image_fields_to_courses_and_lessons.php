<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'course_cover_image')) {
                $table->text('course_cover_image')->nullable()->after('thumbnail_url');
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'lesson_banner_image')) {
                $table->text('lesson_banner_image')->nullable()->after('resource_links');
            }

            if (! Schema::hasColumn('lessons', 'lesson_images')) {
                $table->json('lesson_images')->nullable()->after('lesson_banner_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'lesson_images')) {
                $table->dropColumn('lesson_images');
            }

            if (Schema::hasColumn('lessons', 'lesson_banner_image')) {
                $table->dropColumn('lesson_banner_image');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'course_cover_image')) {
                $table->dropColumn('course_cover_image');
            }
        });
    }
};
