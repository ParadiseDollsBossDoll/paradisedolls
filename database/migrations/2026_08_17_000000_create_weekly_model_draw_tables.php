<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_model_draws', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('week_start')->index();
            $table->date('week_end')->index();
            $table->string('status', 32)->default('draft')->index();
            $table->unsignedInteger('qualification_threshold_cents')->nullable();
            $table->text('notes')->nullable();
            $table->string('recording_url', 2048)->nullable();
            $table->timestamp('drawn_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('winner_entry_id')->nullable();
            $table->unsignedBigInteger('winning_prize_id')->nullable();
            $table->timestamps();
            $table->unique(['week_start', 'title']);
        });

        Schema::create('weekly_model_draw_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_model_draw_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model_name');
            $table->string('model_email')->nullable();
            $table->unsignedInteger('earnings_cents')->default(0);
            $table->boolean('is_qualified')->default(false)->index();
            $table->text('qualification_note')->nullable();
            $table->timestamps();
            $table->index(['weekly_model_draw_id', 'is_qualified'], 'wmd_entries_draw_qualified_idx');
            $table->unique(['weekly_model_draw_id', 'user_id'], 'wmd_entries_draw_user_unique');
        });

        Schema::create('weekly_model_draw_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_model_draw_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('value_cents')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['weekly_model_draw_id', 'sort_order'], 'wmd_prizes_draw_order_idx');
        });

        Schema::table('weekly_model_draws', function (Blueprint $table) {
            $table->foreign('winner_entry_id')->references('id')->on('weekly_model_draw_entries')->nullOnDelete();
            $table->foreign('winning_prize_id')->references('id')->on('weekly_model_draw_prizes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('weekly_model_draws', function (Blueprint $table) {
            $table->dropForeign(['winner_entry_id']);
            $table->dropForeign(['winning_prize_id']);
        });

        Schema::dropIfExists('weekly_model_draw_prizes');
        Schema::dropIfExists('weekly_model_draw_entries');
        Schema::dropIfExists('weekly_model_draws');
    }
};
