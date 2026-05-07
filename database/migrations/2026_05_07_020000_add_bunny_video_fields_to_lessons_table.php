<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('bunny_video_id')->nullable()->after('video_url');
            $table->string('bunny_library_id')->nullable()->after('bunny_video_id');
            $table->string('bunny_video_title')->nullable()->after('bunny_library_id');
            $table->text('bunny_thumbnail_url')->nullable()->after('bunny_video_title');
            $table->string('bunny_upload_fingerprint')->nullable()->after('bunny_thumbnail_url');
            $table->unsignedTinyInteger('bunny_status')->nullable()->after('bunny_upload_fingerprint');

            $table->index('bunny_video_id');
            $table->index('bunny_upload_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['bunny_video_id']);
            $table->dropIndex(['bunny_upload_fingerprint']);
            $table->dropColumn([
                'bunny_video_id',
                'bunny_library_id',
                'bunny_video_title',
                'bunny_thumbnail_url',
                'bunny_upload_fingerprint',
                'bunny_status',
            ]);
        });
    }
};
