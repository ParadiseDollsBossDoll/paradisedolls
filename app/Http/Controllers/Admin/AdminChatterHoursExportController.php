<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Services\ChatterPlatformEarnings;
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
    public function xlsx(Request $request, ChatterCurrency $currency, ChatterPlatformEarnings $earnings, ChatterPayrollService $payroll): StreamedResponse
    {
        [$periodStart, $periodEnd] = $this->exportDateRange($request);
        $timesheets = $this->timesheets($request);
        $sourceShifts = $this->snapshotSourceShifts($timesheets);
        $attendance = $timesheets
            ->flatMap(fn (ChatterTimesheet $timesheet) => collect($timesheet->calculation_snapshot['shifts'] ?? [])->map(function (array $shift) use ($timesheet, $earnings, $payroll, $sourceShifts): array {
                $commission = $payroll->commissionForSnapshotShift(
                    $shift,
                    $this->snapshotCommissionCanBeDerived($shift, $timesheet, $payroll, $sourceShifts),
                );

                return [
                    'clock_in' => $this->excelDateText($shift['started_at'] ?? null),
                    'clock_out' => $this->excelDateText($shift['ended_at'] ?? null),
                    'employee' => $timesheet->user->name,
                    'role' => trim(($shift['work_role'] ?? 'Chatter').(filled($shift['platform'] ?? null) ? ' - '.$shift['platform'] : '').(filled($shift['model_name'] ?? null) ? ' - '.$shift['model_name'] : '')),
                    'clock_in_balance' => $earnings->formatBalance($shift['clock_in_earning_balance_minor'] ?? null, $shift['earning_unit'] ?? null, $shift['earning_currency'] ?? null),
                    'clock_out_balance' => $earnings->formatBalance($shift['clock_out_earning_balance_minor'] ?? null, $shift['earning_unit'] ?? null, $shift['earning_currency'] ?? null),
                    'generated' => $earnings->formatGenerated($shift['generated_earning_units'] ?? null, $shift['generated_earning_pence'] ?? null, $shift['earning_unit'] ?? null, $shift['earning_currency'] ?? null),
                    'commission' => $earnings->formatCommission($commission['pence'], $commission['currency']),
                    'worked_minutes' => (int) ($shift['paid_minutes'] ?? 0),
                ];
            }))
            ->filter(fn (array $shift): bool => $shift['worked_minutes'] > 0)
            ->sortBy('clock_in')
            ->values();
        if ($attendance->count() > 10000) {
            throw ValidationException::withMessages([
                'to' => __('The selected report contains more than 10,000 shifts. Choose a smaller date range.'),
            ]);
        }
        $payrollRows = $timesheets
            ->groupBy('user_id')
            ->map(function ($employeeTimesheets) use ($currency, $payroll, $sourceShifts): array {
                $minutes = (int) $employeeTimesheets->sum('ordinary_minutes');
                $rateMinuteCents = (int) $employeeTimesheets->sum(function (ChatterTimesheet $timesheet): int {
                    return collect($timesheet->calculation_snapshot['shifts'] ?? [])->sum(
                        fn (array $shift): int => ((int) ($shift['hourly_rate_pence'] ?? 0)) * ((int) ($shift['paid_minutes'] ?? 0)),
                    );
                });
                $additionalCents = (int) $employeeTimesheets->sum('adjustment_pence');
                $displaySummaries = $employeeTimesheets->map(fn (ChatterTimesheet $timesheet): array => $this->earningDisplaySummaryForTimesheet($timesheet, $payroll, $sourceShifts));
                $commissionCents = (int) $displaySummaries->sum('commission_usd_pence');
                $foreignCommissionCents = (int) $displaySummaries->sum('commission_gbp_pence');
                $foreignCommissionUsdCents = (int) $displaySummaries->sum('commission_gbp_usd_pence');
                $finalCents = (int) $employeeTimesheets->sum(fn (ChatterTimesheet $timesheet): int => $this->displayGrossPayPence(
                    $timesheet,
                    $this->earningDisplaySummaryForTimesheet($timesheet, $payroll, $sourceShifts),
                    $payroll,
                ));
                $basicCents = (int) $employeeTimesheets->sum(
                    fn (ChatterTimesheet $timesheet): int => $timesheet->base_pay_pence ?? ($timesheet->gross_pay_pence - $timesheet->adjustment_pence - $timesheet->commission_pence),
                );
                $statuses = $employeeTimesheets->map->workflowStatusLabel()->unique()->values();
                $notes = $employeeTimesheets->flatMap(fn (ChatterTimesheet $timesheet) => $timesheet->adjustments->map(function ($adjustment): string {
                    return trim($adjustment->label.($adjustment->note ? ': '.$adjustment->note : ''));
                }))->filter()->unique()->implode('; ');

                return [
                    'employee' => $employeeTimesheets->first()->user->name,
                    'minutes' => $minutes,
                    'rate' => $minutes > 0 ? round($rateMinuteCents / ($minutes * 100), 2) : 0,
                    'basic' => $basicCents / 100,
                    'commission' => $commissionCents / 100,
                    'foreign_commission' => $foreignCommissionCents > 0
                        ? 'GBP '.number_format($foreignCommissionCents / 100, 2).' / $'.number_format($foreignCommissionUsdCents / 100, 2).' USD'
                        : 'GBP 0.00',
                    'foreign_commission_cents' => $foreignCommissionCents,
                    'foreign_commission_usd_cents' => $foreignCommissionUsdCents,
                    'additional' => $additionalCents / 100,
                    'final_usd' => $finalCents / 100,
                    'final_php' => $employeeTimesheets->sum(function (ChatterTimesheet $timesheet) use ($currency, $payroll, $sourceShifts): int {
                        $summary = $this->earningDisplaySummaryForTimesheet($timesheet, $payroll, $sourceShifts);

                        return $timesheet->status === ChatterTimesheet::STATUS_APPROVED
                            ? $currency->phpCentavosForTimesheet($timesheet)
                            : $currency->phpCentavosFromUsdCents($this->displayGrossPayPence($timesheet, $summary, $payroll), $currency->rateForTimesheet($timesheet));
                    }) / 100,
                    'notes' => $notes !== '' ? $notes : '-',
                    'status' => $statuses->count() === 1 ? $statuses->first() : 'Mixed',
                ];
            })
            ->sortBy('employee')
            ->values();
        $grossCents = (int) $timesheets->sum(fn (ChatterTimesheet $timesheet): int => $this->displayGrossPayPence($timesheet, $this->earningDisplaySummaryForTimesheet($timesheet, $payroll, $sourceShifts), $payroll));
        $grossPhpCentavos = (int) $timesheets->sum(function (ChatterTimesheet $timesheet) use ($currency, $payroll, $sourceShifts): int {
            $summary = $this->earningDisplaySummaryForTimesheet($timesheet, $payroll, $sourceShifts);

            return $timesheet->status === ChatterTimesheet::STATUS_APPROVED
                ? $currency->phpCentavosForTimesheet($timesheet)
                : $currency->phpCentavosFromUsdCents($this->displayGrossPayPence($timesheet, $summary, $payroll), $currency->rateForTimesheet($timesheet));
        });
        $exchangeRate = $grossCents !== 0
            ? abs($grossPhpCentavos / $grossCents)
            : (float) $currency->usdToPhpRate();
        $foreignCommissionCents = (int) $payrollRows->sum('foreign_commission_cents');
        $foreignCommissionUsdCents = (int) $payrollRows->sum('foreign_commission_usd_cents');
        $gbpToUsdRate = $foreignCommissionCents !== 0
            ? abs($foreignCommissionUsdCents / $foreignCommissionCents)
            : (float) $currency->gbpToUsdRate();
        [$rows, $merges] = $this->payrollSheet(
            $attendance,
            $payrollRows,
            $periodStart->format('m/d/Y').' - '.$periodEnd->format('m/d/Y'),
            $exchangeRate,
            $gbpToUsdRate,
        );
        $workbook = new DesignedXlsxWorkbook([[
            'name' => 'Payroll',
            'columns' => [13, 13, 13, 13, 13, 13, 16, 16, 13, 13, 14, 13, 14],
            'rows' => $rows,
            'merges' => $merges,
            'freezeRow' => 4,
            'pageMargins' => ['left' => 0.7, 'right' => 0.7, 'top' => 0.75, 'bottom' => 0.75, 'header' => 0.3, 'footer' => 0.3],
            'pageSetup' => ['orientation' => 'landscape', 'paperSize' => 9, 'fitToWidth' => 1, 'fitToHeight' => 0],
        ]]);
        $contents = $workbook->toBinary();

        return response()->streamDownload(fn () => print ($contents), 'paradise-dolls-payroll-'.$periodStart->format('Y-m-d').'-to-'.$periodEnd->format('Y-m-d').'.xlsx', $this->downloadHeaders('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function exportDateRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $today = CarbonImmutable::now(ChatterPayrollService::REPORTING_TIMEZONE);
        $from = isset($validated['from'])
            ? CarbonImmutable::parse($validated['from'], ChatterPayrollService::REPORTING_TIMEZONE)
            : $today->startOfWeek(CarbonInterface::MONDAY);
        $to = isset($validated['to'])
            ? CarbonImmutable::parse($validated['to'], ChatterPayrollService::REPORTING_TIMEZONE)
            : $today->endOfWeek(CarbonInterface::SUNDAY);

        return [$from, $to];
    }

    private function payrollSheet(Collection $attendance, Collection $payrollRows, string $periodLabel, float $exchangeRate, float $gbpToUsdRate): array
    {
        $rows = [
            $this->row(1, $this->styledRow('PARADISE DOLLS', 24), 30),
            $this->row(2, $this->styledRow(null, 24), 30),
            $this->row(3, $this->styledRow('Payroll contact: '.config('paradise.onboarding_email'), 25), 20),
            $this->row(4, $this->cells(['DATE/TIME IN', null, 'DATE/TIME OUT', null, 'EMPLOYEE', null, 'ROLE / SITE / MODEL', null, 'BALANCE IN', 'BALANCE OUT', 'GENERATED', 'COMMISSION', 'HOURS WORKED'], 26), 26),
        ];
        $merges = ['A1:M2', 'A3:M3', 'A4:B4', 'C4:D4', 'E4:F4', 'G4:H4'];
        $rowNumber = 5;

        foreach ($attendance as $index => $shift) {
            $pale = $index % 2 === 0;
            $textStyle = $pale ? 27 : 28;
            $dateStyle = $pale ? 29 : 30;
            $durationStyle = $pale ? 31 : 32;
            $rows[] = $this->row($rowNumber, [
                $this->cell(1, $shift['clock_in'], $dateStyle), $this->cell(2, null, $dateStyle),
                $this->cell(3, $shift['clock_out'], $dateStyle), $this->cell(4, null, $dateStyle),
                $this->cell(5, $shift['employee'], $textStyle), $this->cell(6, null, $textStyle),
                $this->cell(7, $shift['role'], $textStyle), $this->cell(8, null, $textStyle),
                $this->cell(9, $shift['clock_in_balance'], $textStyle),
                $this->cell(10, $shift['clock_out_balance'], $textStyle),
                $this->cell(11, $shift['generated'], $textStyle),
                $this->cell(12, $shift['commission'], $textStyle),
                $this->cell(13, $shift['worked_minutes'] / 1440, $durationStyle),
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
        $totalCells[11] = $this->cell(12, 'TOTAL HOURS', 33);
        $totalCells[12] = $attendanceEnd >= $attendanceStart
            ? $this->formulaCell(13, "SUM(M{$attendanceStart}:M{$attendanceEnd})", $totalMinutes / 1440, 34)
            : $this->cell(13, 0, 34);
        $rows[] = $this->row($rowNumber, $totalCells, 25);
        $merges[] = "A{$rowNumber}:K{$rowNumber}";
        $rowNumber += 4;
        $payrollTitleRow = $rowNumber;
        $rows[] = $this->row($rowNumber, $this->styledRow('PAYROLL AS OF '.$periodLabel, 35), 30);
        $rows[] = $this->row(++$rowNumber, $this->styledRow(null, 35), 30);
        $merges[] = "A{$payrollTitleRow}:M{$rowNumber}";
        $rowNumber += 2;
        $rows[] = $this->row($rowNumber, [
            $this->cell(1, 'The currency conversion applied to this payroll was determined when the report was created.', 36),
            ...array_map(fn (int $column) => $this->cell($column, null, 36), range(2, 8)),
            $this->cell(9, 'USD/PHP', 37), $this->cell(10, $exchangeRate, 38),
            $this->cell(11, 'GBP/USD', 37), $this->cell(12, $gbpToUsdRate, 38), $this->cell(13, null, 37),
        ], 24);
        $merges[] = "A{$rowNumber}:H{$rowNumber}";
        $rowNumber += 2;
        $rows[] = $this->row($rowNumber, $this->cells(['EMPLOYEES NAME', null, 'TOTAL HOURS', 'RATE', 'BASIC PAY', 'USD COMMISSION', 'GBP COMMISSION', 'ADDITIONAL', 'TOTAL PAY USD', 'TOTAL PAY PHP', 'NOTES', null, 'STATUS'], 39), 27);
        $merges[] = "A{$rowNumber}:B{$rowNumber}";
        $merges[] = "K{$rowNumber}:L{$rowNumber}";
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
                $this->cell(6, $employee['commission'], $usdStyle),
                $this->cell(7, $employee['foreign_commission'], $textStyle),
                $this->cell(8, $employee['additional'], $usdStyle),
                $this->cell(9, $employee['final_usd'], $usdStyle),
                $this->cell(10, $employee['final_php'], $phpStyle),
                $this->cell(11, $employee['notes'], $textStyle), $this->cell(12, null, $textStyle),
                $this->cell(13, $employee['status'], $textStyle),
            ], 31);
            $merges[] = "A{$rowNumber}:B{$rowNumber}";
            $merges[] = "K{$rowNumber}:L{$rowNumber}";
            $rowNumber++;
        }

        $payrollEnd = $rowNumber - 1;
        $totalHours = (int) $payrollRows->sum('minutes') / 1440;
        $totalBasic = (float) $payrollRows->sum('basic');
        $totalCommission = (float) $payrollRows->sum('commission');
        $totalForeignCommission = (int) $payrollRows->sum('foreign_commission_cents');
        $totalForeignCommissionUsd = (int) $payrollRows->sum('foreign_commission_usd_cents');
        $totalAdditional = (float) $payrollRows->sum('additional');
        $totalUsd = (float) $payrollRows->sum('final_usd');
        $totalPhp = (float) $payrollRows->sum('final_php');
        $rows[] = $this->row($rowNumber, [
            $this->cell(1, 'TOTAL', 48), $this->cell(2, null, 48),
            $payrollEnd >= $payrollStart ? $this->formulaCell(3, "SUM(C{$payrollStart}:C{$payrollEnd})", $totalHours, 49) : $this->cell(3, 0, 49),
            $this->cell(4, null, 48),
            $payrollEnd >= $payrollStart ? $this->formulaCell(5, "SUM(E{$payrollStart}:E{$payrollEnd})", $totalBasic, 50) : $this->cell(5, 0, 50),
            $payrollEnd >= $payrollStart ? $this->formulaCell(6, "SUM(F{$payrollStart}:F{$payrollEnd})", $totalCommission, 50) : $this->cell(6, 0, 50),
            $this->cell(7, $totalForeignCommission > 0 ? 'GBP '.number_format($totalForeignCommission / 100, 2).' / $'.number_format($totalForeignCommissionUsd / 100, 2).' USD' : 'GBP 0.00', 48),
            $payrollEnd >= $payrollStart ? $this->formulaCell(8, "SUM(H{$payrollStart}:H{$payrollEnd})", $totalAdditional, 50) : $this->cell(8, 0, 50),
            $payrollEnd >= $payrollStart ? $this->formulaCell(9, "SUM(I{$payrollStart}:I{$payrollEnd})", $totalUsd, 50) : $this->cell(9, 0, 50),
            $payrollEnd >= $payrollStart ? $this->formulaCell(10, "SUM(J{$payrollStart}:J{$payrollEnd})", $totalPhp, 51) : $this->cell(10, 0, 51),
            $this->cell(11, null, 48), $this->cell(12, null, 48), $this->cell(13, null, 48),
        ], 25);
        $merges[] = "A{$rowNumber}:B{$rowNumber}";
        $merges[] = "K{$rowNumber}:L{$rowNumber}";

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
                ChatterTimesheet::WORKFLOW_IN_PROGRESS,
                ChatterTimesheet::WORKFLOW_READY_FOR_REVIEW,
                ChatterTimesheet::WORKFLOW_APPROVED,
                ChatterTimesheet::WORKFLOW_CLOSED,
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
            : $today->startOfWeek(CarbonInterface::MONDAY);
        $to = isset($validated['to'])
            ? CarbonImmutable::parse($validated['to'], ChatterPayrollService::REPORTING_TIMEZONE)
            : $today->endOfWeek(CarbonInterface::SUNDAY);

        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['to' => __('Exports are limited to a maximum range of 366 days.')]);
        }

        $query = ChatterTimesheet::query()
            ->with(['user', 'reviewer', 'adjustments.creator'])
            ->where(function (Builder $query) {
                $query->where('ordinary_minutes', '>', 0)
                    ->orWhere('gross_pay_pence', '!=', 0)
                    ->orWhere('adjustment_pence', '!=', 0)
                    ->orWhere('commission_pence', '!=', 0)
                    ->orWhere('foreign_commission_pence', '!=', 0)
                    ->orWhereHas('adjustments', fn (Builder $adjustments) => $adjustments->where('amount_pence', '!=', 0));
            })
            ->when(isset($validated['search']) && trim($validated['search']) !== '', fn (Builder $q) => $q->whereHas('user', fn (Builder $userQuery) => $userQuery
                ->where('name', 'like', '%'.trim($validated['search']).'%')
                ->orWhere('email', 'like', '%'.trim($validated['search']).'%')))
            ->when(isset($validated['status']), function (Builder $query) use ($validated) {
                $status = $validated['status'];

                if (in_array($status, [
                    ChatterTimesheet::WORKFLOW_IN_PROGRESS,
                    ChatterTimesheet::WORKFLOW_READY_FOR_REVIEW,
                    ChatterTimesheet::WORKFLOW_APPROVED,
                    ChatterTimesheet::WORKFLOW_CLOSED,
                ], true)) {
                    $query->whereWorkflowStatus($status);

                    return;
                }

                $query->where('status', $status);
            })
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

    /** @return array{generated_usd_pence: int, generated_gbp_pence: int, commission_usd_pence: int, commission_gbp_pence: int, commission_gbp_usd_pence: int} */
    private function earningDisplaySummaryForTimesheet(ChatterTimesheet $timesheet, ChatterPayrollService $payroll, ?Collection $sourceShifts = null): array
    {
        $summary = $payroll->earningSummaryForSnapshot(
            $timesheet->calculation_snapshot ?? [],
            fn (array $shift): bool => $this->snapshotCommissionCanBeDerived($shift, $timesheet, $payroll, $sourceShifts),
        );

        $result = [
            'generated_usd_pence' => $summary['generated_usd_pence'],
            'generated_gbp_pence' => $summary['generated_gbp_pence'],
            'commission_usd_pence' => max((int) $timesheet->commission_pence, $summary['commission_usd_pence']),
            'commission_gbp_pence' => max((int) $timesheet->foreign_commission_pence, $summary['commission_gbp_pence']),
        ];
        $result['commission_gbp_usd_pence'] = $payroll->foreignCommissionUsdPenceForTimesheet($timesheet, $result);

        return $result;
    }

    private function snapshotSourceShifts(Collection $timesheets): Collection
    {
        $shiftIds = $timesheets
            ->flatMap(fn (ChatterTimesheet $timesheet): array => collect($timesheet->calculation_snapshot['shifts'] ?? [])->pluck('shift_id')->filter()->all())
            ->unique()
            ->values();

        if ($shiftIds->isEmpty()) {
            return collect();
        }

        return ChatterShift::query()
            ->whereIn('id', $shiftIds)
            ->get(['id', 'clocked_out_at', 'commission_currency'])
            ->keyBy('id');
    }

    private function snapshotCommissionCanBeDerived(array $shift, ChatterTimesheet $timesheet, ChatterPayrollService $payroll, ?Collection $sourceShifts): bool
    {
        if ((int) ($shift['commission_pence'] ?? 0) > 0 || (int) ($shift['foreign_commission_pence'] ?? 0) > 0) {
            return true;
        }

        if (! $sourceShifts || ! isset($shift['shift_id'])) {
            return true;
        }

        $sourceShift = $sourceShifts->get((int) $shift['shift_id']);

        if (! $sourceShift) {
            return true;
        }

        $window = $payroll->reportingWindowFor($timesheet->period_start, $timesheet->period_end);

        if (! $sourceShift->clocked_out_at) {
            return false;
        }

        $clockedOutAt = CarbonImmutable::instance($sourceShift->clocked_out_at)->utc()->startOfMinute();

        return $clockedOutAt->greaterThan($window['start']) && $clockedOutAt->lessThanOrEqualTo($window['end']);
    }

    /** @param array{commission_usd_pence: int, commission_gbp_pence?: int} $summary */
    private function displayGrossPayPence(ChatterTimesheet $timesheet, array $summary, ChatterPayrollService $payroll): int
    {
        if ($timesheet->status === ChatterTimesheet::STATUS_APPROVED) {
            return (int) $timesheet->gross_pay_pence;
        }

        $basePayPence = (int) ($timesheet->base_pay_pence
            ?? data_get($timesheet->calculation_snapshot, 'base_pay_pence', (int) $timesheet->gross_pay_pence - (int) $timesheet->adjustment_pence - (int) $timesheet->commission_pence));

        return $basePayPence
            + (int) $summary['commission_usd_pence']
            + $payroll->foreignCommissionUsdPenceForTimesheet($timesheet, $summary)
            + (int) $timesheet->adjustment_pence;
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
        return collect(range(1, 13))
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
