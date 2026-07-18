<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Services\ChatterPayrollService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatterDashboardController extends Controller
{
    public function __invoke(Request $request, ChatterPayrollService $payroll): View
    {
        $user = $request->user()->load('chatterProfile');
        $todayStart = CarbonImmutable::now($user->chatterProfile->timezone)->startOfDay();
        $todayTotals = $payroll->workedTotals($user, $todayStart, $todayStart->addDay());
        $period = $payroll->periodFor(now('UTC'));
        $currentTimesheet = $payroll->refresh($payroll->getOrCreate($user, $period['start']));
        $openShift = ChatterShift::query()
            ->where('user_id', $user->id)
            ->whereNull('clocked_out_at')
            ->with('breaks')
            ->first();
        $activeBreak = $openShift?->breaks->firstWhere('ended_at', null);
        $periodStartUtc = CarbonImmutable::parse($currentTimesheet->period_start->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->utc();
        $periodEndUtc = CarbonImmutable::parse($currentTimesheet->period_end->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->addDay()->utc();
        $currentShifts = ChatterShift::query()
            ->where('user_id', $user->id)
            ->where('clocked_in_at', '<', $periodEndUtc)
            ->where(fn ($query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $periodStartUtc))
            ->with('breaks')
            ->latest('clocked_in_at')
            ->get();
        $timesheets = ChatterTimesheet::query()
            ->where('user_id', $user->id)
            ->latest('period_start')
            ->paginate(8);

        return view('chatter.dashboard', compact(
            'user', 'todayTotals', 'currentTimesheet', 'openShift', 'activeBreak', 'currentShifts', 'timesheets'
        ));
    }
}
