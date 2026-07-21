<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ChatterWorkflowMail;
use App\Models\ChatterBreak;
use App\Models\ChatterPayAdjustment;
use App\Models\ChatterProfile;
use App\Models\ChatterRequest;
use App\Models\ChatterRoleAssignment;
use App\Models\ChatterShift;
use App\Models\ChatterTimeAudit;
use App\Models\ChatterTimesheet;
use App\Models\ChatterWorkRole;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\ChatterAccountService;
use App\Services\ChatterPayrollService;
use App\Support\ChatterCurrency;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AdminChatterHoursController extends Controller
{
    public function index(Request $request): View
    {
        $chatters = User::query()
            ->where('role', 'chatter')
            ->with([
                'chatterProfile',
                'chatterPayRates' => fn ($query) => $query->latest('effective_from'),
                'chatterRoleAssignments.workRole',
                'chatterShifts' => fn ($query) => $query->whereNull('clocked_out_at')->with(['breaks', 'workRole']),
            ])
            ->orderBy('name')
            ->paginate(12, ['*'], 'chatters_page')
            ->withQueryString();

        $requests = ChatterRequest::query()
            ->where('status', ChatterRequest::STATUS_PENDING)
            ->with('reviewer')
            ->latest()
            ->limit(20)
            ->get();

        $workRoles = ChatterWorkRole::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $openShifts = ChatterShift::query()->whereNull('clocked_out_at')->with(['user', 'breaks', 'workRole'])->get();
        $stats = [
            'chatters' => User::query()->where('role', 'chatter')->count(),
            'working' => $openShifts->count(),
            'on_break' => $openShifts->filter(fn (ChatterShift $shift) => $shift->breaks->contains(fn ($break) => $break->ended_at === null))->count(),
            'overdue' => $openShifts->filter(fn (ChatterShift $shift) => $shift->clocked_in_at->lt(now()->subHours(16)))->count(),
            'requests' => ChatterRequest::query()->where('status', ChatterRequest::STATUS_PENDING)->count(),
        ];
        $mode = 'accounts';

        return view('admin.chatter-hours.index', compact(
            'chatters', 'requests', 'openShifts', 'workRoles', 'stats', 'mode'
        ));
    }

    public function attendance(Request $request, ChatterPayrollService $payroll, ChatterCurrency $currency): View
    {
        $filters = $this->filters($request);
        $chatterOptions = User::query()
            ->where('role', 'chatter')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $workRoles = ChatterWorkRole::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $openShifts = ChatterShift::query()->whereNull('clocked_out_at')->with(['user', 'breaks', 'workRole'])->get();
        $attendanceShifts = $this->attendanceQuery($filters)
            ->with(['user', 'breaks', 'workRole'])
            ->latest('clocked_in_at')
            ->paginate(20, ['*'], 'attendance_page')
            ->withQueryString();
        $attendanceShifts->getCollection()->transform(function (ChatterShift $shift) use ($payroll) {
            $totals = $payroll->shiftWorkedTotals(
                $shift,
                $shift->clocked_in_at,
                $shift->clocked_out_at ?: now('UTC'),
            );
            $shift->setAttribute('worked_minutes', $totals['worked_minutes']);
            $shift->setAttribute('break_minutes', $totals['break_minutes']);

            return $shift;
        });

        // Clock actions and explicit mutations refresh draft payroll. A GET
        // request must remain read-only and must not recalculate every record.
        $reportTimesheets = $this->timesheetQuery($filters)->get();
        $timesheets = $this->timesheetQuery($filters)
            ->with(['user.chatterProfile', 'reviewer', 'adjustments'])
            ->latest('period_start')
            ->paginate(15, ['*'], 'timesheets_page')
            ->withQueryString();
        $timesheets->getCollection()->transform(function (ChatterTimesheet $sheet) {
            $snapshot = $sheet->calculation_snapshot ?? [];
            $rates = collect($snapshot['shifts'] ?? [])
                ->filter(fn (array $shift) => isset($shift['hourly_rate_pence']))
                ->map(fn (array $shift) => [
                    'role' => $shift['work_role'] ?? null,
                    'hourly_rate_pence' => (int) $shift['hourly_rate_pence'],
                ])
                ->unique(fn (array $rate) => ($rate['role'] ?? '').':'.$rate['hourly_rate_pence'])
                ->values();

            if ($rates->isEmpty()) {
                $rates = collect($snapshot['rate_versions'] ?? [])
                    ->filter(fn (array $rate) => isset($rate['base_rate_pence']))
                    ->map(fn (array $rate) => [
                        'role' => null,
                        'hourly_rate_pence' => (int) $rate['base_rate_pence'],
                    ])
                    ->unique('hourly_rate_pence')
                    ->values();
            }

            $sheet->setAttribute('payroll_rates', $rates);
            $sheet->setAttribute('basic_pay_pence', (int) $sheet->gross_pay_pence - (int) $sheet->adjustment_pence);

            return $sheet;
        });
        $stats = [
            'chatters' => User::query()->where('role', 'chatter')->count(),
            'working' => $openShifts->count(),
            'on_break' => $openShifts->filter(fn (ChatterShift $shift) => $shift->breaks->contains(fn ($break) => $break->ended_at === null))->count(),
            'overdue' => $openShifts->filter(fn (ChatterShift $shift) => $shift->clocked_in_at->lt(now()->subHours(16)))->count(),
            'pending' => ChatterTimesheet::query()->whereIn('status', [ChatterTimesheet::STATUS_SUBMITTED, ChatterTimesheet::STATUS_CHANGES_REQUESTED])->count(),
            'requests' => ChatterRequest::query()->where('status', ChatterRequest::STATUS_PENDING)->count(),
            'total_minutes' => (int) $reportTimesheets->sum('ordinary_minutes'),
            'adjustment_pence' => (int) $reportTimesheets->sum('adjustment_pence'),
            'basic_pay_pence' => (int) $reportTimesheets->sum(fn (ChatterTimesheet $sheet) => $sheet->gross_pay_pence - $sheet->adjustment_pence),
            'gross_pay_pence' => (int) $reportTimesheets->sum('gross_pay_pence'),
            'gross_pay_php_centavos' => (int) $reportTimesheets->sum(fn (ChatterTimesheet $sheet) => $currency->phpCentavosForTimesheet($sheet)),
        ];
        $currencyDetails = $currency->usdToPhpDetails();
        $usdToPhpRate = $currencyDetails['rate'];
        $mode = 'attendance';

        return view('admin.chatter-hours.index', compact(
            'chatterOptions', 'timesheets', 'openShifts', 'attendanceShifts',
            'workRoles', 'stats', 'filters', 'currency', 'currencyDetails', 'usdToPhpRate', 'mode'
        ));
    }

    public function storeChatter(Request $request, ChatterAccountService $accounts): RedirectResponse
    {
        $data = $this->validateAccount($request);
        $chatter = $accounts->create($data, $request->user());

        return back()->with('status', __('Chatter account created and a secure invitation was queued for :email.', ['email' => $chatter->email]));
    }

    public function approveRequest(Request $request, ChatterRequest $chatterRequest, ChatterAccountService $accounts): RedirectResponse
    {
        abort_unless($chatterRequest->status === ChatterRequest::STATUS_PENDING, 422);
        $data = $this->validatePaySettings($request) + [
            'name' => $chatterRequest->name,
            'email' => $chatterRequest->email,
            'timezone' => $chatterRequest->timezone,
        ];
        validator($data, ['email' => [Rule::unique(User::class, 'email')]])->validate();
        $accounts->create($data, $request->user(), $chatterRequest);

        return back()->with('status', __('The chatter request was approved and the invitation was queued.'));
    }

    public function rejectRequest(Request $request, ChatterRequest $chatterRequest): RedirectResponse
    {
        $validated = $request->validate(['admin_note' => ['required', 'string', 'max:1000']]);
        $chatterRequest->forceFill([
            'status' => ChatterRequest::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        return back()->with('status', __('The chatter request was rejected.'));
    }

    public function resendInvitation(Request $request, User $chatter, ChatterAccountService $accounts): RedirectResponse
    {
        $this->assertChatter($chatter);
        $accounts->sendInvitation($chatter);

        return back()->with('status', __('A fresh secure invitation was queued for :email.', ['email' => $chatter->email]));
    }

    public function updateStatus(Request $request, User $chatter, ChatterPayrollService $payroll): RedirectResponse
    {
        $this->assertChatter($chatter);
        $validated = $request->validate(['employment_status' => ['required', Rule::in([ChatterProfile::STATUS_ACTIVE, ChatterProfile::STATUS_SUSPENDED])]]);

        DB::transaction(function () use ($request, $chatter, $validated, $payroll) {
            $profile = $chatter->chatterProfile()->lockForUpdate()->firstOrFail();
            $before = ['employment_status' => $profile->employment_status];
            $profile->forceFill([
                'employment_status' => $validated['employment_status'],
                'suspended_at' => $validated['employment_status'] === ChatterProfile::STATUS_SUSPENDED ? now() : null,
            ])->save();

            if ($validated['employment_status'] === ChatterProfile::STATUS_SUSPENDED) {
                $shift = $chatter->chatterShifts()->whereNull('clocked_out_at')->with('breaks')->lockForUpdate()->first();
                if ($shift) {
                    $now = now('UTC');
                    $activeBreak = $shift->breaks()->whereNull('ended_at')->lockForUpdate()->first();
                    if ($activeBreak) {
                        $activeBreak->forceFill(['ended_at' => $now, 'active_shift_id' => null])->save();
                        ChatterTimeAudit::create([
                            'chatter_shift_id' => $shift->id,
                            'actor_id' => $request->user()->id,
                            'action' => 'break_ended_on_suspension',
                            'reason' => $request->string('reason')->toString() ?: __('Chatter account suspended.'),
                            'after' => ['ended_at' => $now->toIso8601String()],
                        ]);
                    }
                    $shift->forceFill(['clocked_out_at' => $now, 'active_user_id' => null])->save();
                    ChatterTimeAudit::create([
                        'chatter_shift_id' => $shift->id,
                        'actor_id' => $request->user()->id,
                        'action' => 'clocked_out_on_suspension',
                        'reason' => $request->string('reason')->toString() ?: __('Chatter account suspended.'),
                        'after' => ['clocked_out_at' => $now->toIso8601String()],
                    ]);
                    $payroll->refreshPeriodsTouchedBy($shift->load('user'));
                }
            }

            ChatterTimeAudit::create([
                'actor_id' => $request->user()->id,
                'action' => 'chatter_status_updated',
                'reason' => $request->string('reason')->toString() ?: null,
                'before' => $before,
                'after' => ['employment_status' => $profile->employment_status, 'chatter_id' => $chatter->id],
            ]);
        });

        return back()->with('status', __('Chatter account status updated.'));
    }

    public function destroyChatter(User $chatter, ChatterAccountService $accounts): RedirectResponse
    {
        $this->assertChatter($chatter);
        $name = $accounts->delete($chatter);

        return redirect()
            ->route('admin.chatter-hours.index')
            ->with('status', __('The chatter account for :name and all related records were permanently deleted.', ['name' => $name]));
    }

    public function storePayRate(Request $request, User $chatter): RedirectResponse
    {
        $this->assertChatter($chatter);
        $data = $this->validatePaySettings($request);
        $chatter->chatterPayRates()->updateOrCreate(
            ['effective_from' => $data['effective_from']],
            $data + ['created_by' => $request->user()->id]
        );

        return back()->with('status', __('A new effective-dated pay rate was saved.'));
    }

    public function storeRoleAssignment(Request $request, User $chatter): RedirectResponse
    {
        $this->assertChatter($chatter);
        $data = $request->validate([
            'work_role_id' => ['required', 'integer', Rule::exists(ChatterWorkRole::class, 'id')->where('is_active', true)],
            'hourly_rate' => ['required', 'numeric', 'between:0,1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $assignment = ChatterRoleAssignment::query()->updateOrCreate(
            ['user_id' => $chatter->id, 'chatter_work_role_id' => (int) $data['work_role_id']],
            [
                'hourly_rate_pence' => (int) round(((float) $data['hourly_rate']) * 100),
                'is_active' => (bool) $data['is_active'],
                'created_by' => $request->user()->id,
            ],
        );

        ChatterTimeAudit::create([
            'actor_id' => $request->user()->id,
            'action' => 'work_role_assignment_updated',
            'reason' => __('Role and hourly rate updated from the chatter account manager.'),
            'after' => [
                'chatter_id' => $chatter->id,
                'work_role_id' => $assignment->chatter_work_role_id,
                'hourly_rate_pence' => $assignment->hourly_rate_pence,
                'is_active' => $assignment->is_active,
            ],
        ]);

        return back()->with('status', __('Work role and hourly rate saved. New shifts will use this rate.'));
    }

    public function showTimesheet(ChatterTimesheet $timesheet, ChatterCurrency $currency): View
    {
        $timesheet->load(['user.chatterProfile', 'reviewer', 'adjustments.creator', 'audits.actor']);
        $startUtc = CarbonImmutable::parse($timesheet->period_start->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->utc();
        $endUtc = CarbonImmutable::parse($timesheet->period_end->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->addDay()->utc();
        $shifts = ChatterShift::query()
            ->where('user_id', $timesheet->user_id)
            ->where('clocked_in_at', '<', $endUtc)
            ->where(fn ($query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $startUtc))
            ->with(['breaks', 'audits.actor', 'workRole'])
            ->orderBy('clocked_in_at')
            ->get();

        $usdToPhpRate = $currency->rateForTimesheet($timesheet);
        $grossPayPhpCentavos = $currency->phpCentavosForTimesheet($timesheet);

        return view('admin.chatter-hours.show', compact('timesheet', 'shifts', 'currency', 'usdToPhpRate', 'grossPayPhpCentavos'));
    }

    public function updateShift(Request $request, ChatterTimesheet $timesheet, ChatterShift $shift, ChatterPayrollService $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'clocked_in_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'clocked_out_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:clocked_in_at'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $newStart = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['clocked_in_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc();
        $newEnd = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['clocked_out_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc();

        [$originalShift, $shift] = DB::transaction(function () use ($request, $timesheet, $shift, $validated, $newStart, $newEnd) {
            $lockedTimesheet = ChatterTimesheet::query()->with('user')->lockForUpdate()->findOrFail($timesheet->id);
            $lockedShift = ChatterShift::query()->with(['user', 'breaks'])->lockForUpdate()->findOrFail($shift->id);
            abort_unless($lockedShift->user_id === $lockedTimesheet->user_id, 404);

            $originalStart = CarbonImmutable::instance($lockedShift->clocked_in_at)->utc();
            $originalEnd = CarbonImmutable::instance($lockedShift->clocked_out_at ?: now('UTC'))->utc();
            $this->assertPeriodsEditable($lockedTimesheet, [[$originalStart, $originalEnd], [$newStart, $newEnd]]);

            if ($lockedShift->breaks->contains(fn (ChatterBreak $break) =>
                $break->started_at->lt($newStart) || ($break->ended_at && $break->ended_at->gt($newEnd)))) {
                throw ValidationException::withMessages(['clocked_in_at' => __('The corrected shift must still contain all recorded breaks.')]);
            }

            $overlapExists = ChatterShift::query()
                ->where('user_id', $lockedShift->user_id)
                ->whereKeyNot($lockedShift->id)
                ->where('clocked_in_at', '<', $newEnd)
                ->where(fn (Builder $query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $newStart))
                ->exists();
            if ($overlapExists) {
                throw ValidationException::withMessages(['clocked_in_at' => __('The corrected shift overlaps another recorded shift.')]);
            }

            $originalShift = clone $lockedShift;
            $originalShift->setRelation('user', $lockedShift->user);
            $before = ['clocked_in_at' => $lockedShift->clocked_in_at?->toIso8601String(), 'clocked_out_at' => $lockedShift->clocked_out_at?->toIso8601String()];
            $lockedShift->forceFill([
                'clocked_in_at' => $newStart,
                'clocked_out_at' => $newEnd,
                'active_user_id' => null,
            ])->save();
            $this->audit($request, 'shift_corrected', $validated['reason'], $before, ['clocked_in_at' => $lockedShift->clocked_in_at->toIso8601String(), 'clocked_out_at' => $lockedShift->clocked_out_at->toIso8601String()], $lockedTimesheet, $lockedShift);

            return [$originalShift, $lockedShift->load('user')];
        });

        $payroll->refreshPeriodsTouchedBy($originalShift);
        $payroll->refreshPeriodsTouchedBy($shift);

        return back()->with('status', __('Shift times corrected and recorded in the audit history.'));
    }

    public function updateBreak(Request $request, ChatterTimesheet $timesheet, ChatterBreak $break, ChatterPayrollService $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'started_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ended_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:started_at'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $newStart = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['started_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc();
        $newEnd = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['ended_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc();

        $shift = DB::transaction(function () use ($request, $timesheet, $break, $validated, $newStart, $newEnd) {
            $lockedTimesheet = ChatterTimesheet::query()->with('user')->lockForUpdate()->findOrFail($timesheet->id);
            $lockedBreak = ChatterBreak::query()->lockForUpdate()->findOrFail($break->id);
            $lockedShift = ChatterShift::query()->with('user')->lockForUpdate()->findOrFail($lockedBreak->chatter_shift_id);
            abort_unless($lockedShift->user_id === $lockedTimesheet->user_id, 404);

            $shiftStart = CarbonImmutable::instance($lockedShift->clocked_in_at)->utc();
            $shiftEnd = CarbonImmutable::instance($lockedShift->clocked_out_at ?: now('UTC'))->utc();
            if ($newStart->lt($shiftStart) || $newEnd->gt($shiftEnd)) {
                throw ValidationException::withMessages(['started_at' => __('A break must remain inside its parent shift.')]);
            }

            $this->assertPeriodsEditable($lockedTimesheet, [
                [CarbonImmutable::instance($lockedBreak->started_at)->utc(), CarbonImmutable::instance($lockedBreak->ended_at ?: $shiftEnd)->utc()],
                [$newStart, $newEnd],
            ]);

            $overlapExists = ChatterBreak::query()
                ->where('chatter_shift_id', $lockedShift->id)
                ->whereKeyNot($lockedBreak->id)
                ->where('started_at', '<', $newEnd)
                ->where(fn (Builder $query) => $query->whereNull('ended_at')->orWhere('ended_at', '>', $newStart))
                ->exists();
            if ($overlapExists) {
                throw ValidationException::withMessages(['started_at' => __('The corrected break overlaps another break.')]);
            }

            $before = ['started_at' => $lockedBreak->started_at?->toIso8601String(), 'ended_at' => $lockedBreak->ended_at?->toIso8601String()];
            $lockedBreak->forceFill(['started_at' => $newStart, 'ended_at' => $newEnd, 'active_shift_id' => null])->save();
            $this->audit($request, 'break_corrected', $validated['reason'], $before, ['started_at' => $lockedBreak->started_at->toIso8601String(), 'ended_at' => $lockedBreak->ended_at->toIso8601String()], $lockedTimesheet, $lockedShift);

            return $lockedShift->load('user');
        });

        $payroll->refreshPeriodsTouchedBy($shift);

        return back()->with('status', __('Break times corrected and recorded in the audit history.'));
    }

    public function storeAdjustment(Request $request, ChatterTimesheet $timesheet, ChatterPayrollService $payroll): RedirectResponse
    {
        $this->assertEditable($timesheet);
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'between:-100000,100000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $adjustment = ChatterPayAdjustment::create([
            'chatter_timesheet_id' => $timesheet->id,
            'created_by' => $request->user()->id,
            'amount_pence' => (int) round(((float) $validated['amount']) * 100),
            'label' => $validated['label'],
            'note' => $validated['note'] ?? null,
        ]);
        $this->audit($request, 'pay_adjustment_added', $validated['note'] ?? $validated['label'], null, $adjustment->only(['label', 'amount_pence']), $timesheet);
        $payroll->refresh($timesheet);

        return back()->with('status', __('Pay adjustment added.'));
    }

    public function destroyAdjustment(Request $request, ChatterTimesheet $timesheet, ChatterPayAdjustment $adjustment, ChatterPayrollService $payroll): RedirectResponse
    {
        $this->assertEditable($timesheet);
        abort_unless($adjustment->chatter_timesheet_id === $timesheet->id, 404);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $before = $adjustment->only(['label', 'amount_pence', 'note']);
        $adjustment->delete();
        $this->audit($request, 'pay_adjustment_removed', $validated['reason'], $before, null, $timesheet);
        $payroll->refresh($timesheet);

        return back()->with('status', __('Pay adjustment removed and recorded in the audit history.'));
    }

    public function review(Request $request, ChatterTimesheet $timesheet, ChatterPayrollService $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'changes_requested', 'reject', 'reopen'])],
            'note' => [Rule::requiredIf($request->input('decision') !== 'approve'), 'nullable', 'string', 'max:1500'],
        ]);
        $decision = $validated['decision'];
        $timesheet = DB::transaction(function () use ($request, $timesheet, $payroll, $validated, $decision) {
            $lockedTimesheet = ChatterTimesheet::query()->with('user')->lockForUpdate()->findOrFail($timesheet->id);
            $before = ['status' => $lockedTimesheet->status, 'gross_pay_pence' => $lockedTimesheet->gross_pay_pence];

            if ($decision === 'reopen') {
                abort_unless($lockedTimesheet->status === ChatterTimesheet::STATUS_APPROVED, 422, 'Only an approved timesheet can be reopened.');
                $status = ChatterTimesheet::STATUS_SUBMITTED;
            } else {
                abort_unless($lockedTimesheet->status === ChatterTimesheet::STATUS_SUBMITTED, 422, 'Only a submitted timesheet can be reviewed.');

                if ($decision === 'approve') {
                    $periodEnd = CarbonImmutable::parse(
                        $lockedTimesheet->period_end->toDateString().' 23:59:59',
                        ChatterPayrollService::REPORTING_TIMEZONE,
                    );
                    abort_if($periodEnd->isFuture(), 422, 'This payroll week is not complete yet.');

                    $periodEndUtc = $periodEnd->addSecond()->utc();
                    $hasOpenShift = ChatterShift::query()
                        ->where('user_id', $lockedTimesheet->user_id)
                        ->whereNull('clocked_out_at')
                        ->where('clocked_in_at', '<', $periodEndUtc)
                        ->exists();
                    abort_if($hasOpenShift, 422, 'Clock out before approving this timesheet.');
                }

                $lockedTimesheet = $payroll->refresh($lockedTimesheet);
                $status = match ($decision) {
                    'approve' => ChatterTimesheet::STATUS_APPROVED,
                    'changes_requested' => ChatterTimesheet::STATUS_CHANGES_REQUESTED,
                    default => ChatterTimesheet::STATUS_REJECTED,
                };
            }

            $lockedTimesheet->forceFill([
                'status' => $status,
                'review_note' => $validated['note'] ?? null,
                'reviewed_by' => $decision === 'reopen' ? null : $request->user()->id,
                'reviewed_at' => $decision === 'reopen' ? null : now(),
            ])->save();
            $this->audit($request, 'timesheet_'.$decision, $validated['note'] ?? null, $before, ['status' => $status, 'gross_pay_pence' => $lockedTimesheet->gross_pay_pence], $lockedTimesheet);

            return $lockedTimesheet->fresh('user');
        });
        $status = $timesheet->status;

        $timesheet->user->notify(new SystemNotification(
            title: __('Timesheet :status', ['status' => str_replace('_', ' ', $status)]),
            body: $validated['note'] ?? __('Your weekly timesheet status was updated by the admin team.'),
            actionUrl: route('chatter.dashboard', absolute: false),
            category: 'chatter_timesheet_review',
        ));
        $this->emailChatter(
            $timesheet->user,
            __('Timesheet :status', ['status' => str_replace('_', ' ', $status)]),
            $validated['note'] ?? __('Your weekly timesheet status was updated by the admin team.'),
        );

        return back()->with('status', __('Timesheet review saved.'));
    }

    /** @return array<string, mixed> */
    private function validateAccount(Request $request): array
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $identity = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'lowercase', 'email:rfc', 'max:255', Rule::unique(User::class, 'email'), Rule::unique(ChatterRequest::class, 'email')],
            'timezone' => ['required', 'timezone'],
        ]);

        return $identity + $this->validatePaySettings($request);
    }

    /** @return array<string, mixed> */
    private function validatePaySettings(Request $request): array
    {
        $data = $request->validate([
            'base_hourly_rate' => ['required', 'numeric', 'between:0,1000'],
            'overtime_threshold_hours' => ['required', 'numeric', 'between:0,168'],
            'overtime_multiplier' => ['required', 'numeric', 'between:1,5'],
            'night_premium_multiplier' => ['required', 'numeric', 'between:1,5'],
            'weekend_premium_multiplier' => ['required', 'numeric', 'between:1,5'],
            'night_starts_at' => ['required', 'date_format:H:i'],
            'night_ends_at' => ['required', 'date_format:H:i'],
            'effective_from' => ['required', 'date'],
        ]);

        return [
            'base_rate_pence' => (int) round(((float) $data['base_hourly_rate']) * 100),
            'overtime_threshold_minutes' => (int) round(((float) $data['overtime_threshold_hours']) * 60),
            'overtime_multiplier_bps' => (int) round(((float) $data['overtime_multiplier']) * 10000),
            'night_premium_bps' => (int) round(((float) $data['night_premium_multiplier']) * 10000),
            'weekend_premium_bps' => (int) round(((float) $data['weekend_premium_multiplier']) * 10000),
            'night_starts_at' => $data['night_starts_at'],
            'night_ends_at' => $data['night_ends_at'],
            'effective_from' => $data['effective_from'],
        ];
    }

    /** @return array<string, string|int|null> */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                ChatterTimesheet::STATUS_DRAFT,
                ChatterTimesheet::STATUS_SUBMITTED,
                ChatterTimesheet::STATUS_CHANGES_REQUESTED,
                ChatterTimesheet::STATUS_APPROVED,
                ChatterTimesheet::STATUS_REJECTED,
            ])],
            'chatter_id' => ['nullable', 'integer', 'min:1'],
            'role_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $today = CarbonImmutable::now(ChatterPayrollService::REPORTING_TIMEZONE);
        $from = isset($validated['from'])
            ? CarbonImmutable::parse($validated['from'], ChatterPayrollService::REPORTING_TIMEZONE)
            : $today->startOfWeek(CarbonInterface::MONDAY)->subWeeks(11);
        $to = isset($validated['to'])
            ? CarbonImmutable::parse($validated['to'], ChatterPayrollService::REPORTING_TIMEZONE)
            : $today->endOfWeek(CarbonInterface::SUNDAY);

        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['to' => __('Reports are limited to a maximum range of 366 days.')]);
        }

        return [
            'search' => trim((string) ($validated['search'] ?? '')) ?: null,
            'status' => $validated['status'] ?? null,
            'chatter_id' => isset($validated['chatter_id']) ? (int) $validated['chatter_id'] : null,
            'role_id' => isset($validated['role_id']) ? (int) $validated['role_id'] : null,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    private function timesheetQuery(array $filters): Builder
    {
        return ChatterTimesheet::query()
            ->when($filters['search'], fn (Builder $query, string $search) => $query->whereHas('user', fn (Builder $userQuery) => $userQuery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['chatter_id'], fn (Builder $query, int $id) => $query->where('user_id', $id))
            ->when($filters['from'], fn (Builder $query, string $from) => $query->whereDate('period_end', '>=', $from))
            ->when($filters['to'], fn (Builder $query, string $to) => $query->whereDate('period_start', '<=', $to));
    }

    private function attendanceQuery(array $filters): Builder
    {
        return ChatterShift::query()
            ->when($filters['search'], fn (Builder $query, string $search) => $query->whereHas('user', fn (Builder $userQuery) => $userQuery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['chatter_id'], fn (Builder $query, int $id) => $query->where('user_id', $id))
            ->when($filters['role_id'], fn (Builder $query, int $id) => $query->where('chatter_work_role_id', $id))
            ->when($filters['from'], function (Builder $query, string $from) {
                $start = CarbonImmutable::parse($from, ChatterPayrollService::REPORTING_TIMEZONE)->startOfDay()->utc();
                $query->where(fn (Builder $inner) => $inner->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $start));
            })
            ->when($filters['to'], function (Builder $query, string $to) {
                $end = CarbonImmutable::parse($to, ChatterPayrollService::REPORTING_TIMEZONE)->addDay()->startOfDay()->utc();
                $query->where('clocked_in_at', '<', $end);
            });
    }

    private function assertChatter(User $user): void
    {
        abort_unless($user->isChatter(), 404);
    }

    private function assertEditable(ChatterTimesheet $timesheet): void
    {
        if ($timesheet->status === ChatterTimesheet::STATUS_APPROVED) {
            throw ValidationException::withMessages(['timesheet' => __('Reopen this approved timesheet before editing it.')]);
        }
    }

    /** @param array<int, array{0: CarbonImmutable, 1: CarbonImmutable}> $intervals */
    private function assertPeriodsEditable(ChatterTimesheet $routeTimesheet, array $intervals): void
    {
        $this->assertEditable($routeTimesheet);
        $periodStarts = collect();

        foreach ($intervals as [$start, $end]) {
            if ($end->lessThanOrEqualTo($start)) {
                continue;
            }

            $cursor = $this->periodStartFor($start);
            $last = $this->periodStartFor($end->subSecond());
            while ($cursor->lessThanOrEqualTo($last)) {
                $periodStarts->push($cursor->toDateString());
                $cursor = $cursor->addWeek();
            }
        }

        $periodStarts = $periodStarts->unique()->values();
        if (! $periodStarts->contains($routeTimesheet->period_start->toDateString())) {
            throw ValidationException::withMessages(['timesheet' => __('This record does not belong to the selected timesheet.')]);
        }

        $touchedTimesheets = ChatterTimesheet::query()
            ->where('user_id', $routeTimesheet->user_id)
            ->where(function (Builder $query) use ($periodStarts) {
                foreach ($periodStarts as $periodStart) {
                    $query->orWhereDate('period_start', $periodStart);
                }
            })
            ->lockForUpdate()
            ->get(['id', 'status']);

        if ($touchedTimesheets->contains('status', ChatterTimesheet::STATUS_APPROVED)) {
            throw ValidationException::withMessages([
                'timesheet' => __('Reopen every approved timesheet touched by this correction before editing recorded time.'),
            ]);
        }
    }

    private function periodStartFor(CarbonImmutable $moment): CarbonImmutable
    {
        return $moment
            ->timezone(ChatterPayrollService::REPORTING_TIMEZONE)
            ->startOfWeek(CarbonInterface::MONDAY)
            ->startOfDay();
    }

    private function audit(Request $request, string $action, ?string $reason, ?array $before, ?array $after, ?ChatterTimesheet $timesheet = null, ?ChatterShift $shift = null): void
    {
        ChatterTimeAudit::create([
            'chatter_shift_id' => $shift?->id,
            'chatter_timesheet_id' => $timesheet?->id,
            'actor_id' => $request->user()->id,
            'action' => $action,
            'reason' => $reason,
            'before' => $before,
            'after' => $after,
        ]);
    }

    private function emailChatter(User $chatter, string $heading, string $body): void
    {
        try {
            Mail::to($chatter->email)->queue(new ChatterWorkflowMail(
                subjectLine: $heading,
                heading: $heading,
                body: $body,
                actionUrl: route('chatter.dashboard'),
            ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
