<?php

namespace Tests\Feature;

use App\Mail\AdminActivityAlertMail;
use App\Mail\ChatterInvitationMail;
use App\Mail\ChatterWorkflowMail;
use App\Models\ChatterBreak;
use App\Models\ChatterPayRate;
use App\Models\ChatterProfile;
use App\Models\ChatterRequest;
use App\Models\ChatterShift;
use App\Models\ChatterTimeAudit;
use App\Models\ChatterTimesheet;
use App\Models\User;
use App\Services\ChatterPayrollService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

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
        $this->assertSame('chatter_invitation', $chatter->notifications()->first()?->data['category']);
        Mail::assertQueued(ChatterInvitationMail::class, fn (ChatterInvitationMail $mail) => $mail->chatter->is($chatter));
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

    public function test_admin_exports_filtered_shift_csv_and_styled_workbook(): void
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

        $this->actingAs($admin)->get(route('admin.chatter-hours.export.csv', ['chatter_id' => $chatter->id]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->actingAs($admin)->get(route('admin.chatter-hours.export.xlsx', ['chatter_id' => $chatter->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
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
