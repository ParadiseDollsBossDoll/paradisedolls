<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_content_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('block_type', 40);
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('image_path')->nullable();
            $table->string('file_path')->nullable();
            $table->string('bunny_video_id', 64)->nullable();
            $table->string('bunny_library_id', 64)->nullable();
            $table->string('bunny_video_title')->nullable();
            $table->string('bunny_thumbnail_url', 2000)->nullable();
            $table->string('bunny_upload_fingerprint')->nullable();
            $table->unsignedTinyInteger('bunny_status')->nullable();
            $table->string('duration', 64)->nullable();
            $table->text('presentation_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['lesson_id', 'sort_order']);
            $table->index('block_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_content_blocks');
    }
};
