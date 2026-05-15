<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 32)->nullable()->unique()->after('profile_photo_path');
        });

        DB::table('users')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'referral_code' => 'PD'.strtoupper(base_convert((int) $user->id, 10, 36)).Str::upper(Str::random(4)),
                        ]);
                }
            });

        Schema::create('model_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('model_application_id')->nullable()->unique()->constrained('model_applications')->nullOnDelete();
            $table->string('candidate_name');
            $table->string('candidate_email')->nullable();
            $table->string('candidate_phone')->nullable();
            $table->string('candidate_social_handle')->nullable();
            $table->string('experience_level', 64)->nullable();
            $table->text('note')->nullable();
            $table->json('photo_paths')->nullable();
            $table->boolean('consent_confirmed')->default(false);
            $table->string('source', 32)->default('member_form')->index();
            $table->string('status', 32)->default('referred')->index();
            $table->string('reward_status', 32)->default('not_eligible')->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('reward_marked_paid_at')->nullable();
            $table->foreignId('reward_marked_paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['referrer_id', 'status']);
            $table->index(['candidate_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_referrals');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['referral_code']);
            $table->dropColumn('referral_code');
        });
    }
};
