<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Models\ChatterShift;
use App\Models\ChatterTimeAudit;
use App\Models\ChatterTimesheet;
use App\Services\AdminActivityNotifier;
use App\Services\ChatterPayrollService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChatterTimesheetController extends Controller
{
    public function submit(
        Request $request,
        ChatterTimesheet $timesheet,
        ChatterPayrollService $payroll,
        AdminActivityNotifier $notifier,
    ): RedirectResponse {
        $this->authorizeOwner($request, $timesheet);

        if (! in_array($timesheet->status, [ChatterTimesheet::STATUS_DRAFT, ChatterTimesheet::STATUS_CHANGES_REQUESTED], true)) {
            throw ValidationException::withMessages(['timesheet' => __('This timesheet cannot be submitted in its current status.')]);
        }

        $periodEnd = CarbonImmutable::parse($timesheet->period_end->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->endOfDay();
        if (CarbonImmutable::now(ChatterPayrollService::REPORTING_TIMEZONE)->lessThanOrEqualTo($periodEnd)) {
            throw ValidationException::withMessages(['timesheet' => __('The week must finish before this timesheet can be submitted.')]);
        }

        if (ChatterShift::query()->where('user_id', $request->user()->id)->whereNull('clocked_out_at')->exists()) {
            throw ValidationException::withMessages(['timesheet' => __('Clock out before submitting a timesheet.')]);
        }

        $timesheet = $payroll->refresh($timesheet);
        $timesheet->forceFill([
            'status' => ChatterTimesheet::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'review_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ])->save();
        $this->audit($timesheet, $request, 'timesheet_submitted', null);

        $notifier->notify(
            title: __('Chatter timesheet submitted'),
            body: __(':name submitted working hours for :period.', ['name' => $request->user()->name, 'period' => $timesheet->period_start->format('j M').' - '.$timesheet->period_end->format('j M Y')]),
            actionUrl: route('admin.chatter-hours.timesheets.show', $timesheet, false),
            category: 'chatter_timesheet_submitted',
            actionLabel: __('Review timesheet'),
        );

        return back()->with('status', __('Your timesheet was submitted for review.'));
    }

    public function requestCorrection(Request $request, ChatterTimesheet $timesheet, AdminActivityNotifier $notifier): RedirectResponse
    {
        $this->authorizeOwner($request, $timesheet);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        if (! in_array($timesheet->status, [ChatterTimesheet::STATUS_SUBMITTED, ChatterTimesheet::STATUS_APPROVED], true)) {
            throw ValidationException::withMessages(['timesheet' => __('A correction can only be requested for a submitted or approved timesheet.')]);
        }

        $before = ['status' => $timesheet->status, 'review_note' => $timesheet->review_note];
        $timesheet->forceFill(['review_note' => $validated['reason']])->save();
        $this->audit($timesheet, $request, 'correction_requested', $validated['reason'], $before, ['status' => $timesheet->status, 'review_note' => $timesheet->review_note]);

        $notifier->notify(
            title: __('Timesheet correction requested'),
            body: __(':name requested a correction to a timesheet.', ['name' => $request->user()->name]),
            actionUrl: route('admin.chatter-hours.timesheets.show', $timesheet, false),
            category: 'chatter_timesheet_correction',
            details: ['Reason' => $validated['reason']],
            actionLabel: __('Review correction'),
        );

        return back()->with('status', __('Your correction request was sent to the admin team.'));
    }

    private function authorizeOwner(Request $request, ChatterTimesheet $timesheet): void
    {
        abort_unless((int) $timesheet->user_id === (int) $request->user()->id, 403);
    }

    private function audit(ChatterTimesheet $timesheet, Request $request, string $action, ?string $reason, ?array $before = null, ?array $after = null): void
    {
        ChatterTimeAudit::create([
            'chatter_timesheet_id' => $timesheet->id,
            'actor_id' => $request->user()->id,
            'action' => $action,
            'reason' => $reason,
            'before' => $before,
            'after' => $after,
        ]);
    }
}
