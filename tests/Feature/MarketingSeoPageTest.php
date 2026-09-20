<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingSeoPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_seo_landing_pages_render_with_target_keywords_and_metadata(): void
    {
        config(['app.url' => 'https://getrichwithparadisedolls.com']);

        $pages = [
            '/webcam-jobs' => ['Webcam jobs', 'webcam model jobs'],
            '/webcam-agency' => ['Webcam agency', 'webcam model agency'],
            '/model-agency' => ['Model agency', 'online model agency'],
        ];

        foreach ($pages as $path => [$headline, $relatedKeyword]) {
            $this->get($path)
                ->assertOk()
                ->assertSee($headline)
                ->assertSee($relatedKeyword)
                ->assertSee('<meta name="description"', false)
                ->assertSee('<link rel="canonical"', false)
                ->assertSee('https://getrichwithparadisedolls.com'.$path, false);
        }
    }

    public function test_homepage_has_keyword_focused_title_and_description(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Paradise Dolls Webcam Model Agency &amp; Online Model Jobs', false)
            ->assertSee('webcam model agency', false)
            ->assertSee('online model jobs', false);
    }
}
