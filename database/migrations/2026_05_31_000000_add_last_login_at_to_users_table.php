<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
        });

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->whereNotNull('user_id')
                ->select('user_id', DB::raw('MAX(last_activity) as last_activity'))
                ->groupBy('user_id')
                ->get()
                ->each(function (object $session): void {
                    if (! $session->last_activity) {
                        return;
                    }

                    DB::table('users')
                        ->where('id', $session->user_id)
                        ->update([
                            'last_login_at' => now()->setTimestamp((int) $session->last_activity),
                        ]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login_at');
        });
    }
};
