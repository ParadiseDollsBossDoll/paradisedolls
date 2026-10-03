<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingSeoPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_keyword_focused_title_and_description(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Paradise Dolls Webcam Model Agency &amp; Online Model Jobs', false)
            ->assertSee('webcam model agency', false)
            ->assertSee('online model jobs', false);
    }

    public function test_established_marketing_pages_keep_navigation_and_original_content(): void
    {
        foreach (['work-from-home', 'multistreaming'] as $page) {
            $this->get(route($page))
                ->assertOk()
                ->assertSee('aria-label="Primary navigation"', false)
                ->assertSee('id="marketing-mobile-menu"', false)
                ->assertSeeText('Our Story')
                ->assertSeeText('Work From Paradise')
                ->assertSeeText('Become A Doll')
                ->assertDontSeeText('The short answer')
                ->assertDontSeeText('Related Paradise Dolls guides');
        }
    }
}
