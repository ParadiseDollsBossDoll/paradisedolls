<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DollAfterDarkEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_doll_after_dark_editor(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.site-editor.edit', ['page' => 'doll_after_dark']))
            ->assertOk()->assertSee('name="content[doll_after_dark][hero__body]"', false)
            ->assertSee('name="image_files[doll_after_dark][angel__image]"', false)
            ->assertSee('name="content[doll_after_dark][faq__items][0][question]"', false);
    }

    public function test_saved_text_lists_faqs_and_images_reach_public_page_without_changing_other_pages(): void
    {
        Storage::fake('public');
        SiteSetting::set(MarketingContent::SETTINGS_KEY, ['home' => ['hero' => ['title' => 'Keep home']]]);
        $input = [];
        foreach (MarketingContent::pages()['doll_after_dark']['fields'] ?? [] as $field) {
            $value = $field['default'];
            $input[MarketingContent::fieldId($field['key'])] = $field['type'] === 'list' ? implode("\n", $value) : $value;
        }
        $input['hero__body'] = 'An updated introduction';
        $input['styles__items'] = "First style\nSecond style";
        $input['faq__items'] = [['question' => 'Updated question?', 'answer' => 'Updated answer.']];
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.site-editor.update'), [
                '_page' => 'doll_after_dark',
                'content' => ['doll_after_dark' => $input],
                'image_files' => ['doll_after_dark' => ['hero__image' => UploadedFile::fake()->createWithContent('hero.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII='))]],
            ])->assertSessionHasNoErrors()->assertRedirect(route('admin.site-editor.edit', ['page' => 'doll_after_dark']));
        $settings = SiteSetting::get(MarketingContent::SETTINGS_KEY, []);
        $this->assertSame('Keep home', data_get($settings, 'home.hero.title'));
        $image = data_get($settings, 'doll_after_dark.hero.image');
        Storage::disk('public')->assertExists($image);
        $this->get(route('doll-after-dark'))->assertOk()
            ->assertSee('An updated introduction')->assertSee('First style')->assertSee('Second style')
            ->assertSee('Updated question?')->assertSee('Updated answer.')
            ->assertSee('/storage/'.$image, false)->assertSee('data-pd-marketing-navbar', false)
            ->assertSee('href="'.route('apply').'"', false)
            ->assertSee('COURSE COMING SOON')->assertSee('disabled', false);
    }

    public function test_non_admin_cannot_edit_the_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'model']))
            ->put(route('admin.site-editor.update'), ['_page' => 'doll_after_dark'])
            ->assertForbidden();
    }

    public function test_all_editor_fields_are_rendered_and_html_is_escaped(): void
    {
        $settings = [];
        $expected = [];
        foreach (MarketingContent::pages()['doll_after_dark']['fields'] as $index => $field) {
            $marker = 'Edited field '.$index;
            $value = $marker;
            if ($field['type'] === 'image') {
                $value = 'images/custom-'.$index.'.png';
            } elseif ($field['type'] === 'list') {
                $value = [$marker];
            } elseif ($field['type'] === 'cards') {
                $value = [['question' => $marker, 'answer' => 'Edited answer']];
            }
            data_set($settings, 'doll_after_dark.'.$field['key'], $value);
            $expected[] = $field['type'] === 'image' ? 'images/custom-'.$index.'.png' : $marker;
        }
        SiteSetting::set(MarketingContent::SETTINGS_KEY, $settings);
        $response = $this->get(route('doll-after-dark'))->assertOk();
        foreach ($expected as $value) {
            $response->assertSee($value);
        }
        data_set($settings, 'doll_after_dark.hero.body', '<script>alert(1)</script>');
        data_set($settings, 'doll_after_dark.faq.items', [['question' => '<img src=x onerror=alert(1)>', 'answer' => '<b>Plain text</b>']]);
        SiteSetting::set(MarketingContent::SETTINGS_KEY, $settings);
        $this->get(route('doll-after-dark'))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
