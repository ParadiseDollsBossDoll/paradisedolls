<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private const WITHDRAWN = [
        'webcam-jobs', 'guides', 'how-to-become-a-webcam-model',
        'webcam-model-safety-privacy', 'webcam-agency-vs-independent',
        'webcam-model-earnings-payments', 'webcam-agency', 'model-agency',
    ];

    public function test_withdrawn_pages_do_not_expose_content_through_direct_urls(): void
    {
        foreach (self::WITHDRAWN as $slug) {
            $this->get('/'.$slug)
                ->assertNotFound()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertDontSee('adult')
                ->assertDontSee('independent');
            $this->head('/'.$slug)->assertNotFound();
        }
    }

    public function test_remaining_public_pages_have_no_links_to_withdrawn_content(): void
    {
        $this->get('/apply')->assertRedirect(route('home').'#apply');
        foreach (['/', '/our-story', '/work-from-home', '/work-from-paradise', '/perks', '/multistreaming', '/success-stories'] as $path) {
            $response = $this->get($path)->assertOk();
            foreach (self::WITHDRAWN as $slug) {
                $response->assertDontSee('href="'.url('/'.$slug).'"', false);
            }
            $response->assertDontSeeText('Webcam Jobs')->assertDontSeeText('Guides');
        }
    }

    public function test_sitemap_does_not_advertise_withdrawn_pages(): void
    {
        $sitemap = file_get_contents(public_path('sitemap.xml'));
        foreach (self::WITHDRAWN as $slug) {
            $this->assertStringNotContainsString('/'.$slug.'</loc>', $sitemap);
        }
    }
}
