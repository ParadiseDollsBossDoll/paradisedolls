<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RefreshChatterPayrollForShift;
use App\Mail\ChatterWorkflowMail;
use App\Models\ChatterBreak;
use App\Models\ChatterModelAssignment;
use App\Models\ChatterModelReview;
use App\Models\ChatterPayAdjustment;
use App\Models\ChatterProfile;
use App\Models\ChatterRequest;
use App\Models\ChatterRoleAssignment;
use App\Models\ChatterShift;
use App\Models\ChatterTimeAudit;
use App\Models\ChatterTimesheet;
use App\Models\ChatterWorkRole;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\ChatterAccountService;
use App\Services\ChatterPayrollService;
use App\Services\UserSessionService;
use App\Support\ChatterCurrency;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
                'courseEnrollments.course:id,title,slug,platform_label,is_published,sort_order',
                'chatterShifts' => fn ($query) => $query->whereNull('clocked_out_at')->with(['breaks', 'workRole', 'model:id,name,email']),
                'activeAssignedModels:id,name,email',
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
        $timezoneOptions = $this->timezoneOptions();
        $courseOptions = Course::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'platform_label', 'sort_order']);
        $modelOptions = User::query()
            ->where('role', 'model')
            ->whereHas('modelProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $openShifts = ChatterShift::query()
            ->whereNull('clocked_out_at')
            ->with(['user', 'breaks', 'workRole', 'model:id,name,email'])
            ->latest('clocked_in_at')
            ->get();
        $stats = [
            'chatters' => User::query()->where('role', 'chatter')->count(),
            'working' => $openShifts->count(),
            'on_break' => $openShifts->filter(fn (ChatterShift $shift) => $shift->breaks->contains(fn ($break) => $break->ended_at === null))->count(),
            'overdue' => $openShifts->filter(fn (ChatterShift $shift) => $shift->clocked_in_at->lt(now()->subHours(16)))->count(),
            'requests' => ChatterRequest::query()->where('status', ChatterRequest::STATUS_PENDING)->count(),
        ];
        $mode = 'accounts';

        return view('admin.chatter-hours.index', compact(
            'chatters', 'requests', 'openShifts', 'workRoles', 'timezoneOptions', 'courseOptions', 'modelOptions', 'stats', 'mode'
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
        $openShifts = ChatterShift::query()->whereNull('clocked_out_at')->with(['user', 'breaks', 'workRole', 'model:id,name,email'])->get();
        $attendanceWindow = $this->attendanceWindow($filters);
        $attendanceShiftResults = $this->attendanceQuery($filters)
            ->with(['user', 'breaks', 'workRole', 'model:id,name,email'])
            ->latest('clocked_in_at')
            ->get()
            ->map(fn (ChatterShift $shift) => $payroll->annotateShiftSegment($shift, $attendanceWindow['start'], $attendanceWindow['end']))
            ->filter()
            ->filter(fn (ChatterShift $shift): bool => (int) $shift->getAttribute('worked_minutes') > 0)
            ->sortByDesc(fn (ChatterShift $shift) => $shift->getAttribute('segment_clocked_in_at')?->getTimestamp() ?? 0)
            ->values();
        $attendancePage = LengthAwarePaginator::resolveCurrentPage('attendance_page');
        $attendanceShifts = new LengthAwarePaginator(
            $attendanceShiftResults->forPage($attendancePage, 20)->values(),
            $attendanceShiftResults->count(),
            20,
            $attendancePage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'attendance_page',
            ],
        );

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
            'pending' => ChatterTimesheet::query()->whereWorkflowStatus(ChatterTimesheet::WORKFLOW_READY_FOR_REVIEW)->count(),
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

    public function updateTimezone(Request $request, User $chatter): RedirectResponse
    {
        $this->assertChatter($chatter);
        $validated = $request->validate([
            'timezone' => ['required', 'timezone'],
        ]);

        DB::transaction(function () use ($request, $chatter, $validated): void {
            $lockedChatter = User::query()->lockForUpdate()->findOrFail($chatter->id);
            $profile = $lockedChatter->chatterProfile()->lockForUpdate()->firstOrFail();
            $before = ['timezone' => $profile->timezone];

            $profile->forceFill(['timezone' => $validated['timezone']])->save();

            ChatterTimeAudit::create([
                'actor_id' => $request->user()->id,
                'action' => 'chatter_timezone_updated',
                'reason' => __('Timezone updated from the chatter account manager.'),
                'before' => $before,
                'after' => [
                    'chatter_id' => $chatter->id,
                    'timezone' => $profile->timezone,
                ],
            ]);
        });

        return back()->with('status', __('Chatter timezone updated.'));
    }

    public function updateStatus(
        Request $request,
        User $chatter,
        UserSessionService $sessions
    ): RedirectResponse {
        $this->assertChatter($chatter);
        $validated = $request->validate(['employment_status' => ['required', Rule::in([ChatterProfile::STATUS_ACTIVE, ChatterProfile::STATUS_SUSPENDED])]]);

        $closedShift = DB::transaction(function () use ($request, $chatter, $validated): ?ChatterShift {
            $lockedChatter = User::query()->lockForUpdate()->findOrFail($chatter->id);
            $profile = $lockedChatter->chatterProfile()->lockForUpdate()->firstOrFail();
            $before = ['employment_status' => $profile->employment_status];
            $profile->forceFill([
                'employment_status' => $validated['employment_status'],
                'suspended_at' => $validated['employment_status'] === ChatterProfile::STATUS_SUSPENDED ? now() : null,
            ])->save();

            $closedShift = null;

            if ($validated['employment_status'] === ChatterProfile::STATUS_SUSPENDED) {
                $lockedChatter->forceFill([
                    'auth_session_version' => (int) $lockedChatter->auth_session_version + 1,
                    'remember_token' => Str::random(60),
                ])->save();

                $shift = ChatterShift::query()
                    ->where('active_user_id', $lockedChatter->id)
                    ->with(['breaks', 'model:id,name,email'])
                    ->lockForUpdate()
                    ->first();
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
                    $closedShift = $shift->load('user');
                }
            }

            ChatterTimeAudit::create([
                'actor_id' => $request->user()->id,
                'action' => 'chatter_status_updated',
                'reason' => $request->string('reason')->toString() ?: null,
                'before' => $before,
                'after' => ['employment_status' => $profile->employment_status, 'chatter_id' => $chatter->id],
            ]);

            return $closedShift;
        });

        if ($validated['employment_status'] === ChatterProfile::STATUS_SUSPENDED) {
            $sessions->revokeAll($chatter);
        }

        if ($closedShift) {
            RefreshChatterPayrollForShift::dispatch($closedShift->id)->afterResponse();
        }

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
            'work_role_id' => ['nullable', 'integer', Rule::exists(ChatterWorkRole::class, 'id')->where('is_active', true)],
            'work_role_name' => ['required', 'string', 'max:80'],
            'hourly_rate' => ['required', 'numeric', 'between:0,1000'],
            'is_active' => ['required', 'boolean'],
        ]);
        $previousRoleId = isset($data['work_role_id']) ? (int) $data['work_role_id'] : null;
        $workRole = $this->resolveEditableWorkRole($previousRoleId, $data['work_role_name']);

        $assignment = DB::transaction(function () use ($request, $chatter, $data, $previousRoleId, $workRole): ChatterRoleAssignment {
            $previousAssignment = $previousRoleId
                ? ChatterRoleAssignment::query()
                    ->where('user_id', $chatter->id)
                    ->where('chatter_work_role_id', $previousRoleId)
                    ->with('workRole')
                    ->lockForUpdate()
                    ->first()
                : null;

            if ($previousAssignment && $previousRoleId !== $workRole->id) {
                $previousAssignment->delete();
            }

            $assignment = ChatterRoleAssignment::query()->updateOrCreate(
                ['user_id' => $chatter->id, 'chatter_work_role_id' => $workRole->id],
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
                'before' => $previousAssignment ? [
                    'chatter_id' => $chatter->id,
                    'work_role_id' => $previousAssignment->chatter_work_role_id,
                    'work_role_name' => $previousAssignment->workRole?->name,
                    'hourly_rate_pence' => $previousAssignment->hourly_rate_pence,
                    'is_active' => $previousAssignment->is_active,
                ] : null,
                'after' => [
                    'chatter_id' => $chatter->id,
                    'work_role_id' => $assignment->chatter_work_role_id,
                    'work_role_name' => $workRole->name,
                    'hourly_rate_pence' => $assignment->hourly_rate_pence,
                    'is_active' => $assignment->is_active,
                ],
            ]);

            return $assignment;
        });

        return back()->with('status', __('Work role and hourly rate saved. New shifts will use this rate.'));
    }

    public function storeModelAssignment(Request $request, User $chatter): RedirectResponse
    {
        $this->assertChatter($chatter);
        $validated = $request->validate([
            'model_id' => ['required', Rule::exists(User::class, 'id')->where(fn ($query) => $query->where('role', 'model'))],
        ]);

        $model = User::query()
            ->where('role', 'model')
            ->whereHas('modelProfile')
            ->findOrFail($validated['model_id']);

        $alreadyAssigned = ChatterModelAssignment::query()
            ->where('chatter_id', $chatter->id)
            ->where('model_id', $model->id)
            ->whereNull('ended_at')
            ->exists();

        if ($alreadyAssigned) {
            return back()->with('status', __('That model is already assigned to this chatter.'));
        }

        ChatterModelAssignment::create([
            'chatter_id' => $chatter->id,
            'model_id' => $model->id,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        return back()->with('status', __(':model was added to :chatter weekly review list.', [
            'model' => $model->name,
            'chatter' => $chatter->name,
        ]));
    }

    public function grantCourseAccess(Request $request, User $chatter): RedirectResponse
    {
        $this->assertChatter($chatter);
        $validated = $request->validate([
            'course_id' => ['required', Rule::exists(Course::class, 'id')->where('is_published', true)],
        ]);

        CourseEnrollment::query()->firstOrCreate([
            'course_id' => (int) $validated['course_id'],
            'user_id' => $chatter->id,
        ], [
            'enrolled_at' => now(),
        ]);

        return back()->with('status', __('Course access unlocked for :chatter.', ['chatter' => $chatter->name]));
    }

    public function revokeCourseAccess(Request $request, User $chatter, Course $course): RedirectResponse
    {
        $this->assertChatter($chatter);

        CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->where('user_id', $chatter->id)
            ->get()
            ->each(fn (CourseEnrollment $enrollment) => $enrollment->delete());

        return back()->with('status', __('Course access revoked for :chatter.', ['chatter' => $chatter->name]));
    }

    public function destroyModelAssignment(Request $request, User $chatter, User $model): RedirectResponse
    {
        $this->assertChatter($chatter);
        abort_unless($model->role === 'model', 404);

        $assignment = ChatterModelAssignment::query()
            ->where('chatter_id', $chatter->id)
            ->where('model_id', $model->id)
            ->whereNull('ended_at')
            ->latest('assigned_at')
            ->first();

        if (! $assignment) {
            return back()->withErrors(['model_id' => __('That model is not currently assigned to this chatter.')]);
        }

        $assignment->forceFill(['ended_at' => now()])->save();

        return back()->with('status', __(':model was removed from :chatter weekly review list.', [
            'model' => $model->name,
            'chatter' => $chatter->name,
        ]));
    }

    public function showTimesheet(ChatterTimesheet $timesheet, ChatterCurrency $currency, ChatterPayrollService $payroll): View
    {
        $timesheet->load(['user.chatterProfile', 'reviewer', 'adjustments.creator', 'audits.actor']);
        $window = $payroll->reportingWindowFor($timesheet->period_start, $timesheet->period_end);
        $shifts = ChatterShift::query()
            ->where('user_id', $timesheet->user_id)
            ->where('clocked_in_at', '<', $window['end'])
            ->where(fn ($query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $window['start']))
            ->with(['breaks', 'audits.actor', 'workRole', 'model:id,name,email'])
            ->orderBy('clocked_in_at')
            ->get()
            ->map(fn (ChatterShift $shift) => $payroll->annotateShiftSegment($shift, $window['start'], $window['end']))
            ->filter()
            ->filter(fn (ChatterShift $shift): bool => (int) $shift->getAttribute('worked_minutes') > 0)
            ->values();

        $usdToPhpRate = $currency->rateForTimesheet($timesheet);
        $grossPayPhpCentavos = $currency->phpCentavosForTimesheet($timesheet);
        $missingModelReviews = $this->missingWeeklyModelReviewCount($timesheet);

        return view('admin.chatter-hours.show', compact('timesheet', 'shifts', 'currency', 'usdToPhpRate', 'grossPayPhpCentavos', 'missingModelReviews'));
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

            if ($lockedShift->breaks->contains(fn (ChatterBreak $break) => $break->started_at->lt($newStart) || ($break->ended_at && $break->ended_at->gt($newEnd)))) {
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
                $status = ChatterTimesheet::STATUS_DRAFT;
            } elseif ($decision === 'approve') {
                abort_unless(in_array($lockedTimesheet->status, [
                    ChatterTimesheet::STATUS_DRAFT,
                    ChatterTimesheet::STATUS_SUBMITTED,
                    ChatterTimesheet::STATUS_CHANGES_REQUESTED,
                ], true), 422, 'This timesheet cannot be approved in its current state.');
                abort_unless($lockedTimesheet->periodHasEnded(), 422, 'This payroll week is not complete yet.');

                $missingModelReviews = $this->missingWeeklyModelReviewCount($lockedTimesheet);
                if ($missingModelReviews > 0) {
                    throw ValidationException::withMessages([
                        'timesheet' => trans_choice(
                            'Complete the required weekly model review before approving payroll. :count review is still missing.|Complete the required weekly model reviews before approving payroll. :count reviews are still missing.',
                            $missingModelReviews,
                            ['count' => $missingModelReviews],
                        ),
                    ]);
                }

                $lockedTimesheet = $payroll->refresh($lockedTimesheet);
                $status = ChatterTimesheet::STATUS_APPROVED;
            } else {
                // Retain legacy decisions for existing internal records, but the
                // simplified admin UI no longer creates these workflow states.
                abort_unless($lockedTimesheet->status === ChatterTimesheet::STATUS_SUBMITTED, 422, 'Only a legacy submitted timesheet can receive this decision.');
                $lockedTimesheet = $payroll->refresh($lockedTimesheet);
                $status = $decision === 'changes_requested'
                    ? ChatterTimesheet::STATUS_CHANGES_REQUESTED
                    : ChatterTimesheet::STATUS_REJECTED;
            }

            $lockedTimesheet->forceFill([
                'status' => $status,
                'review_note' => $validated['note'] ?? null,
                'reviewed_by' => $decision === 'reopen' ? null : $request->user()->id,
                'reviewed_at' => $decision === 'reopen' ? null : now(),
                'submitted_at' => $decision === 'reopen' ? null : $lockedTimesheet->submitted_at,
            ])->save();
            $this->audit($request, 'timesheet_'.$decision, $validated['note'] ?? null, $before, ['status' => $status, 'gross_pay_pence' => $lockedTimesheet->gross_pay_pence], $lockedTimesheet);

            return $lockedTimesheet->fresh('user');
        });
        $status = $timesheet->workflowStatusLabel();

        $timesheet->user->notify(new SystemNotification(
            title: __('Timesheet :status', ['status' => $status]),
            body: $validated['note'] ?? __('Your weekly timesheet status was updated by the admin team.'),
            actionUrl: route('chatter.dashboard', absolute: false),
            category: 'chatter_timesheet_review',
        ));
        $this->emailChatter(
            $timesheet->user,
            __('Timesheet :status', ['status' => $status]),
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
            'work_role_id' => ['nullable', 'integer', Rule::exists(ChatterWorkRole::class, 'id')->where('is_active', true)],
            'work_role_name' => ['required_without:work_role_id', 'nullable', 'string', 'max:80'],
        ]);
        $workRole = $this->resolveWorkRole($data['work_role_id'] ?? null, $data['work_role_name'] ?? null);

        return [
            'base_rate_pence' => (int) round(((float) $data['base_hourly_rate']) * 100),
            'overtime_threshold_minutes' => 10080,
            'overtime_multiplier_bps' => 10000,
            'night_premium_bps' => 10000,
            'weekend_premium_bps' => 10000,
            'night_starts_at' => '00:00',
            'night_ends_at' => '00:00',
            'effective_from' => now(ChatterPayrollService::REPORTING_TIMEZONE)->toDateString(),
            'work_role_id' => $workRole->id,
        ];
    }

    private function resolveWorkRole(mixed $workRoleId, ?string $workRoleName): ChatterWorkRole
    {
        if ($workRoleId) {
            return ChatterWorkRole::query()
                ->whereKey((int) $workRoleId)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $name = trim((string) $workRoleName);
        if ($name === '') {
            throw ValidationException::withMessages(['work_role_name' => __('Enter a role for this chatter.')]);
        }

        $baseSlug = Str::slug($name) ?: 'work-role';
        $slug = $baseSlug;
        $counter = 2;
        while (ChatterWorkRole::query()->where('slug', $slug)->exists()) {
            $existing = ChatterWorkRole::query()->where('slug', $slug)->first();
            if ($existing && strcasecmp($existing->name, $name) === 0) {
                if (! $existing->is_active) {
                    $existing->forceFill(['is_active' => true])->save();
                }

                return $existing;
            }
            $slug = $baseSlug.'-'.$counter++;
        }

        return ChatterWorkRole::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
            'sort_order' => ((int) ChatterWorkRole::query()->max('sort_order')) + 10,
        ]);
    }

    private function resolveEditableWorkRole(?int $workRoleId, ?string $workRoleName): ChatterWorkRole
    {
        $name = trim((string) $workRoleName);
        if ($name === '') {
            throw ValidationException::withMessages(['work_role_name' => __('Enter a role for this chatter.')]);
        }

        if ($workRoleId) {
            $currentRole = ChatterWorkRole::query()
                ->whereKey($workRoleId)
                ->where('is_active', true)
                ->firstOrFail();

            if (strcasecmp($currentRole->name, $name) === 0) {
                return $currentRole;
            }
        }

        return $this->resolveWorkRole(null, $name);
    }

    /** @return Collection<int, array{value: string, label: string, search: string}> */
    private function timezoneOptions(): Collection
    {
        $now = CarbonImmutable::now('UTC');

        return collect(DateTimeZone::listIdentifiers())
            ->map(function (string $timezone) use ($now): array {
                $offset = $now->setTimezone($timezone)->getOffset();
                $label = $this->friendlyTimezoneLabel($timezone, $offset);

                return [
                    'value' => $timezone,
                    'label' => $label,
                    'search' => $label.' '.$timezone.' '.str_replace(['/', '_'], ' ', $timezone),
                ];
            })
            ->sortBy([
                fn (array $left, array $right) => $this->timezoneOffset($left['value']) <=> $this->timezoneOffset($right['value']),
                fn (array $left, array $right) => $left['value'] <=> $right['value'],
            ])
            ->values();
    }

    private function friendlyTimezoneLabel(string $timezone, int $offset): string
    {
        $friendlyGroups = [
            '+01:00' => 'Central European Time (CET), West Africa Time (WAT)',
            '+02:00' => 'Eastern European Time (EET), Central Africa Time (CAT)',
            '+03:00' => 'Moscow Time (MSK), East Africa Time (EAT), Arabia Standard Time (AST)',
            '+03:30' => 'Iran Standard Time (IRST)',
            '+04:00' => 'Gulf Standard Time (GST), Georgia, Armenia',
            '+04:30' => 'Afghanistan Time (AFT)',
            '+05:00' => 'Pakistan Standard Time (PKT), Yekaterinburg Time (YEKT)',
            '+05:30' => 'Indian Standard Time (IST), Sri Lanka Standard Time (SLST)',
            '+05:45' => 'Nepal Time (NPT)',
            '+06:00' => 'Bangladesh Standard Time (BST), Bhutan Time (BTT)',
            '+06:30' => 'Myanmar Time (MMT), Cocos Islands',
            '+07:00' => 'Indochina Time (ICT), Western Indonesian Time (WIB)',
        ];

        $offsetLabel = $this->formatUtcOffset($offset);
        $name = $friendlyGroups[$offsetLabel] ?? str_replace(['/', '_'], [' / ', ' '], $timezone);

        return "UTC{$offsetLabel}: {$name} - {$timezone}";
    }

    private function timezoneOffset(string $timezone): int
    {
        return CarbonImmutable::now('UTC')->setTimezone($timezone)->getOffset();
    }

    private function formatUtcOffset(int $offset): string
    {
        $sign = $offset >= 0 ? '+' : '-';
        $offset = abs($offset);
        $hours = intdiv($offset, 3600);
        $minutes = intdiv($offset % 3600, 60);

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }

    /** @return array<string, string|int|null> */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                ChatterTimesheet::WORKFLOW_IN_PROGRESS,
                ChatterTimesheet::WORKFLOW_READY_FOR_REVIEW,
                ChatterTimesheet::WORKFLOW_APPROVED,
                ChatterTimesheet::WORKFLOW_CLOSED,
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
            ->where(function (Builder $query) {
                $query->where('ordinary_minutes', '>', 0)
                    ->orWhere('gross_pay_pence', '!=', 0)
                    ->orWhere('adjustment_pence', '!=', 0)
                    ->orWhereHas('adjustments', fn (Builder $adjustments) => $adjustments->where('amount_pence', '!=', 0));
            })
            ->when($filters['search'], fn (Builder $query, string $search) => $query->whereHas('user', fn (Builder $userQuery) => $userQuery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['status'], function (Builder $query, string $status) {
                if (in_array($status, [
                    ChatterTimesheet::WORKFLOW_IN_PROGRESS,
                    ChatterTimesheet::WORKFLOW_READY_FOR_REVIEW,
                    ChatterTimesheet::WORKFLOW_APPROVED,
                    ChatterTimesheet::WORKFLOW_CLOSED,
                ], true)) {
                    $query->whereWorkflowStatus($status);

                    return;
                }

                $query->where('status', $status);
            })
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

    /** @return array{start: CarbonImmutable, end: CarbonImmutable} */
    private function attendanceWindow(array $filters): array
    {
        return [
            'start' => CarbonImmutable::parse($filters['from'], ChatterPayrollService::REPORTING_TIMEZONE)->startOfDay()->utc(),
            'end' => CarbonImmutable::parse($filters['to'], ChatterPayrollService::REPORTING_TIMEZONE)->addDay()->startOfDay()->utc(),
        ];
    }

    private function assertChatter(User $user): void
    {
        abort_unless($user->isChatter(), 404);
    }

    private function missingWeeklyModelReviewCount(ChatterTimesheet $timesheet): int
    {
        $timesheet->loadMissing('user');
        $requiredModelIds = $timesheet->user
            ->activeAssignedModels()
            ->whereHas('modelProfile')
            ->pluck('users.id');

        if ($requiredModelIds->isEmpty()) {
            return 0;
        }

        $submittedModelIds = ChatterModelReview::query()
            ->where('chatter_id', $timesheet->user_id)
            ->whereDate('week_ending', $timesheet->period_end->toDateString())
            ->whereIn('model_id', $requiredModelIds)
            ->pluck('model_id');

        return $requiredModelIds->diff($submittedModelIds)->count();
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
