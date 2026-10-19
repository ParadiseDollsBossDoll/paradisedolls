<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_sidebar_visits', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('section', 40);
            $table->timestamp('seen_at', 6);
            $table->primary(['user_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_sidebar_visits');
    }
};
