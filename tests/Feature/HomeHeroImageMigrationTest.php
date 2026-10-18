<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroImageMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_update_preserves_client_copy_and_other_settings(): void
    {
        $saved = [
            'home' => ['hero' => ['image' => 'client-old.png', 'title' => '.', 'body' => ['Kayla updated this.'], 'primary_label' => 'Join us']],
            'shared' => ['footer' => ['body' => 'Client footer']],
        ];
        SiteSetting::set('marketing_content', $saved);

        $migration = require database_path('migrations/2026_10_18_000000_set_extended_home_hero_image.php');
        $migration->up();

        data_set($saved, 'home.hero.image', 'images/home/boss-doll-lounge-extended.png');
        $this->assertSame($saved, SiteSetting::get('marketing_content'));

        $migration->down();
        $this->assertSame($saved, SiteSetting::get('marketing_content'));
    }
}
