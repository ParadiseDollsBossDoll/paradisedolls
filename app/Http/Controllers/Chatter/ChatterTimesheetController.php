<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Models\ChatterTimeAudit;
use App\Models\ChatterTimesheet;
use App\Services\AdminActivityNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChatterTimesheetController extends Controller
{
    public function reportProblem(Request $request, ChatterTimesheet $timesheet, AdminActivityNotifier $notifier): RedirectResponse
    {
        $this->authorizeOwner($request, $timesheet);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        if (! $timesheet->periodHasEnded()) {
            throw ValidationException::withMessages(['timesheet' => __('Wait until the payroll week has finished before reporting a timesheet problem.')]);
        }

        $this->audit(
            $timesheet,
            $request,
            'timesheet_problem_reported',
            $validated['reason'],
            ['status' => $timesheet->status],
            ['status' => $timesheet->status],
        );

        $notifier->notify(
            title: __('Timesheet problem reported'),
            body: __(':name reported a problem with a timesheet.', ['name' => $request->user()->name]),
            actionUrl: route('admin.chatter-hours.timesheets.show', $timesheet, false),
            category: 'chatter_timesheet_problem',
            details: ['Reason' => $validated['reason']],
            actionLabel: __('Review problem'),
        );

        return back()->with('status', __('Your timesheet problem was sent to the admin team.'));
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
