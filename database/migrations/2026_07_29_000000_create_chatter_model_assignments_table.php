<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatter_model_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['chatter_id', 'ended_at']);
            $table->index(['model_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatter_model_assignments');
    }
};
