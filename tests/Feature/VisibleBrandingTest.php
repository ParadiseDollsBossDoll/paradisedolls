<?php

namespace Tests\Feature;

use App\Mail\CommunityAccessMail;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisibleBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_use_boss_doll_creators_branding_and_keep_existing_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Boss Doll Creators')
            ->assertSeeText('Paradise Dolls application agreement')
            ->assertSee('images/brand/boss-doll-creators-logo-horizontal-pink.png', false)
            ->assertSee('https://www.tiktok.com/@paradisedollsstreaming', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('images/brand/boss-doll-creators-logo-horizontal-pink.png', false)
            ->assertDontSeeText('Paradise Dolls');
    }

    public function test_member_email_uses_new_visible_brand_and_keeps_existing_contact_addresses(): void
    {
        $this->assertSame('Boss Doll Creators', config('app.name'));
        $this->assertSame('Boss Doll Creators', config('mail.from.name'));

        $member = User::factory()->create([
            'name' => 'Brand Test Model',
            'role' => 'model',
        ]);
        $profile = ModelProfile::create(['user_id' => $member->id])->load('user');

        $html = (new CommunityAccessMail(
            profile: $profile,
            communityUrl: 'https://discord.gg/example',
            whatsappCommunityUrl: 'https://chat.whatsapp.com/example',
            roleName: 'Boss Doll Blueprint',
        ))->render();

        $this->assertStringContainsString('Boss Doll Creators', $html);
        $this->assertStringNotContainsString('Paradise Dolls', $html);
        $this->assertSame('admin@getrichwithparadisedolls.com', config('paradise.onboarding_email'));
    }

    public function test_rebrand_migration_updates_saved_copy_without_changing_existing_urls(): void
    {
        DB::table('site_settings')->updateOrInsert(
            ['key' => 'marketing_content'],
            [
                'value' => json_encode([
                    'headline' => 'Join Paradise Dolls',
                    'contact_url' => 'https://getrichwithparadisedolls.com/apply',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $migration = require database_path('migrations/2026_10_17_000000_rebrand_visible_site_to_boss_doll_creators.php');
        $migration->up();

        $content = json_decode((string) DB::table('site_settings')->where('key', 'marketing_content')->value('value'), true);
        $theme = json_decode((string) DB::table('site_settings')->where('key', 'theme')->value('value'), true);

        $this->assertSame('Join Boss Doll Creators', $content['headline']);
        $this->assertSame('https://getrichwithparadisedolls.com/apply', $content['contact_url']);
        $this->assertSame('boss-light', $theme['preset']);
        $this->assertSame('#FF0080', $theme['primary']);
    }
}
