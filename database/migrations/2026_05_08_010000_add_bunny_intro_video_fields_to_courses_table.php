<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('intro_bunny_video_id')->nullable()->after('intro_video_url');
            $table->string('intro_bunny_library_id')->nullable()->after('intro_bunny_video_id');
            $table->string('intro_bunny_video_title')->nullable()->after('intro_bunny_library_id');
            $table->text('intro_bunny_thumbnail_url')->nullable()->after('intro_bunny_video_title');
            $table->string('intro_bunny_upload_fingerprint')->nullable()->after('intro_bunny_thumbnail_url');
            $table->unsignedTinyInteger('intro_bunny_status')->nullable()->after('intro_bunny_upload_fingerprint');

            $table->index('intro_bunny_video_id');
            $table->index('intro_bunny_upload_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['intro_bunny_video_id']);
            $table->dropIndex(['intro_bunny_upload_fingerprint']);
            $table->dropColumn([
                'intro_bunny_video_id',
                'intro_bunny_library_id',
                'intro_bunny_video_title',
                'intro_bunny_thumbnail_url',
                'intro_bunny_upload_fingerprint',
                'intro_bunny_status',
            ]);
        });
    }
};
