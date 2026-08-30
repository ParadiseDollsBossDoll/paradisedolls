<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Models\ChatterShift;
use App\Services\ChatterClockService;
use App\Services\ChatterPayrollService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChatterClockController extends Controller
{
    public function state(Request $request, ChatterPayrollService $payroll): JsonResponse
    {
        $nowUtc = CarbonImmutable::now('UTC');
        $shift = ChatterShift::query()
            ->where('active_user_id', $request->user()->id)
            ->with(['breaks' => fn ($query) => $query->select([
                'id',
                'chatter_shift_id',
                'started_at',
                'ended_at',
            ])])
            ->first();
        $activeBreak = $shift?->breaks->firstWhere('ended_at', null);

        return response()
            ->json([
                'has_open_shift' => (bool) $shift,
                'on_break' => (bool) $activeBreak,
                'timer_running' => (bool) $shift && ! $activeBreak,
                'worked_seconds' => $shift ? $payroll->shiftWorkedSeconds($shift, $nowUtc) : 0,
            ])
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
                'Pragma' => 'no-cache',
            ]);
    }

    public function clockIn(Request $request, ChatterClockService $clock): RedirectResponse
    {
        $validated = $request->validate([
            'work_role_id' => ['nullable', 'integer'],
            'platform' => ['required', 'string', 'max:100'],
            'model_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'model'))],
            'clock_in_earning_balance' => ['required', 'string', 'max:32'],
        ]);
        $clock->clockIn(
            $request->user(),
            isset($validated['work_role_id']) ? (int) $validated['work_role_id'] : null,
            trim($validated['platform']),
            isset($validated['model_id']) ? (int) $validated['model_id'] : null,
            $validated['clock_in_earning_balance'],
        );

        return back()->with('status', __('You are clocked in.'));
    }

    public function clockOut(Request $request, ChatterClockService $clock): RedirectResponse
    {
        $validated = $request->validate([
            'clock_out_earning_balance' => ['required', 'string', 'max:32'],
        ]);

        $clock->clockOut($request->user(), $validated['clock_out_earning_balance']);

        return back()->with('status', __('Your shift has been recorded.'));
    }

    public function startBreak(Request $request, ChatterClockService $clock): RedirectResponse
    {
        $clock->startBreak($request->user());

        return back()->with('status', __('Your break has started.'));
    }

    public function endBreak(Request $request, ChatterClockService $clock): RedirectResponse
    {
        $clock->endBreak($request->user());

        return back()->with('status', __('Work resumed.'));
    }
}
