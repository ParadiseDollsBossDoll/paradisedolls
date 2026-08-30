<?php

namespace App\Services;

use App\Models\ChatterShift;
use Illuminate\Validation\ValidationException;

class ChatterPlatformEarnings
{
    public const UNIT_TOKENS = 'tokens';
    public const UNIT_GBP = 'gbp';
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_GBP = 'GBP';

    /** @return array<string, mixed> */
    public function clockInSnapshot(string $platform, mixed $balance): array
    {
        $definition = $this->definitionFor($platform);
        $balanceMinor = $this->parseBalance($balance, $definition, 'clock_in_earning_balance');

        return [
            'earning_platform_key' => $definition['key'],
            'earning_unit' => $definition['unit'],
            'earning_currency' => $definition['currency'],
            'earning_unit_value_usd_micro' => $definition['unit_value_usd_micro'],
            'clock_in_earning_balance_minor' => $balanceMinor,
            'commission_bps' => (int) $definition['commission_bps'],
            'commission_currency' => $definition['currency'],
        ];
    }

    /** @return array<string, mixed> */
    public function clockOutSnapshot(ChatterShift $shift, mixed $balance): array
    {
        $definition = $this->definitionForSnapshot($shift);
        $clockOutBalanceMinor = $this->parseBalance($balance, $definition, 'clock_out_earning_balance');
        $clockInBalanceMinor = $shift->clock_in_earning_balance_minor;

        if ($clockInBalanceMinor !== null && $clockOutBalanceMinor < (int) $clockInBalanceMinor) {
            throw ValidationException::withMessages([
                'clock_out_earning_balance' => __('Ending balance must be greater than or equal to the starting balance.'),
            ]);
        }

        $generatedUnits = $this->generatedUnits($clockInBalanceMinor, $clockOutBalanceMinor);
        $generatedMinor = $this->generatedMoneyMinor($generatedUnits, $definition);
        $commissionMinor = $this->commissionMinor($generatedMinor, (int) $shift->commission_bps);

        return [
            'clock_out_earning_balance_minor' => $clockOutBalanceMinor,
            'generated_earning_units' => $generatedUnits,
            'generated_earning_pence' => $generatedMinor,
            'commission_currency' => $definition['currency'],
            'commission_pence' => $commissionMinor,
        ];
    }

    /** @return array<string, mixed> */
    public function definitionFor(string $platform): array
    {
        $normalized = $this->normalize($platform);
        $platforms = config('chatter_platforms.platforms', []);

        foreach ($platforms as $key => $definition) {
            $aliases = $definition['aliases'] ?? [$key];
            foreach ($aliases as $alias) {
                if ($this->normalize((string) $alias) === $normalized) {
                    return $this->withDefaults((string) $key, $definition);
                }
            }
        }

        return $this->withDefaults('standard-token', config('chatter_platforms.default', []));
    }

    /** @return array<string, mixed> */
    public function definitionForSnapshot(ChatterShift $shift): array
    {
        $definition = $this->definitionFor((string) $shift->platform);

        return array_merge($definition, [
            'key' => $shift->earning_platform_key ?: $definition['key'],
            'unit' => $shift->earning_unit ?: $definition['unit'],
            'currency' => $shift->earning_currency ?: $definition['currency'],
            'unit_value_usd_micro' => $shift->earning_unit_value_usd_micro,
            'commission_bps' => $shift->commission_bps ?: $definition['commission_bps'],
        ]);
    }

    public function formatBalance(?int $value, ?string $unit, ?string $currency): string
    {
        if ($value === null) {
            return 'Missing';
        }

        if ($unit === self::UNIT_GBP || $currency === self::CURRENCY_GBP) {
            return 'GBP '.number_format($value / 100, 2);
        }

        return number_format($value).' tokens';
    }

    public function formatGenerated(?int $units, ?int $moneyMinor, ?string $unit, ?string $currency): string
    {
        if ($units === null || $moneyMinor === null) {
            return '0';
        }

        if ($unit === self::UNIT_GBP || $currency === self::CURRENCY_GBP) {
            return 'GBP '.number_format($moneyMinor / 100, 2);
        }

        return number_format($units).' tokens / $'.number_format($moneyMinor / 100, 2).' USD';
    }

    public function formatCommission(?int $minor, ?string $currency): string
    {
        $amount = number_format(((int) $minor) / 100, 2);

        return $currency === self::CURRENCY_GBP ? "GBP {$amount}" : "\${$amount} USD";
    }

    /** @param array<string, mixed> $definition */
    private function parseBalance(mixed $balance, array $definition, string $field): int
    {
        $raw = trim((string) $balance);

        if (($definition['unit'] ?? null) === self::UNIT_GBP) {
            return $this->parseDecimalMinor($raw, $field);
        }

        if (! preg_match('/^\d+$/', $raw)) {
            throw ValidationException::withMessages([
                $field => __('Enter a whole-number token balance.'),
            ]);
        }

        $value = (int) $raw;
        if ($value > 999999999) {
            throw ValidationException::withMessages([
                $field => __('Balance must be 999,999,999 or less.'),
            ]);
        }

        return $value;
    }

    private function parseDecimalMinor(string $raw, string $field): int
    {
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $raw)) {
            throw ValidationException::withMessages([
                $field => __('Enter a valid GBP amount with up to 2 decimal places.'),
            ]);
        }

        [$whole, $decimal] = array_pad(explode('.', $raw, 2), 2, '');
        $wholeValue = (int) $whole;
        if ($wholeValue > 9999999) {
            throw ValidationException::withMessages([
                $field => __('Balance amount is too large.'),
            ]);
        }

        return ($wholeValue * 100) + (int) str_pad($decimal, 2, '0');
    }

    private function generatedUnits(?int $clockInBalanceMinor, ?int $clockOutBalanceMinor): ?int
    {
        if ($clockInBalanceMinor === null || $clockOutBalanceMinor === null || $clockOutBalanceMinor < $clockInBalanceMinor) {
            return null;
        }

        return $clockOutBalanceMinor - $clockInBalanceMinor;
    }

    /** @param array<string, mixed> $definition */
    private function generatedMoneyMinor(?int $generatedUnits, array $definition): int
    {
        if ($generatedUnits === null || $generatedUnits <= 0) {
            return 0;
        }

        if (($definition['unit'] ?? null) === self::UNIT_GBP) {
            return $generatedUnits;
        }

        return intdiv(($generatedUnits * (int) ($definition['unit_value_usd_micro'] ?? 0)) + 5000, 10000);
    }

    private function commissionMinor(int $generatedMoneyMinor, int $commissionBps): int
    {
        if ($generatedMoneyMinor <= 0 || $commissionBps <= 0) {
            return 0;
        }

        return intdiv(($generatedMoneyMinor * $commissionBps) + 5000, 10000);
    }

    /** @param array<string, mixed> $definition */
    private function withDefaults(string $key, array $definition): array
    {
        return [
            'key' => $key,
            'label' => $definition['label'] ?? $key,
            'unit' => $definition['unit'] ?? self::UNIT_TOKENS,
            'currency' => $definition['currency'] ?? self::CURRENCY_USD,
            'unit_value_usd_micro' => array_key_exists('unit_value_usd_micro', $definition)
                ? $definition['unit_value_usd_micro']
                : 50000,
            'commission_bps' => (int) ($definition['commission_bps'] ?? 300),
        ];
    }

    private function normalize(string $value): string
    {
        return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $value));
    }
}
