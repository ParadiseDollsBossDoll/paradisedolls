<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_access_request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_access_request_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('disk', 32)->default('local');
            $table->text('path');
            $table->string('original_name');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();

            $table->index('course_access_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_access_request_files');
    }
};
