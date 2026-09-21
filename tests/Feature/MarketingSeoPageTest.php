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

    public function test_webcam_jobs_page_has_applicant_guidance_and_structured_data(): void
    {
        config(['app.url' => 'https://getrichwithparadisedolls.com']);

        $this->get(route('webcam-jobs'))
            ->assertOk()
            ->assertSeeText('What a webcam job with Paradise Dolls involves')
            ->assertSeeText('Models work on a self-employed basis rather than as employees.')
            ->assertSeeText('How to apply for webcam jobs')
            ->assertSeeText('How much can a webcam model earn?')
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertDontSeeText('Search Focus')
            ->assertDontSeeText('Related search');
    }

    public function test_marketing_navigation_keeps_primary_and_secondary_links_accessible(): void
    {
        $this->get(route('webcam-jobs'))
            ->assertOk()
            ->assertSee('aria-label="Primary navigation"', false)
            ->assertSee('aria-controls="marketing-explore-menu"', false)
            ->assertSee('id="marketing-mobile-menu"', false)
            ->assertSeeText('Home')
            ->assertSeeText('Webcam Jobs')
            ->assertSeeText('Our Story')
            ->assertSeeText('Work From Paradise')
            ->assertSeeText('Become A Doll');
    }
}
