<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'marketing_unsubscribed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('marketing_unsubscribed_at')->nullable()->after('last_login_at');
            });
        }

        if (! Schema::hasTable('email_campaigns')) {
            Schema::create('email_campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('subject');
                $table->longText('body');
                $table->string('action_label')->nullable();
                $table->text('action_url')->nullable();
                $table->string('audience', 32)->default('all_models');
                $table->string('status', 32)->default('draft');
                $table->timestamp('next_send_at')->nullable();
                $table->unsignedSmallInteger('repeat_every_days')->nullable();
                $table->timestamp('last_sent_at')->nullable();
                $table->unsignedInteger('total_runs')->default(0);
                $table->timestamps();

                $table->index(['status', 'next_send_at']);
            });
        }

        if (! Schema::hasTable('email_campaign_runs')) {
            Schema::create('email_campaign_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_campaign_id')->constrained()->cascadeOnDelete();
                $table->string('status', 32)->default('processing');
                $table->string('subject');
                $table->longText('body');
                $table->string('action_label')->nullable();
                $table->text('action_url')->nullable();
                $table->timestamp('scheduled_for')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('recipient_count')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->unsignedInteger('skipped_count')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('email_campaign_deliveries')) {
            Schema::create('email_campaign_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_campaign_run_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('recipient_name');
                $table->string('email');
                $table->string('status', 32)->default('pending');
                $table->timestamp('sent_at')->nullable();
                $table->text('failure_message')->nullable();
                $table->timestamps();

                $table->unique(['email_campaign_run_id', 'user_id']);
                $table->index(['email_campaign_run_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_deliveries');
        Schema::dropIfExists('email_campaign_runs');
        Schema::dropIfExists('email_campaigns');

        if (Schema::hasColumn('users', 'marketing_unsubscribed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('marketing_unsubscribed_at');
            });
        }
    }
};
