<?php

namespace Tests\Feature;

use App\Mail\AdminActivityAlertMail;
use App\Mail\ChatterInvitationMail;
use App\Mail\ChatterWorkflowMail;
use App\Models\ChatterBreak;
use App\Models\ChatterPayAdjustment;
use App\Models\ChatterPayRate;
use App\Models\ChatterProfile;
use App\Models\ChatterRequest;
use App\Models\ChatterRoleAssignment;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Models\ChatterWorkRole;
use App\Models\User;
use App\Services\ChatterPayrollService;
use App\Services\UsdPhpExchangeRateService;
use App\Support\ChatterCurrency;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
        ]);
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
        app(\App\Services\ChatterAccountService::class)->sendInvitation($chatter);
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
        $this->actingAs($chatter)->get(route('chatter.dashboard'))->assertForbidden();
    }

    public function test_clock_actions_prevent_duplicates_and_clock_out_closes_an_active_break(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'))->assertSessionHasNoErrors();
        $this->actingAs($chatter)->post(route('chatter.clock-in'))->assertSessionHasErrors('shift');
        $this->actingAs($chatter)->post(route('chatter.breaks.end'))->assertSessionHasErrors('shift');

        CarbonImmutable::setTestNow('2026-07-13 10:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasErrors('shift');

        CarbonImmutable::setTestNow('2026-07-13 10:30:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'))->assertSessionHasNoErrors();

        $shift = ChatterShift::firstOrFail();
        $break = ChatterBreak::firstOrFail();
        $this->assertNotNull($shift->clocked_out_at);
        $this->assertNull($shift->active_user_id);
        $this->assertNotNull($break->ended_at);
        $this->assertNull($break->active_shift_id);
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'break_ended_on_clock_out']);

        $this->actingAs($chatter)->post(route('chatter.clock-out'))->assertSessionHasErrors('shift');
    }

    public function test_logging_out_warns_an_active_chatter_and_clocks_out_the_shift(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'))->assertSessionHasNoErrors();

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
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-in'))->assertSessionHasNoErrors();
        CarbonImmutable::setTestNow('2026-07-13 08:30:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($admin)->patch(route('admin.chatter-hours.chatters.status', $chatter), [
            'employment_status' => ChatterProfile::STATUS_SUSPENDED,
            'reason' => 'Access ended.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('2026-07-13 09:00:00', ChatterShift::firstOrFail()->clocked_out_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-13 09:00:00', ChatterBreak::firstOrFail()->ended_at->utc()->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'break_ended_on_suspension', 'reason' => 'Access ended.']);
        $this->assertDatabaseHas('chatter_time_audits', ['action' => 'clocked_out_on_suspension', 'reason' => 'Access ended.']);
    }

    public function test_dashboard_timer_and_totals_exclude_breaks_after_refresh_resume_and_clock_out(): void
    {
        $chatter = $this->chatter();
        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');

        $this->actingAs($chatter)->post(route('chatter.clock-in'))->assertSessionHasNoErrors();

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
            ->assertSee('baseWorkedSeconds: 1800', false)
            ->assertSee('timerRunning: false', false);

        $this->actingAs($chatter)->post(route('chatter.breaks.end'))->assertSessionHasNoErrors();

        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertOk()
            ->assertSee('Start Break')
            ->assertSee('baseWorkedSeconds: 1800', false)
            ->assertSee('timerRunning: true', false);

        CarbonImmutable::setTestNow('2026-07-13 10:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'))->assertSessionHasNoErrors();

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

        $this->actingAs($chatter)->post(route('chatter.clock-in'))->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:30:20 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.start'))->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:35:40 UTC');
        $this->actingAs($chatter)->post(route('chatter.breaks.end'))->assertSessionHasNoErrors();

        $this->actingAs($chatter)->get(route('chatter.dashboard'))
            ->assertOk()
            ->assertSee('baseWorkedSeconds: 1810', false)
            ->assertSee('timerRunning: true', false)
            ->assertDontSee('clockedInAt:', false)
            ->assertDontSee('completedBreakSeconds:', false);

        $shift = ChatterShift::query()->firstOrFail();
        $break = ChatterBreak::query()->firstOrFail();

        $this->assertSame('2026-07-13 08:00:10', $shift->clocked_in_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-13 08:30:20', $break->started_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-13 08:35:40', $break->ended_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame(1810, app(ChatterPayrollService::class)->shiftWorkedSeconds($shift->load('breaks')));
    }

    public function test_payroll_excludes_breaks_and_adds_overtime_without_floating_point_money(): void
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
        $this->assertSame(30, $sheet->overtime_minutes);
        $this->assertSame(3300, $sheet->gross_pay_pence);
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
            'hourly_rate' => '15.75',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 08:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-in'), [
            'work_role_id' => $adminTask->id,
        ])->assertSessionHasNoErrors();

        CarbonImmutable::setTestNow('2026-07-13 09:00:00 UTC');
        $this->actingAs($chatter)->post(route('chatter.clock-out'))->assertSessionHasNoErrors();

        $shift = ChatterShift::query()->firstOrFail();
        $this->assertSame($adminTask->id, $shift->chatter_work_role_id);
        $this->assertSame(1575, $shift->hourly_rate_pence);

        $sheet = app(ChatterPayrollService::class)
            ->refresh(app(ChatterPayrollService::class)->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));
        $this->assertSame(60, $sheet->ordinary_minutes);
        $this->assertSame(1575, $sheet->gross_pay_pence);
        $this->assertSame('USD', data_get($sheet->calculation_snapshot, 'currency'));
        $this->assertSame('61.4000', data_get($sheet->calculation_snapshot, 'usd_to_php_rate'));
        $this->assertSame(96705, data_get($sheet->calculation_snapshot, 'gross_pay_php_centavos'));
        $this->assertSame('Admin Task', data_get($sheet->calculation_snapshot, 'shifts.0.work_role'));
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
        ])->assertSessionHasErrors('work_role_id');

        $this->assertDatabaseCount('chatter_shifts', 0);
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
        app(ChatterPayrollService::class)->getOrCreate(
            $chatter,
            CarbonImmutable::parse('2026-07-13', 'Europe/London'),
        );

        $this->actingAs($admin)->get(route('admin.chatter-hours.index'))
            ->assertOk()
            ->assertSee('Chatter accounts')
            ->assertSee('Weekly attendance')
            ->assertSee('Copy application link')
            ->assertSee(route('chatter.apply'))
            ->assertDontSee('Attendance log')
            ->assertDontSee('Payroll summary');

        $this->actingAs($admin)->get(route('admin.chatter-hours.attendance'))
            ->assertOk()
            ->assertSee('Attendance log')
            ->assertSee('Weekly payroll')
            ->assertSee('Manage payroll')
            ->assertDontSee('Payroll summary')
            ->assertDontSee('Weekly timesheets')
            ->assertSee('Currency conversion')
            ->assertSee('Total hours')
            ->assertSee('Rate')
            ->assertSee('Basic pay')
            ->assertSee('Additional')
            ->assertSee('US final pay')
            ->assertSee('PH final pay')
            ->assertSee('Notes')
            ->assertSee('Status');
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

        $sheet->update(['status' => ChatterTimesheet::STATUS_SUBMITTED, 'submitted_at' => now()]);
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

        $this->actingAs($admin)->get(route('admin.chatter-hours.attendance'))
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

        $this->actingAs($admin)->get(route('admin.chatter-hours.attendance'))
            ->assertOk()
            ->assertSee('Weekly payroll')
            ->assertSee('Basic pay')
            ->assertSee('Additional')
            ->assertSee('US final pay')
            ->assertSee('PH final pay')
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

    public function test_overlapping_night_weekend_and_overtime_use_highest_premium_plus_overtime(): void
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

        $this->assertSame(60, $sheet->night_minutes);
        $this->assertSame(60, $sheet->weekend_minutes);
        $this->assertSame(60, $sheet->overtime_minutes);
        $this->assertSame(2400, $sheet->gross_pay_pence);
    }

    public function test_overnight_shift_is_split_across_uk_payroll_weeks(): void
    {
        $chatter = $this->chatter();
        $shift = ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-01-18 23:30:00',
            'clocked_out_at' => '2026-01-19 01:30:00',
            'timezone' => 'Europe/London',
        ])->load('user');

        $payroll = app(ChatterPayrollService::class);
        $payroll->refreshPeriodsTouchedBy($shift);

        $sheets = ChatterTimesheet::query()->where('user_id', $chatter->id)->get()->keyBy(
            fn (ChatterTimesheet $sheet) => $sheet->period_start->toDateString()
        );
        $this->assertSame(30, $sheets->get('2026-01-12')?->ordinary_minutes);
        $this->assertSame(90, $sheets->get('2026-01-19')?->ordinary_minutes);
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

    public function test_approved_timesheet_is_snapshotted_and_chatter_correction_request_does_not_reopen_it(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        ChatterShift::create([
            'user_id' => $chatter->id,
            'clocked_in_at' => '2026-07-13 08:00:00',
            'clocked_out_at' => '2026-07-13 10:00:00',
            'timezone' => 'Europe/London',
        ]);
        $payroll = app(ChatterPayrollService::class);
        $sheet = $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));
        $sheet->update(['status' => ChatterTimesheet::STATUS_SUBMITTED, 'submitted_at' => now()]);

        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $sheet), ['decision' => 'approve'])
            ->assertSessionHasNoErrors();
        $sheet->refresh();
        $approvedPay = $sheet->gross_pay_pence;
        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $sheet->status);
        $this->assertNotEmpty($sheet->calculation_snapshot);
        Mail::assertQueued(ChatterWorkflowMail::class);

        ChatterPayRate::where('user_id', $chatter->id)->update(['base_rate_pence' => 9999]);
        $payroll->refresh($sheet);
        $this->assertSame($approvedPay, $sheet->fresh()->gross_pay_pence);

        $this->actingAs($chatter)->post(route('chatter.timesheets.correction', $sheet), ['reason' => 'My finish time needs checking.'])
            ->assertSessionHasNoErrors();
        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $sheet->fresh()->status);
        $this->assertDatabaseHas('chatter_time_audits', ['chatter_timesheet_id' => $sheet->id, 'action' => 'correction_requested']);
    }

    public function test_admin_cannot_approve_a_draft_or_incomplete_timesheet(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->chatter();
        $payroll = app(ChatterPayrollService::class);

        $draft = $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London'));
        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $draft), ['decision' => 'approve'])
            ->assertStatus(422);
        $this->assertSame(ChatterTimesheet::STATUS_DRAFT, $draft->fresh()->status);

        CarbonImmutable::setTestNow('2026-07-20 12:00:00 UTC');
        $current = $payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-20', 'Europe/London'));
        $current->update(['status' => ChatterTimesheet::STATUS_SUBMITTED, 'submitted_at' => now()]);
        $this->actingAs($admin)->post(route('admin.chatter-hours.timesheets.review', $current), ['decision' => 'approve'])
            ->assertStatus(422);
        $this->assertSame(ChatterTimesheet::STATUS_SUBMITTED, $current->fresh()->status);
        Mail::assertNothingQueued();
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
        ]);
        $payroll = app(ChatterPayrollService::class);
        $payroll->refresh($payroll->getOrCreate($chatter, CarbonImmutable::parse('2026-07-13', 'Europe/London')));

        $response = $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', ['chatter_id' => $chatter->id]));
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
        $this->assertStringContainsString('HOURS WORKED', $sheetXml);
        $this->assertStringContainsString('Monday, July 13, 2026 at', $sheetXml);
        $this->assertStringContainsString('9:00 AM', $sheetXml);
        $this->assertStringNotContainsString('BST', $sheetXml);
        $this->assertStringContainsString('PAYROLL AS OF', $sheetXml);
        $this->assertStringContainsString('EMPLOYEES NAME', $sheetXml);
        $this->assertStringContainsString('US FINAL PAY', $sheetXml);
        $this->assertStringContainsString('PH FINAL PAY', $sheetXml);
        $this->assertStringContainsString('TOTAL HOURS', $sheetXml);
        $this->assertStringContainsString('orientation="landscape"', $sheetXml);
        $this->assertStringContainsString('formatCode="[h]:mm:ss"', $stylesXml);
        $this->assertStringNotContainsString('Break Details', $sheetXml);
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
            'base_hourly_rate' => '12.50',
            'overtime_threshold_hours' => '40',
            'overtime_multiplier' => '1.5',
            'night_premium_multiplier' => '1.2',
            'weekend_premium_multiplier' => '1.5',
            'night_starts_at' => '22:00',
            'night_ends_at' => '06:00',
            'effective_from' => '2026-07-13',
        ];
    }
}
