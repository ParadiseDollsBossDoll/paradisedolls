<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatterTimesheet;
use App\Services\ChatterPayrollService;
use App\Support\ChatterCurrency;
use App\Support\DesignedXlsxWorkbook;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminChatterHoursExportController extends Controller
{
    public function xlsx(Request $request, ChatterCurrency $currency): StreamedResponse
    {
        $timesheets = $this->timesheets($request);
        $attendance = $timesheets
            ->flatMap(fn (ChatterTimesheet $timesheet) => collect($timesheet->calculation_snapshot['shifts'] ?? [])->map(fn (array $shift) => [
                'clock_in' => $this->excelDateText($shift['started_at'] ?? null),
                'clock_out' => $this->excelDateText($shift['ended_at'] ?? null),
                'employee' => $timesheet->user->name,
                'role' => $shift['work_role'] ?? 'Chatter',
                'worked_minutes' => (int) ($shift['paid_minutes'] ?? 0),
            ]))
            ->sortBy('clock_in')
            ->values();
        if ($attendance->count() > 10000) {
            throw ValidationException::withMessages([
                'to' => __('The selected report contains more than 10,000 shifts. Choose a smaller date range.'),
            ]);
        }
        $payrollRows = $timesheets
            ->groupBy('user_id')
            ->map(function ($employeeTimesheets) use ($currency): array {
                $minutes = (int) $employeeTimesheets->sum('ordinary_minutes');
                $rateMinuteCents = (int) $employeeTimesheets->sum(function (ChatterTimesheet $timesheet): int {
                    return collect($timesheet->calculation_snapshot['shifts'] ?? [])->sum(
                        fn (array $shift): int => ((int) ($shift['hourly_rate_pence'] ?? 0)) * ((int) ($shift['paid_minutes'] ?? 0)),
                    );
                });
                $additionalCents = (int) $employeeTimesheets->sum('adjustment_pence');
                $finalCents = (int) $employeeTimesheets->sum('gross_pay_pence');
                $basicCents = $finalCents - $additionalCents;
                $statuses = $employeeTimesheets->map->statusLabel()->unique()->values();
                $notes = $employeeTimesheets->flatMap(fn (ChatterTimesheet $timesheet) => $timesheet->adjustments->map(function ($adjustment): string {
                    return trim($adjustment->label.($adjustment->note ? ': '.$adjustment->note : ''));
                }))->filter()->unique()->implode('; ');

                return [
                    'employee' => $employeeTimesheets->first()->user->name,
                    'minutes' => $minutes,
                    'rate' => $minutes > 0 ? round($rateMinuteCents / ($minutes * 100), 2) : 0,
                    'basic' => $basicCents / 100,
                    'additional' => $additionalCents / 100,
                    'final_usd' => $finalCents / 100,
                    'final_php' => $employeeTimesheets->sum(fn (ChatterTimesheet $timesheet) => $currency->phpCentavosForTimesheet($timesheet)) / 100,
                    'notes' => $notes !== '' ? $notes : '-',
                    'status' => $statuses->count() === 1 ? $statuses->first() : 'Mixed',
                ];
            })
            ->sortBy('employee')
            ->values();
        $grossCents = (int) $timesheets->sum('gross_pay_pence');
        $grossPhpCentavos = (int) $timesheets->sum(fn (ChatterTimesheet $timesheet) => $currency->phpCentavosForTimesheet($timesheet));
        $exchangeRate = $grossCents !== 0
            ? abs($grossPhpCentavos / $grossCents)
            : (float) $currency->usdToPhpRate();
        $firstTimesheet = $timesheets->sortBy('period_start')->first();
        $lastTimesheet = $timesheets->sortByDesc('period_end')->first();
        $periodStart = $firstTimesheet
            ? CarbonImmutable::parse($firstTimesheet->period_start->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)
            : ($request->filled('from')
                ? CarbonImmutable::parse($request->input('from'), ChatterPayrollService::REPORTING_TIMEZONE)
                : CarbonImmutable::now(ChatterPayrollService::REPORTING_TIMEZONE)->startOfWeek());
        $periodEnd = $lastTimesheet
            ? CarbonImmutable::parse($lastTimesheet->period_end->toDateString(), ChatterPayrollService::REPORTING_TIMEZONE)
            : ($request->filled('to')
                ? CarbonImmutable::parse($request->input('to'), ChatterPayrollService::REPORTING_TIMEZONE)
                : $periodStart->endOfWeek());

        [$rows, $merges] = $this->payrollSheet(
            $attendance,
            $payrollRows,
            $periodStart->format('m/d/Y').' - '.$periodEnd->format('m/d/Y'),
            $exchangeRate,
        );
        $workbook = new DesignedXlsxWorkbook([[
            'name' => 'Payroll',
            'columns' => [13, 13, 13, 13, 13, 13, 10, 10, 18, 14],
            'rows' => $rows,
            'merges' => $merges,
            'freezeRow' => 4,
            'pageMargins' => ['left' => 0.7, 'right' => 0.7, 'top' => 0.75, 'bottom' => 0.75, 'header' => 0.3, 'footer' => 0.3],
            'pageSetup' => ['orientation' => 'landscape', 'paperSize' => 9, 'fitToWidth' => 1, 'fitToHeight' => 0],
        ]]);
        $contents = $workbook->toBinary();

        return response()->streamDownload(fn () => print ($contents), 'paradise-dolls-payroll-'.$periodStart->format('Y-m-d').'-to-'.$periodEnd->format('Y-m-d').'.xlsx', $this->downloadHeaders('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
    }

    private function payrollSheet(Collection $attendance, Collection $payrollRows, string $periodLabel, float $exchangeRate): array
    {
        $rows = [
            $this->row(1, $this->styledRow('PARADISE DOLLS', 24), 30),
            $this->row(2, $this->styledRow(null, 24), 30),
            $this->row(3, $this->styledRow('Payroll contact: '.config('paradise.onboarding_email'), 25), 20),
            $this->row(4, $this->cells(['DATE/TIME IN', null, 'DATE/TIME OUT', null, 'EMPLOYEE', null, 'ROLE', null, 'BONUS', 'HOURS WORKED'], 26), 26),
        ];
        $merges = ['A1:J2', 'A3:J3', 'A4:B4', 'C4:D4', 'E4:F4', 'G4:H4'];
        $rowNumber = 5;

        foreach ($attendance as $index => $shift) {
            $pale = $index % 2 === 0;
            $textStyle = $pale ? 27 : 28;
            $dateStyle = $pale ? 29 : 30;
            $durationStyle = $pale ? 31 : 32;
            $usdStyle = $pale ? 52 : 53;
            $rows[] = $this->row($rowNumber, [
                $this->cell(1, $shift['clock_in'], $dateStyle), $this->cell(2, null, $dateStyle),
                $this->cell(3, $shift['clock_out'], $dateStyle), $this->cell(4, null, $dateStyle),
                $this->cell(5, $shift['employee'], $textStyle), $this->cell(6, null, $textStyle),
                $this->cell(7, $shift['role'], $textStyle), $this->cell(8, null, $textStyle),
                $this->cell(9, null, $usdStyle),
                $this->cell(10, $shift['worked_minutes'] / 1440, $durationStyle),
            ], 34);
            $merges[] = "A{$rowNumber}:B{$rowNumber}";
            $merges[] = "C{$rowNumber}:D{$rowNumber}";
            $merges[] = "E{$rowNumber}:F{$rowNumber}";
            $merges[] = "G{$rowNumber}:H{$rowNumber}";
            $rowNumber++;
        }

        $attendanceStart = 5;
        $attendanceEnd = $rowNumber - 1;
        $totalMinutes = (int) $attendance->sum('worked_minutes');
        $totalCells = $this->styledRow(null, 33);
        $totalCells[8] = $this->cell(9, 'TOTAL HOURS', 33);
        $totalCells[9] = $attendanceEnd >= $attendanceStart
            ? $this->formulaCell(10, "SUM(J{$attendanceStart}:J{$attendanceEnd})", $totalMinutes / 1440, 34)
            : $this->cell(10, 0, 34);
        $rows[] = $this->row($rowNumber, $totalCells, 25);
        $merges[] = "A{$rowNumber}:H{$rowNumber}";
        $rowNumber += 4;
        $payrollTitleRow = $rowNumber;
        $rows[] = $this->row($rowNumber, $this->styledRow('PAYROLL AS OF '.$periodLabel, 35), 30);
        $rows[] = $this->row(++$rowNumber, $this->styledRow(null, 35), 30);
        $merges[] = "A{$payrollTitleRow}:J{$rowNumber}";
        $rowNumber += 2;
        $rows[] = $this->row($rowNumber, [
            $this->cell(1, 'The currency conversion applied to this payroll was determined when the report was created.', 36),
            ...array_map(fn (int $column) => $this->cell($column, null, 36), range(2, 6)),
            $this->cell(7, 'USD/PHP', 37), $this->cell(8, 'Conversion', 37),
            $this->cell(9, $exchangeRate, 38), $this->cell(10, null, 37),
        ], 24);
        $merges[] = "A{$rowNumber}:F{$rowNumber}";
        $rowNumber += 2;
        $rows[] = $this->row($rowNumber, $this->cells(['EMPLOYEES NAME', null, 'TOTAL HOURS', 'RATE', 'BASIC PAY', 'ADDITIONAL', 'US FINAL PAY', 'PH FINAL PAY', 'NOTES', 'STATUS'], 39), 27);
        $merges[] = "A{$rowNumber}:B{$rowNumber}";
        $rowNumber++;
        $payrollStart = $rowNumber;

        foreach ($payrollRows as $index => $employee) {
            $pale = $index % 2 === 0;
            $textStyle = $pale ? 40 : 41;
            $durationStyle = $pale ? 42 : 43;
            $usdStyle = $pale ? 44 : 45;
            $phpStyle = $pale ? 46 : 47;
            $rows[] = $this->row($rowNumber, [
                $this->cell(1, $employee['employee'], $textStyle), $this->cell(2, null, $textStyle),
                $this->cell(3, $employee['minutes'] / 1440, $durationStyle),
                $this->cell(4, $employee['rate'], $usdStyle),
                $this->cell(5, $employee['basic'], $usdStyle),
                $this->cell(6, $employee['additional'], $usdStyle),
                $this->formulaCell(7, "E{$rowNumber}+F{$rowNumber}", $employee['final_usd'], $usdStyle),
                $this->cell(8, $employee['final_php'], $phpStyle),
                $this->cell(9, $employee['notes'], $textStyle),
                $this->cell(10, $employee['status'], $textStyle),
            ], 31);
            $merges[] = "A{$rowNumber}:B{$rowNumber}";
            $rowNumber++;
        }

        $payrollEnd = $rowNumber - 1;
        $totalHours = (int) $payrollRows->sum('minutes') / 1440;
        $totalBasic = (float) $payrollRows->sum('basic');
        $totalAdditional = (float) $payrollRows->sum('additional');
        $totalUsd = (float) $payrollRows->sum('final_usd');
        $totalPhp = (float) $payrollRows->sum('final_php');
        $rows[] = $this->row($rowNumber, [
            $this->cell(1, 'TOTAL', 48), $this->cell(2, null, 48),
            $payrollEnd >= $payrollStart ? $this->formulaCell(3, "SUM(C{$payrollStart}:C{$payrollEnd})", $totalHours, 49) : $this->cell(3, 0, 49),
            $this->cell(4, null, 48),
            $payrollEnd >= $payrollStart ? $this->formulaCell(5, "SUM(E{$payrollStart}:E{$payrollEnd})", $totalBasic, 50) : $this->cell(5, 0, 50),
            $payrollEnd >= $payrollStart ? $this->formulaCell(6, "SUM(F{$payrollStart}:F{$payrollEnd})", $totalAdditional, 50) : $this->cell(6, 0, 50),
            $payrollEnd >= $payrollStart ? $this->formulaCell(7, "SUM(G{$payrollStart}:G{$payrollEnd})", $totalUsd, 50) : $this->cell(7, 0, 50),
            $payrollEnd >= $payrollStart ? $this->formulaCell(8, "SUM(H{$payrollStart}:H{$payrollEnd})", $totalPhp, 51) : $this->cell(8, 0, 51),
            $this->cell(9, null, 48), $this->cell(10, null, 48),
        ], 25);
        $merges[] = "A{$rowNumber}:B{$rowNumber}";

        return [$rows, $merges];
    }

    private function excelDateText(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $local = CarbonImmutable::parse($value)->timezone(ChatterPayrollService::REPORTING_TIMEZONE);

        return $local->format('l, F j, Y').' at'."\n".$local->format('g:i A');
    }

    private function timesheets(Request $request): Collection
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                ChatterTimesheet::STATUS_DRAFT,
                ChatterTimesheet::STATUS_SUBMITTED,
                ChatterTimesheet::STATUS_CHANGES_REQUESTED,
                ChatterTimesheet::STATUS_APPROVED,
                ChatterTimesheet::STATUS_REJECTED,
            ])],
            'chatter_id' => ['nullable', 'integer', 'min:1'],
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
            throw ValidationException::withMessages(['to' => __('Exports are limited to a maximum range of 366 days.')]);
        }

        $query = ChatterTimesheet::query()
            ->with(['user', 'reviewer', 'adjustments.creator'])
            ->when(isset($validated['search']) && trim($validated['search']) !== '', fn (Builder $q) => $q->whereHas('user', fn (Builder $userQuery) => $userQuery
                ->where('name', 'like', '%'.trim($validated['search']).'%')
                ->orWhere('email', 'like', '%'.trim($validated['search']).'%')))
            ->when(isset($validated['status']), fn (Builder $q) => $q->where('status', $validated['status']))
            ->when(isset($validated['chatter_id']), fn (Builder $q) => $q->where('user_id', (int) $validated['chatter_id']))
            ->whereDate('period_end', '>=', $from->toDateString())
            ->whereDate('period_start', '<=', $to->toDateString())
            ->orderBy('period_start')
            ->limit(1001)
            ->get();

        if ($query->count() > 1000) {
            throw ValidationException::withMessages([
                'to' => __('The selected report contains more than 1,000 timesheets. Choose a smaller date range.'),
            ]);
        }

        // Payroll snapshots are refreshed by clock and admin mutation actions.
        // Exporting is intentionally read-only so a GET cannot alter pay records.
        return $query;
    }

    private function row(int $number, array $cells, ?int $height = null): array
    {
        return ['r' => $number, 'height' => $height, 'cells' => $cells];
    }

    private function cell(int $column, mixed $value, int $style): array
    {
        return ['col' => $column, 'value' => $value, 'style' => $style];
    }

    private function formulaCell(int $column, string $formula, int|float $cachedValue, int $style): array
    {
        return ['col' => $column, 'value' => $cachedValue, 'style' => $style, 'formula' => $formula];
    }

    private function styledRow(mixed $firstValue, int $style): array
    {
        return collect(range(1, 10))
            ->map(fn (int $column): array => $this->cell($column, $column === 1 ? $firstValue : null, $style))
            ->all();
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
