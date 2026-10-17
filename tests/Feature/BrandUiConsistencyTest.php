<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandUiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_fonts_and_premium_surface_rules_are_bundled_locally(): void
    {
        $this->assertFileExists(public_path('fonts/dejavu/DejaVuSans.ttf'));
        $this->assertFileExists(public_path('fonts/dejavu/DejaVuSans-Bold.ttf'));
        $this->assertFileExists(public_path('fonts/dejavu/LICENSE'));

        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('@font-face', $css);
        $this->assertStringContainsString('font-family: "DejaVu Sans"', $css);
        $this->assertStringContainsString('--pd-gloss-highlight', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

    public function test_pages_use_the_dedicated_square_brand_favicon(): void
    {
        $this->assertFileExists(public_path('images/brand/boss-doll-creators-favicon.png'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('boss-doll-creators-favicon.png', false)
            ->assertDontSee('boss-doll-creators-mark-pink.png', false);
    }

    public function test_marketing_primary_actions_use_the_shared_accessible_brand_style(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('pd-premium-ui', false)
            ->assertSee('pd-cta-primary', false)
            ->assertDontSee('bg-[#EEB4C3]', false);
    }

    public function test_image_hero_pages_start_with_a_transparent_navbar_and_safe_hero_spacing(): void
    {
        $this->get(route('work-from-home'))
            ->assertOk()
            ->assertSee('pd-marketing-navbar--transparent', false)
            ->assertSee('pd-marketing-image-hero', false);

        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('.pd-marketing-navbar--transparent', $css);
        $this->assertStringContainsString('.pd-marketing-navbar--solid', $css);
        $this->assertStringContainsString('padding-bottom: clamp(4rem, 8vh, 6rem)', $css);
    }

    public function test_light_guest_pages_use_the_visible_pink_logo_and_contrast_safe_theme_tokens(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('boss-doll-creators-logo-horizontal-pink.png', false)
            ->assertDontSee('boss-doll-creators-logo-horizontal-white.png', false)
            ->assertSee('function contrastRatio', false)
            ->assertSee("var darkText = '#1A0E14'", false)
            ->assertDontSee("--pd-primary-hover',pl", false);
    }

    public function test_multistreaming_strategy_numbers_use_the_readable_index_style(): void
    {
        $this->get(route('multistreaming'))
            ->assertOk()
            ->assertSee('pd-strategy-index', false)
            ->assertDontSee('text-boss-pink shrink-0', false);
    }
}
