<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->string('platform', 100)->nullable()->after('timezone')->index();
        });
    }

    public function down(): void
    {
        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->dropIndex(['platform']);
            $table->dropColumn('platform');
        });
    }
};
