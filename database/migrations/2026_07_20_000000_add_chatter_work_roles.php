<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatter_work_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('chatter_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chatter_work_role_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('hourly_rate_pence')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'chatter_work_role_id']);
        });

        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->foreignId('chatter_work_role_id')->nullable()->after('active_user_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('hourly_rate_pence')->nullable()->after('chatter_work_role_id');
            $table->index(['chatter_work_role_id', 'clocked_in_at']);
        });

        $now = now();
        $chatterRoleId = DB::table('chatter_work_roles')->insertGetId([
            'name' => 'Chatter',
            'slug' => 'chatter',
            'is_active' => true,
            'sort_order' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('chatter_work_roles')->insert([
            'name' => 'Admin Task',
            'slug' => 'admin-task',
            'is_active' => true,
            'sort_order' => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->where('role', 'chatter')->orderBy('id')->eachById(function ($user) use ($chatterRoleId, $now): void {
            $ratePence = (int) (DB::table('chatter_pay_rates')
                ->where('user_id', $user->id)
                ->orderByDesc('effective_from')
                ->value('base_rate_pence') ?? 0);

            DB::table('chatter_role_assignments')->insertOrIgnore([
                'user_id' => $user->id,
                'chatter_work_role_id' => $chatterRoleId,
                'hourly_rate_pence' => $ratePence,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        DB::table('chatter_shifts')->whereNull('chatter_work_role_id')->update([
            'chatter_work_role_id' => $chatterRoleId,
        ]);
    }

    public function down(): void
    {
        Schema::table('chatter_shifts', function (Blueprint $table) {
            $table->dropForeign(['chatter_work_role_id']);
            $table->dropIndex(['chatter_work_role_id', 'clocked_in_at']);
            $table->dropColumn(['chatter_work_role_id', 'hourly_rate_pence']);
        });

        Schema::dropIfExists('chatter_role_assignments');
        Schema::dropIfExists('chatter_work_roles');
    }
};
