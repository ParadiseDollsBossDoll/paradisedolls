<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditablePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_previous_layout_and_published_navigation_titles_are_restored(): void
    {
        SiteSetting::set(MarketingContent::SETTINGS_KEY, ['blank_page_1' => [
            'published' => true,
            'title' => 'Client hero title',
            'hero' => ['image' => 'images/17.jpeg', 'eyebrow' => 'Client eyebrow', 'body' => ['Banner description']],
            'intro' => ['title' => 'Below banner heading'],
            'body' => ['Below banner paragraph'],
        ]]);

        $this->get(route('editable-page', 'blank_page_1'))->assertOk()
            ->assertDontSee('pd-marketing-image-hero-title', false)
            ->assertSee('block h-auto w-full', false)
            ->assertSee('Below banner paragraph');

        $this->get('/')->assertOk()
            ->assertSee('href="'.route('editable-page', 'blank_page_1').'"', false)
            ->assertSee('Client hero title');
        SiteSetting::set(MarketingContent::SETTINGS_KEY, ['blank_page_1' => ['published' => false, 'title' => 'Client hero title']]);
        $this->get('/')->assertDontSee('href="'.route('editable-page', 'blank_page_1').'"', false)
            ->assertDontSee('Client hero title');
    }

    public function test_draft_can_be_edited_and_uploaded_images_do_not_publish_it(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.site-editor.update'), [
                '_page' => 'blank_page_2',
                'content' => ['blank_page_2' => ['published' => '0', 'title' => 'Private draft']],
                'image_files' => ['blank_page_2' => [
                    'logo' => UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=')),
                ]],
            ])->assertSessionHasNoErrors();
        $settings = SiteSetting::get(MarketingContent::SETTINGS_KEY);
        Storage::disk('local')->assertExists(data_get($settings, 'blank_page_2.logo'));
        $assetUrl = MarketingContent::imageUrl(data_get($settings, 'blank_page_2.logo'));
        $this->get($assetUrl)->assertOk();
        $this->get(route('editable-page', 'blank_page_2'))->assertNotFound();
        $this->get(route('admin.site-editor.preview', 'blank_page_2'))->assertOk()->assertSee('Private draft');
        $this->get('/')->assertDontSee('Private draft');
        $this->app['auth']->forgetGuards();
        $this->get($assetUrl)->assertNotFound();
    }

    public function test_drafts_are_hidden_and_preview_is_admin_only(): void
    {
        foreach (MarketingContent::blankPageKeys() as $page) {
            $this->get(route('editable-page', $page))->assertNotFound();
            $this->get(route('admin.site-editor.preview', $page))->assertRedirect(route('login'));
        }
        $this->actingAs(User::factory()->create(['role' => 'model']))
            ->get(route('admin.site-editor.preview', 'blank_page_1'))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.site-editor.preview', 'blank_page_1'))->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_admin_can_save_publish_and_unpublish_without_changing_home(): void
    {
        SiteSetting::set(MarketingContent::SETTINGS_KEY, ['home' => ['hero' => ['title' => 'Client homepage']]]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $input = ['_page' => 'blank_page_1', 'content' => ['blank_page_1' => [
            'published' => '1', 'title' => 'Client page', 'body' => 'Client introduction',
        ]]];
        $this->put(route('admin.site-editor.update'), $input)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Client homepage', data_get(SiteSetting::get(MarketingContent::SETTINGS_KEY), 'home.hero.title'));
        $this->get(route('editable-page', 'blank_page_1'))->assertOk()->assertSee('Client introduction');
        $this->get(route('editable-page', 'blank_page_2'))->assertNotFound();
        $input['content']['blank_page_1']['published'] = '0';
        $this->put(route('admin.site-editor.update'), $input)->assertSessionHasNoErrors();
        $this->get(route('editable-page', 'blank_page_1'))->assertNotFound();
    }

    public function test_blank_title_cannot_be_published(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.site-editor.update'), [
                '_page' => 'blank_page_1', 'content' => ['blank_page_1' => ['published' => '1']],
            ])->assertSessionHasErrors('content.blank_page_1.title');
        $this->get(route('editable-page', 'blank_page_1'))->assertNotFound();
    }
}
