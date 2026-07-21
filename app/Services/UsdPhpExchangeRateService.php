<?php

namespace App\Services;

use App\Models\SiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class UsdPhpExchangeRateService
{
    public const SETTINGS_KEY = 'chatter_payroll_currency';

    private const FAILURE_CACHE_KEY = 'chatter_payroll_currency.refresh_failed';

    /** @return array{rate: string, rate_date: ?string, fetched_at: ?string, provider: string, is_fallback: bool, is_stale: bool} */
    public function current(): array
    {
        $stored = $this->stored();

        if (! config('services.chatter_payroll.exchange_rate_enabled', true)) {
            return $this->fallback($stored);
        }

        if ($this->isFresh($stored) || Cache::has(self::FAILURE_CACHE_KEY)) {
            return $this->normalize($stored);
        }

        return $this->refresh();
    }

    /** @return array{rate: string, rate_date: ?string, fetched_at: ?string, provider: string, is_fallback: bool, is_stale: bool} */
    public function refresh(): array
    {
        $stored = $this->stored();

        if (! config('services.chatter_payroll.exchange_rate_enabled', true)) {
            return $this->fallback($stored);
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.chatter_payroll.exchange_rate_timeout', 5))
                ->retry(2, 200)
                ->get((string) config('services.chatter_payroll.exchange_rate_url'), [
                    'base' => 'USD',
                    'symbols' => 'PHP',
                ])
                ->throw();

            $rate = (float) $response->json('rates.PHP');
            if ($rate <= 0 || $rate > 1000) {
                throw new RuntimeException('The exchange-rate provider returned an invalid USD/PHP rate.');
            }

            $details = [
                'usd_to_php_rate' => number_format($rate, 4, '.', ''),
                'rate_date' => $response->json('date'),
                'fetched_at' => now('UTC')->toIso8601String(),
                'provider' => 'Frankfurter',
            ];

            SiteSetting::set(self::SETTINGS_KEY, $details);
            Cache::forget(self::FAILURE_CACHE_KEY);

            return $this->normalize($details);
        } catch (Throwable $exception) {
            Cache::put(
                self::FAILURE_CACHE_KEY,
                true,
                now()->addMinutes((int) config('services.chatter_payroll.exchange_rate_retry_minutes', 15)),
            );

            Log::warning('Automatic USD/PHP exchange-rate refresh failed; using the last known rate.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->fallback($stored);
        }
    }

    /** @return array<string, mixed> */
    private function stored(): array
    {
        $settings = SiteSetting::get(self::SETTINGS_KEY, []);

        return is_array($settings) ? $settings : [];
    }

    /** @param array<string, mixed> $details */
    private function isFresh(array $details): bool
    {
        if (empty($details['fetched_at'])) {
            return false;
        }

        try {
            $fetchedAt = CarbonImmutable::parse((string) $details['fetched_at'], 'UTC');
        } catch (Throwable) {
            return false;
        }

        return $fetchedAt->greaterThan(
            now('UTC')->subHours((int) config('services.chatter_payroll.exchange_rate_refresh_hours', 6)),
        );
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array{rate: string, rate_date: ?string, fetched_at: ?string, provider: string, is_fallback: bool, is_stale: bool}
     */
    private function normalize(array $details): array
    {
        $rate = $details['usd_to_php_rate']
            ?? config('services.chatter_payroll.usd_to_php_rate_fallback', '61.40');

        return [
            'rate' => number_format(max(0.0001, (float) $rate), 4, '.', ''),
            'rate_date' => isset($details['rate_date']) ? (string) $details['rate_date'] : null,
            'fetched_at' => isset($details['fetched_at']) ? (string) $details['fetched_at'] : null,
            'provider' => (string) ($details['provider'] ?? 'Configured fallback'),
            'is_fallback' => empty($details['fetched_at']),
            'is_stale' => ! $this->isFresh($details),
        ];
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array{rate: string, rate_date: ?string, fetched_at: ?string, provider: string, is_fallback: bool, is_stale: bool}
     */
    private function fallback(array $stored): array
    {
        return $this->normalize($stored);
    }
}
