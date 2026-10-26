<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultistreamingComparisonRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_is_removed_without_removing_surrounding_sections(): void
    {
        $this->get(route('multistreaming'))->assertOk()
            ->assertDontSee('Premium and freemium - both sides working together')
            ->assertDontSee('Public rooms. Token tipping. Massive traffic.')
            ->assertDontSee('Pay-per-minute. Private sessions. Quality spenders.')
            ->assertSee(marketing_content('multistreaming.different.title'))
            ->assertSee(marketing_content('multistreaming.change.title'))
            ->assertSee('data-pd-marketing-navbar', false);

        $fields = array_column(MarketingContent::pages()['multistreaming']['fields'], 'key');
        foreach ($fields as $key) {
            $this->assertDoesNotMatchRegularExpression('/^(industry|freemium|premium)\./', $key);
        }
    }

    public function test_old_saved_comparison_content_cannot_restore_the_section(): void
    {
        SiteSetting::set(MarketingContent::SETTINGS_KEY, ['multistreaming' => [
            'industry' => ['title' => 'Old comparison heading'],
            'freemium' => ['title' => 'Old freemium card'],
            'premium' => ['title' => 'Old premium card'],
        ]]);
        $this->get(route('multistreaming'))->assertOk()
            ->assertDontSee('Old comparison heading')
            ->assertDontSee('Old freemium card')
            ->assertDontSee('Old premium card')
            ->assertSee(marketing_content('multistreaming.built.title'));
    }
}
