<?php

namespace App\Support;

use App\Models\ChatterTimesheet;
use App\Services\UsdPhpExchangeRateService;

class ChatterCurrency
{
    private const RATE_SCALE = 10000;

    public function __construct(private readonly UsdPhpExchangeRateService $exchangeRates) {}

    public function usdToPhpRate(): string
    {
        return $this->exchangeRates->current()['rate'];
    }

    /** @return array{rate: string, rate_date: ?string, fetched_at: ?string, provider: string, is_fallback: bool, is_stale: bool} */
    public function usdToPhpDetails(): array
    {
        return $this->exchangeRates->current();
    }

    public function phpCentavosFromUsdCents(int $usdCents, string|float|null $rate = null): int
    {
        $scaledRate = (int) round(((float) ($rate ?? $this->usdToPhpRate())) * self::RATE_SCALE);
        $absolute = intdiv((abs($usdCents) * $scaledRate) + intdiv(self::RATE_SCALE, 2), self::RATE_SCALE);

        return $usdCents < 0 ? -$absolute : $absolute;
    }

    public function rateForTimesheet(ChatterTimesheet $timesheet): string
    {
        $snapshottedRate = $timesheet->status === ChatterTimesheet::STATUS_APPROVED
            ? data_get($timesheet->calculation_snapshot, 'usd_to_php_rate')
            : null;

        return number_format(
            (float) ($snapshottedRate ?? $this->usdToPhpRate()),
            4,
            '.',
            '',
        );
    }

    public function phpCentavosForTimesheet(ChatterTimesheet $timesheet): int
    {
        $snapshotted = $timesheet->status === ChatterTimesheet::STATUS_APPROVED
            ? data_get($timesheet->calculation_snapshot, 'gross_pay_php_centavos')
            : null;

        return $snapshotted !== null
            ? (int) $snapshotted
            : $this->phpCentavosFromUsdCents($timesheet->gross_pay_pence, $this->rateForTimesheet($timesheet));
    }
}
