<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatter_performance_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chatter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_anonymous')->default(true);
            $table->string('model_name')->nullable();
            $table->string('chatter_name')->nullable();
            $table->date('review_date')->nullable();
            $table->string('review_period')->nullable();

            $table->unsignedTinyInteger('communication_rating');
            $table->unsignedTinyInteger('professionalism_rating');
            $table->unsignedTinyInteger('friendliness_rating');
            $table->unsignedTinyInteger('response_time_rating');
            $table->unsignedTinyInteger('reliability_rating');
            $table->unsignedTinyInteger('customer_engagement_rating');
            $table->unsignedTinyInteger('sales_encouragement_rating');
            $table->unsignedTinyInteger('support_motivation_rating');
            $table->unsignedTinyInteger('boundary_understanding_rating');
            $table->unsignedTinyInteger('overall_performance_rating');

            $table->string('sounds_like_model');
            $table->string('understands_personality');
            $table->string('respects_boundaries');
            $table->string('performance_feeling');
            $table->string('wants_to_help');
            $table->string('confidence_improved');
            $table->string('customer_engagement_improved');
            $table->string('continue_working');

            $table->text('does_well')->nullable();
            $table->text('could_improve')->nullable();
            $table->text('missed_opportunities')->nullable();
            $table->text('natural_speech')->nullable();
            $table->string('uncomfortable_incident');
            $table->text('uncomfortable_explanation')->nullable();
            $table->text('one_change')->nullable();
            $table->text('recognition')->nullable();
            $table->text('anything_else')->nullable();

            $table->string('overall_satisfaction');
            $table->string('would_recommend');
            $table->boolean('contact_requested')->default(false);

            $table->decimal('average_score', 3, 2);
            $table->string('status')->default('submitted');
            $table->string('management_performance')->nullable();
            $table->string('model_wishes_to_remain')->nullable();
            $table->boolean('additional_training_required')->nullable();
            $table->text('manager_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['chatter_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatter_performance_reviews');
    }
};
