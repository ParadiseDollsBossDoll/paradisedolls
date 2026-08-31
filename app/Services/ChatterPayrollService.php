<?php

namespace App\Services;

use App\Models\ChatterPayRate;
use App\Models\ChatterProfile;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Models\User;
use App\Support\ChatterCurrency;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ChatterPayrollService
{
    public const REPORTING_TIMEZONE = 'Europe/London';
    private const BASIS_POINTS = 10000;
    private const PAY_DENOMINATOR = 600000;

    public function __construct(private readonly ChatterCurrency $currency)
    {
    }

    /** @return array{start: CarbonImmutable, end: CarbonImmutable} */
    public function periodFor(CarbonInterface $moment): array
    {
        $local = CarbonImmutable::instance($moment)->timezone(self::REPORTING_TIMEZONE);
        $start = $local->startOfWeek(CarbonInterface::MONDAY)->startOfDay();

        return ['start' => $start, 'end' => $start->addDays(6)->endOfDay()];
    }

    public function getOrCreate(User $user, CarbonInterface $periodStart): ChatterTimesheet
    {
        $start = CarbonImmutable::instance($periodStart)
            ->timezone(self::REPORTING_TIMEZONE)
            ->startOfWeek(CarbonInterface::MONDAY)
            ->startOfDay();

        $timesheet = ChatterTimesheet::query()
            ->where('user_id', $user->id)
            ->whereDate('period_start', $start->toDateString())
            ->first();

        return $timesheet ?: ChatterTimesheet::query()->create([
            'user_id' => $user->id,
            'period_start' => $start->toDateString(),
            'period_end' => $start->addDays(6)->toDateString(),
            'status' => ChatterTimesheet::STATUS_DRAFT,
        ]);
    }

    public function ensureWeeklyTimesheetsForActiveChatters(?CarbonInterface $through = null): int
    {
        $through ??= CarbonImmutable::now('UTC');
        $created = 0;

        User::query()
            ->where('role', 'chatter')
            ->whereHas('chatterProfile', fn ($query) => $query->where('employment_status', ChatterProfile::STATUS_ACTIVE))
            ->with('chatterProfile')
            ->chunkById(100, function (Collection $chatters) use ($through, &$created): void {
                foreach ($chatters as $chatter) {
                    $created += $this->ensureWeeklyTimesheetsFor($chatter, $through);
                }
            });

        return $created;
    }

    public function ensureWeeklyTimesheetsFor(User $user, CarbonInterface $through): int
    {
        $user->loadMissing('chatterProfile');
        $startedAt = $user->chatterProfile?->started_at ?: $user->created_at ?: $through;
        $cursor = $this->periodFor(CarbonImmutable::instance($startedAt))['start'];
        $lastPeriod = $this->periodFor($through)['start'];
        $created = 0;

        while ($cursor->lessThanOrEqualTo($lastPeriod)) {
            $timesheet = $this->getOrCreate($user, $cursor);

            if ($timesheet->wasRecentlyCreated) {
                $this->refresh($timesheet);
                $created++;
            }

            $cursor = $cursor->addWeek();
        }

        return $created;
    }

    public function refresh(ChatterTimesheet $timesheet): ChatterTimesheet
    {
        if ($timesheet->status === ChatterTimesheet::STATUS_APPROVED) {
            return $timesheet->refresh();
        }

        $timesheet->loadMissing('user', 'adjustments');
        $calculation = $this->calculate($timesheet->user, $timesheet->period_start, $timesheet->period_end, $timesheet);

        $timesheet->forceFill([
            'ordinary_minutes' => $calculation['ordinary_minutes'],
            'break_minutes' => $calculation['break_minutes'],
            'night_minutes' => $calculation['night_minutes'],
            'weekend_minutes' => $calculation['weekend_minutes'],
            'overtime_minutes' => $calculation['overtime_minutes'],
            'adjustment_pence' => $calculation['adjustment_pence'],
            'base_pay_pence' => $calculation['base_pay_pence'],
            'commission_pence' => $calculation['commission_pence'],
            'foreign_commission_currency' => $calculation['foreign_commission_currency'],
            'foreign_commission_pence' => $calculation['foreign_commission_pence'],
            'foreign_commission_usd_pence' => $calculation['foreign_commission_usd_pence'],
            'gbp_to_usd_rate' => $calculation['gbp_to_usd_rate'],
            'gbp_to_usd_rate_date' => $calculation['gbp_to_usd_rate_date'],
            'gbp_to_usd_rate_fetched_at' => $calculation['gbp_to_usd_rate_fetched_at'],
            'gbp_to_usd_rate_provider' => $calculation['gbp_to_usd_rate_provider'],
            'gross_pay_pence' => $calculation['gross_pay_pence'],
            'calculation_snapshot' => $calculation['snapshot'],
        ])->save();

        return $timesheet->refresh();
    }

    public function refreshPeriodsTouchedBy(ChatterShift $shift): void
    {
        $end = $shift->clocked_out_at ?: now('UTC');
        $period = $this->periodFor($shift->clocked_in_at);
        $lastPeriod = $this->periodFor($end);
        $cursor = $period['start'];

        while ($cursor->lessThanOrEqualTo($lastPeriod['start'])) {
            $timesheet = $this->getOrCreate($shift->user, $cursor);
            $this->refresh($timesheet);
            $cursor = $cursor->addWeek();
        }
    }

    /** @return array{start: CarbonImmutable, end: CarbonImmutable} */
    public function reportingWindowFor(CarbonInterface $periodStart, CarbonInterface $periodEnd): array
    {
        $startLocal = CarbonImmutable::parse($periodStart->toDateString().' 00:00:00', self::REPORTING_TIMEZONE);
        $endExclusiveLocal = CarbonImmutable::parse($periodEnd->toDateString().' 00:00:00', self::REPORTING_TIMEZONE)->addDay();

        return ['start' => $startLocal->utc()->startOfMinute(), 'end' => $endExclusiveLocal->utc()->startOfMinute()];
    }

    /** @return array{started_at: CarbonImmutable, ended_at: ?CarbonImmutable, ended_at_exclusive: CarbonImmutable, worked_minutes: int, paid_minutes: int, break_minutes: int, is_open: bool}|null */
    public function shiftSegmentForWindow(ChatterShift $shift, CarbonInterface $start, CarbonInterface $end): ?array
    {
        $startUtc = CarbonImmutable::instance($start)->utc()->startOfMinute();
        $endUtc = CarbonImmutable::instance($end)->utc()->startOfMinute();
        $nowUtc = CarbonImmutable::now('UTC')->startOfMinute();
        $shiftStart = CarbonImmutable::instance($shift->clocked_in_at)->utc()->startOfMinute()->max($startUtc);
        $rawEnd = $shift->clocked_out_at
            ? CarbonImmutable::instance($shift->clocked_out_at)->utc()->startOfMinute()
            : $nowUtc;
        $shiftEnd = $rawEnd->min($endUtc);

        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            return null;
        }

        $shift->loadMissing('breaks');
        $breakMinutes = $this->breakMinutesWithin($shift->breaks, $shiftStart, $shiftEnd);
        $elapsedMinutes = (int) $shiftStart->diffInMinutes($shiftEnd);
        $paidMinutes = max(0, $elapsedMinutes - $breakMinutes);
        $isOpen = $shift->clocked_out_at === null && $rawEnd->lessThan($endUtc);

        return [
            'started_at' => $shiftStart,
            'ended_at' => $isOpen ? null : $this->displaySegmentEnd($shiftEnd, $rawEnd),
            'ended_at_exclusive' => $shiftEnd,
            'worked_minutes' => $paidMinutes,
            'paid_minutes' => $paidMinutes,
            'break_minutes' => $breakMinutes,
            'is_open' => $isOpen,
        ];
    }

    public function annotateShiftSegment(ChatterShift $shift, CarbonInterface $start, CarbonInterface $end): ?ChatterShift
    {
        $segment = $this->shiftSegmentForWindow($shift, $start, $end);

        if ($segment === null) {
            return null;
        }

        $shift->setAttribute('segment_clocked_in_at', $segment['started_at']);
        $shift->setAttribute('segment_clocked_out_at', $segment['ended_at']);
        $shift->setAttribute('segment_clocked_out_at_exclusive', $segment['ended_at_exclusive']);
        $shift->setAttribute('segment_is_open', $segment['is_open']);
        $shift->setAttribute('worked_minutes', $segment['worked_minutes']);
        $shift->setAttribute('paid_minutes', $segment['paid_minutes']);
        $shift->setAttribute('break_minutes', $segment['break_minutes']);

        return $shift;
    }

    /** @return array{worked_minutes: int, paid_minutes: int, break_minutes: int} */
    public function workedTotals(User $user, CarbonInterface $start, CarbonInterface $end): array
    {
        $startUtc = CarbonImmutable::instance($start)->utc()->startOfMinute();
        $endUtc = CarbonImmutable::instance($end)->utc()->startOfMinute();
        $nowUtc = CarbonImmutable::now('UTC')->startOfMinute();
        $workedMinutes = 0;
        $breakMinutes = 0;

        $shifts = ChatterShift::query()
            ->where('user_id', $user->id)
            ->where('clocked_in_at', '<', $endUtc)
            ->where(fn ($query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $startUtc))
            ->with(['breaks', 'workRole', 'model:id,name,email'])
            ->get();

        foreach ($shifts as $shift) {
            $shiftStart = CarbonImmutable::instance($shift->clocked_in_at)->utc()->startOfMinute()->max($startUtc);
            $rawEnd = $shift->clocked_out_at
                ? CarbonImmutable::instance($shift->clocked_out_at)->utc()->startOfMinute()
                : $nowUtc;
            $shiftEnd = $rawEnd->min($endUtc);

            if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
                continue;
            }

            $shiftBreakMinutes = $this->breakMinutesWithin($shift->breaks, $shiftStart, $shiftEnd);
            $elapsedMinutes = (int) $shiftStart->diffInMinutes($shiftEnd);
            $breakMinutes += $shiftBreakMinutes;
            $workedMinutes += max(0, $elapsedMinutes - $shiftBreakMinutes);
        }

        return [
            'worked_minutes' => $workedMinutes,
            'paid_minutes' => $workedMinutes,
            'break_minutes' => $breakMinutes,
        ];
    }

    /** @return array{worked_minutes: int, paid_minutes: int, break_minutes: int} */
    public function shiftWorkedTotals(ChatterShift $shift, CarbonInterface $start, CarbonInterface $end): array
    {
        $segment = $this->shiftSegmentForWindow($shift, $start, $end);

        if ($segment === null) {
            return ['worked_minutes' => 0, 'paid_minutes' => 0, 'break_minutes' => 0];
        }

        return [
            'worked_minutes' => $segment['worked_minutes'],
            'paid_minutes' => $segment['paid_minutes'],
            'break_minutes' => $segment['break_minutes'],
        ];
    }

    public function shiftWorkedSeconds(ChatterShift $shift, ?CarbonInterface $until = null): int
    {
        $shift->loadMissing('breaks');
        $start = CarbonImmutable::instance($shift->clocked_in_at)->utc();
        $end = CarbonImmutable::instance($until ?: $shift->clocked_out_at ?: now('UTC'))->utc();

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $intervals = $shift->breaks
            ->map(function ($break) use ($start, $end): ?array {
                $breakStart = CarbonImmutable::instance($break->started_at)->utc()->max($start);
                $breakEnd = CarbonImmutable::instance($break->ended_at ?: $end)->utc()->min($end);

                return $breakEnd->greaterThan($breakStart) ? [$breakStart, $breakEnd] : null;
            })
            ->filter()
            ->sortBy(fn (array $interval) => $interval[0]->getTimestamp())
            ->values();

        $merged = [];
        foreach ($intervals as [$intervalStart, $intervalEnd]) {
            $lastIndex = count($merged) - 1;
            if ($lastIndex >= 0 && $intervalStart->lessThanOrEqualTo($merged[$lastIndex][1])) {
                if ($intervalEnd->greaterThan($merged[$lastIndex][1])) {
                    $merged[$lastIndex][1] = $intervalEnd;
                }
                continue;
            }

            $merged[] = [$intervalStart, $intervalEnd];
        }

        $breakSeconds = array_sum(array_map(
            fn (array $interval): int => (int) $interval[0]->diffInSeconds($interval[1]),
            $merged,
        ));

        return max(0, (int) $start->diffInSeconds($end) - $breakSeconds);
    }

    /** @return array{currency: string, pence: int} */
    public function commissionForShift(ChatterShift $shift): array
    {
        $currency = $shift->commission_currency
            ?: $shift->earning_currency
            ?: ChatterPlatformEarnings::CURRENCY_USD;
        $storedPence = max(0, (int) $shift->commission_pence);

        if ($storedPence > 0) {
            return ['currency' => $currency, 'pence' => $storedPence];
        }

        return [
            'currency' => $currency,
            'pence' => $this->commissionFromGenerated(
                (int) $shift->generated_earning_pence,
                (int) ($shift->commission_bps ?: 300),
            ),
        ];
    }

    /** @param array<string, mixed> $shift */
    public function commissionForSnapshotShift(array $shift, bool $deriveMissing = true): array
    {
        $currency = (string) ($shift['foreign_commission_currency']
            ?? $shift['commission_currency']
            ?? $shift['earning_currency']
            ?? ChatterPlatformEarnings::CURRENCY_USD);
        $storedPence = $currency === ChatterPlatformEarnings::CURRENCY_GBP
            ? (int) ($shift['foreign_commission_pence'] ?? $shift['commission_pence'] ?? 0)
            : (int) ($shift['commission_pence'] ?? 0);

        if ($storedPence > 0) {
            return ['currency' => $currency, 'pence' => $storedPence];
        }

        if (! $deriveMissing) {
            return ['currency' => $currency, 'pence' => 0];
        }

        return [
            'currency' => $currency,
            'pence' => $this->commissionFromGenerated(
                (int) ($shift['generated_earning_pence'] ?? 0),
                (int) (($shift['commission_bps'] ?? 0) ?: 300),
            ),
        ];
    }

    /** @param array<string, mixed> $snapshot */
    public function earningSummaryForSnapshot(array $snapshot, ?callable $shouldDeriveMissing = null): array
    {
        $summary = [
            'generated_usd_pence' => 0,
            'generated_gbp_pence' => 0,
            'commission_usd_pence' => 0,
            'commission_gbp_pence' => 0,
        ];

        foreach (($snapshot['shifts'] ?? []) as $shift) {
            if (! is_array($shift)) {
                continue;
            }

            $deriveMissing = $shouldDeriveMissing ? (bool) $shouldDeriveMissing($shift) : true;
            $commission = $this->commissionForSnapshotShift($shift, $deriveMissing);

            if ($commission['pence'] <= 0) {
                continue;
            }

            if ($commission['currency'] === ChatterPlatformEarnings::CURRENCY_GBP) {
                $summary['generated_gbp_pence'] += max(0, (int) ($shift['generated_earning_pence'] ?? 0));
                $summary['commission_gbp_pence'] += $commission['pence'];
            } else {
                $summary['generated_usd_pence'] += max(0, (int) ($shift['generated_earning_pence'] ?? 0));
                $summary['commission_usd_pence'] += $commission['pence'];
            }
        }

        return $summary;
    }

    /** @return array<string, mixed> */
    public function calculate(
        User $user,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
        ?ChatterTimesheet $timesheet = null,
    ): array {
        $window = $this->reportingWindowFor($periodStart, $periodEnd);
        $startUtc = $window['start'];
        $endExclusiveUtc = $window['end'];
        $nowUtc = CarbonImmutable::now('UTC')->startOfMinute();

        $shifts = ChatterShift::query()
            ->where('user_id', $user->id)
            ->where('clocked_in_at', '<', $endExclusiveUtc)
            ->where(function ($query) use ($startUtc) {
                $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $startUtc);
            })
            ->with(['breaks', 'workRole', 'model:id,name,email'])
            ->orderBy('clocked_in_at')
            ->get();

        $rates = ChatterPayRate::query()
            ->where('user_id', $user->id)
            ->whereDate('effective_from', '<=', $periodEnd->toDateString())
            ->orderBy('effective_from')
            ->get();

        $paidMinutes = 0;
        $breakMinutes = 0;
        $nightMinutes = 0;
        $weekendMinutes = 0;
        $overtimeMinutes = 0;
        $payNumerator = 0;
        $commissionPence = 0;
        $foreignCommissionCurrency = null;
        $foreignCommissionPence = 0;
        $shiftRows = [];

        foreach ($shifts as $shift) {
            $shiftStart = CarbonImmutable::instance($shift->clocked_in_at)->utc()->startOfMinute()->max($startUtc);
            $rawEnd = $shift->clocked_out_at
                ? CarbonImmutable::instance($shift->clocked_out_at)->utc()->startOfMinute()
                : $nowUtc;
            $shiftEnd = $rawEnd->min($endExclusiveUtc);

            if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
                continue;
            }

            $row = [
                'shift_id' => $shift->id,
                'work_role_id' => $shift->chatter_work_role_id,
                'work_role' => $shift->workRole?->name ?? 'Chatter',
                'platform' => $shift->platform,
                'model_id' => $shift->model_id,
                'model_name' => $shift->model?->name,
                'model_email' => $shift->model?->email,
                'hourly_rate_pence' => $shift->hourly_rate_pence,
                'earning_platform_key' => $shift->earning_platform_key,
                'earning_unit' => $shift->earning_unit,
                'earning_currency' => $shift->earning_currency,
                'earning_unit_value_usd_micro' => $shift->earning_unit_value_usd_micro,
                'clock_in_earning_balance_minor' => $shift->clock_in_earning_balance_minor,
                'clock_out_earning_balance_minor' => $shift->clock_out_earning_balance_minor,
                'generated_earning_units' => $shift->generated_earning_units,
                'generated_earning_pence' => $shift->generated_earning_pence,
                'commission_bps' => $shift->commission_bps,
                'commission_currency' => $shift->commission_currency,
                'commission_pence' => 0,
                'foreign_commission_currency' => null,
                'foreign_commission_pence' => 0,
                'started_at' => $shiftStart->toIso8601String(),
                'ended_at' => $this->displaySegmentEnd($shiftEnd, $rawEnd)->toIso8601String(),
                'ended_at_exclusive' => $shiftEnd->toIso8601String(),
                'paid_minutes' => 0,
                'break_minutes' => 0,
                'pay_pence' => 0,
            ];
            $rowPayNumerator = 0;
            $cursor = $shiftStart;

            while ($cursor->lessThan($shiftEnd)) {
                if ($this->minuteIsBreak($cursor, $shift->breaks, $shiftEnd)) {
                    $breakMinutes++;
                    $row['break_minutes']++;
                    $cursor = $cursor->addMinute();
                    continue;
                }

                $local = $cursor->timezone(self::REPORTING_TIMEZONE);
                $baseRatePence = $shift->hourly_rate_pence ?? $this->rateForDate($rates, $local)?->base_rate_pence;
                $paidMinutes++;
                $row['paid_minutes']++;

                if ($baseRatePence !== null) {
                    $minuteNumerator = $baseRatePence * self::BASIS_POINTS;
                    $payNumerator += $minuteNumerator;
                    $rowPayNumerator += $minuteNumerator;
                }

                $cursor = $cursor->addMinute();
            }

            $row['pay_pence'] = $this->roundPay($rowPayNumerator);
            if ($this->commissionBelongsToWindow($shift, $startUtc, $endExclusiveUtc)) {
                $shiftCommission = $this->commissionForShift($shift);

                if ($shiftCommission['currency'] === ChatterPlatformEarnings::CURRENCY_GBP) {
                    $row['foreign_commission_currency'] = ChatterPlatformEarnings::CURRENCY_GBP;
                    $row['foreign_commission_pence'] = $shiftCommission['pence'];
                    $foreignCommissionCurrency = ChatterPlatformEarnings::CURRENCY_GBP;
                    $foreignCommissionPence += $shiftCommission['pence'];
                } else {
                    $row['commission_currency'] = ChatterPlatformEarnings::CURRENCY_USD;
                    $row['commission_pence'] = $shiftCommission['pence'];
                    $commissionPence += $shiftCommission['pence'];
                }
            }
            $shiftRows[] = $row;
        }

        $adjustmentPence = $timesheet?->adjustments->sum('amount_pence') ?? 0;
        $basePayPence = $this->roundPay($payNumerator);
        $gbpToUsdDetails = $foreignCommissionPence > 0 && $foreignCommissionCurrency === ChatterPlatformEarnings::CURRENCY_GBP
            ? $this->currency->gbpToUsdDetails()
            : null;
        $gbpToUsdRate = $gbpToUsdDetails['rate'] ?? null;
        $foreignCommissionUsdPence = $gbpToUsdRate
            ? $this->currency->usdCentsFromGbpPence($foreignCommissionPence, $gbpToUsdRate)
            : 0;
        $grossPayPence = $basePayPence + $commissionPence + $foreignCommissionUsdPence + $adjustmentPence;
        $currencyDetails = $this->currency->usdToPhpDetails();
        $usdToPhpRate = $currencyDetails['rate'];
        $grossPayPhpCentavos = $this->currency->phpCentavosFromUsdCents($grossPayPence, $usdToPhpRate);

        return [
            'ordinary_minutes' => $paidMinutes,
            'break_minutes' => $breakMinutes,
            'night_minutes' => $nightMinutes,
            'weekend_minutes' => $weekendMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'adjustment_pence' => $adjustmentPence,
            'base_pay_pence' => $basePayPence,
            'commission_pence' => $commissionPence,
            'foreign_commission_currency' => $foreignCommissionCurrency,
            'foreign_commission_pence' => $foreignCommissionPence,
            'foreign_commission_usd_pence' => $foreignCommissionUsdPence,
            'gbp_to_usd_rate' => $gbpToUsdRate,
            'gbp_to_usd_rate_date' => $gbpToUsdDetails['rate_date'] ?? null,
            'gbp_to_usd_rate_fetched_at' => $gbpToUsdDetails['fetched_at'] ?? null,
            'gbp_to_usd_rate_provider' => $gbpToUsdDetails['provider'] ?? null,
            'gross_pay_pence' => $grossPayPence,
            'snapshot' => [
                'currency' => 'USD',
                'base_currency_minor_unit' => 'cent',
                'gbp_to_usd_rate' => $gbpToUsdRate,
                'gbp_to_usd_rate_date' => $gbpToUsdDetails['rate_date'] ?? null,
                'gbp_to_usd_rate_fetched_at' => $gbpToUsdDetails['fetched_at'] ?? null,
                'gbp_to_usd_rate_provider' => $gbpToUsdDetails['provider'] ?? null,
                'usd_to_php_rate' => $usdToPhpRate,
                'usd_to_php_rate_date' => $currencyDetails['rate_date'],
                'usd_to_php_rate_fetched_at' => $currencyDetails['fetched_at'],
                'usd_to_php_rate_provider' => $currencyDetails['provider'],
                'gross_pay_php_centavos' => $grossPayPhpCentavos,
                'base_pay_pence' => $basePayPence,
                'commission_pence' => $commissionPence,
                'foreign_commission_currency' => $foreignCommissionCurrency,
                'foreign_commission_pence' => $foreignCommissionPence,
                'foreign_commission_usd_pence' => $foreignCommissionUsdPence,
                'adjustment_pence' => $adjustmentPence,
                'commission_allocation' => 'full_shift_commission_is_counted_in_the_payroll_week_that_contains_clock_out; gbp_commission_is_converted_to_usd_for_total_pay_using_the_payroll_gbp_to_usd_reference_rate',
                'reporting_timezone' => self::REPORTING_TIMEZONE,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'generated_at' => now('UTC')->toIso8601String(),
                'shifts' => $shiftRows,
                'rate_versions' => $rates->map(fn (ChatterPayRate $rate) => [
                    'id' => $rate->id,
                    'effective_from' => $rate->effective_from->toDateString(),
                    'base_rate_pence' => $rate->base_rate_pence,
                ])->values()->all(),
            ],
        ];
    }

    /** @param array{commission_gbp_pence?: int} $summary */
    public function foreignCommissionUsdPenceForTimesheet(ChatterTimesheet $timesheet, array $summary): int
    {
        if ($timesheet->status === ChatterTimesheet::STATUS_APPROVED) {
            return (int) data_get(
                $timesheet->calculation_snapshot,
                'foreign_commission_usd_pence',
                (int) $timesheet->foreign_commission_usd_pence,
            );
        }

        $stored = (int) $timesheet->foreign_commission_usd_pence;
        if ($stored > 0) {
            return $stored;
        }

        $gbpPence = (int) ($summary['commission_gbp_pence'] ?? $timesheet->foreign_commission_pence ?? 0);

        return $gbpPence > 0
            ? $this->currency->usdCentsFromGbpPence($gbpPence)
            : 0;
    }

    private function minuteIsBreak(CarbonImmutable $minute, Collection $breaks, CarbonImmutable $shiftEnd): bool
    {
        foreach ($breaks as $break) {
            $start = CarbonImmutable::instance($break->started_at)->utc()->startOfMinute();
            $end = $break->ended_at
                ? CarbonImmutable::instance($break->ended_at)->utc()->startOfMinute()
                : $shiftEnd;

            if ($minute->greaterThanOrEqualTo($start) && $minute->lessThan($end)) {
                return true;
            }
        }

        return false;
    }

    private function breakMinutesWithin(Collection $breaks, CarbonImmutable $start, CarbonImmutable $end): int
    {
        $intervals = $breaks
            ->map(function ($break) use ($start, $end): ?array {
                $breakStart = CarbonImmutable::instance($break->started_at)->utc()->startOfMinute()->max($start);
                $breakEnd = ($break->ended_at
                    ? CarbonImmutable::instance($break->ended_at)->utc()->startOfMinute()
                    : $end)->min($end);

                return $breakEnd->greaterThan($breakStart) ? [$breakStart, $breakEnd] : null;
            })
            ->filter()
            ->sortBy(fn (array $interval) => $interval[0]->getTimestamp())
            ->values();

        if ($intervals->isEmpty()) {
            return 0;
        }

        $merged = [];
        foreach ($intervals as [$intervalStart, $intervalEnd]) {
            $lastIndex = count($merged) - 1;
            if ($lastIndex >= 0 && $intervalStart->lessThanOrEqualTo($merged[$lastIndex][1])) {
                if ($intervalEnd->greaterThan($merged[$lastIndex][1])) {
                    $merged[$lastIndex][1] = $intervalEnd;
                }
                continue;
            }

            $merged[] = [$intervalStart, $intervalEnd];
        }

        return array_sum(array_map(
            fn (array $interval): int => (int) $interval[0]->diffInMinutes($interval[1]),
            $merged,
        ));
    }

    private function displaySegmentEnd(CarbonImmutable $segmentEnd, CarbonImmutable $rawEnd): CarbonImmutable
    {
        if ($rawEnd->greaterThan($segmentEnd)) {
            return $segmentEnd->subMinute();
        }

        return $segmentEnd;
    }

    private function commissionBelongsToWindow(ChatterShift $shift, CarbonImmutable $startUtc, CarbonImmutable $endExclusiveUtc): bool
    {
        if (! $shift->clocked_out_at) {
            return false;
        }

        $clockedOutAt = CarbonImmutable::instance($shift->clocked_out_at)->utc()->startOfMinute();

        return $clockedOutAt->greaterThan($startUtc) && $clockedOutAt->lessThanOrEqualTo($endExclusiveUtc);
    }

    private function rateForDate(Collection $rates, CarbonImmutable $local): ?ChatterPayRate
    {
        return $rates->last(fn (ChatterPayRate $rate) => $rate->effective_from->toDateString() <= $local->toDateString());
    }

    private function roundPay(int $numerator): int
    {
        return intdiv($numerator + intdiv(self::PAY_DENOMINATOR, 2), self::PAY_DENOMINATOR);
    }

    private function commissionFromGenerated(int $generatedPence, int $commissionBps): int
    {
        if ($generatedPence <= 0 || $commissionBps <= 0) {
            return 0;
        }

        return intdiv(($generatedPence * $commissionBps) + intdiv(self::BASIS_POINTS, 2), self::BASIS_POINTS);
    }
}
