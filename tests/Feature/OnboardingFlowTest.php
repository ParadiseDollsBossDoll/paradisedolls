<?php

namespace Tests\Feature;

use App\Mail\AccountApprovalMail;
use App\Mail\ApplicationSubmittedMail;
use App\Mail\CommunityAccessMail;
use App\Mail\MemberApplicationApprovedMail;
use App\Mail\ModelInformationSubmittedMail;
use App\Mail\VerificationResubmissionMail;
use App\Mail\VerificationSubmissionReceivedMail;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_submission_can_include_photos_and_notifies_onboarding(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post(route('apply.store'), [
            'name' => 'Kayla Test',
            'email' => 'kayla@example.com',
            'phone_country' => 'PH',
            'phone_number' => '912 345 6789',
            'experience_level' => 'beginner',
            'social_handle' => '@kayla',
            'message' => 'I want to build remote income.',
            'age_confirmed' => '1',
            'photos' => [
                $this->fakePng('photo-one.png'),
            ],
        ])->assertRedirect(route('home').'#apply');

        $application = ModelApplication::first();

        $this->assertNotNull($application);
        $this->assertSame('+639123456789', $application->phone);
        $this->assertCount(1, $application->photo_paths);
        Storage::disk('local')->assertExists($application->photo_paths[0]);
        Mail::assertQueued(ApplicationSubmittedMail::class);
    }

    public function test_application_submission_rejects_invalid_phone_numbers(): void
    {
        Mail::fake();

        $this->post(route('apply.store'), [
            'name' => 'Kayla Test',
            'email' => 'kayla@example.com',
            'phone_country' => 'PH',
            'phone_number' => 'call me maybe',
            'experience_level' => 'beginner',
            'age_confirmed' => '1',
        ])->assertSessionHasErrors('phone_number');

        $this->assertDatabaseCount('model_applications', 0);
        Mail::assertNothingSent();
    }

    public function test_application_submission_rejects_invalid_email_addresses(): void
    {
        Mail::fake();

        $this->post(route('apply.store'), [
            'name' => 'Kayla Test',
            'email' => 'not-an-email',
            'phone_country' => 'PH',
            'phone_number' => '912 345 6789',
            'experience_level' => 'beginner',
            'age_confirmed' => '1',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('model_applications', 0);
        Mail::assertNothingSent();
    }

    public function test_admin_approval_creates_member_profile_and_sends_application_email(): void
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

        $admin = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create([
            'name' => 'Approved Model',
            'email' => 'approved@example.com',
            'experience_level' => 'none',
            'age_confirmed' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect();

        $member = User::where('email', 'approved@example.com')->first();

        $this->assertNotNull($member);
        $this->assertSame('model', $member->role);
        $this->assertDatabaseHas('model_profiles', [
            'user_id' => $member->id,
            'model_application_id' => $application->id,
        ]);
        Mail::assertQueued(MemberApplicationApprovedMail::class);
    }

    public function test_admin_approval_shows_temporary_password_when_mailer_cannot_deliver(): void
    {
        Mail::fake();
        config(['mail.default' => 'log']);

        $admin = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create([
            'name' => 'Fallback Model',
            'email' => 'fallback@example.com',
            'experience_level' => 'none',
            'age_confirmed' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect()
            ->assertSessionHas('warning')
            ->assertSessionHas('approval_fallback_email', 'fallback@example.com')
            ->assertSessionHas('approval_fallback_password');

        $this->assertDatabaseHas('users', [
            'email' => 'fallback@example.com',
            'role' => 'model',
        ]);
        Mail::assertNothingSent();
    }

    public function test_admin_approval_shows_temporary_password_when_smtp_credentials_are_placeholders(): void
    {
        Mail::fake();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.username' => 'your-email@gmail.com',
            'mail.mailers.smtp.password' => 'your_16_character_google_app_password',
            'mail.from.address' => 'your-email@gmail.com',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create([
            'name' => 'Placeholder Model',
            'email' => 'placeholder@example.com',
            'experience_level' => 'none',
            'age_confirmed' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect()
            ->assertSessionHas('warning')
            ->assertSessionHas('approval_fallback_email', 'placeholder@example.com')
            ->assertSessionHas('approval_fallback_password');

        Mail::assertNothingSent();
    }

    public function test_member_can_submit_information_and_verification_documents(): void
    {
        Mail::fake();
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->put(route('member.onboarding.update'), [
                'legal_name' => 'Legal Name',
                'stage_name' => 'Stage Name',
                'date_of_birth' => now()->subYears(21)->format('Y-m-d'),
                'phone_country' => 'GB',
                'phone_number' => '7700 900555',
                'country' => 'United Kingdom',
                'city' => 'London',
                'timezone' => 'Europe/London',
                'platforms' => ['Stripchat', 'OnlyFans'],
                'equipment' => ['Phone', 'Ring light'],
                'availability' => 'Evenings and weekends.',
                'goals' => 'Build a consistent online income.',
                'experience_notes' => 'Beginner.',
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_phone_country' => 'PH',
                'emergency_contact_phone_number' => '985 474 7065',
                'discord_username' => 'stage-name',
                'discord_user_id' => '1234567890',
            ])
            ->assertRedirect(route('member.verification.edit'));

        $profile = $member->modelProfile()->first();
        $this->assertNotNull($profile->information_submitted_at);
        $this->assertSame('+447700900555', $profile->phone);
        $this->assertSame('+639854747065', $profile->emergency_contact_phone);
        $this->assertSame(['Stripchat', 'OnlyFans'], $profile->platforms);
        $this->assertSame('stage-name', $profile->discord_username);
        Mail::assertQueued(ModelInformationSubmittedMail::class);

        $this->actingAs($member)
            ->post(route('member.verification.store'), [
                'id_document' => $this->fakePng('id.png'),
                'selfie_with_id' => $this->fakePng('selfie.png'),
            ])
            ->assertRedirect(route('member.dashboard'));

        $profile->refresh();

        $this->assertSame(ModelProfile::VERIFICATION_SUBMITTED, $profile->verification_status);
        Storage::disk('local')->assertExists($profile->id_document_path);
        Storage::disk('local')->assertExists($profile->selfie_with_id_path);
        Mail::assertQueued(VerificationSubmissionReceivedMail::class);

        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertSeeText('Submitted for review. The admin team is reviewing your onboarding details and verification IDs.');
    }

    public function test_member_onboarding_rejects_invalid_phone_numbers(): void
    {
        Mail::fake();

        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->put(route('member.onboarding.update'), [
                'legal_name' => 'Legal Name',
                'stage_name' => 'Stage Name',
                'date_of_birth' => now()->subYears(21)->format('Y-m-d'),
                'phone_country' => 'GB',
                'phone_number' => 'call me maybe',
                'country' => 'United Kingdom',
                'city' => 'London',
                'timezone' => 'Europe/London',
                'availability' => 'Evenings and weekends.',
                'goals' => 'Build a consistent online income.',
            ])
            ->assertSessionHasErrors('phone_number');

        $this->assertNull($member->modelProfile()->first()?->information_submitted_at);
        Mail::assertNothingQueued();
    }

    public function test_member_onboarding_rejects_invalid_emergency_contact_phone_numbers(): void
    {
        Mail::fake();

        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->put(route('member.onboarding.update'), [
                'legal_name' => 'Legal Name',
                'stage_name' => 'Stage Name',
                'date_of_birth' => now()->subYears(21)->format('Y-m-d'),
                'phone_country' => 'GB',
                'phone_number' => '7700 900555',
                'country' => 'United Kingdom',
                'city' => 'London',
                'timezone' => 'Europe/London',
                'availability' => 'Evenings and weekends.',
                'goals' => 'Build a consistent online income.',
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_phone_country' => 'PH',
                'emergency_contact_phone_number' => 'call me maybe',
            ])
            ->assertSessionHasErrors('emergency_contact_phone_number');

        $this->assertNull($member->modelProfile()->first()?->information_submitted_at);
        Mail::assertNothingQueued();
    }

    public function test_member_cannot_submit_verification_before_information_form(): void
    {
        Mail::fake();
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->post(route('member.verification.store'), [
                'id_document' => $this->fakePng('id.png'),
                'selfie_with_id' => $this->fakePng('selfie.png'),
            ])
            ->assertRedirect(route('member.onboarding.edit'))
            ->assertSessionHasErrors('profile');

        $profile = $member->modelProfile()->first();

        $this->assertNotNull($profile);
        $this->assertNull($profile->verification_submitted_at);

        $this->actingAs($member)
            ->get(route('member.verification.edit'))
            ->assertOk()
            ->assertSeeText('Submit the Model Information Form before uploading verification documents.')
            ->assertDontSeeText('Submit Verification');
    }

    public function test_admin_resubmission_requires_note_and_emails_member(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
            'verification_submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.reject-verification', $profile))
            ->assertSessionHasErrors('verification_notes');

        $this->assertSame(ModelProfile::VERIFICATION_SUBMITTED, $profile->fresh()->verification_status);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.reject-verification', $profile), [
                'verification_notes' => 'Please upload a clearer selfie holding your ID.',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame(ModelProfile::VERIFICATION_REJECTED, $profile->verification_status);
        $this->assertSame('Please upload a clearer selfie holding your ID.', $profile->verification_notes);
        Mail::assertQueued(VerificationResubmissionMail::class);

        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertSeeText('Resubmission instructions')
            ->assertSeeText('Please upload a clearer selfie holding your ID.');

        $this->actingAs($member)
            ->get(route('member.verification.edit'))
            ->assertOk()
            ->assertSeeText('Resubmission instructions')
            ->assertSeeText('Please upload a clearer selfie holding your ID.');

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index'))
            ->assertOk()
            ->assertSeeText('Discord Invites');
    }

    public function test_admin_can_approve_verification_and_send_community_access(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
            'verification_submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.verify', $profile))
            ->assertRedirect();

        $this->assertSame(ModelProfile::VERIFICATION_VERIFIED, $profile->fresh()->verification_status);
        Mail::assertQueued(AccountApprovalMail::class);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.community-invite', $profile))
            ->assertRedirect();

        $this->assertNotNull($profile->fresh()->community_invited_at);
        Mail::assertQueued(CommunityAccessMail::class);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.community-role-assigned', $profile))
            ->assertRedirect();

        $this->assertNotNull($profile->fresh()->community_role_assigned_at);
    }

    private function fakePng(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }
}
