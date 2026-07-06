<?php

namespace Tests\Feature;

use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OnboardingClientAmendmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_onboarding_shows_updated_platform_work_and_service_options(): void
    {
        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->get(route('member.onboarding.edit'))
            ->assertOk()
            ->assertSeeText('XXPANDER')
            ->assertSeeTextInOrder([
                'Freemium Streaming',
                'Premium Streaming',
                'Fan Subscription Platforms',
                'All Types',
            ])
            ->assertSeeText('Free public cam platforms with optional tipping')
            ->assertSeeText('Twerking')
            ->assertDontSeeText('Twerking / Ass Play');
    }

    public function test_bank_details_are_encrypted_and_visible_to_the_member_and_admin(): void
    {
        Mail::fake();

        $member = User::factory()->create(['role' => 'model']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($member)
            ->put(route('member.onboarding.update'), [
                'legal_name' => 'Bank Details Model',
                'stage_name' => 'Secure Doll',
                'date_of_birth' => now()->subYears(21)->format('Y-m-d'),
                'phone_country' => 'GB',
                'phone_number' => '7700 900555',
                'country' => 'United Kingdom',
                'city' => 'London',
                'timezone' => 'Europe/London',
                'availability' => 'Evenings',
                'goals' => 'Build a reliable income.',
                'payout_methods' => ['Bank Transfer'],
                'payout_country' => 'United Kingdom',
                'payout_account_name' => 'Bank Details Model',
                'payout_bank_name' => 'Example Bank',
                'payout_sort_code' => '12-34-56',
                'payout_account_number' => '12345678',
                'payout_iban' => 'GB12EXAMPLE1234567890',
            ])
            ->assertRedirect(route('member.verification.edit'));

        $profile = ModelProfile::query()->where('user_id', $member->id)->firstOrFail();
        $this->assertSame('Bank Details Model', $profile->payout_account_name);
        $this->assertSame('Example Bank', $profile->payout_bank_name);
        $this->assertSame('12-34-56', $profile->payout_sort_code);
        $this->assertSame('12345678', $profile->payout_account_number);
        $this->assertSame('GB12EXAMPLE1234567890', $profile->payout_iban);

        $stored = DB::table('model_profiles')->where('id', $profile->id)->first();
        $this->assertNotSame('12345678', $stored->payout_account_number);
        $this->assertNotSame('GB12EXAMPLE1234567890', $stored->payout_iban);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSeeText('Name on account')
            ->assertSeeText('Bank Details Model')
            ->assertSeeText('Example Bank')
            ->assertSeeText('12-34-56')
            ->assertSeeText('12345678')
            ->assertSeeText('GB12EXAMPLE1234567890');
    }
}
