<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('model_profiles', 'approved_platforms')) {
                $table->json('approved_platforms')->nullable()->after('current_platforms');
            }

            if (! Schema::hasColumn('model_profiles', 'work_status')) {
                $table->string('work_status')->default('not_active')->after('onboarding_stage')->index();
            }

            if (! Schema::hasColumn('model_profiles', 'work_status_updated_at')) {
                $table->timestamp('work_status_updated_at')->nullable()->after('work_status');
            }

            if (! Schema::hasColumn('model_profiles', 'work_status_note')) {
                $table->text('work_status_note')->nullable()->after('work_status_updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table): void {
            foreach (['approved_platforms', 'work_status_note', 'work_status_updated_at', 'work_status'] as $column) {
                if (Schema::hasColumn('model_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
