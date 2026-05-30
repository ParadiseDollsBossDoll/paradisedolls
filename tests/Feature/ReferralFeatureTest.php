<?php

namespace Tests\Feature;

use App\Mail\ApplicationSubmittedMail;
use App\Mail\MemberApplicationApprovedMail;
use App\Models\ModelApplication;
use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReferralFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_referral_with_required_photos_and_consent(): void
    {
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->get(route('member.referrals.index'))
            ->assertOk()
            ->assertSee('Refer a Model')
            ->assertSee($member->fresh()->referral_code);

        $this->actingAs($member)
            ->post(route('member.referrals.store'), [
                'candidate_name' => 'Referral Candidate',
                'candidate_email' => 'candidate@example.com',
                'candidate_phone' => '+639123456789',
                'candidate_social_handle' => '@candidate',
                'experience_level' => 'beginner',
                'note' => 'She would be a strong fit.',
                'consent_confirmed' => '1',
                'photos' => [
                    $this->fakePng('candidate.png'),
                ],
            ])
            ->assertRedirect(route('member.referrals.index'));

        $referral = ModelReferral::first();

        $this->assertNotNull($referral);
        $this->assertSame($member->id, $referral->referrer_id);
        $this->assertSame(ModelReferral::STATUS_REFERRED, $referral->status);
        $this->assertSame(ModelReferral::REWARD_NOT_ELIGIBLE, $referral->reward_status);
        $this->assertTrue($referral->consent_confirmed);
        $this->assertCount(1, $referral->photo_paths);
        Storage::disk('local')->assertExists($referral->photo_paths[0]);
    }

    public function test_referral_form_requires_photos_consent_and_a_contact_method(): void
    {
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->post(route('member.referrals.store'), [
                'candidate_name' => 'Referral Candidate',
                'experience_level' => 'beginner',
            ])
            ->assertSessionHasErrors(['candidate_email', 'photos', 'consent_confirmed']);

        $this->assertDatabaseCount('model_referrals', 0);
    }

    public function test_member_can_submit_referral_with_phone_contact_instead_of_email(): void
    {
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->post(route('member.referrals.store'), [
                'candidate_name' => 'Phone Only Candidate',
                'candidate_phone' => '+639123456789',
                'experience_level' => 'none',
                'consent_confirmed' => '1',
                'photos' => [
                    $this->fakePng('phone-only.png'),
                ],
            ])
            ->assertRedirect(route('member.referrals.index'));

        $this->assertDatabaseHas('model_referrals', [
            'candidate_name' => 'Phone Only Candidate',
            'candidate_email' => null,
            'candidate_phone' => '+639123456789',
        ]);
    }

    public function test_public_application_with_referral_code_links_to_referrer(): void
    {
        Mail::fake();
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);
        $referralCode = $member->referral_code;

        $this->get(route('apply', ['ref' => $referralCode]))
            ->assertRedirect(route('home', ['ref' => $referralCode]).'#apply');

        $this->post(route('apply.store'), [
            'name' => 'Linked Applicant',
            'email' => 'linked@example.com',
            'phone_country' => 'PH',
            'phone_number' => '912 345 6789',
            'experience_level' => 'beginner',
            'age_confirmed' => '1',
            'referral_code' => $referralCode,
        ])->assertRedirect(route('home').'#apply');

        $application = ModelApplication::where('email', 'linked@example.com')->first();
        $referral = ModelReferral::where('candidate_email', 'linked@example.com')->first();

        $this->assertNotNull($application);
        $this->assertNotNull($referral);
        $this->assertSame($member->id, $referral->referrer_id);
        $this->assertSame($application->id, $referral->model_application_id);
        $this->assertSame(ModelReferral::STATUS_PENDING, $referral->status);
        Mail::assertSent(ApplicationSubmittedMail::class);
    }

    public function test_public_application_with_invalid_referral_code_submits_without_referral(): void
    {
        Mail::fake();

        $this->post(route('apply.store'), [
            'name' => 'Normal Applicant',
            'email' => 'normal@example.com',
            'phone_country' => 'PH',
            'phone_number' => '912 345 6789',
            'experience_level' => 'beginner',
            'age_confirmed' => '1',
            'referral_code' => 'NOTREAL',
        ])->assertRedirect(route('home').'#apply');

        $this->assertDatabaseHas('model_applications', [
            'email' => 'normal@example.com',
        ]);
        $this->assertDatabaseCount('model_referrals', 0);
    }

    public function test_admin_can_convert_referral_and_reward_becomes_eligible_on_approval_then_paid(): void
    {
        Mail::fake();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.username' => 'sender@example.com',
            'mail.mailers.smtp.password' => 'test-password',
            'mail.from.address' => 'sender@example.com',
        ]);
        Storage::fake('local');
        Storage::disk('local')->put('referrals/photos/candidate.png', 'photo');

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);

        $referral = ModelReferral::create([
            'referrer_id' => $member->id,
            'candidate_name' => 'Reward Candidate',
            'candidate_email' => 'reward@example.com',
            'experience_level' => 'beginner',
            'photo_paths' => ['referrals/photos/candidate.png'],
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_MEMBER_FORM,
            'status' => ModelReferral::STATUS_REFERRED,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Referral Leads')
            ->assertSee('Reward Candidate');

        $this->actingAs($admin)
            ->post(route('admin.applications.referrals.convert', $referral))
            ->assertRedirect();

        $referral->refresh();
        $application = $referral->application;

        $this->assertNotNull($application);
        $this->assertSame(ModelReferral::STATUS_PENDING, $referral->status);
        $this->assertSame(['referrals/photos/candidate.png'], $application->photo_paths);

        $this->actingAs($admin)
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect();

        $referral->refresh();

        $this->assertSame(ModelReferral::STATUS_JOINED, $referral->status);
        $this->assertSame(ModelReferral::REWARD_ELIGIBLE, $referral->reward_status);
        Mail::assertSent(MemberApplicationApprovedMail::class);

        $this->actingAs($admin)
            ->post(route('admin.applications.referrals.reward-paid', $referral))
            ->assertRedirect();

        $this->assertSame(ModelReferral::REWARD_PAID, $referral->fresh()->reward_status);
    }

    public function test_non_model_users_cannot_access_member_referrals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('member.referrals.index'))
            ->assertRedirect(route('admin.models.progress'));
    }

    private function fakePng(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }
}
