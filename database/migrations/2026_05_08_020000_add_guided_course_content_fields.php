<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'sort_order']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->text('thumbnail_url')->nullable()->after('description');
            $table->string('difficulty_level')->nullable()->after('thumbnail_url');
            $table->string('estimated_duration')->nullable()->after('difficulty_level');
            $table->text('what_you_will_learn')->nullable()->after('estimated_duration');
            $table->text('requirements')->nullable()->after('what_you_will_learn');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('course_module_id')->nullable()->after('course_id')->constrained('course_modules')->nullOnDelete();
            $table->text('overview')->nullable()->after('body');
            $table->text('steps')->nullable()->after('overview');
            $table->text('tips')->nullable()->after('steps');
            $table->text('safety_notes')->nullable()->after('tips');
            $table->text('resource_links')->nullable()->after('safety_notes');
            $table->boolean('is_published')->default(true)->after('resource_links');

            $table->index(['course_id', 'course_module_id']);
            $table->index(['course_id', 'is_published', 'sort_order']);
        });

        $now = now();

        DB::table('courses')
            ->select(['id'])
            ->orderBy('id')
            ->get()
            ->each(function ($course) use ($now): void {
                $moduleId = DB::table('course_modules')->insertGetId([
                    'course_id' => $course->id,
                    'title' => 'Core Training',
                    'description' => null,
                    'is_published' => true,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('lessons')
                    ->where('course_id', $course->id)
                    ->update([
                        'course_module_id' => $moduleId,
                        'is_published' => true,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['course_module_id']);
            $table->dropIndex(['course_id', 'course_module_id']);
            $table->dropIndex(['course_id', 'is_published', 'sort_order']);
            $table->dropColumn([
                'course_module_id',
                'overview',
                'steps',
                'tips',
                'safety_notes',
                'resource_links',
                'is_published',
            ]);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'thumbnail_url',
                'difficulty_level',
                'estimated_duration',
                'what_you_will_learn',
                'requirements',
            ]);
        });

        Schema::dropIfExists('course_modules');
    }
};
