<?php

namespace Tests\Unit;

use App\Services\TranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'services.translation.enabled' => false,
            'services.translation.provider' => 'google',
            'services.translation.cache_ttl' => 300,
            'services.translation.timeout' => 10,
            'services.azure_translator.key' => null,
            'services.azure_translator.region' => null,
            'services.azure_translator.endpoint' => 'https://api.cognitive.microsofttranslator.com',
            'services.google_translate.api_key' => null,
        ]);
    }

    public function test_disabled_service_returns_original_text_without_http_call(): void
    {
        Http::fake();

        $service = app(TranslationService::class);

        $this->assertFalse($service->isActive());
        $this->assertSame(['Apply now'], $service->translateBatch(['Apply now'], 'th'));

        Http::assertNothingSent();
    }

    public function test_missing_api_key_returns_original_text_without_http_call(): void
    {
        config(['services.translation.enabled' => true]);

        Http::fake();

        $service = app(TranslationService::class);

        $this->assertFalse($service->isActive());
        $this->assertSame(['Welcome'], $service->translateBatch(['Welcome'], 'pt'));

        Http::assertNothingSent();
    }

    public function test_successful_azure_translation_is_cached(): void
    {
        config([
            'services.translation.enabled' => true,
            'services.translation.provider' => 'azure',
            'services.azure_translator.key' => 'test-key',
            'services.azure_translator.region' => 'eastus',
        ]);

        Http::fake([
            'https://api.cognitive.microsofttranslator.com/translate*' => Http::response([
                [
                    'translations' => [
                        ['text' => 'Aplicar agora', 'to' => 'pt'],
                    ],
                ],
            ]),
        ]);

        $service = app(TranslationService::class);

        $this->assertSame(['Aplicar agora'], $service->translateBatch(['Apply now'], 'pt'));
        $this->assertSame(['Aplicar agora'], $service->translateBatch(['Apply now'], 'pt'));

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->hasHeader('Ocp-Apim-Subscription-Key', 'test-key')
            && $request->hasHeader('Ocp-Apim-Subscription-Region', 'eastus'));
    }

    public function test_azure_api_failure_falls_back_to_original_text(): void
    {
        config([
            'services.translation.enabled' => true,
            'services.translation.provider' => 'azure',
            'services.azure_translator.key' => 'test-key',
        ]);

        Http::fake([
            'https://api.cognitive.microsofttranslator.com/translate*' => Http::response([], 500),
        ]);

        $service = app(TranslationService::class);

        $this->assertSame(['Welcome back'], $service->translateBatch(['Welcome back'], 'th'));
    }

    public function test_google_provider_works_when_configured(): void
    {
        config([
            'services.translation.enabled' => true,
            'services.translation.provider' => 'google',
            'services.google_translate.api_key' => 'test-key',
        ]);

        Http::fake([
            'https://translation.googleapis.com/language/translate/v2*' => Http::response([
                'data' => [
                    'translations' => [
                        ['translatedText' => 'Inscreva-se hoje'],
                    ],
                ],
            ]),
        ]);

        $service = app(TranslationService::class);

        $this->assertSame(['Inscreva-se hoje'], $service->translateBatch(['Apply today'], 'pt'));
    }
}
