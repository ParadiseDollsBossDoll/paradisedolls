<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('courses', 'short_description')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->text('short_description')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('courses', 'short_description')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('short_description');
            });
        }
    }
};
