<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->string('earning_platform_key', 40)->nullable()->after('model_id');
            $table->string('earning_unit', 20)->nullable()->after('earning_platform_key');
            $table->char('earning_currency', 3)->nullable()->after('earning_unit');
            $table->unsignedBigInteger('earning_unit_value_usd_micro')->nullable()->after('earning_currency');
            $table->unsignedBigInteger('clock_in_earning_balance_minor')->nullable()->after('earning_unit_value_usd_micro');
            $table->unsignedBigInteger('clock_out_earning_balance_minor')->nullable()->after('clock_in_earning_balance_minor');
            $table->integer('generated_earning_units')->nullable()->after('clock_out_earning_balance_minor');
            $table->integer('generated_earning_pence')->default(0)->after('generated_earning_units');
            $table->unsignedSmallInteger('commission_bps')->default(300)->after('generated_earning_pence');
            $table->char('commission_currency', 3)->nullable()->after('commission_bps');
            $table->integer('commission_pence')->default(0)->after('commission_currency');
        });

        Schema::table('chatter_timesheets', function (Blueprint $table) {
            $table->integer('base_pay_pence')->default(0)->after('adjustment_pence');
            $table->integer('commission_pence')->default(0)->after('base_pay_pence');
            $table->char('foreign_commission_currency', 3)->nullable()->after('commission_pence');
            $table->integer('foreign_commission_pence')->default(0)->after('foreign_commission_currency');
        });
    }

    public function down(): void
    {
        Schema::table('chatter_timesheets', function (Blueprint $table) {
            $table->dropColumn([
                'base_pay_pence',
                'commission_pence',
                'foreign_commission_currency',
                'foreign_commission_pence',
            ]);
        });

        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->dropColumn([
                'earning_platform_key',
                'earning_unit',
                'earning_currency',
                'earning_unit_value_usd_micro',
                'clock_in_earning_balance_minor',
                'clock_out_earning_balance_minor',
                'generated_earning_units',
                'generated_earning_pence',
                'commission_bps',
                'commission_currency',
                'commission_pence',
            ]);
        });
    }
};
