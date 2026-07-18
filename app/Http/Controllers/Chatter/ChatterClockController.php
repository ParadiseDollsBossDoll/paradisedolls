<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Services\ChatterClockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatterClockController extends Controller
{
    public function clockIn(Request $request, ChatterClockService $clock): RedirectResponse
    {
        $clock->clockIn($request->user());

        return back()->with('status', __('You are clocked in.'));
    }

    public function clockOut(Request $request, ChatterClockService $clock): RedirectResponse
    {
        $clock->clockOut($request->user());

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

        return back()->with('status', __('Your break has ended.'));
    }
}
