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
        $startUtc = CarbonImmutable::instance($start)->utc()->startOfMinute();
        $endUtc = CarbonImmutable::instance($end)->utc()->startOfMinute();
        $nowUtc = CarbonImmutable::now('UTC')->startOfMinute();
        $shiftStart = CarbonImmutable::instance($shift->clocked_in_at)->utc()->startOfMinute()->max($startUtc);
        $rawEnd = $shift->clocked_out_at
            ? CarbonImmutable::instance($shift->clocked_out_at)->utc()->startOfMinute()
            : $nowUtc;
        $shiftEnd = $rawEnd->min($endUtc);

        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            return ['worked_minutes' => 0, 'paid_minutes' => 0, 'break_minutes' => 0];
        }

        $shift->loadMissing('breaks');
        $breakMinutes = $this->breakMinutesWithin($shift->breaks, $shiftStart, $shiftEnd);
        $elapsedMinutes = (int) $shiftStart->diffInMinutes($shiftEnd);
        $paidMinutes = max(0, $elapsedMinutes - $breakMinutes);

        return [
            'worked_minutes' => $paidMinutes,
            'paid_minutes' => $paidMinutes,
            'break_minutes' => $breakMinutes,
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

    /** @return array<string, mixed> */
    public function calculate(
        User $user,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
        ?ChatterTimesheet $timesheet = null,
    ): array {
        $startLocal = CarbonImmutable::parse($periodStart->toDateString().' 00:00:00', self::REPORTING_TIMEZONE);
        $endExclusiveLocal = CarbonImmutable::parse($periodEnd->toDateString().' 00:00:00', self::REPORTING_TIMEZONE)->addDay();
        $startUtc = $startLocal->utc();
        $endExclusiveUtc = $endExclusiveLocal->utc();
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
                'started_at' => $shiftStart->toIso8601String(),
                'ended_at' => $shiftEnd->toIso8601String(),
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
            $shiftRows[] = $row;
        }

        $adjustmentPence = $timesheet?->adjustments->sum('amount_pence') ?? 0;
        $grossPayPence = $this->roundPay($payNumerator) + $adjustmentPence;
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
            'gross_pay_pence' => $grossPayPence,
            'snapshot' => [
                'currency' => 'USD',
                'base_currency_minor_unit' => 'cent',
                'usd_to_php_rate' => $usdToPhpRate,
                'usd_to_php_rate_date' => $currencyDetails['rate_date'],
                'usd_to_php_rate_fetched_at' => $currencyDetails['fetched_at'],
                'usd_to_php_rate_provider' => $currencyDetails['provider'],
                'gross_pay_php_centavos' => $grossPayPhpCentavos,
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

    private function rateForDate(Collection $rates, CarbonImmutable $local): ?ChatterPayRate
    {
        return $rates->last(fn (ChatterPayRate $rate) => $rate->effective_from->toDateString() <= $local->toDateString());
    }

    private function roundPay(int $numerator): int
    {
        return intdiv($numerator + intdiv(self::PAY_DENOMINATOR, 2), self::PAY_DENOMINATOR);
    }
}
