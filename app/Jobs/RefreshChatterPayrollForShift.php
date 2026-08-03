<?php

namespace App\Jobs;

use App\Models\ChatterShift;
use App\Services\ChatterPayrollService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshChatterPayrollForShift implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $shiftId) {}

    public function handle(ChatterPayrollService $payroll): void
    {
        $shift = ChatterShift::query()->with('user')->find($this->shiftId);

        if ($shift) {
            $payroll->refreshPeriodsTouchedBy($shift);
        }
    }
}
