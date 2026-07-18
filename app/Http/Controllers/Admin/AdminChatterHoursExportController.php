<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Services\ChatterPayrollService;
use App\Support\DesignedXlsxWorkbook;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminChatterHoursExportController extends Controller
{
    public function csv(Request $request, ChatterPayrollService $payroll): StreamedResponse
    {
        $timesheets = $this->timesheets($request, $payroll);
        $headers = ['Chatter', 'Email', 'Week Start', 'Week End', 'Status', 'Shift Start (UTC)', 'Shift End (UTC)', 'Paid Minutes', 'Break Minutes', 'Estimated Pay GBP'];

        return response()->streamDownload(function () use ($timesheets, $headers): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers);

            foreach ($timesheets as $timesheet) {
                foreach ($timesheet->calculation_snapshot['shifts'] ?? [] as $shift) {
                    fputcsv($stream, [
                        $timesheet->user->name,
                        $timesheet->user->email,
                        $timesheet->period_start->toDateString(),
                        $timesheet->period_end->toDateString(),
                        $timesheet->statusLabel(),
                        $shift['started_at'] ?? '',
                        $shift['ended_at'] ?? '',
                        $shift['paid_minutes'] ?? 0,
                        $shift['break_minutes'] ?? 0,
                        number_format(((int) ($shift['pay_pence'] ?? 0)) / 100, 2, '.', ''),
                    ]);
                }
            }

            fclose($stream);
        }, 'paradise-dolls-chatter-hours-'.now()->format('Y-m-d').'.csv', $this->downloadHeaders('text/csv; charset=UTF-8'));
    }

    public function xlsx(Request $request, ChatterPayrollService $payroll): StreamedResponse
    {
        $timesheets = $this->timesheets($request, $payroll);
        $summaryRows = [
            $this->row(1, [$this->cell(1, 'Paradise Dolls - Chatter Hours & Pay Review', 1)], 28),
            $this->row(3, $this->cells(['Chatter', 'Email', 'Week', 'Status', 'Paid Hours', 'Break Hours', 'Night Hours', 'Weekend Hours', 'Overtime Hours', 'Adjustments', 'Estimated Gross Pay'], 9)),
        ];
        $shiftRows = [
            $this->row(1, [$this->cell(1, 'Shift Details', 1)], 28),
            $this->row(3, $this->cells(['Chatter', 'Week Start', 'Shift Start UTC', 'Shift End UTC', 'Paid Minutes', 'Break Minutes', 'Estimated Pay'], 9)),
        ];
        $adjustmentRows = [
            $this->row(1, [$this->cell(1, 'Pay Adjustments', 1)], 28),
            $this->row(3, $this->cells(['Chatter', 'Week Start', 'Label', 'Amount GBP', 'Note', 'Added By'], 9)),
        ];
        $breakRows = [
            $this->row(1, [$this->cell(1, 'Break Details', 1)], 28),
            $this->row(3, $this->cells(['Chatter', 'Week Start', 'Shift Start UTC', 'Break Start UTC', 'Break End UTC', 'Break Minutes'], 9)),
        ];
        $approvalRows = [
            $this->row(1, [$this->cell(1, 'Submission & Approval Information', 1)], 28),
            $this->row(3, $this->cells(['Chatter', 'Week Start', 'Status', 'Submitted At UTC', 'Reviewed By', 'Reviewed At UTC', 'Review Note', 'Snapshot Generated At UTC'], 9)),
        ];
        $summaryRow = $shiftRow = $adjustmentRow = $breakRow = $approvalRow = 4;

        foreach ($timesheets as $timesheet) {
            $summaryRows[] = $this->row($summaryRow++, $this->cells([
                $timesheet->user->name,
                $timesheet->user->email,
                $timesheet->period_start->format('d M Y').' - '.$timesheet->period_end->format('d M Y'),
                $timesheet->statusLabel(),
                round($timesheet->ordinary_minutes / 60, 2),
                round($timesheet->break_minutes / 60, 2),
                round($timesheet->night_minutes / 60, 2),
                round($timesheet->weekend_minutes / 60, 2),
                round($timesheet->overtime_minutes / 60, 2),
                $timesheet->adjustment_pence / 100,
                $timesheet->gross_pay_pence / 100,
            ], 7));

            foreach ($timesheet->calculation_snapshot['shifts'] ?? [] as $shift) {
                $shiftRows[] = $this->row($shiftRow++, $this->cells([
                    $timesheet->user->name,
                    $timesheet->period_start->toDateString(),
                    $shift['started_at'] ?? '',
                    $shift['ended_at'] ?? '',
                    $shift['paid_minutes'] ?? 0,
                    $shift['break_minutes'] ?? 0,
                    ((int) ($shift['pay_pence'] ?? 0)) / 100,
                ], 7));
            }

            foreach ($this->breaksFor($timesheet) as $break) {
                $breakRows[] = $this->row($breakRow++, $this->cells([
                    $timesheet->user->name,
                    $timesheet->period_start->toDateString(),
                    $break->shift->clocked_in_at?->utc()->toIso8601String(),
                    $break->started_at?->utc()->toIso8601String(),
                    $break->ended_at?->utc()->toIso8601String(),
                    $break->ended_at ? $break->started_at->diffInMinutes($break->ended_at) : null,
                ], 7));
            }

            foreach ($timesheet->adjustments as $adjustment) {
                $adjustmentRows[] = $this->row($adjustmentRow++, $this->cells([
                    $timesheet->user->name,
                    $timesheet->period_start->toDateString(),
                    $adjustment->label,
                    $adjustment->amount_pence / 100,
                    $adjustment->note,
                    $adjustment->creator?->name,
                ], 7));
            }

            $approvalRows[] = $this->row($approvalRow++, $this->cells([
                $timesheet->user->name,
                $timesheet->period_start->toDateString(),
                $timesheet->statusLabel(),
                $timesheet->submitted_at?->utc()->toIso8601String(),
                $timesheet->reviewer?->name,
                $timesheet->reviewed_at?->utc()->toIso8601String(),
                $timesheet->review_note,
                data_get($timesheet->calculation_snapshot, 'generated_at'),
            ], 7));
        }

        $workbook = new DesignedXlsxWorkbook([
            ['name' => 'Timesheet Summary', 'columns' => [24, 30, 28, 20, 14, 14, 14, 16, 16, 14, 20], 'rows' => $summaryRows, 'merges' => ['A1:K1'], 'freezeRow' => 3],
            ['name' => 'Shift Details', 'columns' => [24, 16, 28, 28, 15, 15, 18], 'rows' => $shiftRows, 'merges' => ['A1:G1'], 'freezeRow' => 3],
            ['name' => 'Breaks', 'columns' => [24, 16, 28, 28, 28, 16], 'rows' => $breakRows, 'merges' => ['A1:F1'], 'freezeRow' => 3],
            ['name' => 'Adjustments', 'columns' => [24, 16, 24, 16, 42, 24], 'rows' => $adjustmentRows, 'merges' => ['A1:F1'], 'freezeRow' => 3],
            ['name' => 'Approval Information', 'columns' => [24, 16, 20, 26, 24, 26, 42, 28], 'rows' => $approvalRows, 'merges' => ['A1:H1'], 'freezeRow' => 3],
        ]);
        $contents = $workbook->toBinary();

        return response()->streamDownload(fn () => print($contents), 'paradise-dolls-chatter-timesheets-'.now()->format('Y-m-d').'.xlsx', $this->downloadHeaders('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
    }

    private function timesheets(Request $request, ChatterPayrollService $payroll)
    {
        $query = ChatterTimesheet::query()
            ->with(['user', 'reviewer', 'adjustments.creator'])
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->integer('chatter_id'), fn (Builder $q, int $id) => $q->where('user_id', $id))
            ->when($request->filled('from'), fn (Builder $q) => $q->whereDate('period_end', '>=', $request->input('from')))
            ->when($request->filled('to'), fn (Builder $q) => $q->whereDate('period_start', '<=', $request->input('to')))
            ->orderBy('period_start')
            ->get();

        return $query->map(function (ChatterTimesheet $timesheet) use ($payroll) {
            return $timesheet->status === ChatterTimesheet::STATUS_APPROVED ? $timesheet : $payroll->refresh($timesheet);
        })->each->loadMissing(['user', 'reviewer', 'adjustments.creator']);
    }

    private function breaksFor(ChatterTimesheet $timesheet)
    {
        $startUtc = CarbonImmutable::parse($timesheet->period_start->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->utc();
        $endUtc = CarbonImmutable::parse($timesheet->period_end->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)->addDay()->utc();

        return ChatterShift::query()
            ->where('user_id', $timesheet->user_id)
            ->where('clocked_in_at', '<', $endUtc)
            ->where(fn (Builder $query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $startUtc))
            ->with(['breaks' => fn ($query) => $query->where('started_at', '<', $endUtc)->where(fn ($breakQuery) => $breakQuery->whereNull('ended_at')->orWhere('ended_at', '>', $startUtc))])
            ->get()
            ->flatMap(fn (ChatterShift $shift) => $shift->breaks->each->setRelation('shift', $shift));
    }

    private function row(int $number, array $cells, ?int $height = null): array
    {
        return ['r' => $number, 'height' => $height, 'cells' => $cells];
    }

    private function cell(int $column, mixed $value, int $style): array
    {
        return ['col' => $column, 'value' => $value, 'style' => $style];
    }

    private function cells(array $values, int $style): array
    {
        return collect($values)->values()->map(fn ($value, int $index) => $this->cell($index + 1, $value, $style))->all();
    }

    private function downloadHeaders(string $contentType): array
    {
        return ['Content-Type' => $contentType, 'Cache-Control' => 'private, no-store, max-age=0', 'Pragma' => 'no-cache', 'X-Content-Type-Options' => 'nosniff'];
    }
}
