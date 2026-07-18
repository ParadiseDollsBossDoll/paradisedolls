<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatter_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('timezone', 64)->default('Europe/London');
            $table->string('status', 32)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chatter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('timezone', 64)->default('Europe/London');
            $table->string('employment_status', 32)->default('active')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chatter_pay_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('base_rate_pence')->default(0);
            $table->unsignedInteger('overtime_threshold_minutes')->default(2400);
            $table->unsignedInteger('overtime_multiplier_bps')->default(15000);
            $table->unsignedInteger('night_premium_bps')->default(12000);
            $table->unsignedInteger('weekend_premium_bps')->default(15000);
            $table->time('night_starts_at')->default('22:00:00');
            $table->time('night_ends_at')->default('06:00:00');
            $table->date('effective_from');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'effective_from']);
            $table->index(['user_id', 'effective_from']);
        });

        Schema::create('chatter_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('active_user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->timestamp('clocked_in_at')->index();
            $table->timestamp('clocked_out_at')->nullable()->index();
            $table->string('timezone', 64)->default('Europe/London');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'clocked_in_at']);
        });

        Schema::create('chatter_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatter_shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('active_shift_id')->nullable()->unique()->constrained('chatter_shifts')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['chatter_shift_id', 'started_at']);
        });

        Schema::create('chatter_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 32)->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->unsignedInteger('ordinary_minutes')->default(0);
            $table->unsignedInteger('break_minutes')->default(0);
            $table->unsignedInteger('night_minutes')->default(0);
            $table->unsignedInteger('weekend_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->integer('adjustment_pence')->default(0);
            $table->integer('gross_pay_pence')->default(0);
            $table->json('calculation_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'period_start']);
            $table->index(['period_start', 'status']);
        });

        Schema::create('chatter_pay_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatter_timesheet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('amount_pence');
            $table->string('label');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('chatter_time_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatter_shift_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('chatter_timesheet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64)->index();
            $table->text('reason')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatter_time_audits');
        Schema::dropIfExists('chatter_pay_adjustments');
        Schema::dropIfExists('chatter_timesheets');
        Schema::dropIfExists('chatter_breaks');
        Schema::dropIfExists('chatter_shifts');
        Schema::dropIfExists('chatter_pay_rates');
        Schema::dropIfExists('chatter_profiles');
        Schema::dropIfExists('chatter_requests');
    }
};
