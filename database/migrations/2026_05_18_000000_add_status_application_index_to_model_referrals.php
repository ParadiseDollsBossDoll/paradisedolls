<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_referrals', function (Blueprint $table) {
            $table->index(
                ['status', 'model_application_id'],
                'model_referrals_status_application_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('model_referrals', function (Blueprint $table) {
            $table->dropIndex('model_referrals_status_application_index');
        });
    }
};
