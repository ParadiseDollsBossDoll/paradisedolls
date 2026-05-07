<?php

namespace Tests\Feature;

use App\Mail\AccountApprovalMail;
use App\Mail\ApplicationSubmittedMail;
use App\Mail\CommunityAccessMail;
use App\Mail\MemberApplicationApprovedMail;
use App\Mail\ModelInformationSubmittedMail;
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
            'phone' => '+447700900123',
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
        $this->assertCount(1, $application->photo_paths);
        Storage::disk('local')->assertExists($application->photo_paths[0]);
        Mail::assertSent(ApplicationSubmittedMail::class);
    }

    public function test_admin_approval_creates_member_profile_and_sends_application_email(): void
    {
        Mail::fake();

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
        Mail::assertSent(MemberApplicationApprovedMail::class);
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
                'phone' => '+447700900555',
                'country' => 'United Kingdom',
                'city' => 'London',
                'timezone' => 'Europe/London',
                'platforms' => ['Stripchat', 'OnlyFans'],
                'equipment' => ['Phone', 'Ring light'],
                'availability' => 'Evenings and weekends.',
                'goals' => 'Build a consistent online income.',
                'experience_notes' => 'Beginner.',
                'discord_username' => 'stage-name',
                'discord_user_id' => '1234567890',
            ])
            ->assertRedirect(route('member.dashboard'));

        $profile = $member->modelProfile()->first();
        $this->assertNotNull($profile->information_submitted_at);
        $this->assertSame(['Stripchat', 'OnlyFans'], $profile->platforms);
        $this->assertSame('stage-name', $profile->discord_username);
        Mail::assertSent(ModelInformationSubmittedMail::class);

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
        Mail::assertSent(VerificationSubmissionReceivedMail::class);
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
        Mail::assertSent(AccountApprovalMail::class);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.community-invite', $profile))
            ->assertRedirect();

        $this->assertNotNull($profile->fresh()->community_invited_at);
        Mail::assertSent(CommunityAccessMail::class);

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
