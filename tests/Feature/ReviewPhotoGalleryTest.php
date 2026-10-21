<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_page_displays_all_five_supplied_photos(): void
    {
        foreach (['success-stories', 'home'] as $route) {
            $response = $this->get(route($route))->assertOk()
                ->assertSee($route === 'home' ? 'pd-review-moving-track' : 'pd-review-gallery', false);
            foreach (range(1, 5) as $number) {
                $response->assertSee("images/reviews/review-{$number}.jpeg", false);
                $this->assertFileExists(public_path("images/reviews/review-{$number}.jpeg"));
            }
            $response->assertSee('Open full-size review:', false);
        }
    }

    public function test_site_editor_photo_choices_and_page_copy_remain_authoritative(): void
    {
        SiteSetting::set(MarketingContent::SETTINGS_KEY, ['success_stories' => [
            'hero' => ['title' => 'Client review headline'],
            'review_photos' => [['image' => 'marketing/site-editor/new-review.png', 'title' => 'Client supplied review']],
        ]]);
        $this->get(route('success-stories'))->assertOk()
            ->assertSee('Client review headline')
            ->assertSee('marketing/site-editor/new-review.png', false)
            ->assertDontSee('images/reviews/review-1.jpeg', false);
    }

    public function test_four_additional_reviews_appear_on_both_public_pages(): void
    {
        foreach (['home', 'success-stories'] as $route) {
            $response = $this->get(route($route))->assertOk();
            foreach (['ruby' => 'Ruby - Model', 'precious' => 'Precious - Chatter', 'jhon-paul' => 'Jhon Paul - Chatter', 'alina' => 'Alina - Model'] as $file => $title) {
                $path = "images/reviews/{$file}.jpeg";
                $response->assertSee($path, false)->assertSee($title);
                $this->assertFileExists(public_path($path));
            }
        }
    }

    public function test_review_update_preserves_editor_content_and_does_not_duplicate_photos(): void
    {
        $existing = ['image' => 'marketing/site-editor/custom.png', 'title' => 'Custom review'];
        $ruby = ['image' => 'images/reviews/ruby.jpeg', 'title' => 'Edited Ruby title'];
        SiteSetting::set(MarketingContent::SETTINGS_KEY, [
            'home' => ['hero' => ['title' => 'Keep my homepage']],
            'success_stories' => [
                'hero' => ['title' => 'Keep my review headline'],
                'review_photos' => [$existing, $ruby],
            ],
        ]);

        $migration = require database_path('migrations/2026_10_22_000000_add_four_supplied_review_photos.php');
        SiteSetting::get(MarketingContent::SETTINGS_KEY);
        $migration->up();
        $migration->up();
        $migration->down();

        $content = SiteSetting::get(MarketingContent::SETTINGS_KEY);
        $this->assertSame('Keep my homepage', $content['home']['hero']['title']);
        $this->assertSame('Keep my review headline', $content['success_stories']['hero']['title']);
        $photos = $content['success_stories']['review_photos'];
        $this->assertCount(5, $photos);
        $this->assertSame([$existing, $ruby], array_slice($photos, 0, 2));
        foreach (['home', 'success-stories'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee('marketing/site-editor/custom.png', false)
                ->assertSee('images/reviews/alina.jpeg', false);
        }
    }

    public function test_review_update_leaves_default_backed_settings_untouched(): void
    {
        foreach ([[], ['success_stories' => ['review_photos' => []]]] as $content) {
            SiteSetting::set(MarketingContent::SETTINGS_KEY, $content);
            $migration = require database_path('migrations/2026_10_22_000000_add_four_supplied_review_photos.php');
            $migration->up();
            $this->assertSame($content, SiteSetting::get(MarketingContent::SETTINGS_KEY));
            $this->assertCount(9, MarketingContent::items('success_stories.review_photos'));
        }
    }
}
