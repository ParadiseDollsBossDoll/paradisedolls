<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatter_timesheets', function (Blueprint $table) {
            $table->integer('foreign_commission_usd_pence')->default(0)->after('foreign_commission_pence');
            $table->decimal('gbp_to_usd_rate', 12, 6)->nullable()->after('foreign_commission_usd_pence');
            $table->date('gbp_to_usd_rate_date')->nullable()->after('gbp_to_usd_rate');
            $table->timestamp('gbp_to_usd_rate_fetched_at')->nullable()->after('gbp_to_usd_rate_date');
            $table->string('gbp_to_usd_rate_provider')->nullable()->after('gbp_to_usd_rate_fetched_at');
        });
    }

    public function down(): void
    {
        Schema::table('chatter_timesheets', function (Blueprint $table) {
            $table->dropColumn([
                'foreign_commission_usd_pence',
                'gbp_to_usd_rate',
                'gbp_to_usd_rate_date',
                'gbp_to_usd_rate_fetched_at',
                'gbp_to_usd_rate_provider',
            ]);
        });
    }
};
