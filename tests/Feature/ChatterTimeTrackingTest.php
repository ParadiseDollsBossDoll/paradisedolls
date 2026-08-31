<?php

namespace Tests\Feature;

use App\Http\Middleware\EnforceAuthenticatedSessionPolicy;
use App\Mail\AdminActivityAlertMail;
use App\Mail\ChatterInvitationMail;
use App\Mail\ChatterWorkflowMail;
use App\Models\ChatterBreak;
use App\Models\ChatterModelAssignment;
use App\Models\ChatterPayAdjustment;
use App\Models\ChatterPayRate;
use App\Models\ChatterProfile;
use App\Models\ChatterRequest;
use App\Models\ChatterRoleAssignment;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Models\ChatterWorkRole;
use App\Models\CommunityChannelAccess;
use App\Models\Course;
use App\Models\User;
use App\Services\ChatterAccountService;
use App\Services\ChatterPayrollService;
use App\Services\CourseCommunityService;
use App\Services\UsdPhpExchangeRateService;
use App\Support\ChatterCurrency;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ChatterTimeTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_public_chatter_request_remains_pending_and_notifies_admin(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->post(route('chatter.apply.store'), [
            'name' => 'Remote Chatter',
            'email' => 'CHATTER@example.com',
            'timezone' => 'Asia/Manila',
        ])->assertRedirect(route('chatter.apply'));

        $this->assertDatabaseHas('chatter_requests', [
            'email' => 'chatter@example.com',
            'status' => ChatterRequest::STATUS_PENDING,
        ]);
        $this->assertSame('chatter_request', $admin->notifications()->first()?->data['category']);
        Mail::assertQueued(AdminActivityAlertMail::class);
    }

    public function test_public_chatter_request_does_not_reveal_existing_accounts_or_duplicate_requests(): void
    {
        Mail::fake();
        User::factory()->create(['role' => 'chatter', 'email' => 'existing@example.com']);
        $payload = ['name' => 'Existing Chatter', 'email' => 'existing@example.com', 'timezone' => 'Europe/London'];

        $existingResponse = $this->post(route('chatter.apply.store'), $payload);
        $existingResponse->assertRedirect(route('chatter.apply'));
        $genericStatus = session('status');
        $this->assertNotNull($genericStatus);
        $this->assertDatabaseMissing('chatter_requests', ['email' => 'existing@example.com']);

        $newPayload = ['name' => 'New Chatter', 'email' => 'new@example.com', 'timezone' => 'Europe/London'];
        $this->post(route('chatter.apply.store'), $newPayload)->assertSessionHas('status', $genericStatus);
        $this->post(route('chatter.apply.store'), $newPayload)->assertSessionHas('status', $genericStatus);

        $this->assertSame(1, ChatterRequest::query()->where('email', 'new@example.com')->count());
        Mail::assertQueued(AdminActivityAlertMail::class, 1);
    }

    public function test_admin_can_create_chatter_with_effective_rate_and_secure_invitation(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.chatter-hours.chatters.store'), $this->accountPayload())
            ->assertSessionHasNoErrors();

        $chatter = User::where('email', 'worker@example.com')->firstOrFail();
        $this->assertTrue($chatter->isChatter());
        $this->assertTrue($chatter->chatterProfile->isActive());
        $this->assertSame(1250, $chatter->chatterPayRates()->value('base_rate_pence'));
        $this->assertDatabaseHas('chatter_role_assignments', [
            'user_id' => $chatter->id,
            'hourly_rate_pence' => 1250,
            'is_active' => true,
        ]);
        $this->assertSame('chatter_invitation', $chatter->notifications()->first()?->data['category']);
        Mail::assertQueued(ChatterInvitationMail::class, fn (ChatterInvitationMail $mail) => $mail->chatter->is($chatter));
    }

    public function test_admin_can_update_existing_chatter_timezone(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();

        $this->actingAs($admin)
            ->patch(route('admin.chatter-hours.chatters.timezone', $chatter), [
                'timezone' => 'Asia/Manila',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Chatter timezone updated.');

        $this->assertDatabaseHas('chatter_profiles', [
            'user_id' => $chatter->id,
            'timezone' => 'Asia/Manila',
        ]);
        $this->assertDatabaseHas('chatter_time_audits', [
            'actor_id' => $admin->id,
            'action' => 'chatter_timezone_updated',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.chatter-hours.chatters.timezone', $chatter), [
                'timezone' => 'Not/A_Timezone',
            ])
            ->assertSessionHasErrors('timezone');
    }

    public function test_admin_can_update_existing_chatter_role_name_rate_and_clock_in_availability(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $originalRole = ChatterWorkRole::query()->where('slug', 'chatter')->firstOrFail();
        ChatterRoleAssignment::create([
            'user_id' => $chatter->id,
            'chatter_work_role_id' => $originalRole->id,
            'hourly_rate_pence' => 1200,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.chatter-hours.chatters.roles', $chatter), [
                'work_role_id' => $originalRole->id,
                'work_role_name' => 'Stripchat Training',
                'hourly_rate' => '15.75',
                'is_active' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Work role and hourly rate saved. New shifts will use this rate.');

        $newRole = ChatterWorkRole::query()->where('name', 'Stripchat Training')->firstOrFail();
        $this->assertSame('Chatter', $originalRole->fresh()->name);
        $this->assertDatabaseMissing('chatter_role_assignments', [
            'user_id' => $chatter->id,
            'chatter_work_role_id' => $originalRole->id,
        ]);
        $this->assertDatabaseHas('chatter_role_assignments', [
            'user_id' => $chatter->id,
            'chatter_work_role_id' => $newRole->id,
            'hourly_rate_pence' => 1575,
            'is_active' => false,
            'created_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('chatter_time_audits', [
            'actor_id' => $admin->id,
            'action' => 'work_role_assignment_updated',
        ]);
    }

    public function test_admin_can_manually_grant_and_revoke_chatter_course_access(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $unlocked = Course::create([
            'title' => 'Stripchat Chatter Training',
            'slug' => 'stripchat-chatter-training',
            'platform_label' => 'Stripchat',
            'description' => 'Training for chatters.',
            'has_course_outline' => true,
            'course_outline_url' => 'academy/stripchat-outline.pdf',
            'is_published' => true,
        ]);
        $lesson = $unlocked->lessons()->create([
            'title' => 'Platform basics',
            'sort_order' => 1,
            'is_published' => true,
        ]);
        $locked = Course::create([
            'title' => 'Chaturbate Chatter Training',
            'slug' => 'chaturbate-chatter-training',
            'platform_label' => 'Chaturbate',
            'description' => 'Locked training.',
            'is_published' => true,
        ]);
        $communityChannel = app(CourseCommunityService::class)->ensureForCourse($unlocked, $admin);
        Storage::disk('local')->put('academy/stripchat-outline.pdf', 'outline');

        $this->actingAs($chatter)
            ->get(route('chatter.courses.index'))
            ->assertOk()
            ->assertDontSee('Stripchat Chatter Training')
            ->assertDontSee('Chaturbate Chatter Training');

        $this->actingAs($chatter)
            ->get(route('chatter.courses.outline', $unlocked->slug))
            ->assertRedirect(route('chatter.courses.index'));

        $this->actingAs($admin)
            ->post(route('admin.chatter-hours.chatters.courses', $chatter), [
                'course_id' => $unlocked->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $unlocked->id,
            'user_id' => $chatter->id,
        ]);
        $this->assertFalse(CommunityChannelAccess::query()
            ->where('community_channel_id', $communityChannel->id)
            ->where('user_id', $chatter->id)
            ->exists());

        $this->actingAs($chatter)
            ->get(route('chatter.courses.index'))
            ->assertOk()
            ->assertSee('Stripchat Chatter Training')
            ->assertDontSee('Chaturbate Chatter Training');

        $this->actingAs($chatter)
            ->get(route('chatter.courses.show', $unlocked->slug))
            ->assertOk()
            ->assertSee('Resume Course')
            ->assertDontSee('Open Community')
            ->assertDontSee('Request Access');

        $this->actingAs($chatter)
            ->post(route('chatter.courses.learn', $unlocked->slug))
            ->assertRedirect(route('chatter.courses.learn.show', $unlocked->slug));

        $this->actingAs($chatter)
            ->get(route('chatter.courses.outline', $unlocked->slug))
            ->assertOk();

        $this->actingAs($chatter)
            ->get(route('chatter.courses.learn.show', $unlocked->slug))
            ->assertOk()
            ->assertDontSee('Ask in Community')
            ->assertDontSee('community/channels/'.$communityChannel->slug, false);

        $this->actingAs($chatter)
            ->get(route('community.channels.show', $communityChannel->slug))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($chatter)
            ->get(route('chatter.courses.show', $locked->slug))
            ->assertNotFound();

        $this->actingAs($admin)
            ->delete(route('admin.chatter-hours.chatters.courses.destroy', [$chatter, $unlocked]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $unlocked->id,
            'user_id' => $chatter->id,
        ]);

        $this->actingAs($chatter)
            ->get(route('chatter.courses.lessons.show', [$unlocked->slug, $lesson]))
            ->assertRedirect(route('chatter.courses.index'));
    }

    public function test_admin_can_permanently_delete_a_chatter_and_all_related_records(): void
    {
        Mail::fake();
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $chatter->forceFill(['profile_photo_path' => 'profile-photos/chatter.jpg'])->save();
        Storage::disk('public')->put($chatter->profile_photo_path, 'photo');

        ChatterRequest::create([
            'name' => $chatter->name,
            'email' => $chatter->email,
            'timezone' => 'Europe/London',
            'status' => ChatterRequest::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);
        $role = ChatterWorkRole::query()->where('slug', 'chatter')->firstOrFail();
        ChatterRoleAssignment::create([
            'user_id' => $chatter->id,
            'chatter_work_role_id' => $role->id,
            'hourly_rate_pence' => 1200,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        $shift = ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 10:00:00',
            'timezone' => 'Europe/London',
        ] + $this->tokenEarningSnapshot());
        ChatterBreak::create([
            'chatter_shift_id' => $shift->id,
            'started_at' => '2026-07-13 09:00:00',
            'ended_at' => '2026-07-13 09:15:00',
        ]);
        $timesheet = ChatterTimesheet::create([
            'user_id' => $chatter->id,
            'period_start' => '2026-07-13',
            'period_end' => '2026-07-19',
        ]);
        ChatterPayAdjustment::create([
            'chatter_timesheet_id' => $timesheet->id,
            'created_by' => $admin->id,
            'amount_pence' => 500,
            'label' => 'Bonus',
        ]);
        app(ChatterAccountService::class)->sendInvitation($chatter);
        DB::table('sessions')->insert([
            'id' => 'chatter-session',
            'user_id' => $chatter->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.chatter-hours.index'))
            ->assertOk()
            ->assertSee('Delete account')
            ->assertSee('Delete chatter account?');

        $this->actingAs($admin)
            ->delete(route('admin.chatter-hours.chatters.destroy', $chatter))
            ->assertRedirect(route('admin.chatter-hours.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $chatter->id]);
        $this->assertDatabaseMissing('chatter_profiles', ['user_id' => $chatter->id]);
        $this->assertDatabaseMissing('chatter_pay_rates', ['user_id' => $chatter->id]);
        $this->assertDatabaseMissing('chatter_role_assignments', ['user_id' => $chatter->id]);
        $this->assertDatabaseMissing('chatter_shifts', ['id' => $shift->id]);
        $this->assertDatabaseMissing('chatter_breaks', ['chatter_shift_id' => $shift->id]);
        $this->assertDatabaseMissing('chatter_timesheets', ['id' => $timesheet->id]);
        $this->assertDatabaseMissing('chatter_pay_adjustments', ['chatter_timesheet_id' => $timesheet->id]);
        $this->assertDatabaseMissing('chatter_requests', ['email' => $chatter->email]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $chatter->email]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $chatter->id]);
        Storage::disk('public')->assertMissing('profile-photos/chatter.jpg');

        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->delete(route('admin.chatter-hours.chatters.destroy', $otherAdmin))
            ->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_chatter_role_is_isolated_from_member_and_admin_pages(): void
    {
        $chatter = $this->chatter();

        $this->actingAs($chatter)->get(route('chatter.dashboard'))->assertOk();
        $this->actingAs($chatter)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($chatter)->get(route('member.onboarding.edit'))->assertRedirect(route('chatter.dashboard'));

        $chatter->chatterProfile->update(['employment_status' => ChatterProfile::STATUS_SUSPENDED]);
        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_idle_limits_are_enforced_by_role(): void
    {
        config([
            'session.lifetime' => 720,
            'session.authenticated_lifetime' => 120,
            'session.chatter_lifetime' => 720,
        ]);

        CarbonImmutable::setTestNow('2026-07-13 12:00:00 UTC');
        $chatter = $this->chatter();
        $this->actingAs($chatter)
            ->withSession([
                EnforceAuthenticatedSessionPolicy::SESSION_VERSION_KEY => 0,
                EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY => now()->subHours(3)->timestamp,
            ])
            ->get(route('chatter.dashboard'))
            ->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->withSession([
                EnforceAuthenticatedSessionPolicy::SESSION_VERSION_KEY => 0,
                EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY => now()->subHours(3)->timestamp,
            ])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_chatter_session_expires_after_the_chatter_idle_limit(): void
    {
        config([
            'session.lifetime' => 720,
            'session.chatter_lifetime' => 720,
        ]);

        CarbonImmutable::setTestNow('2026-07-13 21:00:00 UTC');
        $chatter = $this->chatter();

        $this->actingAs($chatter)
            ->withSession([
                EnforceAuthenticatedSessionPolicy::SESSION_VERSION_KEY => 0,
                EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY => now()->subHours(13)->timestamp,
            ])
            ->get(route('chatter.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_chatter_login_never_creates_a_remember_me_cookie(): void
    {
        $chatter = $this->chatter();

        $response = $this->post(route('login'), [
            'email' => $chatter->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($chatter);
        $this->assertNotContains(
            Auth::guard('web')->getRecallerName(),
            collect($response->headers->getCookies())->map->getName()->all(),
        );
    }

    public function test_session_version_change_revokes_an_existing_chatter_session(): void
    {
        $chatter = $this->chatter();
        $chatter->forceFill(['auth_session_version' => 2])->save();

        $this->actingAs($chatter)
            ->withSession([
                EnforceAuthenticatedSessionPolicy::SESSION_VERSION_KEY => 1,
                EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY => now()->timestamp,
            ])
            ->get(route('chatter.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_missing_session_version_cannot_bypass_credential_revocation(): void
    {
        $chatter = $this->chatter();
        $chatter->forceFill(['auth_session_version' => 1])->save();

        $this->actingAs($chatter)
            ->withSession([
                EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY => now()->timestamp,
            ])
            ->get(route('chatter.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_passive_chatter_state_sync_does_not_extend_idle_session_lifetime(): void
    {
        config([
            'session.lifetime' => 720,
            'session.chatter_lifetime' => 720,
        ]);

        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $chatter = $this->chatter();
        $initialActivity = now()->timestamp;

        $this->actingAs($chatter)
            ->withSession([
                EnforceAuthenticatedSessionPolicy::SESSION_VERSION_KEY => 0,
                EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY => $initialActivity,
            ]);

        CarbonImmutable::setTestNow('2026-07-13 11:00:00 UTC');
        $this->postJson(route('chatter.state'))
            ->assertOk()
            ->assertSessionHas(
                EnforceAuthenticatedSessionPolicy::LAST_ACTIVITY_KEY,
                $initialActivity,
            );

        CarbonImmutable::setTestNow('2026-07-13 21:00:01 UTC');
        $this->postJson(route('chatter.state'))
            ->assertUnauthorized();

        $this->assertGuest();
    }

    public function test_clock_actions_prevent_duplicates_and_clock_out_closes_an_active_break(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), ['platform' => 'Chaturbate', 'clock_in_earning_balance' => '1000'])->assertSessionHasNoErrors();
        $this->actingAs($chatter)->post(route('chatter.clock-in'), ['platform' => 'Chaturbate', 'clock_in_earning_balance' => '1000'])->assertSessionHasErrors('shift');
        $this->actingAs($chatter)->post(route('chatter.breaks.end'))->assertSessionHasErrors('shift');

        CarbonImmutable::setTestNow('2026-07-13 10:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasErrors('shift');

        CarbonImmutable::setTestNow('2026-07-13 10:30:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '1000'])->assertSessionHasNoErrors();

        $shift = ChatterShift::firstOrFail();
        $break = ChatterBreak::firstOrFail();
        $this->assertNotNull($shift->clocked_out_at);
        $this->assertNull($shift->active_user_id);
        $this->assertNotNull($break->ended_at);
        $this->assertNull($break->active_shift_id);
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'break_ended_on_clock_out']);

        $this->actingAs($chatter)->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '1000'])->assertSessionHasErrors('shift');
    }

    public function test_logging_out_warns_an_active_chatter_and_clocks_out_the_shift(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), ['platform' => 'Chaturbate', 'clock_in_earning_balance' => '1000'])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:30:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();

        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertOk()
            ->assertSee('Clock out and sign out?')
            ->assertSee('Signing out will clock you out now')
            ->assertSee('Clock Out &amp; Sign Out', false);

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($chatter)->post(route('logout'))->assertRedirect('/');

        $this->assertGuest();
        $shift = ChatterShift::firstOrFail();
        $break = ChatterBreak::firstOrFail();
        $this->assertTrue($shift->clocked_out_at->equalTo(CarbonImmutable::parse('2026-07-13 09:00:00 UTC')));
        $this->assertNull($shift->active_user_id);
        $this->assertTrue($break->ended_at->equalTo(CarbonImmutable::parse('2026-07-13 09:00:00 UTC')));
        $this->assertNull($break->active_shift_id);
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'break_ended_on_logout']);
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'clocked_out_on_logout']);
        $this->assertSame(30, ChatterTimesheet::firstOrFail()->ordinary_minutes);
    }

    public function test_suspending_an_active_chatter_closes_and_audits_the_break_and_shift(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-in'), ['platform' => 'Chaturbate', 'clock_in_earning_balance' => '1000'])->assertSessionHasNoErrors();
        CarbonImmutable::setTestNow('2026-07-13 08:30:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();
        DB::table('sessions')->insert([
            'id' => 'suspended-chatter-session',
            'user_id' => $chatter->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'payload' => '',
            'last_activity' => time(),
        ]);

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($admin)->patch(route('admin.chatter-hours.chatters.status', $chatter), [
            'employment_status' => ChatterProfile::STATUS_SUSPENDED,
            'reason' => 'Access ended.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('2026-07-13 09:00:00', ChatterShift::firstOrFail()->clocked_out_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-13 09:00:00', ChatterBreak::firstOrFail()->ended_at->utc()->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'break_ended_on_suspension', 'reason' => 'Access ended.']);
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'clocked_out_on_suspension', 'reason' => 'Access ended.']);
        $this->assertDatabaseMissing('sessions', ['id' => 'suspended-chatter-session']);
    }

    public function test_dashboard_timer_and_totals_exclude_breaks_after_refresh_resume_and_clock_out(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), ['platform' => 'Chaturbate', 'clock_in_earning_balance' => '1000'])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:30:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:30:00 UTC');
        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertOk()
            ->assertSee('Resume Work')
            ->assertSee('Hours Worked')
            ->assertSee('Today worked')
            ->assertSee('This week worked')
            ->assertSee('This month worked')
            ->assertDontSee('Today paid')
            ->assertDontSee('Week paid')
            ->assertDontSee('Week breaks')
            ->assertSee('initialWorkedSeconds: 1800', false)
            ->assertSee('initialTimerRunning: false', false)
            ->assertSee('chatter\/state', false);

        $this->actingAs($chatter)->post(route('chatter.breaks.end'))->assertSessionHasNoErrors();

        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertOk()
            ->assertSee('Start Break')
            ->assertSee('initialWorkedSeconds: 1800', false)
            ->assertSee('initialTimerRunning: true', false);

        CarbonImmutable::setTestNow('2026-07-13 10:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '1000'])->assertSessionHasNoErrors();

        $sheet = app(ChatterPayrollService::class)
            ->refresh(app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame(60, $sheet->ordinary_minutes);
        $this->assertSame(60, $sheet->break_minutes);
        $this->assertDatabaseHas('chatter_shifts', [
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 10:00:00',
        ]);
        $this->assertDatabaseHas('chatter_breaks', [
            'started_at' => '2026-07-13 08:30:00',
            'ended_at' => '2026-07-13 09:30:00',
        ]);
    }

    public function test_resume_uses_exact_server_worked_time_and_does_not_restore_a_short_break(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:10 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), ['platform' => 'Chaturbate', 'clock_in_earning_balance' => '1000'])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:30:20 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:35:40 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.end'))->assertSessionHasNoErrors();

        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertOk()
            ->assertSee('initialWorkedSeconds: 1810', false)
            ->assertSee('initialTimerRunning: true', false)
            ->assertDontSee('clockedInAt:', false)
            ->assertDontSee('completedBreakSeconds:', false);

        $shift = ChatterShift::query()->firstOrFail();
        $break = ChatterBreak::query()->firstOrFail();

        $this->assertSame('2026-07-13 08:00:10', $shift->clocked_in_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-13 08:30:20', $break->started_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-13 08:35:40', $break->ended_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame(1810, app(ChatterPayrollService::class)->shiftWorkedSeconds($shift->load('breaks')));
    }

    public function test_state_sync_keeps_shift_state_server_authoritative_and_excludes_breaks(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), ['platform' => 'Chaturbate', 'clock_in_earning_balance' => '1000'])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:30:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:30:00 UTC');
        $this->actingAs($chatter)->postJson(route('chatter.state'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
            ->assertHeader('Pragma', 'no-cache')
            ->assertJson([
                'has_open_shift' => true,
                'on_break' => true,
                'timer_running' => false,
                'worked_seconds' => 1800,
            ]);

        $this->actingAs($chatter)->post(route('chatter.breaks.end'))->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 10:00:00 UTC');
        $this->actingAs($chatter)->postJson(route('chatter.state'))
            ->assertOk()
            ->assertJson([
                'has_open_shift' => true,
                'on_break' => false,
                'timer_running' => true,
                'worked_seconds' => 3600,
            ]);

        $this->assertNull(ChatterShift::firstOrFail()->clocked_out_at);
    }

    public function test_state_sync_is_not_available_via_get(): void
    {
        $this->actingAs($this->chatter())
            ->getJson('/chatter/state')
            ->assertMethodNotAllowed();
    }

    public function test_payroll_excludes_breaks_and_uses_fixed_shift_rate_without_floating_point_money(): void
    {
        $chatter = $this->chatter(['overtime_threshold_minutes' => 120]);
        $shift = ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 11:00:00',
            'timezone' => 'Europe/London',
        ]);
        $shift->breaks()->create(['started_at' => '2026-07-13 09:00:00', 'ended_at' => '2026-07-13 09:30:00']);

        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame(150, $sheet->ordinary_minutes);
        $this->assertSame(30, $sheet->break_minutes);
        $this->assertSame(0, $sheet->overtime_minutes);
        $this->assertSame(3000, $sheet->gross_pay_pence);
        $totals = $payroll->workedTotals($chatter, CarbonImmutable::parse('2026-07-13 08:00:00 UTC'), CarbonImmutable::parse('2026-07-13 11:00:00 UTC'));
        $this->assertSame(150, $totals['paid_minutes']);
        $this->assertSame(150, $totals['worked_minutes']);
        $this->assertSame(30, $totals['break_minutes']);
    }

    public function test_admin_can_assign_a_second_work_role_and_shift_uses_its_frozen_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $adminTask = ChatterWorkRole::query()->where('slug', 'admin-task')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.chatter-hours.chatters.roles', $chatter), [
            'work_role_id' => $adminTask->id,
            'work_role_name' => $adminTask->name,
            'hourly_rate' => '15.75',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'work_role_id' => $adminTask->id,
            'platform' => 'Stripchat',
            'clock_in_earning_balance' => '1000',
        ])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '1000'])->assertSessionHasNoErrors();

        $shift = ChatterShift::query()->firstOrFail();
        $this->assertSame($adminTask->id, $shift->chatter_work_role_id);
        $this->assertSame(1575, $shift->hourly_rate_pence);
        $this->assertSame('Stripchat', $shift->platform);

        $sheet = app(ChatterPayrollService::class)
            ->refresh(app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));
        $this->assertSame(60, $sheet->ordinary_minutes);
        $this->assertSame(1575, $sheet->gross_pay_pence);
        $this->assertSame('USD', data_get($sheet->calculation_snapshot, 'currency'));
        $this->assertSame('61.4000', data_get($sheet->calculation_snapshot, 'usd_to_php_rate'));
        $this->assertSame(96705, data_get($sheet->calculation_snapshot, 'gross_pay_php_centavos'));
        $this->assertSame('Admin Task', data_get($sheet->calculation_snapshot, 'shifts.0.work_role'));
        $this->assertSame('Stripchat', data_get($sheet->calculation_snapshot, 'shifts.0.platform'));
        $this->assertSame(1575, data_get($sheet->calculation_snapshot, 'shifts.0.hourly_rate_pence'));

        ChatterRoleAssignment::query()->where('user_id', $chatter->id)->where('chatter_work_role_id', $adminTask->id)->update([
            'hourly_rate_pence' => 3000,
        ]);
        $this->assertSame(1575, $shift->fresh()->hourly_rate_pence);
    }

    public function test_chatter_cannot_clock_in_with_a_role_not_assigned_to_them(): void
    {
        $chatter = $this->chatter();
        $adminTask = ChatterWorkRole::query()->where('slug', 'admin-task')->firstOrFail();

        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'work_role_id' => $adminTask->id,
            'platform' => 'Stripchat',
            'clock_in_earning_balance' => '1000',
        ])->assertSessionHasErrors('work_role_id');

        $this->assertDatabaseCount('chatter_shifts', 0);
    }

    public function test_chatter_clock_in_stores_the_assigned_model_with_the_platform(): void
    {
        $chatter = $this->chatter();
        $assignedModel = User::factory()->create(['role' => 'model', 'name' => 'Assigned Model']);
        $otherModel = User::factory()->create(['role' => 'model', 'name' => 'Other Model']);
        $assignedModel->modelProfile()->create([
            'verification_status' => 'verified',
        ]);
        $otherModel->modelProfile()->create([
            'verification_status' => 'verified',
        ]);
        ChatterModelAssignment::create([
            'chatter_id' => $chatter->id,
            'model_id' => $assignedModel->id,
            'assigned_at' => now(),
        ]);

        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'platform' => 'Stripchat',
            'clock_in_earning_balance' => '1000',
        ])->assertSessionHasErrors('model_id');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'platform' => 'Stripchat',
            'model_id' => $otherModel->id,
            'clock_in_earning_balance' => '1000',
        ])->assertSessionHasErrors('model_id');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'platform' => 'Stripchat',
            'model_id' => $assignedModel->id,
            'clock_in_earning_balance' => '1000',
        ])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '1000'])->assertSessionHasNoErrors();

        $shift = ChatterShift::query()->firstOrFail();
        $this->assertSame('Stripchat', $shift->platform);
        $this->assertSame($assignedModel->id, $shift->model_id);

        $sheet = app(ChatterPayrollService::class)
            ->refresh(app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame($assignedModel->id, data_get($sheet->calculation_snapshot, 'shifts.0.model_id'));
        $this->assertSame('Assigned Model', data_get($sheet->calculation_snapshot, 'shifts.0.model_name'));
        $this->assertSame('Stripchat', data_get($sheet->calculation_snapshot, 'shifts.0.platform'));
    }

    public function test_chatter_must_enter_earning_balances_when_clocking_in_and_out(): void
    {
        $chatter = $this->chatter();

        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'platform' => 'Chaturbate',
        ])->assertSessionHasErrors('clock_in_earning_balance');

        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'platform' => 'Chaturbate',
            'clock_in_earning_balance' => '1000',
        ])->assertSessionHasNoErrors();

        $this->actingAs($chatter)
            ->post(route('chatter.clock-out'))
            ->assertSessionHasErrors('clock_out_earning_balance');

        $this->actingAs($chatter)
            ->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '999'])
            ->assertSessionHasErrors('clock_out_earning_balance');

        $this->actingAs($chatter)
            ->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '2000'])
            ->assertSessionHasNoErrors();

        $shift = ChatterShift::query()->firstOrFail();
        $this->assertSame(1000, $shift->clock_in_earning_balance_minor);
        $this->assertSame(2000, $shift->clock_out_earning_balance_minor);
        $this->assertSame(1000, $shift->generated_earning_units);
    }

    public function test_chatter_token_commission_is_calculated_per_shift_and_added_to_payroll(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $assignedModel = User::factory()->create(['role' => 'model', 'name' => 'Commission Model']);
        $assignedModel->modelProfile()->create(['verification_status' => 'verified']);
        ChatterModelAssignment::create([
            'chatter_id' => $chatter->id,
            'model_id' => $assignedModel->id,
            'assigned_at' => now(),
        ]);

        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'platform' => 'Stripchat',
            'model_id' => $assignedModel->id,
            'clock_in_earning_balance' => '1000',
        ])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($chatter)
            ->post(route('chatter.clock-out'), ['clock_out_earning_balance' => '2000'])
            ->assertSessionHasNoErrors();

        $shift = ChatterShift::query()->with('workRole', 'model')->firstOrFail();
        $this->assertSame(1000, $shift->generated_earning_units);
        $this->assertSame(5000, $shift->generated_earning_pence);
        $this->assertSame(300, $shift->commission_bps);
        $this->assertSame('USD', $shift->commission_currency);
        $this->assertSame(50000, $shift->earning_unit_value_usd_micro);
        $this->assertSame(150, $shift->commission_pence);
        $this->assertSame('Stripchat', $shift->platform);
        $this->assertSame($assignedModel->id, $shift->model_id);

        $sheet = app(ChatterPayrollService::class)
            ->refresh(app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame(60, $sheet->ordinary_minutes);
        $this->assertSame(1200, $sheet->base_pay_pence);
        $this->assertSame(150, $sheet->commission_pence);
        $this->assertSame(1350, $sheet->gross_pay_pence);
        $this->assertSame(1000, data_get($sheet->calculation_snapshot, 'shifts.0.clock_in_earning_balance_minor'));
        $this->assertSame(2000, data_get($sheet->calculation_snapshot, 'shifts.0.clock_out_earning_balance_minor'));
        $this->assertSame(1000, data_get($sheet->calculation_snapshot, 'shifts.0.generated_earning_units'));
        $this->assertSame(5000, data_get($sheet->calculation_snapshot, 'shifts.0.generated_earning_pence'));
        $this->assertSame(150, data_get($sheet->calculation_snapshot, 'shifts.0.commission_pence'));
        $this->assertSame('Commission Model', data_get($sheet->calculation_snapshot, 'shifts.0.model_name'));

        $this->actingAs($admin)
            ->get(route('admin.chatter-hours.timesheets.show', $sheet))
            ->assertOk()
            ->assertSee('Clock-in balance')
            ->assertSee('1,000')
            ->assertSee('Clock-out balance')
            ->assertSee('2,000')
            ->assertSee('Generated earnings')
            ->assertSee('$1.50 USD');
    }

    public function test_platform_specific_token_values_calculate_commission_correctly(): void
    {
        $cases = [
            ['Stripchat', 5000, 150],
            ['SC', 5000, 150],
            ['Chaturbate', 5000, 150],
            ['CB', 5000, 150],
            ['Camsoda', 5000, 150],
            ['MyFreeCams', 5000, 150],
            ['MFC', 5000, 150],
            ['CAM4', 10000, 300],
            ['Bongacams', 2500, 75],
        ];

        foreach ($cases as [$platform, $generatedCents, $commissionCents]) {
            $chatter = $this->chatter();

            CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
            $this->actingAs($chatter)->post(route('chatter.clock-in'), [
                'platform' => $platform,
                'clock_in_earning_balance' => '1000',
            ])->assertSessionHasNoErrors();

            CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
            $this->actingAs($chatter)->post(route('chatter.clock-out'), [
                'clock_out_earning_balance' => '2000',
            ])->assertSessionHasNoErrors();

            $shift = ChatterShift::query()->where('user_id', $chatter->id)->firstOrFail();
            $this->assertSame('tokens', $shift->earning_unit);
            $this->assertSame('USD', $shift->earning_currency);
            $this->assertSame(1000, $shift->generated_earning_units);
            $this->assertSame($generatedCents, $shift->generated_earning_pence);
            $this->assertSame('USD', $shift->commission_currency);
            $this->assertSame($commissionCents, $shift->commission_pence);
        }
    }

    public function test_generated_earnings_recover_commission_for_payroll_snapshot_and_admin_display(): void
    {
        CarbonImmutable::setTestNow('2026-08-31 12:00:00 Europe/London');
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter(['base_rate_pence' => 300]);
        $model = User::factory()->create(['role' => 'model', 'name' => 'Token Test Model']);
        $model->modelProfile()->create(['verification_status' => 'verified']);
        $role = ChatterWorkRole::query()->where('slug', 'chatter')->firstOrFail();

        ChatterShift::create(array_merge([
            'user_id' => $chatter->id,
            'chatter_work_role_id' => $role->id,
            'hourly_rate_pence' => 300,
            'clocked_in_at' => CarbonImmutable::parse('2026-08-31 19:35:00', 'Europe/London')->utc(),
            'clocked_out_at' => CarbonImmutable::parse('2026-08-31 22:07:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
            'platform' => 'Stripchat',
            'model_id' => $model->id,
        ], $this->tokenEarningSnapshot(1300, 5000), [
            'commission_pence' => 0,
        ]));

        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-08-31', 'Europe/London')));

        $this->assertSame(3700, data_get($sheet->calculation_snapshot, 'shifts.0.generated_earning_units'));
        $this->assertSame(18500, data_get($sheet->calculation_snapshot, 'shifts.0.generated_earning_pence'));
        $this->assertSame(555, data_get($sheet->calculation_snapshot, 'shifts.0.commission_pence'));
        $this->assertSame(555, $sheet->commission_pence);
        $this->assertSame(760, $sheet->base_pay_pence);
        $this->assertSame(1315, $sheet->gross_pay_pence);

        $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-08-31',
            'to' => '2026-09-06',
        ]))
            ->assertOk()
            ->assertSee('Balance in')
            ->assertSee('1,300 tokens')
            ->assertSee('Balance out')
            ->assertSee('5,000 tokens')
            ->assertSee('3,700 tokens / $185.00 USD')
            ->assertSee('$5.55 USD')
            ->assertSee('Generated: $185.00 USD')
            ->assertSee('Total pay USD')
            ->assertSee('Base + USD commission + GBP converted + adjustments')
            ->assertSee('$13.15')
            ->assertSee('Token Test Model');
    }

    public function test_export_derives_commission_when_saved_snapshot_commission_is_zero(): void
    {
        CarbonImmutable::setTestNow('2026-08-31 12:00:00 Europe/London');
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter(['base_rate_pence' => 300]);

        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => CarbonImmutable::parse('2026-08-31 19:35:00', 'Europe/London')->utc(),
            'clocked_out_at' => CarbonImmutable::parse('2026-08-31 22:07:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
            'platform' => 'Stripchat',
        ] + $this->tokenEarningSnapshot(1300, 5000));

        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-08-31', 'Europe/London')));
        $snapshot = $sheet->calculation_snapshot;
        data_set($snapshot, 'shifts.0.commission_pence', 0);
        $sheet->forceFill([
            'commission_pence' => 0,
            'gross_pay_pence' => (int) $sheet->base_pay_pence,
            'calculation_snapshot' => $snapshot,
        ])->save();

        $sheetXml = $this->sheetXmlFromStreamedResponse(
            $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx')),
        );

        $this->assertStringContainsString('3,700 tokens / $185.00 USD', $sheetXml);
        $this->assertStringContainsString('$5.55 USD', $sheetXml);
        $this->assertStringContainsString('USD COMMISSION', $sheetXml);
    }

    public function test_babestation_commission_is_calculated_in_gbp_and_converted_into_usd_payroll(): void
    {
        config()->set('services.chatter_payroll.exchange_rate_enabled', false);
        config()->set('services.chatter_payroll.gbp_to_usd_rate_fallback', '1.2500');
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter(['base_rate_pence' => 200]);

        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'platform' => 'Babestation',
            'clock_in_earning_balance' => '100.00',
        ])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'), [
            'clock_out_earning_balance' => '200.00',
        ])->assertSessionHasNoErrors();

        $shift = ChatterShift::query()->firstOrFail();
        $this->assertSame('gbp', $shift->earning_unit);
        $this->assertSame('GBP', $shift->earning_currency);
        $this->assertNull($shift->earning_unit_value_usd_micro);
        $this->assertSame(10000, $shift->generated_earning_units);
        $this->assertSame(10000, $shift->generated_earning_pence);
        $this->assertSame('GBP', $shift->commission_currency);
        $this->assertSame(300, $shift->commission_pence);

        $sheet = app(ChatterPayrollService::class)
            ->refresh(app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame(60, $sheet->ordinary_minutes);
        $this->assertSame(200, $sheet->base_pay_pence);
        $this->assertSame(0, $sheet->commission_pence);
        $this->assertSame('GBP', $sheet->foreign_commission_currency);
        $this->assertSame(300, $sheet->foreign_commission_pence);
        $this->assertSame(375, $sheet->foreign_commission_usd_pence);
        $this->assertSame(575, $sheet->gross_pay_pence);
        $this->assertSame('1.2500', data_get($sheet->calculation_snapshot, 'gbp_to_usd_rate'));
        $this->assertSame(375, data_get($sheet->calculation_snapshot, 'foreign_commission_usd_pence'));

        $this->actingAs($admin)
            ->get(route('admin.chatter-hours.timesheets.show', $sheet))
            ->assertOk()
            ->assertSee('GBP 100.00')
            ->assertSee('GBP 3.00')
            ->assertSee('Commission GBP')
            ->assertSee('GBP as USD')
            ->assertSee('$3.75');

        $sheetXml = $this->sheetXmlFromStreamedResponse(
            $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', ['chatter_id' => $chatter->id])),
        );
        $this->assertStringContainsString('BALANCE IN', $sheetXml);
        $this->assertStringContainsString('GBP 100.00', $sheetXml);
        $this->assertStringContainsString('GBP 3.00', $sheetXml);
        $this->assertStringContainsString('$3.75 USD', $sheetXml);
        $this->assertStringContainsString('GBP COMMISSION', $sheetXml);
        $this->assertStringContainsString('GBP/USD', $sheetXml);
    }

    public function test_missing_shift_earning_balances_receive_zero_commission(): void
    {
        $chatter = $this->chatter();
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 09:00:00',
            'timezone' => 'Europe/London',
        ]);

        $sheet = app(ChatterPayrollService::class)
            ->refresh(app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame(1200, $sheet->base_pay_pence);
        $this->assertSame(0, $sheet->commission_pence);
        $this->assertSame(1200, $sheet->gross_pay_pence);
        $this->assertNull(data_get($sheet->calculation_snapshot, 'shifts.0.generated_earning_units'));
        $this->assertSame(0, data_get($sheet->calculation_snapshot, 'shifts.0.commission_pence'));
    }

    public function test_admin_chatter_hours_pages_separate_accounts_from_attendance_and_payroll(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 09:00:00',
            'timezone' => 'Europe/London',
        ]);
        $payroll = app(ChatterPayrollService::class);
        $payroll->refresh($payroll->getOrCreate(
            $chatter,
            CarbonImmutable::parse('2026-07-13', 'Europe/London'),
        ));

        $this->actingAs($admin)->get(route('admin.chatter-hours.index'))
            ->assertOk()
            ->assertSee('Chatter accounts')
            ->assertSee('Weekly attendance')
            ->assertSee('Working now')
            ->assertSee('No chatters currently working.')
            ->assertSee('Account active')
            ->assertSee('Copy application link')
            ->assertSee(route('chatter.apply'))
            ->assertDontSee('Attendance log')
            ->assertDontSee('Payroll summary');

        $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]))
            ->assertOk()
            ->assertSee('Attendance log')
            ->assertSee('Weekly payroll')
            ->assertSee('Manage payroll')
            ->assertDontSee('Payroll summary')
            ->assertDontSee('Weekly timesheets')
            ->assertSee('Currency conversion')
            ->assertSee('Total hours')
            ->assertSee('Rate')
            ->assertSee('Base pay')
            ->assertSee('Commission')
            ->assertSee('Additional')
            ->assertSee('Total pay USD')
            ->assertSee('Total pay PHP')
            ->assertSee('Notes')
            ->assertSee('Status');
    }

    public function test_admin_chatter_accounts_show_working_now_list_separate_from_account_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $workingChatter = $this->chatter();
        $workingChatter->forceFill(['name' => 'Working Chatter', 'email' => 'working@example.com'])->save();
        $closedChatter = $this->chatter();
        $closedChatter->forceFill(['name' => 'Closed Chatter', 'email' => 'closed@example.com'])->save();
        $suspendedChatter = $this->chatter();
        $suspendedChatter->forceFill(['name' => 'Suspended Chatter', 'email' => 'suspended@example.com'])->save();
        $suspendedChatter->chatterProfile->update(['employment_status' => ChatterProfile::STATUS_SUSPENDED]);
        $role = ChatterWorkRole::query()->where('slug', 'admin-task')->firstOrFail();
        $model = User::factory()->create(['role' => 'model', 'name' => 'Assigned Live Model']);
        $model->modelProfile()->create(['verification_status' => 'verified']);

        ChatterShift::create([
            'user_id' => $workingChatter->id,
            'active_user_id' => $workingChatter->id,
            'chatter_work_role_id' => $role->id,
            'hourly_rate_pence' => 300,
            'clocked_in_at' => CarbonImmutable::parse('2026-07-13 09:15:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
            'platform' => 'Stripchat',
            'model_id' => $model->id,
        ]);
        ChatterShift::create([
            'user_id' => $closedChatter->id,
            'chatter_work_role_id' => $role->id,
            'hourly_rate_pence' => 300,
            'clocked_in_at' => CarbonImmutable::parse('2026-07-13 08:00:00', 'Europe/London')->utc(),
            'clocked_out_at' => CarbonImmutable::parse('2026-07-13 09:00:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
            'platform' => 'Hidden Closed Platform',
            'model_id' => $model->id,
        ]);

        $this->actingAs($admin)->get(route('admin.chatter-hours.index'))
            ->assertOk()
            ->assertSee('Working now')
            ->assertSee('Working Chatter')
            ->assertSee('Stripchat')
            ->assertSee('Assigned Live Model')
            ->assertSee('Mon, 13 Jul 9:15 AM')
            ->assertSee('Admin Task')
            ->assertSee('$3.00 USD/hr')
            ->assertSee('Account active')
            ->assertSee('Account suspended')
            ->assertDontSee('Hidden Closed Platform');
    }

    public function test_admin_attendance_and_payroll_tables_hide_zero_work_but_keep_payable_records(): void
    {
        CarbonImmutable::setTestNow('2026-07-20 12:00:00 Europe/London');
        $admin = User::factory()->create(['role' => 'admin']);
        $zeroChatter = $this->chatter();
        $workingChatter = $this->chatter();
        $adjustedChatter = $this->chatter();
        $payroll = app(ChatterPayrollService::class);

        $zeroShift = ChatterShift::create([
            'user_id' => $zeroChatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 09:00:00',
            'timezone' => 'Europe/London',
        ]);
        ChatterBreak::create([
            'chatter_shift_id' => $zeroShift->id,
            'started_at' => '2026-07-13 08:00:00',
            'ended_at' => '2026-07-13 09:00:00',
        ]);
        $zeroTimesheet = $payroll->refresh($payroll->getOrCreate($zeroChatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $workedShift = ChatterShift::create([
            'user_id' => $workingChatter->id,
            'clocked_in_at' => '2026-07-13 10:00:00',
            'clocked_out_at' => '2026-07-13 11:00:00',
            'timezone' => 'Europe/London',
        ]);
        $workedTimesheet = $payroll->refresh($payroll->getOrCreate($workingChatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $adjustedTimesheet = $payroll->getOrCreate($adjustedChatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));
        ChatterPayAdjustment::create([
            'chatter_timesheet_id' => $adjustedTimesheet->id,
            'created_by' => $admin->id,
            'amount_pence' => 500,
            'label' => 'Visibility bonus',
        ]);
        ChatterPayAdjustment::create([
            'chatter_timesheet_id' => $adjustedTimesheet->id,
            'created_by' => $admin->id,
            'amount_pence' => -500,
            'label' => 'Visibility deduction',
        ]);
        $adjustedTimesheet = $payroll->refresh($adjustedTimesheet);
        $this->assertSame(0, $adjustedTimesheet->ordinary_minutes);
        $this->assertSame(0, $adjustedTimesheet->gross_pay_pence);

        $response = $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]));
        $response->assertOk();

        $visibleShiftIds = $response->viewData('attendanceShifts')->getCollection()->pluck('id');
        $this->assertFalse($visibleShiftIds->contains($zeroShift->id));
        $this->assertTrue($visibleShiftIds->contains($workedShift->id));

        $visibleTimesheetIds = $response->viewData('timesheets')->getCollection()->pluck('id');
        $this->assertFalse($visibleTimesheetIds->contains($zeroTimesheet->id));
        $this->assertTrue($visibleTimesheetIds->contains($workedTimesheet->id));
        $this->assertTrue($visibleTimesheetIds->contains($adjustedTimesheet->id));

        $this->actingAs($admin)
            ->get(route('admin.chatter-hours.timesheets.show', $zeroTimesheet))
            ->assertOk();
    }

    public function test_admin_attendance_and_export_default_to_current_uk_payroll_week(): void
    {
        CarbonImmutable::setTestNow('2026-08-31 00:05:00 Europe/London');
        $admin = User::factory()->create(['role' => 'admin']);
        $oldChatter = $this->chatter();
        $oldChatter->forceFill(['name' => 'Old Week Chatter'])->save();
        $currentChatter = $this->chatter();
        $currentChatter->forceFill(['name' => 'Current Week Chatter'])->save();
        $payroll = app(ChatterPayrollService::class);

        ChatterShift::create([
            'user_id' => $oldChatter->id,
            'clocked_in_at' => CarbonImmutable::parse('2026-08-24 10:00:00', 'Europe/London')->utc(),
            'clocked_out_at' => CarbonImmutable::parse('2026-08-24 11:00:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
            'platform' => 'Old Week Platform',
        ] + $this->tokenEarningSnapshot());
        $payroll->refresh($payroll->getOrCreate($oldChatter, CarbonImmutable::parse('2026-08-24', 'Europe/London')));

        ChatterShift::create([
            'user_id' => $currentChatter->id,
            'clocked_in_at' => CarbonImmutable::parse('2026-08-31 10:00:00', 'Europe/London')->utc(),
            'clocked_out_at' => CarbonImmutable::parse('2026-08-31 11:00:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
            'platform' => 'Current Week Platform',
        ] + $this->tokenEarningSnapshot());
        $payroll->refresh($payroll->getOrCreate($currentChatter, CarbonImmutable::parse('2026-08-31', 'Europe/London')));

        $defaultResponse = $this->actingAs($admin)->get(route('admin.chatter-hours.attendance'));
        $defaultResponse->assertOk()
            ->assertSee('Current Week Platform')
            ->assertDontSee('Old Week Platform');
        $this->assertSame('2026-08-31', $defaultResponse->viewData('filters')['from']);
        $this->assertSame('2026-09-06', $defaultResponse->viewData('filters')['to']);
        $defaultResponse->assertSee('name="from" value="2026-08-31"', false);
        $defaultResponse->assertSee('name="to" value="2026-09-06"', false);
        $defaultResponse->assertSee('UK payroll time');
        $defaultResponse->assertSee('Mon, 31 Aug 2026 12:05 AM UK Time');
        $defaultResponse->assertSee('Viewing payroll week: 31 Aug 2026 - 06 Sep 2026');
        $defaultResponse->assertSee(e(route('admin.chatter-hours.export.xlsx', [
            'from' => '2026-08-31',
            'to' => '2026-09-06',
        ])), false);

        $filteredResponse = $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-08-24',
            'to' => '2026-08-30',
        ]));
        $filteredResponse->assertOk()
            ->assertSee('Old Week Platform')
            ->assertDontSee('Current Week Platform');
        $filteredResponse->assertSee(e(route('admin.chatter-hours.export.xlsx', [
            'from' => '2026-08-24',
            'to' => '2026-08-30',
        ])), false);

        $defaultExportResponse = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx'));
        $defaultExportResponse->assertOk();
        $this->assertStringContainsString(
            'paradise-dolls-payroll-2026-08-31-to-2026-09-06.xlsx',
            (string) $defaultExportResponse->headers->get('content-disposition'),
        );
        $defaultExport = $this->sheetXmlFromStreamedResponse($defaultExportResponse);
        $this->assertStringContainsString('Current Week Platform', $defaultExport);
        $this->assertStringNotContainsString('Old Week Platform', $defaultExport);
        $this->assertStringContainsString('PAYROLL AS OF 08/31/2026 - 09/06/2026', $defaultExport);

        $filteredExportResponse = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', [
            'from' => '2026-08-24',
            'to' => '2026-08-30',
        ]));
        $filteredExportResponse->assertOk();
        $this->assertStringContainsString(
            'paradise-dolls-payroll-2026-08-24-to-2026-08-30.xlsx',
            (string) $filteredExportResponse->headers->get('content-disposition'),
        );
        $filteredExport = $this->sheetXmlFromStreamedResponse($filteredExportResponse);
        $this->assertStringContainsString('Old Week Platform', $filteredExport);
        $this->assertStringNotContainsString('Current Week Platform', $filteredExport);
        $this->assertStringContainsString('PAYROLL AS OF 08/24/2026 - 08/30/2026', $filteredExport);

        $mixedBoundaryExportResponse = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', [
            'from' => '2026-08-30',
            'to' => '2026-09-06',
        ]));
        $mixedBoundaryExportResponse->assertOk();
        $this->assertStringContainsString(
            'paradise-dolls-payroll-2026-08-30-to-2026-09-06.xlsx',
            (string) $mixedBoundaryExportResponse->headers->get('content-disposition'),
        );
        $this->assertStringContainsString(
            'PAYROLL AS OF 08/30/2026 - 09/06/2026',
            $this->sheetXmlFromStreamedResponse($mixedBoundaryExportResponse),
        );
    }

    public function test_chatter_timesheets_use_automatic_workflow_labels_without_submission(): void
    {
        CarbonImmutable::setTestNow('2026-07-20 12:00:00 Europe/London');
        $chatter = $this->chatter();
        $payroll = app(ChatterPayrollService::class);
        $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));

        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertOk()
            ->assertSee('In progress')
            ->assertSee('Ready for review')
            ->assertSee('Report a problem')
            ->assertDontSee('Submit');

        $this->assertFalse(Route::has('chatter.timesheets.submit'));
    }

    public function test_scheduled_command_creates_missing_weekly_timesheets_for_active_chatters_only(): void
    {
        CarbonImmutable::setTestNow('2026-08-24 12:00:00 Europe/London');
        $active = $this->chatter();
        $active->chatterProfile->update(['started_at' => '2026-08-10 09:00:00']);
        $suspended = $this->chatter();
        $suspended->chatterProfile->update([
            'employment_status' => ChatterProfile::STATUS_SUSPENDED,
            'started_at' => '2026-08-10 09:00:00',
        ]);

        Artisan::call('chatter-timesheets:ensure');
        Artisan::call('chatter-timesheets:ensure');

        $this->assertSame(3, ChatterTimesheet::query()->where('user_id', $active->id)->count());
        $this->assertSame(
            ['2026-08-10', '2026-08-17', '2026-08-24'],
            ChatterTimesheet::query()->where('user_id', $active->id)->orderBy('period_start')->get()->map(fn (ChatterTimesheet $sheet) => $sheet->period_start->format('Y-m-d'))->all(),
        );
        $this->assertSame(0, ChatterTimesheet::query()->where('user_id', $suspended->id)->count());
    }

    public function test_automatic_currency_rate_updates_drafts_and_approved_php_pay_is_preserved(): void
    {
        config()->set('services.chatter_payroll.exchange_rate_enabled', true);
        config()->set('services.chatter_payroll.exchange_rate_url', 'https://rates.test/latest');
        $providerRate = 60.25;
        $providerDate = '2026-07-17';
        Http::fake(function () use (&$providerRate, &$providerDate) {
            return Http::response([
                'base' => 'USD',
                'date' => $providerDate,
                'rates' => ['PHP' => $providerRate],
            ]);
        });

        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        ChatterPayRate::where('user_id', $chatter->id)->update(['base_rate_pence' => 1000]);
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 09:00:00',
            'timezone' => 'Europe/London',
        ]);

        $currency = app(ChatterCurrency::class);
        app(UsdPhpExchangeRateService::class)->refresh();
        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame('60.2500', $currency->usdToPhpRate());
        $this->assertSame(1000, $sheet->gross_pay_pence);
        $this->assertSame(60250, $currency->phpCentavosForTimesheet($sheet));

        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $sheet), [
            'decision' => 'approve',
        ])->assertSessionHasNoErrors();
        $sheet->refresh();

        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $sheet->status);
        $this->assertSame('60.2500', $currency->rateForTimesheet($sheet));
        $this->assertSame(60250, $currency->phpCentavosForTimesheet($sheet));

        $providerRate = 62.00;
        $providerDate = '2026-07-18';
        app(UsdPhpExchangeRateService::class)->refresh();

        $this->assertSame('62.0000', $currency->usdToPhpRate());
        $this->assertSame('60.2500', $currency->rateForTimesheet($sheet->fresh()));
        $this->assertSame(60250, $currency->phpCentavosForTimesheet($sheet->fresh()));

        $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]))
            ->assertOk()
            ->assertSee('Automatic reference rate')
            ->assertSee('62.0000')
            ->assertDontSee('Save rate');
    }

    public function test_automatic_currency_rate_uses_the_protected_fallback_when_provider_is_unavailable(): void
    {
        config()->set('services.chatter_payroll.exchange_rate_enabled', true);
        config()->set('services.chatter_payroll.exchange_rate_url', 'https://rates.test/latest');
        config()->set('services.chatter_payroll.usd_to_php_rate_fallback', '61.40');
        Http::fake([
            'https://rates.test/latest*' => Http::response([], 503),
        ]);

        $details = app(UsdPhpExchangeRateService::class)->refresh();

        $this->assertSame('61.4000', $details['rate']);
        $this->assertTrue($details['is_fallback']);
        $this->assertTrue($details['is_stale']);
    }

    public function test_weekly_payroll_combines_pay_and_adjustment_notes_without_duplicate_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 09:00:00',
            'timezone' => 'Europe/London',
        ]);

        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));
        ChatterPayAdjustment::create([
            'chatter_timesheet_id' => $sheet->id,
            'created_by' => $admin->id,
            'amount_pence' => 500,
            'label' => 'Performance bonus',
            'note' => 'Excellent customer support this week.',
        ]);
        $payroll->refresh($sheet);

        $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]))
            ->assertOk()
            ->assertSee('Weekly payroll')
            ->assertSee('Base pay')
            ->assertSee('Commission')
            ->assertSee('Additional')
            ->assertSee('Total pay USD')
            ->assertSee('Total pay PHP')
            ->assertSee('Base + USD commission + GBP converted + adjustments')
            ->assertSee('$12.00 USD/hr')
            ->assertSee('$12.00')
            ->assertSee('+$5.00')
            ->assertSee('$17.00')
            ->assertSee('Performance bonus')
            ->assertSee('Excellent customer support this week.')
            ->assertSee('1 USD =')
            ->assertDontSee('Payroll summary')
            ->assertDontSee('Weekly timesheets')
            ->assertDontSee('Approval progress')
            ->assertDontSee('Approved periods keep their saved rate');
    }

    public function test_night_weekend_and_overtime_do_not_change_fixed_rate_pay(): void
    {
        $chatter = $this->chatter([
            'overtime_threshold_minutes' => 0,
            'night_premium_bps' => 12000,
            'weekend_premium_bps' => 15000,
        ]);
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-18 22:00:00',
            'clocked_out_at' => '2026-07-18 23:00:00',
            'timezone' => 'Europe/London',
        ]);

        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $this->assertSame(0, $sheet->night_minutes);
        $this->assertSame(0, $sheet->weekend_minutes);
        $this->assertSame(0, $sheet->overtime_minutes);
        $this->assertSame(1200, $sheet->gross_pay_pence);
    }

    public function test_sunday_to_monday_shift_is_split_across_uk_payroll_weeks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $role = ChatterWorkRole::query()->where('slug', 'admin-task')->firstOrFail();
        $model = User::factory()->create(['role' => 'model', 'name' => 'Split Model']);
        $model->modelProfile()->create(['verification_status' => 'verified']);
        $shift = ChatterShift::create([
            'user_id' => $chatter->id,
            'chatter_work_role_id' => $role->id,
            'hourly_rate_pence' => 300,
            'platform' => 'Stripchat',
            'model_id' => $model->id,
            'clocked_in_at' => CarbonImmutable::parse('2026-07-19 22:00:00', 'Europe/London')->utc(),
            'clocked_out_at' => CarbonImmutable::parse('2026-07-20 01:00:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
        ])->load('user');

        $payroll = app(ChatterPayrollService::class);
        $payroll->refreshPeriodsTouchedBy($shift);

        $sheets = ChatterTimesheet::query()->where('user_id', $chatter->id)->get()->keyBy(
            fn (ChatterTimesheet $sheet) => $sheet->period_start->toDateString()
        );
        $previousWeek = $sheets->get('2026-07-13');
        $nextWeek = $sheets->get('2026-07-20');

        $this->assertSame(120, $previousWeek?->ordinary_minutes);
        $this->assertSame(600, $previousWeek?->gross_pay_pence);
        $this->assertSame(60, $nextWeek?->ordinary_minutes);
        $this->assertSame(300, $nextWeek?->gross_pay_pence);

        $this->assertSame('Admin Task', data_get($previousWeek->calculation_snapshot, 'shifts.0.work_role'));
        $this->assertSame('Stripchat', data_get($previousWeek->calculation_snapshot, 'shifts.0.platform'));
        $this->assertSame($model->id, data_get($previousWeek->calculation_snapshot, 'shifts.0.model_id'));
        $this->assertSame('Split Model', data_get($previousWeek->calculation_snapshot, 'shifts.0.model_name'));
        $this->assertSame(300, data_get($previousWeek->calculation_snapshot, 'shifts.0.hourly_rate_pence'));
        $this->assertSame('2026-07-19 22:00', CarbonImmutable::parse(data_get($previousWeek->calculation_snapshot, 'shifts.0.started_at'))->timezone('Europe/London')->format('Y-m-d H:i'));
        $this->assertSame('2026-07-19 23:59', CarbonImmutable::parse(data_get($previousWeek->calculation_snapshot, 'shifts.0.ended_at'))->timezone('Europe/London')->format('Y-m-d H:i'));
        $this->assertSame('2026-07-20 00:00', CarbonImmutable::parse(data_get($nextWeek->calculation_snapshot, 'shifts.0.started_at'))->timezone('Europe/London')->format('Y-m-d H:i'));
        $this->assertSame('2026-07-20 01:00', CarbonImmutable::parse(data_get($nextWeek->calculation_snapshot, 'shifts.0.ended_at'))->timezone('Europe/London')->format('Y-m-d H:i'));

        $previousAttendance = $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]));
        $previousAttendance->assertOk();
        $previousSegment = $previousAttendance->viewData('attendanceShifts')->getCollection()->firstWhere('id', $shift->id);
        $this->assertNotNull($previousSegment);
        $this->assertSame(120, (int) $previousSegment->getAttribute('worked_minutes'));
        $this->assertSame('2026-07-19 22:00', $previousSegment->getAttribute('segment_clocked_in_at')->timezone('Europe/London')->format('Y-m-d H:i'));
        $this->assertSame('2026-07-19 23:59', $previousSegment->getAttribute('segment_clocked_out_at')->timezone('Europe/London')->format('Y-m-d H:i'));

        $nextAttendance = $this->actingAs($admin)->get(route('admin.chatter-hours.attendance', [
            'from' => '2026-07-20',
            'to' => '2026-07-26',
        ]));
        $nextAttendance->assertOk();
        $nextSegment = $nextAttendance->viewData('attendanceShifts')->getCollection()->firstWhere('id', $shift->id);
        $this->assertNotNull($nextSegment);
        $this->assertSame(60, (int) $nextSegment->getAttribute('worked_minutes'));
        $this->assertSame('2026-07-20 00:00', $nextSegment->getAttribute('segment_clocked_in_at')->timezone('Europe/London')->format('Y-m-d H:i'));
        $this->assertSame('2026-07-20 01:00', $nextSegment->getAttribute('segment_clocked_out_at')->timezone('Europe/London')->format('Y-m-d H:i'));

        $previousExport = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', [
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]));
        $previousExport->assertOk();
        $previousSheetXml = $this->sheetXmlFromStreamedResponse($previousExport);
        $this->assertStringContainsString('Sunday, July 19, 2026 at', $previousSheetXml);
        $this->assertStringContainsString('10:00 PM', $previousSheetXml);
        $this->assertStringContainsString('11:59 PM', $previousSheetXml);
        $this->assertStringContainsString('Admin Task - Stripchat - Split Model', $previousSheetXml);

        $nextExport = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', [
            'from' => '2026-07-20',
            'to' => '2026-07-26',
        ]));
        $nextExport->assertOk();
        $nextSheetXml = $this->sheetXmlFromStreamedResponse($nextExport);
        $this->assertStringContainsString('Monday, July 20, 2026 at', $nextSheetXml);
        $this->assertStringContainsString('12:00 AM', $nextSheetXml);
        $this->assertStringContainsString('1:00 AM', $nextSheetXml);
    }

    public function test_break_crossing_uk_payroll_cutoff_is_split_out_of_each_week(): void
    {
        $chatter = $this->chatter();
        $shift = ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => CarbonImmutable::parse('2026-07-19 22:00:00', 'Europe/London')->utc(),
            'clocked_out_at' => CarbonImmutable::parse('2026-07-20 01:00:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
        ])->load('user');
        ChatterBreak::create([
            'chatter_shift_id' => $shift->id,
            'started_at' => CarbonImmutable::parse('2026-07-19 23:50:00', 'Europe/London')->utc(),
            'ended_at' => CarbonImmutable::parse('2026-07-20 00:10:00', 'Europe/London')->utc(),
        ]);

        $payroll = app(ChatterPayrollService::class);
        $payroll->refreshPeriodsTouchedBy($shift);

        $sheets = ChatterTimesheet::query()->where('user_id', $chatter->id)->get()->keyBy(
            fn (ChatterTimesheet $sheet) => $sheet->period_start->toDateString()
        );

        $this->assertSame(110, $sheets->get('2026-07-13')?->ordinary_minutes);
        $this->assertSame(10, $sheets->get('2026-07-13')?->break_minutes);
        $this->assertSame(50, $sheets->get('2026-07-20')?->ordinary_minutes);
        $this->assertSame(10, $sheets->get('2026-07-20')?->break_minutes);
    }

    public function test_uk_daylight_saving_transition_uses_real_elapsed_minutes(): void
    {
        $chatter = $this->chatter();
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-03-29 00:30:00',
            'clocked_out_at' => '2026-03-29 02:30:00',
            'timezone' => 'Europe/London',
        ]);

        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-03-23', 'Europe/London')));

        $this->assertSame(120, $sheet->ordinary_minutes);
    }

    public function test_approved_timesheet_is_snapshotted_and_chatter_problem_report_does_not_reopen_it(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $shift = ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 10:00:00',
            'timezone' => 'Europe/London',
        ]);
        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));
        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $sheet), ['decision' => 'approve'])
            ->assertSessionHasNoErrors();
        $sheet->refresh();
        $approvedPay = $sheet->gross_pay_pence;
        $approvedCommission = $sheet->commission_pence;
        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $sheet->status);
        $this->assertNotEmpty($sheet->calculation_snapshot);
        Mail::assertQueued(ChatterWorkflowMail::class);

        ChatterPayRate::where('user_id', $chatter->id)->update(['base_rate_pence' => 9999]);
        $shift->forceFill([
            'clock_out_earning_balance_minor' => 3000,
            'generated_earning_units' => 2000,
            'generated_earning_pence' => 10000,
            'commission_pence' => 300,
        ])->save();
        $payroll->refresh($sheet);
        $this->assertSame($approvedPay, $sheet->fresh()->gross_pay_pence);
        $this->assertSame($approvedCommission, $sheet->fresh()->commission_pence);

        $this->actingAs($chatter)->post(route('chatter.timesheets.problem', $sheet), ['reason' => 'My finish time needs checking.'])
            ->assertSessionHasNoErrors();
        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $sheet->fresh()->status);
        $this->assertSame($approvedPay, $sheet->fresh()->gross_pay_pence);
        $this->assertDatabaseHas('chatter_time_audits', ['chatter_timesheet_id' => $sheet->id, 'action' => 'timesheet_problem_reported']);

        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $sheet), [
            'decision' => 'reopen',
            'note' => 'Checking the reported finish time.',
        ])->assertSessionHasNoErrors();
        $this->assertSame(ChatterTimesheet::STATUS_DRAFT, $sheet->fresh()->status);
        $this->assertSame('Ready for review', $sheet->fresh()->workflowStatusLabel());

        $this->actingAs($admin)->patch(route('admin.chatter-hours.shifts.update', [$sheet, $shift]), [
            'clocked_in_at' => '2026-07-13T08:00',
            'clocked_out_at' => '2026-07-13T09:30',
            'reason' => 'Confirmed the corrected finish time.',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $sheet), ['decision' => 'approve'])
            ->assertSessionHasNoErrors();

        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $sheet->fresh()->status);
        $this->assertSame(90, $sheet->fresh()->ordinary_minutes);
    }

    public function test_admin_can_approve_a_completed_draft_but_not_a_current_timesheet(): void
    {
        Mail::fake();
        CarbonImmutable::setTestNow('2026-07-20 12:00:00 Europe/London');
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $payroll = app(ChatterPayrollService::class);

        $draft = $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));
        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $draft), ['decision' => 'approve'])
            ->assertSessionHasNoErrors();
        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $draft->fresh()->status);

        $current = $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-20', 'Europe/London'));
        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $current), ['decision' => 'approve'])
            ->assertStatus(422);
        $this->assertSame(ChatterTimesheet::STATUS_DRAFT, $current->fresh()->status);
        Mail::assertQueued(ChatterWorkflowMail::class, 1);
    }

    public function test_admin_can_approve_completed_payroll_segment_while_boundary_crossing_shift_stays_open(): void
    {
        CarbonImmutable::setTestNow('2026-07-20 00:30:00 Europe/London');
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $sheet = app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));
        ChatterShift::create([
            'user_id' => $chatter->id,
            'active_user_id' => $chatter->id,
            'clocked_in_at' => CarbonImmutable::parse('2026-07-19 22:00:00', 'Europe/London')->utc(),
            'timezone' => 'Europe/London',
        ]);

        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $sheet), ['decision' => 'approve'])
            ->assertSessionHasNoErrors();

        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $sheet->fresh()->status);
        $this->assertSame(120, $sheet->fresh()->ordinary_minutes);
        $this->assertNull(ChatterShift::firstOrFail()->clocked_out_at);
    }

    public function test_admin_cannot_correct_a_shift_that_touches_another_approved_week(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $shift = ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-19 22:00:00',
            'clocked_out_at' => '2026-07-20 02:00:00',
            'timezone' => 'Europe/London',
        ]);
        $payroll = app(ChatterPayrollService::class);
        $firstWeek = $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));
        $secondWeek = $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-20', 'Europe/London'));
        $secondWeek->update(['status' => ChatterTimesheet::STATUS_APPROVED]);

        $this->actingAs($admin)->patch(route('admin.chatter-hours.shifts.update', [$firstWeek, $shift]), [
            'clocked_in_at' => '2026-07-19T23:00',
            'clocked_out_at' => '2026-07-20T03:00',
            'reason' => 'Correcting the recorded time.',
        ])->assertSessionHasErrors('timesheet');

        $this->assertSame('2026-07-19 22:00:00', $shift->fresh()->clocked_in_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $secondWeek->fresh()->status);
    }

    public function test_admin_exports_filtered_payroll_workbook(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 10:00:00',
            'timezone' => 'Europe/London',
        ] + $this->tokenEarningSnapshot());
        $payroll = app(ChatterPayrollService::class);
        $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $response = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', [
            'chatter_id' => $chatter->id,
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]));
        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = tempnam(sys_get_temp_dir(), 'payroll-xlsx-test-');
        $this->assertNotFalse($path);
        file_put_contents($path, $response->streamedContent());

        $archive = new ZipArchive;
        $this->assertTrue($archive->open($path) === true);
        $workbookXml = $archive->getFromName('xl/workbook.xml');
        $sheetXml = $archive->getFromName('xl/worksheets/sheet1.xml');
        $stylesXml = $archive->getFromName('xl/styles.xml');
        $archive->close();
        @unlink($path);

        $this->assertIsString($workbookXml);
        $this->assertIsString($sheetXml);
        $this->assertIsString($stylesXml);
        $this->assertStringContainsString('name="Payroll"', $workbookXml);
        $this->assertStringContainsString('PARADISE DOLLS', $sheetXml);
        $this->assertStringContainsString('DATE/TIME IN', $sheetXml);
        $this->assertStringContainsString('BALANCE IN', $sheetXml);
        $this->assertStringContainsString('BALANCE OUT', $sheetXml);
        $this->assertStringContainsString('GENERATED', $sheetXml);
        $this->assertStringContainsString('COMMISSION', $sheetXml);
        $this->assertStringContainsString('USD COMMISSION', $sheetXml);
        $this->assertStringContainsString('GBP COMMISSION', $sheetXml);
        $this->assertStringContainsString('HOURS WORKED', $sheetXml);
        $this->assertStringContainsString('Monday, July 13, 2026 at', $sheetXml);
        $this->assertStringContainsString('9:00 AM', $sheetXml);
        $this->assertStringNotContainsString('BST', $sheetXml);
        $this->assertStringContainsString('PAYROLL AS OF', $sheetXml);
        $this->assertStringContainsString('EMPLOYEES NAME', $sheetXml);
        $this->assertStringContainsString('BASIC PAY', $sheetXml);
        $this->assertStringContainsString('TOTAL PAY USD', $sheetXml);
        $this->assertStringContainsString('TOTAL PAY PHP', $sheetXml);
        $this->assertStringContainsString('TOTAL HOURS', $sheetXml);
        $this->assertStringContainsString('orientation="landscape"', $sheetXml);
        $this->assertStringContainsString('formatCode="[h]:mm:ss"', $stylesXml);
        $this->assertStringNotContainsString('Break Details', $sheetXml);
    }

    public function test_admin_payroll_export_excludes_zero_work_rows_and_keeps_payable_rows(): void
    {
        CarbonImmutable::setTestNow('2026-07-20 12:00:00 Europe/London');
        $admin = User::factory()->create(['role' => 'admin']);
        $zeroChatter = $this->chatter();
        $zeroChatter->forceFill(['name' => 'Zero Payroll Export'])->save();
        $adjustedChatter = $this->chatter();
        $adjustedChatter->forceFill(['name' => 'Adjustment Payroll Export'])->save();
        $workingChatter = $this->chatter();
        $workingChatter->forceFill(['name' => 'Working Payroll Export'])->save();
        $payroll = app(ChatterPayrollService::class);

        $payroll->refresh($payroll->getOrCreate($zeroChatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $adjustedTimesheet = $payroll->getOrCreate($adjustedChatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));
        ChatterPayAdjustment::create([
            'chatter_timesheet_id' => $adjustedTimesheet->id,
            'created_by' => $admin->id,
            'amount_pence' => 500,
            'label' => 'Export-only bonus',
        ]);
        $payroll->refresh($adjustedTimesheet);

        $zeroShift = ChatterShift::create([
            'user_id' => $workingChatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 09:00:00',
            'timezone' => 'Europe/London',
            'platform' => 'Hidden Zero Platform',
        ]);
        ChatterBreak::create([
            'chatter_shift_id' => $zeroShift->id,
            'started_at' => '2026-07-13 08:00:00',
            'ended_at' => '2026-07-13 09:00:00',
        ]);
        ChatterShift::create([
            'user_id' => $workingChatter->id,
            'clocked_in_at' => '2026-07-13 10:00:00',
            'clocked_out_at' => '2026-07-13 11:00:00',
            'timezone' => 'Europe/London',
            'platform' => 'Visible Work Platform',
        ]);
        $payroll->refresh($payroll->getOrCreate($workingChatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $response = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', [
            'from' => '2026-07-13',
            'to' => '2026-07-19',
        ]));
        $response->assertOk();

        $path = tempnam(sys_get_temp_dir(), 'payroll-zero-filter-test-');
        $this->assertNotFalse($path);
        file_put_contents($path, $response->streamedContent());

        $archive = new ZipArchive;
        $this->assertTrue($archive->open($path) === true);
        $sheetXml = $archive->getFromName('xl/worksheets/sheet1.xml');
        $archive->close();
        @unlink($path);

        $this->assertIsString($sheetXml);
        $this->assertStringNotContainsString('Zero Payroll Export', $sheetXml);
        $this->assertStringContainsString('Adjustment Payroll Export', $sheetXml);
        $this->assertStringContainsString('Working Payroll Export', $sheetXml);
        $this->assertStringNotContainsString('Hidden Zero Platform', $sheetXml);
        $this->assertStringContainsString('Visible Work Platform', $sheetXml);
    }

    private function sheetXmlFromStreamedResponse($response): string
    {
        $path = tempnam(sys_get_temp_dir(), 'payroll-xlsx-test-');
        $this->assertNotFalse($path);
        file_put_contents($path, $response->streamedContent());

        $archive = new ZipArchive;
        $this->assertTrue($archive->open($path) === true);
        $sheetXml = $archive->getFromName('xl/worksheets/sheet1.xml');
        $archive->close();
        @unlink($path);

        $this->assertIsString($sheetXml);

        return $sheetXml;
    }

    private function tokenEarningSnapshot(int $clockIn = 1000, int $clockOut = 2000, int $unitValueUsdMicro = 50000, string $platformKey = 'stripchat'): array
    {
        $generatedUnits = $clockOut - $clockIn;
        $generatedCents = intdiv(($generatedUnits * $unitValueUsdMicro) + 5000, 10000);

        return [
            'earning_platform_key' => $platformKey,
            'earning_unit' => 'tokens',
            'earning_currency' => 'USD',
            'earning_unit_value_usd_micro' => $unitValueUsdMicro,
            'clock_in_earning_balance_minor' => $clockIn,
            'clock_out_earning_balance_minor' => $clockOut,
            'generated_earning_units' => $generatedUnits,
            'generated_earning_pence' => $generatedCents,
            'commission_bps' => 300,
            'commission_currency' => 'USD',
            'commission_pence' => intdiv(($generatedCents * 300) + 5000, 10000),
        ];
    }

    private function gbpEarningSnapshot(int $clockInPence = 10000, int $clockOutPence = 20000): array
    {
        $generatedPence = $clockOutPence - $clockInPence;

        return [
            'earning_platform_key' => 'babestation',
            'earning_unit' => 'gbp',
            'earning_currency' => 'GBP',
            'earning_unit_value_usd_micro' => null,
            'clock_in_earning_balance_minor' => $clockInPence,
            'clock_out_earning_balance_minor' => $clockOutPence,
            'generated_earning_units' => $generatedPence,
            'generated_earning_pence' => $generatedPence,
            'commission_bps' => 300,
            'commission_currency' => 'GBP',
            'commission_pence' => intdiv(($generatedPence * 300) + 5000, 10000),
        ];
    }

    private function chatter(array $rateOverrides = []): User
    {
        $user = User::factory()->create(['role' => 'chatter']);
        ChatterProfile::create([
            'user_id' => $user->id,
            'timezone' => 'Europe/London',
            'employment_status' => ChatterProfile::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
        ChatterPayRate::create(array_merge([
            'user_id' => $user->id,
            'base_rate_pence' => 1200,
            'overtime_threshold_minutes' => 2400,
            'overtime_multiplier_bps' => 15000,
            'night_premium_bps' => 12000,
            'weekend_premium_bps' => 15000,
            'night_starts_at' => '22:00',
            'night_ends_at' => '06:00',
            'effective_from' => '2026-01-01',
        ], $rateOverrides));

        return $user->refresh();
    }

    private function accountPayload(): array
    {
        return [
            'name' => 'Shift Worker',
            'email' => 'worker@example.com',
            'timezone' => 'Europe/London',
            'work_role_id' => ChatterWorkRole::query()->where('slug', 'chatter')->value('id'),
            'base_hourly_rate' => '12.50',
        ];
    }
}
