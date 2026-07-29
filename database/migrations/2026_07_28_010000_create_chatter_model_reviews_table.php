<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatter_model_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chatter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('users')->cascadeOnDelete();
            $table->date('week_ending');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedTinyInteger('overall_rating');
            $table->text('overall_rating_explanation');
            $table->string('energy_motivation');
            $table->text('energy_comments')->nullable();
            $table->string('effort_towards_earnings');
            $table->text('effort_comments')->nullable();
            $table->string('communication_teamwork');
            $table->text('communication_comments')->nullable();
            $table->string('followed_guidance');
            $table->text('guidance_examples')->nullable();
            $table->text('went_well');
            $table->text('could_improve');
            $table->boolean('shift_issues')->default(false);
            $table->text('shift_issues_explanation')->nullable();
            $table->boolean('security_concerns')->default(false);
            $table->text('security_concerns_explanation')->nullable();
            $table->text('authorised_actions')->nullable();
            $table->text('performance_strategies')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->text('coaching_recommendations')->nullable();
            $table->boolean('recognition')->default(false);
            $table->text('recognition_reason')->nullable();
            $table->text('additional_comments')->nullable();
            $table->boolean('declaration_accepted')->default(false);
            $table->string('signature');
            $table->timestamps();

            $table->unique(['chatter_id', 'model_id', 'week_ending'], 'chatter_model_reviews_week_unique');
            $table->index(['week_ending', 'created_at']);
            $table->index(['model_id', 'week_ending']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatter_model_reviews');
    }
};
