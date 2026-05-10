<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_content_blocks', function (Blueprint $table): void {
            $table->json('settings')->nullable()->after('presentation_url');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_content_blocks', function (Blueprint $table): void {
            $table->dropColumn('settings');
        });
    }
};
