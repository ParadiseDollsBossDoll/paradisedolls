<?php

namespace Tests\Feature;

use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ChatterTimesheet;
use App\Models\User;
use App\Services\AdminSidebarBadges;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSidebarBadgesTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_a_section_clears_only_that_admins_unseen_badge_without_approving_items(): void
    {
        $this->travelTo(now()->startOfSecond());
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create(['name' => 'New Model', 'email' => 'new@example.com', 'age_confirmed' => true]);
        ModelProfile::create(['user_id' => User::factory()->create()->id, 'verification_status' => ModelProfile::VERIFICATION_SUBMITTED]);

        $this->assertSame(1, app(AdminSidebarBadges::class)->counts($admin)['applications']);
        $this->travel(1)->seconds();
        $this->actingAs($admin)->get(route('admin.applications.index'))->assertOk();

        $this->assertSame(0, app(AdminSidebarBadges::class)->counts($admin)['applications']);
        $this->assertSame(1, app(AdminSidebarBadges::class)->counts($other)['applications']);
        $this->assertSame(1, app(AdminSidebarBadges::class)->counts($admin)['onboarding']);
        $this->assertSame(ModelApplication::STATUS_PENDING, $application->fresh()->status);
        $this->assertDatabaseMissing('admin_sidebar_visits', ['user_id' => $admin->id, 'section' => 'onboarding']);

        $this->travel(1)->seconds();
        ModelApplication::create(['name' => 'Another Model', 'email' => 'another@example.com', 'age_confirmed' => true]);
        $this->assertSame(1, app(AdminSidebarBadges::class)->counts($admin)['applications']);
    }

    public function test_non_admin_and_failed_visits_do_not_record_seen_state(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $this->actingAs($member)->get(route('admin.applications.index'))->assertForbidden();
        $this->assertSame(0, DB::table('admin_sidebar_visits')->count());
    }

    public function test_older_tabs_cannot_restore_seen_badges_and_updates_reappear(): void
    {
        $this->travelTo(now()->startOfSecond());
        $admin = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create(['name' => 'New Model', 'email' => 'new@example.com', 'age_confirmed' => true]);
        $badges = app(AdminSidebarBadges::class);
        $badges->markViewed($admin, 'applications', now());
        $badges->markViewed($admin, 'applications', now()->subHour());
        $this->assertSame(0, $badges->counts($admin)['applications']);
        $this->travel(2)->seconds();
        $application->update(['name' => 'Updated Model']);
        $this->assertSame(1, $badges->counts($admin)['applications']);
    }

    public function test_timesheets_become_unseen_when_payroll_period_ends_without_row_updates(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-18 20:00:00', 'Europe/London')->utc());
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = User::factory()->create(['role' => 'chatter']);
        ChatterTimesheet::create(['user_id' => $chatter->id, 'period_start' => '2026-10-12', 'period_end' => '2026-10-18']);
        $badges = app(AdminSidebarBadges::class);
        $badges->markViewed($admin, 'chatters', now());
        $this->assertSame(0, $badges->counts($admin)['chatters']);
        $this->travel(1)->days();
        $this->assertSame(1, $badges->counts($admin)['chatters']);
        $badges->markViewed($admin, 'chatters', now());
        $this->assertSame(0, $badges->counts($admin)['chatters']);
    }
}
