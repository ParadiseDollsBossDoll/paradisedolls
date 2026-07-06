<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranslationEndpointTest extends TestCase
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

    public function test_languages_endpoint_prioritizes_requested_languages(): void
    {
        $response = $this->getJson(route('translation.languages'))
            ->assertOk()
            ->assertJsonPath('enabled', false);

        $codes = collect($response->json('languages'))->pluck('code')->take(7)->all();

        $this->assertSame(['en', 'es', 'pt', 'fr', 'de', 'ru', 'th'], $codes);
    }

    public function test_language_selector_has_search_and_requested_priority_languages(): void
    {
        $selector = Blade::render('<x-language-selector />');

        $this->assertStringContainsString('data-pd-language-search', $selector);

        foreach (['Spanish', 'Portuguese', 'French', 'German', 'Russian'] as $language) {
            $this->assertStringContainsString($language, $selector);
        }
    }

    public function test_translate_endpoint_returns_original_text_without_credentials(): void
    {
        config(['services.translation.enabled' => true]);

        Http::fake();

        $this->postJson(route('translation.translate'), [
            'target' => 'th',
            'texts' => ['Apply today'],
        ])
            ->assertOk()
            ->assertJsonPath('enabled', false)
            ->assertJsonPath('translations.0', 'Apply today');

        Http::assertNothingSent();
    }

    public function test_translate_endpoint_rejects_unsupported_language(): void
    {
        $this->postJson(route('translation.translate'), [
            'target' => 'xx',
            'texts' => ['Apply today'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['target']);
    }

    public function test_translate_endpoint_uses_google_when_configured(): void
    {
        config([
            'services.translation.enabled' => true,
            'services.translation.provider' => 'google',
            'services.google_translate.api_key' => 'test-key',
        ]);

        Http::fake([
            'https://translation.googleapis.com/language/translate/v2/languages*' => Http::response([
                'data' => [
                    'languages' => [
                        ['language' => 'en', 'name' => 'English'],
                        ['language' => 'th', 'name' => 'Thai'],
                        ['language' => 'pt', 'name' => 'Portuguese'],
                    ],
                ],
            ]),
            'https://translation.googleapis.com/language/translate/v2*' => Http::response([
                'data' => [
                    'translations' => [
                        ['translatedText' => 'Inscreva-se hoje'],
                    ],
                ],
            ]),
        ]);

        $this->postJson(route('translation.translate'), [
            'target' => 'pt',
            'texts' => ['Apply today'],
        ])
            ->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonPath('translations.0', 'Inscreva-se hoje');
    }

    public function test_translate_endpoint_uses_azure_when_configured(): void
    {
        config([
            'services.translation.enabled' => true,
            'services.translation.provider' => 'azure',
            'services.azure_translator.key' => 'test-key',
            'services.azure_translator.region' => 'eastus',
        ]);

        Http::fake([
            'https://api.cognitive.microsofttranslator.com/languages*' => Http::response([
                'translation' => [
                    'en' => ['name' => 'English'],
                    'th' => ['name' => 'Thai'],
                    'pt' => ['name' => 'Portuguese'],
                ],
            ]),
            'https://api.cognitive.microsofttranslator.com/translate*' => Http::response([
                [
                    'translations' => [
                        ['text' => 'Inscreva-se hoje', 'to' => 'pt'],
                    ],
                ],
            ]),
        ]);

        $this->postJson(route('translation.translate'), [
            'target' => 'pt',
            'texts' => ['Apply today'],
        ])
            ->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonPath('translations.0', 'Inscreva-se hoje');
    }

    public function test_translation_endpoint_is_throttled(): void
    {
        $server = ['REMOTE_ADDR' => '203.0.113.44'];

        for ($i = 0; $i < 60; $i++) {
            $this->withServerVariables($server)
                ->getJson(route('translation.languages'))
                ->assertOk();
        }

        $this->withServerVariables($server)
            ->getJson(route('translation.languages'))
            ->assertStatus(429);
    }
}
