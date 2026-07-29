<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatter_model_reviews', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->index(['reviewed_at', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('chatter_model_reviews', function (Blueprint $table): void {
            $table->dropIndex(['reviewed_at', 'submitted_at']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('reviewed_at');
        });
    }
};
