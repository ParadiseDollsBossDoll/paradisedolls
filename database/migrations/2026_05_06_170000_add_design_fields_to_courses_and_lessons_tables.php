<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('platform_color', 32)->nullable();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('duration', 64)->nullable();
            $table->boolean('has_pdf')->default(false);
            $table->text('pdf_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['duration', 'has_pdf', 'pdf_url']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('platform_color');
        });
    }
};
