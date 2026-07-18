<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ChatterWorkflowMail;
use App\Models\ChatterBreak;
use App\Models\ChatterPayAdjustment;
use App\Models\ChatterPayRate;
use App\Models\ChatterProfile;
use App\Models\ChatterRequest;
use App\Models\ChatterShift;
use App\Models\ChatterTimeAudit;
use App\Models\ChatterTimesheet;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\ChatterAccountService;
use App\Services\ChatterPayrollService;
use Carbon\CarbonImmutable;
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
        $filters = $this->filters($request);
        $chatters = User::query()
            ->where('role', 'chatter')
            ->with(['chatterProfile', 'chatterPayRates' => fn ($query) => $query->latest('effective_from'), 'chatterShifts' => fn ($query) => $query->whereNull('clocked_out_at')->with('breaks')])
            ->when($filters['search'], fn (Builder $query, string $search) => $query->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(12, ['*'], 'chatters_page')
            ->withQueryString();

        $timesheets = $this->timesheetQuery($filters)
            ->with(['user.chatterProfile', 'reviewer'])
            ->latest('period_start')
            ->paginate(15, ['*'], 'timesheets_page')
            ->withQueryString();

        $requests = ChatterRequest::query()
            ->with('reviewer')
            ->latest()
            ->limit(20)
            ->get();

        $chatterOptions = User::query()
            ->where('role', 'chatter')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $openShifts = ChatterShift::query()->whereNull('clocked_out_at')->with(['user', 'breaks'])->get();
        $reportTotals = $this->timesheetQuery($filters)
            ->selectRaw('COALESCE(SUM(ordinary_minutes), 0) as total_minutes, COALESCE(SUM(gross_pay_pence), 0) as gross_pay_pence')
            ->first();
        $stats = [
            'chatters' => User::query()->where('role', 'chatter')->count(),
            'working' => $openShifts->count(),
            'on_break' => $openShifts->filter(fn (ChatterShift $shift) => $shift->breaks->contains(fn ($break) => $break->ended_at === null))->count(),
            'overdue' => $openShifts->filter(fn (ChatterShift $shift) => $shift->clocked_in_at->lt(now()->subHours(16)))->count(),
            'pending' => ChatterTimesheet::query()->whereIn('status', [ChatterTimesheet::STATUS_SUBMITTED, ChatterTimesheet::STATUS_CHANGES_REQUESTED])->count(),
            'requests' => ChatterRequest::query()->where('status', ChatterRequest::STATUS_PENDING)->count(),
            'total_minutes' => (int) ($reportTotals?->total_minutes ?? 0),
            'gross_pay_pence' => (int) ($reportTotals?->gross_pay_pence ?? 0),
        ];

        return view('admin.chatter-hours.index', compact('chatters', 'chatterOptions', 'timesheets', 'requests', 'openShifts', 'stats', 'filters'));
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
                    $now = now('UTC')->startOfMinute();
                    $shift->breaks()->whereNull('ended_at')->update(['ended_at' => $now, 'active_shift_id' => null]);
                    $shift->forceFill(['clocked_out_at' => $now, 'active_user_id' => null])->save();
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

    public function showTimesheet(ChatterTimesheet $timesheet, ChatterPayrollService $payroll): View
    {
        if ($timesheet->status !== ChatterTimesheet::STATUS_APPROVED) {
            $timesheet = $payroll->refresh($timesheet);
        }

        $timesheet->load(['user.chatterProfile', 'reviewer', 'adjustments.creator', 'audits.actor']);
        $startUtc = CarbonImmutable::parse($timesheet->period_start->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->utc();
        $endUtc = CarbonImmutable::parse($timesheet->period_end->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->addDay()->utc();
        $shifts = ChatterShift::query()
            ->where('user_id', $timesheet->user_id)
            ->where('clocked_in_at', '<', $endUtc)
            ->where(fn ($query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $startUtc))
            ->with(['breaks', 'audits.actor'])
            ->orderBy('clocked_in_at')
            ->get();

        return view('admin.chatter-hours.show', compact('timesheet', 'shifts'));
    }

    public function updateShift(Request $request, ChatterTimesheet $timesheet, ChatterShift $shift, ChatterPayrollService $payroll): RedirectResponse
    {
        $this->assertEditable($timesheet);
        abort_unless($shift->user_id === $timesheet->user_id, 404);
        $validated = $request->validate([
            'clocked_in_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'clocked_out_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:clocked_in_at'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $originalShift = clone $shift;
        $originalShift->setRelation('user', $timesheet->user);
        $before = ['clocked_in_at' => $shift->clocked_in_at?->toIso8601String(), 'clocked_out_at' => $shift->clocked_out_at?->toIso8601String()];
        $shift->forceFill([
            'clocked_in_at' => CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['clocked_in_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc(),
            'clocked_out_at' => CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['clocked_out_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc(),
            'active_user_id' => null,
        ])->save();
        $this->audit($request, 'shift_corrected', $validated['reason'], $before, ['clocked_in_at' => $shift->clocked_in_at->toIso8601String(), 'clocked_out_at' => $shift->clocked_out_at->toIso8601String()], $timesheet, $shift);
        $payroll->refreshPeriodsTouchedBy($originalShift);
        $payroll->refreshPeriodsTouchedBy($shift->load('user'));

        return back()->with('status', __('Shift times corrected and recorded in the audit history.'));
    }

    public function updateBreak(Request $request, ChatterTimesheet $timesheet, ChatterBreak $break, ChatterPayrollService $payroll): RedirectResponse
    {
        $this->assertEditable($timesheet);
        abort_unless($break->shift()->where('user_id', $timesheet->user_id)->exists(), 404);
        $validated = $request->validate([
            'started_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ended_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:started_at'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $before = ['started_at' => $break->started_at?->toIso8601String(), 'ended_at' => $break->ended_at?->toIso8601String()];
        $break->forceFill([
            'started_at' => CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['started_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc(),
            'ended_at' => CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['ended_at'], ChatterPayrollService::REPORTING_TIMEZONE)->utc(),
            'active_shift_id' => null,
        ])->save();
        $this->audit($request, 'break_corrected', $validated['reason'], $before, ['started_at' => $break->started_at->toIso8601String(), 'ended_at' => $break->ended_at->toIso8601String()], $timesheet, $break->shift);
        $payroll->refresh($timesheet);

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
        $before = ['status' => $timesheet->status, 'gross_pay_pence' => $timesheet->gross_pay_pence];

        if ($decision === 'reopen') {
            abort_unless($timesheet->status === ChatterTimesheet::STATUS_APPROVED, 422);
            $status = ChatterTimesheet::STATUS_SUBMITTED;
        } else {
            abort_if($timesheet->status === ChatterTimesheet::STATUS_APPROVED, 422, 'Reopen the approved timesheet before changing its decision.');
            $timesheet = $payroll->refresh($timesheet);
            $status = match ($decision) {
                'approve' => ChatterTimesheet::STATUS_APPROVED,
                'changes_requested' => ChatterTimesheet::STATUS_CHANGES_REQUESTED,
                default => ChatterTimesheet::STATUS_REJECTED,
            };
        }

        $timesheet->forceFill([
            'status' => $status,
            'review_note' => $validated['note'] ?? null,
            'reviewed_by' => $decision === 'reopen' ? null : $request->user()->id,
            'reviewed_at' => $decision === 'reopen' ? null : now(),
        ])->save();
        $this->audit($request, 'timesheet_'.$decision, $validated['note'] ?? null, $before, ['status' => $status, 'gross_pay_pence' => $timesheet->gross_pay_pence], $timesheet);

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

    /** @return array<string, string|null> */
    private function filters(Request $request): array
    {
        return [
            'search' => trim($request->string('search')->toString()) ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'chatter_id' => $request->integer('chatter_id') ?: null,
            'from' => $request->date('from')?->toDateString(),
            'to' => $request->date('to')?->toDateString(),
        ];
    }

    private function timesheetQuery(array $filters): Builder
    {
        return ChatterTimesheet::query()
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['chatter_id'], fn (Builder $query, int $id) => $query->where('user_id', $id))
            ->when($filters['from'], fn (Builder $query, string $from) => $query->whereDate('period_end', '>=', $from))
            ->when($filters['to'], fn (Builder $query, string $to) => $query->whereDate('period_start', '<=', $to));
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
