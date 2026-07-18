<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('model_profiles', 'manual_fully_onboarded_at')) {
            Schema::table('model_profiles', function (Blueprint $table): void {
                $table->timestamp('manual_fully_onboarded_at')->nullable()->after('community_role_assigned_at');
            });
        }

        if (! Schema::hasColumn('model_profiles', 'manual_fully_onboarded_by')) {
            Schema::table('model_profiles', function (Blueprint $table): void {
                $table->foreignId('manual_fully_onboarded_by')
                    ->nullable()
                    ->after('manual_fully_onboarded_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('model_profiles', 'manual_fully_onboarded_note')) {
            Schema::table('model_profiles', function (Blueprint $table): void {
                $table->text('manual_fully_onboarded_note')->nullable()->after('manual_fully_onboarded_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('model_profiles', function (Blueprint $table): void {
            if (Schema::hasColumn('model_profiles', 'manual_fully_onboarded_by')) {
                $table->dropConstrainedForeignId('manual_fully_onboarded_by');
            }

            if (Schema::hasColumn('model_profiles', 'manual_fully_onboarded_note')) {
                $table->dropColumn('manual_fully_onboarded_note');
            }

            if (Schema::hasColumn('model_profiles', 'manual_fully_onboarded_at')) {
                $table->dropColumn('manual_fully_onboarded_at');
            }
        });
    }
};
