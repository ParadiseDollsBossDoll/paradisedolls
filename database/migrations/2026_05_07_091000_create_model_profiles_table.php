<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('model_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('legal_name')->nullable();
            $table->string('stage_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->nullable();
            $table->json('platforms')->nullable();
            $table->json('equipment')->nullable();
            $table->text('availability')->nullable();
            $table->text('goals')->nullable();
            $table->text('experience_notes')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->timestamp('information_submitted_at')->nullable();
            $table->string('verification_status', 32)->default('not_requested')->index();
            $table->text('id_document_path')->nullable();
            $table->text('selfie_with_id_path')->nullable();
            $table->text('platform_codes_path')->nullable();
            $table->timestamp('verification_submitted_at')->nullable();
            $table->foreignId('verification_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verification_reviewed_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamp('community_invited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_profiles');
    }
};
