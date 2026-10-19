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
            $response = $this->get(route($route))->assertOk()->assertSee('pd-review-gallery', false);
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
}
