<?php

namespace Tests\Feature;

use App\Mail\AccountApprovalMail;
use App\Mail\AdminActivityAlertMail;
use App\Mail\ApplicationSubmittedMail;
use App\Mail\CommunityAccessMail;
use App\Mail\MemberApplicationApprovedMail;
use App\Mail\ModelInformationSubmittedMail;
use App\Mail\VerificationRequestMail;
use App\Mail\VerificationResubmissionMail;
use App\Mail\VerificationSubmissionReceivedMail;
use App\Models\Course;
use App\Models\CourseAccessRequest;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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

        $admin = User::factory()->create(['role' => 'admin']);

        $this->post(route('apply.store'), [
            'name' => 'Kayla Test',
            'email' => 'kayla@example.com',
            'phone_country' => 'PH',
            'phone_number' => '912 345 6789',
            'experience_level' => 'beginner',
            'social_handle' => '@kayla',
            'message' => 'I want to build remote income.',
            'age_confirmed' => '1',
            'terms_accepted' => '1',
            'photos' => [
                $this->fakePng('photo-one.png'),
            ],
        ])->assertRedirect(route('home').'#apply');

        $application = ModelApplication::first();

        $this->assertNotNull($application);
        $this->assertSame('+639123456789', $application->phone);
        $this->assertNotNull($application->terms_accepted_at);
        $this->assertSame(ModelApplication::TERMS_VERSION, $application->terms_version);
        $this->assertCount(1, $application->photo_paths);
        Storage::disk('local')->assertExists($application->photo_paths[0]);
        $this->assertSame('application_submitted', $admin->notifications()->first()?->data['category']);
        Mail::assertSent(ApplicationSubmittedMail::class);
    }

    public function test_admin_is_notified_when_member_begins_onboarding(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Starter Model',
            'email' => 'starter@example.com',
            'role' => 'model',
        ]);

        $this->actingAs($member)
            ->get(route('member.onboarding.edit'))
            ->assertOk();

        $profile = $member->modelProfile()->first();

        $this->assertNotNull($profile?->onboarding_started_at);
        $this->assertSame('onboarding_started', $admin->notifications()->first()?->data['category']);
        Mail::assertQueued(AdminActivityAlertMail::class, fn (AdminActivityAlertMail $mail) => $mail->subjectLine === 'Onboarding started: Starter Model');

        $this->actingAs($member)
            ->get(route('member.onboarding.edit'))
            ->assertOk();

        $this->assertSame(1, $admin->notifications()->count());
        Mail::assertQueued(AdminActivityAlertMail::class, 1);
    }

    public function test_application_submission_rejects_photos_larger_than_ten_megabytes(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post(route('apply.store'), [
            'name' => 'Kayla Test',
            'email' => 'kayla@example.com',
            'phone_country' => 'PH',
            'phone_number' => '912 345 6789',
            'experience_level' => 'beginner',
            'age_confirmed' => '1',
            'terms_accepted' => '1',
            'photos' => [
                $this->fakeLargePng('large-photo.png'),
            ],
        ])->assertSessionHasErrors('photos.0');

        $this->assertDatabaseCount('model_applications', 0);
        Mail::assertNothingSent();
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
            'terms_accepted' => '1',
        ])->assertSessionHasErrors('phone_number');

        $this->assertDatabaseCount('model_applications', 0);
        Mail::assertNothingSent();
    }

    public function test_application_submission_requires_terms_acceptance(): void
    {
        Mail::fake();

        $this->post(route('apply.store'), [
            'name' => 'Kayla Test',
            'email' => 'kayla@example.com',
            'phone_country' => 'PH',
            'phone_number' => '912 345 6789',
            'experience_level' => 'beginner',
            'age_confirmed' => '1',
        ])->assertSessionHasErrors('terms_accepted');

        $this->assertDatabaseCount('model_applications', 0);
        Mail::assertNothingSent();
    }

    public function test_application_submission_accepts_expanded_country_phone_prefixes(): void
    {
        Mail::fake();

        $this->post(route('apply.store'), [
            'name' => 'Island Applicant',
            'email' => 'island@example.com',
            'phone_country' => 'DO-829',
            'phone_number' => '555 1234',
            'experience_level' => 'beginner',
            'age_confirmed' => '1',
            'terms_accepted' => '1',
        ])->assertRedirect(route('home').'#apply');

        $this->assertSame('+18295551234', ModelApplication::first()?->phone);
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
            'terms_accepted' => '1',
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
        $notification = $member->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('application_approved', $notification->data['category']);
        $this->assertSame('Application approved', $notification->data['title']);
        Mail::assertSent(MemberApplicationApprovedMail::class);
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

    public function test_admin_can_resend_application_approval_email_with_fresh_temporary_password(): void
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
        $member = User::factory()->create([
            'name' => 'Approved Model',
            'email' => 'approved@example.com',
            'role' => 'model',
            'password' => Hash::make('old-password'),
            'email_verified_at' => null,
        ]);
        $application = ModelApplication::create([
            'name' => 'Approved Model',
            'email' => 'approved@example.com',
            'experience_level' => 'none',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'user_id' => $member->id,
        ])->save();
        ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.resend-approval-email', $application))
            ->assertRedirect()
            ->assertSessionHas('status', 'Approval email resent to approved@example.com. A fresh temporary password was created for the member.');

        Mail::assertSent(MemberApplicationApprovedMail::class, function (MemberApplicationApprovedMail $mail) use ($member) {
            return $mail->memberName === 'Approved Model'
                && $mail->loginUrl === route('login')
                && $mail->onboardingUrl === route('member.onboarding.edit')
                && Hash::check($mail->temporaryPassword, $member->fresh()->password);
        });

        $member->refresh();

        $this->assertNotNull($member->email_verified_at);
        $this->assertFalse(Hash::check('old-password', $member->password));
        $this->assertSame('application_approval_resent', $member->notifications()->first()?->data['category']);
    }

    public function test_admin_cannot_resend_application_approval_email_after_member_has_logged_in(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'email' => 'logged-in@example.com',
            'role' => 'model',
            'last_login_at' => now(),
        ]);
        $application = ModelApplication::create([
            'name' => 'Logged In Model',
            'email' => 'logged-in@example.com',
            'experience_level' => 'none',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.resend-approval-email', $application))
            ->assertRedirect()
            ->assertSessionHasErrors('application');

        Mail::assertNothingSent();
    }

    public function test_admin_cannot_resend_application_approval_email_after_onboarding_is_submitted(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'email' => 'submitted@example.com',
            'role' => 'model',
            'last_login_at' => null,
        ]);
        $application = ModelApplication::create([
            'name' => 'Submitted Model',
            'email' => 'submitted@example.com',
            'experience_level' => 'none',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
            'information_submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.resend-approval-email', $application))
            ->assertRedirect()
            ->assertSessionHasErrors('application');

        Mail::assertNothingSent();
    }

    public function test_admin_cannot_resend_application_approval_email_for_pending_application(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create([
            'name' => 'Pending Model',
            'email' => 'pending-resend@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.resend-approval-email', $application))
            ->assertRedirect()
            ->assertSessionHasErrors('application');

        Mail::assertNothingSent();
    }

    public function test_admin_can_see_resend_application_approval_email_action_on_onboarding_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Onboarding Model',
            'email' => 'onboarding-approved@example.com',
            'role' => 'model',
        ]);
        $application = ModelApplication::create([
            'name' => 'Onboarding Model',
            'email' => 'onboarding-approved@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSee('Resend Application Approval Email')
            ->assertSee(route('admin.applications.resend-approval-email', $application), false);
    }

    public function test_admin_can_see_application_photos_on_onboarding_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Photo Model',
            'email' => 'photo-model@example.com',
            'role' => 'model',
        ]);
        $application = ModelApplication::create([
            'name' => 'Photo Model',
            'email' => 'photo-model@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
            'photo_paths' => [
                'applications/photos/photo-one.jpg',
                'applications/photos/photo-two.jpg',
            ],
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSeeText('Application Photos')
            ->assertSeeText('2 photos')
            ->assertSee(route('admin.applications.photos.view', [$application, 0]), false)
            ->assertSee(route('admin.applications.photos.show', [$application, 1]), false);
    }

    public function test_admin_does_not_see_resend_application_approval_email_action_after_onboarding_is_submitted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Submitted Model',
            'email' => 'onboarding-submitted@example.com',
            'role' => 'model',
            'last_login_at' => null,
        ]);
        $application = ModelApplication::create([
            'name' => 'Submitted Model',
            'email' => 'onboarding-submitted@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
            'information_submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertDontSee('Resend Application Approval Email')
            ->assertDontSee(route('admin.applications.resend-approval-email', $application), false);
    }

    public function test_admin_can_delete_rejected_application_and_uploaded_photos(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $referrer = User::factory()->create(['role' => 'model']);

        $application = ModelApplication::create([
            'name' => 'Rejected Model',
            'email' => 'rejected@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
            'photo_paths' => ['applications/photos/rejected.jpg'],
        ]);
        $application->forceFill(['status' => ModelApplication::STATUS_REJECTED])->save();

        $referral = ModelReferral::create([
            'referrer_id' => $referrer->id,
            'model_application_id' => $application->id,
            'candidate_name' => 'Rejected Model',
            'candidate_email' => 'rejected@example.com',
            'experience_level' => 'beginner',
            'photo_paths' => ['applications/photos/rejected.jpg'],
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
            'status' => ModelReferral::STATUS_REJECTED,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ]);

        Storage::disk('local')->put('applications/photos/rejected.jpg', 'photo');

        $this->actingAs($admin)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Delete Rejected Application')
            ->assertSee('Delete rejected application?')
            ->assertDontSee('return confirm', false);

        $this->actingAs($admin)
            ->delete(route('admin.applications.destroy', $application))
            ->assertRedirect()
            ->assertSessionHas('status', 'Rejected application deleted.');

        $this->assertDatabaseMissing('model_applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('model_referrals', ['id' => $referral->id]);
        Storage::disk('local')->assertMissing('applications/photos/rejected.jpg');
    }

    public function test_admin_cannot_delete_pending_application(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create([
            'name' => 'Pending Model',
            'email' => 'pending@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.applications.destroy', $application))
            ->assertRedirect()
            ->assertSessionHasErrors('application');

        $this->assertDatabaseHas('model_applications', ['id' => $application->id]);
    }

    public function test_admin_can_see_member_delete_action_on_onboarding_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Onboarding Model',
            'email' => 'onboarding-model@example.com',
            'role' => 'model',
        ]);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSee('Delete member account')
            ->assertDontSee('Onboarding stage')
            ->assertSee(route('admin.models.destroy', $member), false);
    }

    public function test_member_onboarding_shows_expanded_platform_website_options(): void
    {
        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($member)
            ->get(route('member.onboarding.edit'))
            ->assertOk()
            ->assertSee('AdultWork')
            ->assertSee('Stripchat')
            ->assertSee('Chaturbate')
            ->assertSee('Babestation')
            ->assertSee('LiveJasmin')
            ->assertSee('BongaCams')
            ->assertSee('CamSoda')
            ->assertSee('MyFreeCams')
            ->assertSee('Streamate')
            ->assertSee('Fansly')
            ->assertSee('ManyVids')
            ->assertSee('Clips4Sale');
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
                'platforms' => ['AdultWork', 'CAM4', 'OnlyFans', 'Stripchat', 'Fansly'],
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
        $this->assertSame(['AdultWork', 'CAM4', 'OnlyFans', 'Stripchat', 'Fansly'], $profile->platforms);
        $this->assertSame('stage-name', $profile->discord_username);
        Mail::assertQueued(ModelInformationSubmittedMail::class);
        Mail::assertQueued(AdminActivityAlertMail::class, fn (AdminActivityAlertMail $mail) => $mail->subjectLine === 'Onboarding form completed: '.$member->name);

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
        Mail::assertQueued(AdminActivityAlertMail::class, fn (AdminActivityAlertMail $mail) => $mail->subjectLine === 'Verification ID uploaded: '.$member->name);

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

    public function test_member_verification_no_longer_accepts_platform_code_proof(): void
    {
        Mail::fake();
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'verification_reviewed_by' => $admin->id,
            'verification_reviewed_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);

        $this->actingAs($member)
            ->get(route('member.verification.edit'))
            ->assertOk()
            ->assertSeeText('Existing file on record. Leave blank to keep it.')
            ->assertDontSeeText('Platform codes')
            ->assertDontSee('name="platform_codes"', false);

        $this->actingAs($member)
            ->post(route('member.verification.store'), [
                'platform_codes' => $this->fakePng('stripchat-qr.png'),
            ])
            ->assertSessionHasErrors('id_document');

        $profile->refresh();

        $this->assertSame(ModelProfile::VERIFICATION_VERIFIED, $profile->verification_status);
        $this->assertSame('verifications/1/id.jpg', $profile->id_document_path);
        $this->assertSame('verifications/1/selfie.jpg', $profile->selfie_with_id_path);
        $this->assertNull($profile->platform_codes_path);
        Mail::assertNothingQueued();
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
            ->assertSeeText('Discord Invites')
            ->assertDontSee('Manual Access Controls')
            ->assertDontSee('Current phase');
    }

    public function test_admin_can_approve_existing_documents_after_a_reverification_request(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $submittedAt = now()->subDay()->startOfSecond();
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
            'verification_submitted_at' => $submittedAt,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.reject-verification', $profile), [
                'verification_notes' => 'Please submit verification again.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.onboarding.request-verification', $profile))
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame(ModelProfile::VERIFICATION_REQUESTED, $profile->verification_status);
        $this->assertTrue($profile->canApproveVerification());
        $this->assertTrue($profile->verification_submitted_at->equalTo($submittedAt));

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSeeText('Approve & Send Approval Email')
            ->assertSeeText('The member does not need to submit them again.');

        $this->actingAs($admin)
            ->post(route('admin.onboarding.verify', $profile))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $profile->refresh();

        $this->assertSame(ModelProfile::VERIFICATION_VERIFIED, $profile->verification_status);
        $this->assertTrue($profile->verification_submitted_at->equalTo($submittedAt));
        Mail::assertQueued(VerificationResubmissionMail::class);
        Mail::assertQueued(VerificationRequestMail::class);
        Mail::assertQueued(AccountApprovalMail::class);
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
        $notification = $member->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('verification_approved', $notification->data['category']);
        $this->assertSame('Verification approved', $notification->data['title']);
        Mail::assertQueued(AccountApprovalMail::class);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.community-invite', $profile), [
                'community_url' => 'https://discord.gg/freshInvite',
            ])
            ->assertRedirect();

        $profile->refresh();
        $this->assertNotNull($profile->community_invited_at);
        $this->assertSame('https://discord.gg/freshInvite', $profile->community_invite_url);
        Mail::assertQueued(
            CommunityAccessMail::class,
            fn (CommunityAccessMail $mail) => $mail->communityUrl === 'https://discord.gg/freshInvite'
        );

        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertSee('https://discord.gg/freshInvite', false)
            ->assertSeeText('Open Discord invite');

        $this->actingAs($admin)
            ->post(route('admin.onboarding.community-role-assigned', $profile))
            ->assertRedirect();

        $this->assertNotNull($profile->fresh()->community_role_assigned_at);
    }

    public function test_admin_must_enter_valid_discord_invite_before_sending_community_access(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'verification_reviewed_by' => $admin->id,
            'verification_reviewed_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.community-invite', $profile), [
                'community_url' => 'https://example.com/not-discord',
            ])
            ->assertSessionHasErrors('community_url');

        $this->assertNull($profile->fresh()->community_invited_at);
        Mail::assertNotQueued(CommunityAccessMail::class);
    }

    public function test_admin_cannot_assign_community_chat_before_verification(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
            'community_invited_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.community-role-assigned', $profile))
            ->assertSessionHasErrors('profile');

        $this->assertNull($profile->fresh()->community_role_assigned_at);
    }

    public function test_admin_can_save_custom_verification_instructions_for_member(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
        ]);

        $instructions = 'Upload your ID, selfie with ID, and the platform QR screenshot from the callback.';

        $this->actingAs($admin)
            ->post(route('admin.onboarding.verification-instructions', $profile), [
                'verification_request_instructions' => $instructions,
            ])
            ->assertRedirect();

        $this->assertSame($instructions, $profile->fresh()->verification_request_instructions);

        $this->actingAs($member)
            ->get(route('member.verification.edit'))
            ->assertOk()
            ->assertSeeText('Instructions from Kayla')
            ->assertSeeText($instructions);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.request-verification', $profile))
            ->assertRedirect();

        Mail::assertQueued(VerificationRequestMail::class, function (VerificationRequestMail $mail) use ($instructions) {
            return str_contains($mail->render(), $instructions);
        });
    }

    public function test_admin_can_update_stage_and_unlock_or_lock_course_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Stripchat Verification Walkthrough',
            'slug' => 'stripchat-verification-walkthrough',
            'platform_label' => 'Stripchat',
            'description' => 'Platform-specific walkthrough.',
            'is_published' => true,
        ]);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.stage', $profile), [
                'onboarding_stage' => ModelProfile::STAGE_CALLBACK,
            ])
            ->assertRedirect();

        $this->assertSame(ModelProfile::STAGE_CALLBACK, $profile->fresh()->onboarding_stage);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.courses.unlock', [$profile, $course]))
            ->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('community_channels', [
            'course_id' => $course->id,
        ]);
        $channelId = $course->communityChannels()->firstOrFail()->id;
        $this->assertDatabaseHas('community_channel_accesses', [
            'community_channel_id' => $channelId,
            'user_id' => $member->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.courses.lock', [$profile, $course]))
            ->assertRedirect();

        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseMissing('community_channel_accesses', [
            'community_channel_id' => $channelId,
            'user_id' => $member->id,
        ]);
    }

    public function test_admin_can_request_course_access_resubmission_and_member_can_resubmit(): void
    {
        Mail::fake();
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Stripchat QR Review',
            'slug' => 'stripchat-qr-review',
            'platform_label' => 'Stripchat',
            'description' => 'Platform-specific QR review.',
            'is_published' => true,
        ]);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $accessRequest = CourseAccessRequest::create([
            'course_id' => $course->id,
            'user_id' => $member->id,
            'status' => CourseAccessRequest::STATUS_PENDING,
            'member_notes' => 'I followed the QR code.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.courses.resubmission', [$profile, $course]))
            ->assertSessionHasErrors('admin_notes');

        $this->assertSame(CourseAccessRequest::STATUS_PENDING, $accessRequest->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.courses.resubmission', [$profile, $course]), [
                'admin_notes' => 'Please upload the QR screenshot as course proof and explain what happened.',
            ])
            ->assertRedirect();

        $accessRequest->refresh();

        $this->assertSame(CourseAccessRequest::STATUS_REJECTED, $accessRequest->status);
        $this->assertSame('Please upload the QR screenshot as course proof and explain what happened.', $accessRequest->admin_notes);
        $this->assertSame($admin->id, $accessRequest->reviewed_by);

        $notification = $member->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('course_access_resubmission', $notification->data['category']);
        $this->assertSame('Course access needs resubmission', $notification->data['title']);
        $this->assertTrue($course->fresh()->accessRequestFor($member)->isRejected());
        $this->assertSame('Please upload the QR screenshot as course proof and explain what happened.', $course->fresh()->accessRequestFor($member)->admin_notes);
        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($member)
            ->get(route('member.courses.show', $course->slug))
            ->assertOk()
            ->assertSee('Please upload the QR screenshot as course proof and explain what happened.')
            ->assertSee('Admin note')
            ->assertSee('Course proof files')
            ->assertSee('Upload screenshots or proof Kayla requested for this specific course.')
            ->assertDontSee('Upload Platform Codes')
            ->assertSee('Resubmit Access Request');

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'Uploaded the QR screenshot as course proof.',
                'proof_files' => [
                    UploadedFile::fake()->create('qr-screenshot.png', 100, 'image/png'),
                ],
            ])
            ->assertRedirect(route('member.courses.show', $course->slug));

        $accessRequest->refresh();

        $this->assertSame(CourseAccessRequest::STATUS_PENDING, $accessRequest->status);
        $this->assertSame('Uploaded the QR screenshot as course proof.', $accessRequest->member_notes);
        $this->assertNull($accessRequest->admin_notes);
        $this->assertNull($accessRequest->reviewed_by);
        $this->assertSame(1, $accessRequest->proofFiles()->count());
    }

    public function test_admin_cannot_unlock_course_until_member_is_verified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'OnlyFans Verification Walkthrough',
            'slug' => 'onlyfans-verification-walkthrough',
            'platform_label' => 'OnlyFans',
            'description' => 'Platform-specific walkthrough.',
            'is_published' => true,
        ]);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.courses.unlock', [$profile, $course]))
            ->assertSessionHasErrors('profile');

        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_community_access_requires_assigned_role_for_models(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);

        $this->actingAs($member)
            ->get(route('community.show'))
            ->assertRedirect(route('member.dashboard'));

        $this->actingAs($member)
            ->getJson(route('community.channels.index'))
            ->assertForbidden();

        $profile->forceFill([
            'community_invited_at' => now(),
            'community_role_assigned_at' => now(),
        ])->save();

        $this->actingAs($member)
            ->getJson(route('community.channels.index'))
            ->assertForbidden();

        $profile->forceFill([
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
        ])->save();

        $this->actingAs($member)
            ->get(route('community.show'))
            ->assertOk();
    }

    private function fakePng(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function fakeLargePng(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, str_pad($png, (10 * 1024 * 1024) + 1, '0'));
    }
}
