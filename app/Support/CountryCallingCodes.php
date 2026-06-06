<?php

namespace App\Support;

class CountryCallingCodes
{
    /**
     * @param  array<string, array{name: string, code: string, prefixes?: array<int, string>}>  $callingCodes
     * @return array<int, array{value: string, country: string, name: string, code: string, prefix: string, dialNum: int, flag: string, search: string}>
     */
    public static function phoneOptions(array $callingCodes): array
    {
        return collect($callingCodes)
            ->flatMap(function (array $country, string $countryCode) {
                $prefixes = self::prefixes($country);

                return collect($prefixes)->map(function (string $prefix) use ($country, $countryCode, $prefixes) {
                    $displayCode = self::displayCode($prefix, $country['code']);
                    $digits = self::digits($prefix);

                    return [
                        'value' => count($prefixes) > 1 ? $countryCode.'-'.self::suffix($prefix, $country['code']) : $countryCode,
                        'country' => $countryCode,
                        'name' => $country['name'],
                        'code' => $displayCode,
                        'prefix' => self::compactPrefix($prefix),
                        'dialNum' => (int) $digits,
                        'flag' => 'https://flagcdn.com/w40/'.strtolower($countryCode).'.png',
                        'search' => strtolower($countryCode.' '.$country['name'].' '.$displayCode.' '.$digits),
                    ];
                });
            })
            ->sort(fn (array $a, array $b) => $a['dialNum'] <=> $b['dialNum']
                ?: strcasecmp($a['name'], $b['name'])
                ?: strcasecmp($a['code'], $b['code']))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array{name: string, code: string, prefixes?: array<int, string>}>  $callingCodes
     * @return array<string, array{country: string, name: string, prefix: string}>
     */
    public static function phonePrefixLookup(array $callingCodes): array
    {
        return collect(self::phoneOptions($callingCodes))
            ->mapWithKeys(fn (array $option) => [
                $option['value'] => [
                    'country' => $option['country'],
                    'name' => $option['name'],
                    'prefix' => $option['prefix'],
                ],
            ])
            ->all();
    }

    /**
     * @param  array<int, array{value: string, country: string}>  $phoneOptions
     */
    public static function normalizeSelection(?string $selected, array $phoneOptions, string $fallbackCountry = 'GB'): string
    {
        $selected = trim((string) $selected);
        $byValue = collect($phoneOptions)->keyBy('value');

        if ($selected !== '' && $byValue->has($selected)) {
            return $selected;
        }

        if ($selected !== '') {
            $firstForSelectedCountry = collect($phoneOptions)->firstWhere('country', $selected);

            if ($firstForSelectedCountry) {
                return $firstForSelectedCountry['value'];
            }
        }

        $firstForFallback = collect($phoneOptions)->firstWhere('country', $fallbackCountry);

        return $firstForFallback['value'] ?? (string) ($phoneOptions[0]['value'] ?? $fallbackCountry);
    }

    /**
     * @param  array<string, array{name: string}>  $callingCodes
     * @return array<int, string>
     */
    public static function countryOptions(array $callingCodes): array
    {
        return collect($callingCodes)
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array{name: string, code: string, prefixes?: array<int, string>}>  $callingCodes
     * @return array{country: string, number: string}
     */
    public static function splitPhone(?string $phone, ?string $countryName, array $callingCodes): array
    {
        $phoneOptions = self::phoneOptions($callingCodes);
        $defaultCountry = self::countryCodeForName($countryName, $callingCodes) ?? 'GB';
        $defaultSelection = self::normalizeSelection($defaultCountry, $phoneOptions);
        $phone = trim((string) $phone);

        if ($phone === '') {
            return [
                'country' => $defaultSelection,
                'number' => '',
            ];
        }

        if (! str_starts_with($phone, '+')) {
            return [
                'country' => $defaultSelection,
                'number' => $phone,
            ];
        }

        $digits = self::digits($phone);
        $preferredCountry = self::countryCodeForName($countryName, $callingCodes);
        $preferredOption = null;

        if ($preferredCountry) {
            $preferredOption = collect($phoneOptions)->firstWhere('country', $preferredCountry);
        }

        $preferredOption ??= collect($phoneOptions)->firstWhere('value', $defaultSelection);

        if ($preferredOption) {
            $preferredDigits = self::digits((string) $preferredOption['prefix']);

            if ($preferredDigits !== '' && str_starts_with($digits, $preferredDigits)) {
                return [
                    'country' => $preferredOption['value'],
                    'number' => substr($digits, strlen($preferredDigits)),
                ];
            }
        }

        $countriesByCodeLength = collect($phoneOptions)
            ->map(fn (array $option) => [
                'country' => $option['value'],
                'digits' => self::digits($option['prefix']),
            ])
            ->sortByDesc(fn (array $country) => strlen($country['digits']));

        foreach ($countriesByCodeLength as $country) {
            if ($country['digits'] !== '' && str_starts_with($digits, $country['digits'])) {
                return [
                    'country' => $country['country'],
                    'number' => substr($digits, strlen($country['digits'])),
                ];
            }
        }

        return [
            'country' => $defaultSelection,
            'number' => $phone,
        ];
    }

    /**
     * @param  array<string, array{name: string}>  $callingCodes
     */
    public static function countryCodeForName(?string $countryName, array $callingCodes): ?string
    {
        if (! $countryName) {
            return null;
        }

        foreach ($callingCodes as $countryCode => $country) {
            if (strcasecmp($country['name'], $countryName) === 0) {
                return $countryCode;
            }
        }

        return null;
    }

    /**
     * @param  array{name: string, code: string, prefixes?: array<int, string>}  $country
     * @return array<int, string>
     */
    private static function prefixes(array $country): array
    {
        return array_values($country['prefixes'] ?? [$country['code']]);
    }

    private static function compactPrefix(string $prefix): string
    {
        return '+'.self::digits($prefix);
    }

    private static function displayCode(string $prefix, string $baseCode): string
    {
        $prefixDigits = self::digits($prefix);
        $baseDigits = self::digits($baseCode);

        if ($baseDigits !== '' && str_starts_with($prefixDigits, $baseDigits) && strlen($prefixDigits) > strlen($baseDigits)) {
            return '+'.$baseDigits.' '.substr($prefixDigits, strlen($baseDigits));
        }

        return '+'.$prefixDigits;
    }

    private static function suffix(string $prefix, string $baseCode): string
    {
        $prefixDigits = self::digits($prefix);
        $baseDigits = self::digits($baseCode);

        if ($baseDigits !== '' && str_starts_with($prefixDigits, $baseDigits)) {
            return substr($prefixDigits, strlen($baseDigits)) ?: $prefixDigits;
        }

        return $prefixDigits;
    }

    private static function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: '';
    }
}
