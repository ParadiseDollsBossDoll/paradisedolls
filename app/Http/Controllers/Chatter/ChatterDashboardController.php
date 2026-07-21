<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Services\ChatterPayrollService;
use App\Support\ChatterCurrency;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatterDashboardController extends Controller
{
    public function __invoke(Request $request, ChatterPayrollService $payroll, ChatterCurrency $currency): View
    {
        $user = $request->user()->load(['chatterProfile', 'chatterRoleAssignments.workRole']);
        $tz = $user->chatterProfile->timezone ?: config('app.timezone', ChatterPayrollService::REPORTING_TIMEZONE);
        $nowUtc = CarbonImmutable::now('UTC');
        $todayStart = CarbonImmutable::now($tz)->startOfDay();
        $todayTotals = $payroll->workedTotals($user, $todayStart, $todayStart->addDay());
        $period = $payroll->periodFor(now('UTC'));
        $currentTimesheet = $payroll->refresh($payroll->getOrCreate($user, $period['start']));
        $monthStart = CarbonImmutable::now($tz)->startOfMonth();
        $monthTotals = $payroll->workedTotals($user, $monthStart, $monthStart->addMonth());
        $openShift = ChatterShift::query()
            ->where('user_id', $user->id)
            ->whereNull('clocked_out_at')
            ->with(['breaks', 'workRole'])
            ->first();
        $availableWorkRoles = $user->chatterRoleAssignments
            ->filter(fn ($assignment) => $assignment->is_active && $assignment->workRole?->is_active)
            ->sortBy(fn ($assignment) => $assignment->workRole->sort_order)
            ->values();
        $activeBreak = $openShift?->breaks->firstWhere('ended_at', null);
        $activeWorkedSeconds = $openShift ? $payroll->shiftWorkedSeconds($openShift, $nowUtc) : 0;
        $activeTimerRunning = (bool) $openShift && ! $activeBreak;
        $periodStartUtc = CarbonImmutable::parse($currentTimesheet->period_start->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->utc();
        $periodEndUtc = CarbonImmutable::parse($currentTimesheet->period_end->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->addDay()->utc();
        $currentShifts = ChatterShift::query()
            ->where('user_id', $user->id)
            ->where('clocked_in_at', '<', $periodEndUtc)
            ->where(fn ($query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $periodStartUtc))
            ->with(['breaks', 'workRole'])
            ->latest('clocked_in_at')
            ->get()
            ->map(function (ChatterShift $shift) use ($payroll, $periodStartUtc, $periodEndUtc) {
                $totals = $payroll->shiftWorkedTotals($shift, $periodStartUtc, $periodEndUtc);

                $shift->setAttribute('worked_minutes', $totals['worked_minutes']);
                $shift->setAttribute('break_minutes', $totals['break_minutes']);

                return $shift;
            });
        $timesheets = ChatterTimesheet::query()
            ->where('user_id', $user->id)
            ->latest('period_start')
            ->paginate(8);
        $usdToPhpRate = $currency->rateForTimesheet($currentTimesheet);
        $currentPayPhpCentavos = $currency->phpCentavosForTimesheet($currentTimesheet);

        return view('chatter.dashboard', compact(
            'user',
            'tz',
            'todayTotals',
            'monthTotals',
            'currentTimesheet',
            'openShift',
            'activeBreak',
            'activeWorkedSeconds',
            'activeTimerRunning',
            'availableWorkRoles',
            'currentShifts',
            'timesheets',
            'currency',
            'usdToPhpRate',
            'currentPayPhpCentavos'
        ));
    }
}
