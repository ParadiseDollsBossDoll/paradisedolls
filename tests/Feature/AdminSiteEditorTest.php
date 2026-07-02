<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSiteEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_main_site_editor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.site-editor.edit'))
            ->assertOk()
            ->assertSee('Site Editor')
            ->assertSee('Home')
            ->assertSee('Shared Navbar/Footer');
    }

    public function test_non_admin_cannot_open_main_site_editor(): void
    {
        $model = User::factory()->create(['role' => 'model']);

        $this->actingAs($model)
            ->get(route('admin.site-editor.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_update_home_hero_copy_and_public_home_uses_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $titleField = MarketingContent::fieldId('hero.title');
        $bodyField = MarketingContent::fieldId('hero.body');

        $this->actingAs($admin)
            ->put(route('admin.site-editor.update'), [
                '_page' => 'home',
                'content' => [
                    'home' => [
                        $titleField => 'Kayla can edit this headline',
                        $bodyField => "First editable paragraph.\n\nSecond editable paragraph.",
                    ],
                ],
            ])
            ->assertRedirect(route('admin.site-editor.edit', ['page' => 'home']));

        $settings = SiteSetting::get(MarketingContent::SETTINGS_KEY, []);

        $this->assertSame('Kayla can edit this headline', data_get($settings, 'home.hero.title'));
        $this->assertSame(['First editable paragraph.', 'Second editable paragraph.'], data_get($settings, 'home.hero.body'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kayla can edit this headline')
            ->assertSee('First editable paragraph.');
    }

    public function test_public_footer_has_social_media_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://www.tiktok.com/@paradisedollsstreaming', false)
            ->assertSee('https://snapchat.com/t/XDWG3Kkz', false)
            ->assertSee('https://www.instagram.com/barbiebossdoll/', false)
            ->assertSee('https://api.whatsapp.com/send?phone=447346924436', false)
            ->assertSee('https://t.me/paradisedolls26', false)
            ->assertSee('https://www.facebook.com/share/19BBXuqjvS/?mibextid=wwXIfr', false)
            ->assertSee('Open TikTok')
            ->assertSee('Open Snapchat')
            ->assertSee('Open Instagram')
            ->assertSee('Open WhatsApp')
            ->assertSee('Open Telegram')
            ->assertSee('Open Facebook');
    }

    public function test_admin_can_upload_marketing_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $imageField = MarketingContent::fieldId('hero.image');

        $this->actingAs($admin)
            ->put(route('admin.site-editor.update'), [
                '_page' => 'home',
                'content' => [
                    'home' => [
                        $imageField => '',
                    ],
                ],
                'image_files' => [
                    'home' => [
                        $imageField => $this->fakePng('hero.png'),
                    ],
                ],
            ])
            ->assertRedirect(route('admin.site-editor.edit', ['page' => 'home']));

        $path = data_get(SiteSetting::get(MarketingContent::SETTINGS_KEY, []), 'home.hero.image');

        $this->assertIsString($path);
        $this->assertStringStartsWith('marketing/site-editor/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_invalid_link_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $urlField = MarketingContent::fieldId('hero.primary_url');

        $this->actingAs($admin)
            ->from(route('admin.site-editor.edit', ['page' => 'home']))
            ->put(route('admin.site-editor.update'), [
                '_page' => 'home',
                'content' => [
                    'home' => [
                        $urlField => 'javascript:alert(1)',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.site-editor.edit', ['page' => 'home']))
            ->assertSessionHasErrors([
                "content.home.{$urlField}",
            ]);
    }

    public function test_invalid_image_upload_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $imageField = MarketingContent::fieldId('hero.image');

        $this->actingAs($admin)
            ->from(route('admin.site-editor.edit', ['page' => 'home']))
            ->put(route('admin.site-editor.update'), [
                '_page' => 'home',
                'content' => [
                    'home' => [
                        $imageField => '',
                    ],
                ],
                'image_files' => [
                    'home' => [
                        $imageField => UploadedFile::fake()->createWithContent('bad.txt', 'not an image'),
                    ],
                ],
            ])
            ->assertRedirect(route('admin.site-editor.edit', ['page' => 'home']))
            ->assertSessionHasErrors([
                "image_files.home.{$imageField}",
            ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );
    }
}
