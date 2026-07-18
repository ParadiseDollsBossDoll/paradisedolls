<?php

namespace App\Services;

use App\Models\ChatterPayRate;
use App\Models\ChatterShift;
use App\Models\ChatterTimesheet;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ChatterPayrollService
{
    public const REPORTING_TIMEZONE = 'Europe/London';
    private const BASIS_POINTS = 10000;
    private const PAY_DENOMINATOR = 600000;

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

        return ChatterTimesheet::query()->firstOrCreate(
            ['user_id' => $user->id, 'period_start' => $start->toDateString()],
            ['period_end' => $start->addDays(6)->toDateString(), 'status' => ChatterTimesheet::STATUS_DRAFT]
        );
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

    /** @return array{paid_minutes: int, break_minutes: int} */
    public function workedTotals(User $user, CarbonInterface $start, CarbonInterface $end): array
    {
        $startUtc = CarbonImmutable::instance($start)->utc()->startOfMinute();
        $endUtc = CarbonImmutable::instance($end)->utc()->startOfMinute();
        $nowUtc = CarbonImmutable::now('UTC')->startOfMinute();
        $paidMinutes = 0;
        $breakMinutes = 0;

        $shifts = ChatterShift::query()
            ->where('user_id', $user->id)
            ->where('clocked_in_at', '<', $endUtc)
            ->where(fn ($query) => $query->whereNull('clocked_out_at')->orWhere('clocked_out_at', '>', $startUtc))
            ->with('breaks')
            ->get();

        foreach ($shifts as $shift) {
            $shiftStart = CarbonImmutable::instance($shift->clocked_in_at)->utc()->startOfMinute()->max($startUtc);
            $rawEnd = $shift->clocked_out_at
                ? CarbonImmutable::instance($shift->clocked_out_at)->utc()->startOfMinute()
                : $nowUtc;
            $shiftEnd = $rawEnd->min($endUtc);

            for ($cursor = $shiftStart; $cursor->lessThan($shiftEnd); $cursor = $cursor->addMinute()) {
                if ($this->minuteIsBreak($cursor, $shift->breaks, $shiftEnd)) {
                    $breakMinutes++;
                } else {
                    $paidMinutes++;
                }
            }
        }

        return [
            'paid_minutes' => $paidMinutes,
            'break_minutes' => $breakMinutes,
        ];
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
            ->with('breaks')
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

            $row = ['shift_id' => $shift->id, 'started_at' => $shiftStart->toIso8601String(), 'ended_at' => $shiftEnd->toIso8601String(), 'paid_minutes' => 0, 'break_minutes' => 0, 'pay_pence' => 0];
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
                $rate = $this->rateForDate($rates, $local);
                $paidMinutes++;
                $row['paid_minutes']++;

                $night = $rate ? $this->isNightMinute($local, $rate) : false;
                $weekend = $local->isWeekend();
                $overtime = $rate && $paidMinutes > $rate->overtime_threshold_minutes;

                if ($night) {
                    $nightMinutes++;
                }
                if ($weekend) {
                    $weekendMinutes++;
                }
                if ($overtime) {
                    $overtimeMinutes++;
                }

                if ($rate) {
                    $premiumBps = max(
                        self::BASIS_POINTS,
                        $night ? $rate->night_premium_bps : self::BASIS_POINTS,
                        $weekend ? $rate->weekend_premium_bps : self::BASIS_POINTS,
                    );
                    $combinedBps = self::BASIS_POINTS
                        + ($premiumBps - self::BASIS_POINTS)
                        + ($overtime ? $rate->overtime_multiplier_bps - self::BASIS_POINTS : 0);
                    $minuteNumerator = $rate->base_rate_pence * $combinedBps;
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

        return [
            'ordinary_minutes' => $paidMinutes,
            'break_minutes' => $breakMinutes,
            'night_minutes' => $nightMinutes,
            'weekend_minutes' => $weekendMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'adjustment_pence' => $adjustmentPence,
            'gross_pay_pence' => $grossPayPence,
            'snapshot' => [
                'currency' => 'GBP',
                'reporting_timezone' => self::REPORTING_TIMEZONE,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'generated_at' => now('UTC')->toIso8601String(),
                'shifts' => $shiftRows,
                'rate_versions' => $rates->map(fn (ChatterPayRate $rate) => [
                    'id' => $rate->id,
                    'effective_from' => $rate->effective_from->toDateString(),
                    'base_rate_pence' => $rate->base_rate_pence,
                    'overtime_threshold_minutes' => $rate->overtime_threshold_minutes,
                    'overtime_multiplier_bps' => $rate->overtime_multiplier_bps,
                    'night_premium_bps' => $rate->night_premium_bps,
                    'weekend_premium_bps' => $rate->weekend_premium_bps,
                    'night_starts_at' => $rate->night_starts_at,
                    'night_ends_at' => $rate->night_ends_at,
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

    private function rateForDate(Collection $rates, CarbonImmutable $local): ?ChatterPayRate
    {
        return $rates->last(fn (ChatterPayRate $rate) => $rate->effective_from->toDateString() <= $local->toDateString());
    }

    private function isNightMinute(CarbonImmutable $local, ChatterPayRate $rate): bool
    {
        $time = $local->format('H:i:s');
        $start = $rate->night_starts_at;
        $end = $rate->night_ends_at;

        return $start <= $end
            ? $time >= $start && $time < $end
            : $time >= $start || $time < $end;
    }

    private function roundPay(int $numerator): int
    {
        return intdiv($numerator + intdiv(self::PAY_DENOMINATOR, 2), self::PAY_DENOMINATOR);
    }
}
