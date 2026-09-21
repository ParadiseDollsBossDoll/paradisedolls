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
            '/work-from-home' => ['Work from home webcam jobs', 'remote webcam model jobs'],
            '/multistreaming' => ['Multistreaming for webcam models', 'webcam streaming setup'],
            '/how-to-become-a-webcam-model' => ['How to become a webcam model', 'webcam modelling for beginners'],
            '/webcam-model-safety-privacy' => ['Webcam model safety and privacy', 'is webcam modelling safe'],
            '/webcam-agency-vs-independent' => ['Webcam agency vs independent', 'webcam agency contract'],
            '/webcam-model-earnings-payments' => ['Webcam model earnings and payments', 'how do webcam models get paid'],
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

    public function test_webcam_agency_page_explains_services_terms_and_responsibilities(): void
    {
        config(['app.url' => 'https://getrichwithparadisedolls.com']);

        $this->get(route('webcam-agency'))
            ->assertOk()
            ->assertSeeText('What a webcam agency should actually do')
            ->assertSeeText('What Paradise Dolls provides')
            ->assertSeeText('Agency support without hiding your responsibilities')
            ->assertSeeText('Built from more than 15 years in the industry')
            ->assertSeeText('Understand the terms, not just the benefits')
            ->assertSeeText('six-month agency term')
            ->assertSeeText('Does a webcam agency guarantee earnings?')
            ->assertSee('Webcam Agency for Women - Paradise Dolls', false)
            ->assertSee('https://getrichwithparadisedolls.com/webcam-agency', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertDontSeeText('Search Focus')
            ->assertDontSeeText('Related search');
    }

    public function test_model_agency_page_explains_online_representation_and_global_eligibility(): void
    {
        config(['app.url' => 'https://getrichwithparadisedolls.com']);

        $this->get(route('model-agency'))
            ->assertOk()
            ->assertSeeText('A model agency built for online creators')
            ->assertSeeText('Online model management, not fashion casting')
            ->assertSeeText('An international application does not mean automatic acceptance')
            ->assertSeeText('What the Paradise Dolls agency provides')
            ->assertSeeText('Questions every model should ask an agency')
            ->assertSeeText('Can women apply from outside the United Kingdom?')
            ->assertSeeText('minimum six-month term')
            ->assertSee('Online Model Agency for Women - Paradise Dolls', false)
            ->assertSee('https://getrichwithparadisedolls.com/model-agency', false)
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
            ->assertSeeText('Guides')
            ->assertSeeText('Our Story')
            ->assertSeeText('Work From Paradise')
            ->assertSeeText('Become A Doll');
    }

    public function test_guides_hub_exposes_the_topic_library_and_structured_data(): void
    {
        config(['app.url' => 'https://getrichwithparadisedolls.com']);

        $this->get(route('guides'))
            ->assertOk()
            ->assertSeeText('Webcam model guides for informed decisions')
            ->assertSeeText('How to become a webcam model')
            ->assertSeeText('Webcam model safety and privacy')
            ->assertSeeText('Webcam agency vs independent')
            ->assertSeeText('Webcam model earnings and payments')
            ->assertSee('https://getrichwithparadisedolls.com/guides', false)
            ->assertSee('"@type":"CollectionPage"', false)
            ->assertSee('"@type":"ItemList"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_beginner_guides_render_people_first_advice_and_faq_schema(): void
    {
        config(['app.url' => 'https://getrichwithparadisedolls.com']);

        $guides = [
            'how-to-become-a-webcam-model' => [
                'Understand the work before creating accounts',
                'Can I become a webcam model without showing my face?',
            ],
            'webcam-model-safety-privacy' => [
                'Privacy can be strengthened, but anonymity cannot be guaranteed',
                'What should I do if someone threatens to expose me?',
            ],
            'webcam-agency-vs-independent' => [
                'Understand agency fees, contracts, and account ownership',
                'How can I find a legitimate webcam agency?',
            ],
            'webcam-model-earnings-payments' => [
                'Separate gross activity from final take-home pay',
                'Are tokens the same as cash?',
            ],
        ];

        foreach ($guides as $routeName => [$section, $question]) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSeeText($section)
                ->assertSeeText($question)
                ->assertSee('"@type":"WebPage"', false)
                ->assertSee('"@type":"FAQPage"', false)
                ->assertSee('"@type":"BreadcrumbList"', false);
        }
    }

    public function test_work_from_home_and_multistreaming_pages_use_current_focused_guidance(): void
    {
        $this->get(route('work-from-home'))
            ->assertOk()
            ->assertSeeText('Work from home webcam jobs, explained honestly')
            ->assertSeeText('Build a setup that is private, stable, and comfortable')
            ->assertSeeText('Can webcam models work from home?')
            ->assertSee('"@type":"FAQPage"', false);

        $this->get(route('multistreaming'))
            ->assertOk()
            ->assertSeeText('Multistreaming for webcam models without losing control')
            ->assertSeeText('Learn one workflow before adding more platforms')
            ->assertSeeText('Do all webcam platforms allow multistreaming?')
            ->assertSee('"@type":"FAQPage"', false);
    }
}
