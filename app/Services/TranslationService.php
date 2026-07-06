<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TranslationService
{
    private const SOURCE_LANGUAGE = 'en';

    private const PRIORITY_CODES = ['en', 'es', 'pt', 'fr', 'de', 'ru', 'th'];

    private const SUPPORTED_PROVIDERS = ['azure', 'google'];

    /**
     * Broad fallback list used before API credentials are set.
     *
     * @var array<string, string>
     */
    private const FALLBACK_LANGUAGES = [
        'en' => 'English',
        'th' => 'Thai',
        'pt' => 'Portuguese',
        'af' => 'Afrikaans',
        'sq' => 'Albanian',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'hy' => 'Armenian',
        'az' => 'Azerbaijani',
        'eu' => 'Basque',
        'be' => 'Belarusian',
        'bn' => 'Bengali',
        'bs' => 'Bosnian',
        'bg' => 'Bulgarian',
        'ca' => 'Catalan',
        'ceb' => 'Cebuano',
        'zh-Hans' => 'Chinese (Simplified)',
        'zh-Hant' => 'Chinese (Traditional)',
        'co' => 'Corsican',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'nl' => 'Dutch',
        'eo' => 'Esperanto',
        'et' => 'Estonian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'fy' => 'Frisian',
        'gl' => 'Galician',
        'ka' => 'Georgian',
        'de' => 'German',
        'el' => 'Greek',
        'gu' => 'Gujarati',
        'ht' => 'Haitian Creole',
        'ha' => 'Hausa',
        'haw' => 'Hawaiian',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hmn' => 'Hmong',
        'hu' => 'Hungarian',
        'is' => 'Icelandic',
        'ig' => 'Igbo',
        'id' => 'Indonesian',
        'ga' => 'Irish',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'jv' => 'Javanese',
        'kn' => 'Kannada',
        'kk' => 'Kazakh',
        'km' => 'Khmer',
        'rw' => 'Kinyarwanda',
        'ko' => 'Korean',
        'ku' => 'Kurdish',
        'ky' => 'Kyrgyz',
        'lo' => 'Lao',
        'la' => 'Latin',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'lb' => 'Luxembourgish',
        'mk' => 'Macedonian',
        'mg' => 'Malagasy',
        'ms' => 'Malay',
        'ml' => 'Malayalam',
        'mt' => 'Maltese',
        'mi' => 'Maori',
        'mr' => 'Marathi',
        'mn' => 'Mongolian',
        'my' => 'Myanmar',
        'ne' => 'Nepali',
        'no' => 'Norwegian',
        'ny' => 'Nyanja',
        'or' => 'Odia',
        'ps' => 'Pashto',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pa' => 'Punjabi',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sm' => 'Samoan',
        'gd' => 'Scots Gaelic',
        'sr' => 'Serbian',
        'st' => 'Sesotho',
        'sn' => 'Shona',
        'sd' => 'Sindhi',
        'si' => 'Sinhala',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'so' => 'Somali',
        'es' => 'Spanish',
        'su' => 'Sundanese',
        'sw' => 'Swahili',
        'sv' => 'Swedish',
        'tl' => 'Tagalog',
        'tg' => 'Tajik',
        'ta' => 'Tamil',
        'tt' => 'Tatar',
        'te' => 'Telugu',
        'tr' => 'Turkish',
        'tk' => 'Turkmen',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'ug' => 'Uyghur',
        'uz' => 'Uzbek',
        'vi' => 'Vietnamese',
        'cy' => 'Welsh',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'zu' => 'Zulu',
    ];

    public function isActive(): bool
    {
        if (! $this->translationEnabled()) {
            return false;
        }

        return match ($this->provider()) {
            'azure' => filled($this->azureKey()),
            'google' => filled($this->googleApiKey()),
            default => false,
        };
    }

    public function priorityCodes(): array
    {
        return self::PRIORITY_CODES;
    }

    public function languages(): array
    {
        $fallback = $this->formatLanguages(self::FALLBACK_LANGUAGES);

        if (! $this->isActive()) {
            return $fallback;
        }

        $cacheKey = 'translation:'.$this->provider().':languages:v3';
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $languages = match ($this->provider()) {
            'azure' => $this->fetchAzureLanguages(),
            'google' => $this->fetchGoogleLanguages(),
            default => [],
        };

        if ($languages === []) {
            return $fallback;
        }

        $formatted = $this->prioritizeLanguages($this->formatLanguages($languages));

        Cache::put($cacheKey, $formatted, $this->cacheTtl());

        return $formatted;
    }

    public function isSupportedLanguage(string $code): bool
    {
        $code = $this->normalizeLanguageCode($code);

        return collect($this->languages())->contains('code', $code);
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, string>
     */
    public function translateBatch(array $texts, string $targetLanguage): array
    {
        $targetLanguage = $this->normalizeLanguageCode($targetLanguage);

        if ($targetLanguage === self::SOURCE_LANGUAGE || ! $this->isActive()) {
            return $texts;
        }

        $translations = $texts;
        $pending = [];

        foreach ($texts as $index => $text) {
            if (! $this->shouldTranslate($text)) {
                continue;
            }

            $key = $this->translationCacheKey($targetLanguage, $text);
            $cached = Cache::get($key);

            if (is_string($cached)) {
                $translations[$index] = $cached;

                continue;
            }

            $pending[$index] = $text;
        }

        foreach (array_chunk($pending, 80, true) as $chunk) {
            $translatedChunk = $this->translateChunk(array_values($chunk), $targetLanguage);

            foreach (array_keys($chunk) as $position => $originalIndex) {
                $translated = $translatedChunk[$position] ?? $texts[$originalIndex];
                $translations[$originalIndex] = $translated;

                if ($translated !== $texts[$originalIndex]) {
                    Cache::put(
                        $this->translationCacheKey($targetLanguage, $texts[$originalIndex]),
                        $translated,
                        $this->cacheTtl()
                    );
                }
            }
        }

        return $translations;
    }

    private function translationEnabled(): bool
    {
        return (bool) config('services.translation.enabled', config('services.google_translate.enabled', false));
    }

    private function provider(): string
    {
        $provider = strtolower(trim((string) config('services.translation.provider', 'google')));

        return in_array($provider, self::SUPPORTED_PROVIDERS, true) ? $provider : 'google';
    }

    private function googleApiKey(): ?string
    {
        $key = config('services.google_translate.api_key');

        return is_string($key) ? trim($key) : null;
    }

    private function azureKey(): ?string
    {
        $key = config('services.azure_translator.key');

        return is_string($key) ? trim($key) : null;
    }

    private function azureRegion(): ?string
    {
        $region = config('services.azure_translator.region');

        return is_string($region) ? trim($region) : null;
    }

    private function azureEndpoint(string $path): string
    {
        $endpoint = config('services.azure_translator.endpoint', 'https://api.cognitive.microsofttranslator.com');
        $endpoint = is_string($endpoint) && trim($endpoint) !== ''
            ? rtrim(trim($endpoint), '/')
            : 'https://api.cognitive.microsofttranslator.com';

        return $endpoint.'/'.ltrim($path, '/');
    }

    private function cacheTtl(): int
    {
        return max(60, (int) config('services.translation.cache_ttl', config('services.google_translate.cache_ttl', 604800)));
    }

    private function timeout(): int
    {
        return max(3, (int) config('services.translation.timeout', config('services.google_translate.timeout', 10)));
    }

    private function normalizeLanguageCode(string $code): string
    {
        $lower = strtolower(trim($code));

        if ($this->provider() === 'google') {
            return match ($lower) {
                'zh-hans', 'zh-cn' => 'zh-CN',
                'zh-hant', 'zh-tw' => 'zh-TW',
                default => $lower,
            };
        }

        return match ($lower) {
            'zh-cn', 'zh-hans' => 'zh-Hans',
            'zh-tw', 'zh-hant' => 'zh-Hant',
            'pt-br' => 'pt-BR',
            'pt-pt' => 'pt-PT',
            'sr-cyrl' => 'sr-Cyrl',
            'sr-latn' => 'sr-Latn',
            default => $lower,
        };
    }

    private function shouldTranslate(string $text): bool
    {
        return trim($text) !== '' && (bool) preg_match('/[A-Za-z]/', $text);
    }

    /**
     * @return array<string, string>
     */
    private function fetchAzureLanguages(): array
    {
        try {
            $response = Http::timeout($this->timeout())
                ->withHeaders($this->azureHeaders())
                ->get($this->azureEndpoint('languages'), [
                    'api-version' => '3.0',
                    'scope' => 'translation',
                ]);
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $translationLanguages = $response->json('translation', []);

        if (! is_array($translationLanguages)) {
            return [];
        }

        return collect($translationLanguages)
            ->mapWithKeys(function ($language, string $code) {
                if (! is_array($language)) {
                    return [];
                }

                $name = (string) ($language['name'] ?? $language['nativeName'] ?? '');

                return $code !== '' && $name !== '' ? [$code => $name] : [];
            })
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function fetchGoogleLanguages(): array
    {
        try {
            $response = Http::timeout($this->timeout())->get(
                'https://translation.googleapis.com/language/translate/v2/languages',
                [
                    'key' => $this->googleApiKey(),
                    'target' => self::SOURCE_LANGUAGE,
                ]
            );
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('data.languages', []))
            ->mapWithKeys(function (array $language) {
                $code = (string) ($language['language'] ?? '');
                $name = (string) ($language['name'] ?? '');

                return $code !== '' && $name !== '' ? [$code => $name] : [];
            })
            ->all();
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, string>
     */
    private function translateChunk(array $texts, string $targetLanguage): array
    {
        return match ($this->provider()) {
            'azure' => $this->translateAzureChunk($texts, $targetLanguage),
            'google' => $this->translateGoogleChunk($texts, $targetLanguage),
            default => $texts,
        };
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, string>
     */
    private function translateAzureChunk(array $texts, string $targetLanguage): array
    {
        $query = http_build_query([
            'api-version' => '3.0',
            'from' => self::SOURCE_LANGUAGE,
            'to' => $targetLanguage,
        ]);

        try {
            $response = Http::timeout($this->timeout())
                ->withHeaders($this->azureHeaders(contentType: true))
                ->post(
                    $this->azureEndpoint('translate').'?'.$query,
                    collect($texts)->map(fn (string $text) => ['Text' => $text])->all()
                );
        } catch (\Throwable) {
            return $texts;
        }

        if (! $response->successful()) {
            return $texts;
        }

        $translated = collect($response->json())
            ->map(fn (array $item) => (string) data_get($item, 'translations.0.text', ''))
            ->values()
            ->all();

        return count($translated) === count($texts) ? $translated : $texts;
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, string>
     */
    private function translateGoogleChunk(array $texts, string $targetLanguage): array
    {
        try {
            $response = Http::timeout($this->timeout())->post(
                'https://translation.googleapis.com/language/translate/v2?key='.$this->googleApiKey(),
                [
                    'q' => $texts,
                    'source' => self::SOURCE_LANGUAGE,
                    'target' => $targetLanguage,
                    'format' => 'text',
                ]
            );
        } catch (\Throwable) {
            return $texts;
        }

        if (! $response->successful()) {
            return $texts;
        }

        $translated = collect($response->json('data.translations', []))
            ->map(fn (array $item) => html_entity_decode((string) ($item['translatedText'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            ->values()
            ->all();

        return count($translated) === count($texts) ? $translated : $texts;
    }

    /**
     * @return array<string, string>
     */
    private function azureHeaders(bool $contentType = false): array
    {
        $headers = [
            'Ocp-Apim-Subscription-Key' => $this->azureKey(),
        ];

        if (filled($this->azureRegion())) {
            $headers['Ocp-Apim-Subscription-Region'] = $this->azureRegion();
        }

        if ($contentType) {
            $headers['Content-Type'] = 'application/json; charset=UTF-8';
        }

        return array_filter($headers, fn ($value) => filled($value));
    }

    /**
     * @param  array<string, string>  $languages
     * @return array<int, array{code: string, name: string, priority: bool}>
     */
    private function formatLanguages(array $languages): array
    {
        return $this->prioritizeLanguages(
            collect($languages)
                ->map(fn (string $name, string $code) => [
                    'code' => $this->normalizeLanguageCode($code),
                    'name' => $name,
                    'priority' => in_array($this->normalizeLanguageCode($code), self::PRIORITY_CODES, true),
                ])
                ->values()
                ->all()
        );
    }

    /**
     * @param  array<int, array{code: string, name: string, priority: bool}>  $languages
     * @return array<int, array{code: string, name: string, priority: bool}>
     */
    private function prioritizeLanguages(array $languages): array
    {
        $byCode = collect($languages)->keyBy('code');

        return collect(self::PRIORITY_CODES)
            ->map(fn (string $code) => $byCode->get($code))
            ->filter()
            ->merge(
                $byCode
                    ->reject(fn (array $language) => in_array($language['code'], self::PRIORITY_CODES, true))
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
            )
            ->values()
            ->all();
    }

    private function translationCacheKey(string $targetLanguage, string $text): string
    {
        return 'translation:'.$this->provider().':text:'.$targetLanguage.':'.sha1($text);
    }
}
