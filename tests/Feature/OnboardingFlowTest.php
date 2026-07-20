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
use App\Models\EmailCampaign;
use App\Models\EmailCampaignDelivery;
use App\Models\EmailCampaignRun;
use App\Models\Lesson;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ModelReferral;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\EmailCampaignDispatcher;
use App\Support\OnboardingFormDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

    public function test_applications_page_only_marks_applications_as_fully_onboarded_after_completion_or_manual_override(): void
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
            'name' => 'Status Model',
            'email' => 'status@example.com',
            'experience_level' => 'none',
            'age_confirmed' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertDontSee('Fully Onboarded');

        $application->profile()->first()->forceFill([
            'manual_fully_onboarded_at' => now(),
            'manual_fully_onboarded_by' => $admin->id,
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Fully Onboarded');
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

    public function test_admin_can_update_pending_application_email_before_approval(): void
    {
        Mail::fake();
        $this->configureDeliverableMailer();

        $admin = User::factory()->create(['role' => 'admin']);
        $referrer = User::factory()->create(['role' => 'model']);
        $application = ModelApplication::create([
            'name' => 'Email Fix Model',
            'email' => 'wrong@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);
        $referral = ModelReferral::create([
            'referrer_id' => $referrer->id,
            'model_application_id' => $application->id,
            'candidate_name' => 'Email Fix Model',
            'candidate_email' => 'wrong@example.com',
            'experience_level' => 'beginner',
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.applications.email.update', $application), [
                'email' => 'Corrected@Example.COM',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Application email updated to corrected@example.com.');

        $this->assertSame('corrected@example.com', $application->fresh()->email);
        $this->assertSame('corrected@example.com', $referral->fresh()->candidate_email);

        $this->actingAs($admin)
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect();

        $member = User::where('email', 'corrected@example.com')->first();

        $this->assertNotNull($member);
        $this->assertSame($member->id, $application->fresh()->user_id);
        Mail::assertSent(MemberApplicationApprovedMail::class, fn (MemberApplicationApprovedMail $mail) => $mail->hasTo('corrected@example.com'));
    }

    public function test_admin_can_update_approved_application_email_and_resend_when_member_has_not_started(): void
    {
        Mail::fake();
        $this->configureDeliverableMailer();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Approved Email Fix',
            'email' => 'old-approved@example.com',
            'role' => 'model',
            'password' => Hash::make('old-password'),
            'email_verified_at' => null,
            'last_login_at' => null,
        ]);
        $application = ModelApplication::create([
            'name' => 'Approved Email Fix',
            'email' => 'old-approved@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        $secondaryApplication = ModelApplication::create([
            'name' => 'Approved Email Fix Follow-up',
            'email' => 'old-approved@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);
        $secondaryApplication->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
        ]);
        DB::table('sessions')->insert([
            'id' => 'approved-member-session',
            'user_id' => $member->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);
        DB::table('password_reset_tokens')->insert([
            'email' => 'old-approved@example.com',
            'token' => 'token',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.applications.email.update', $application), [
                'email' => 'new-approved@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Application email updated to new-approved@example.com and the approval email was resent with a fresh temporary password.');

        $member->refresh();

        $this->assertSame('new-approved@example.com', $member->email);
        $this->assertSame('new-approved@example.com', $application->fresh()->email);
        $this->assertSame('new-approved@example.com', $secondaryApplication->fresh()->email);
        $this->assertNotNull($member->email_verified_at);
        $this->assertDatabaseMissing('sessions', ['id' => 'approved-member-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'old-approved@example.com']);
        Mail::assertSent(MemberApplicationApprovedMail::class, function (MemberApplicationApprovedMail $mail) use ($member) {
            return $mail->hasTo('new-approved@example.com')
                && Hash::check($mail->temporaryPassword, $member->fresh()->password);
        });
    }

    public function test_admin_updates_approved_application_email_without_resend_after_member_started(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Started Model',
            'email' => 'started-old@example.com',
            'role' => 'model',
            'last_login_at' => now(),
        ]);
        $application = ModelApplication::create([
            'name' => 'Started Model',
            'email' => 'started-old@example.com',
            'experience_level' => 'beginner',
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
            ->patch(route('admin.applications.email.update', $application), [
                'email' => 'started-new@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertSame('started-new@example.com', $member->fresh()->email);
        $this->assertSame('started-new@example.com', $application->fresh()->email);
        Mail::assertNothingSent();
    }

    public function test_admin_cannot_update_application_email_to_duplicate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'email' => 'taken@example.com',
            'role' => 'model',
        ]);
        $application = ModelApplication::create([
            'name' => 'Duplicate Email Model',
            'email' => 'unique@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.applications.email.update', $application), [
                'email' => 'taken@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame('unique@example.com', $application->fresh()->email);
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
            ->assertSee(route('admin.applications.destroy', $application), false)
            ->assertSee('Delete Application')
            ->assertSee('Delete application?')
            ->assertDontSee('return confirm', false);

        $this->actingAs($admin)
            ->delete(route('admin.applications.destroy', $application))
            ->assertRedirect()
            ->assertSessionHas('status', 'Application deleted.');

        $this->assertDatabaseMissing('model_applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('model_referrals', ['id' => $referral->id]);
        Storage::disk('local')->assertMissing('applications/photos/rejected.jpg');
    }

    public function test_admin_can_delete_pending_application_and_uploaded_photos(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $application = ModelApplication::create([
            'name' => 'Pending Model',
            'email' => 'pending@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
            'photo_paths' => ['applications/photos/pending.jpg'],
        ]);

        Storage::disk('local')->put('applications/photos/pending.jpg', 'photo');

        $this->actingAs($admin)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee(route('admin.applications.destroy', $application), false);

        $this->actingAs($admin)
            ->delete(route('admin.applications.destroy', $application))
            ->assertRedirect()
            ->assertSessionHas('status', 'Application deleted.');

        $this->assertDatabaseMissing('model_applications', ['id' => $application->id]);
        Storage::disk('local')->assertMissing('applications/photos/pending.jpg');
    }

    public function test_admin_can_delete_approved_application_and_linked_member_records(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Approved Model',
            'email' => 'approved-delete@example.com',
            'role' => 'model',
            'profile_photo_path' => 'profile-photos/approved-model.jpg',
        ]);
        $referrer = User::factory()->create(['role' => 'model']);
        $application = ModelApplication::create([
            'name' => 'Approved Model',
            'email' => 'approved-delete@example.com',
            'experience_level' => 'beginner',
            'age_confirmed' => true,
            'photo_paths' => ['applications/photos/approved.jpg'],
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
            'id_document_path' => 'verifications/'.$member->id.'/id.jpg',
            'selfie_with_id_path' => 'verifications/'.$member->id.'/selfie.jpg',
        ]);
        $referral = ModelReferral::create([
            'referrer_id' => $referrer->id,
            'model_application_id' => $application->id,
            'candidate_name' => 'Approved Model',
            'candidate_email' => 'approved-delete@example.com',
            'experience_level' => 'beginner',
            'photo_paths' => ['applications/photos/referral-approved.jpg'],
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
            'status' => ModelReferral::STATUS_JOINED,
            'reward_status' => ModelReferral::REWARD_ELIGIBLE,
        ]);
        $testimonial = Testimonial::create([
            'submitted_by' => $member->id,
            'name' => 'Approved Model',
            'headline' => 'Real story',
            'quote' => 'Paradise Dolls helped me build confidence.',
            'image_path' => 'testimonials/approved-model.jpg',
        ]);
        $course = Course::create([
            'title' => 'Approved Course',
            'slug' => 'approved-course',
            'is_published' => true,
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'First Lesson',
            'is_published' => true,
        ]);
        $accessRequest = CourseAccessRequest::create([
            'course_id' => $course->id,
            'user_id' => $member->id,
            'status' => CourseAccessRequest::STATUS_PENDING,
        ]);
        $accessRequest->proofFiles()->create([
            'disk' => 'local',
            'path' => 'course-access-proofs/'.$member->id.'/proof.jpg',
            'original_name' => 'proof.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 10,
        ]);
        $campaign = EmailCampaign::create([
            'created_by' => $admin->id,
            'name' => 'Welcome campaign',
            'subject' => 'Welcome',
            'body' => 'Hello',
        ]);
        $run = EmailCampaignRun::create([
            'email_campaign_id' => $campaign->id,
            'subject' => 'Welcome',
            'body' => 'Hello',
            'started_at' => now(),
        ]);
        $delivery = EmailCampaignDelivery::create([
            'email_campaign_run_id' => $run->id,
            'user_id' => $member->id,
            'recipient_name' => $member->name,
            'email' => $member->email,
            'status' => EmailCampaignDelivery::STATUS_SENT,
            'sent_at' => now(),
        ]);
        DB::table('lesson_progress')->insert([
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('course_enrollments')->insert([
            'course_id' => $course->id,
            'user_id' => $member->id,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sessions')->insert([
            'id' => 'approved-delete-session',
            'user_id' => $member->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);
        DB::table('password_reset_tokens')->insert([
            'email' => 'approved-delete@example.com',
            'token' => 'token',
            'created_at' => now(),
        ]);

        Storage::disk('public')->put('profile-photos/approved-model.jpg', 'photo');
        Storage::disk('public')->put('testimonials/approved-model.jpg', 'photo');
        Storage::disk('local')->put('applications/photos/approved.jpg', 'photo');
        Storage::disk('local')->put('applications/photos/referral-approved.jpg', 'photo');
        Storage::disk('local')->put('verifications/'.$member->id.'/id.jpg', 'id');
        Storage::disk('local')->put('verifications/'.$member->id.'/selfie.jpg', 'selfie');
        Storage::disk('local')->put('course-access-proofs/'.$member->id.'/proof.jpg', 'proof');

        $this->actingAs($admin)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee(route('admin.applications.destroy', $application), false)
            ->assertSee('linked member account');

        $this->actingAs($admin)
            ->delete(route('admin.applications.destroy', $application))
            ->assertRedirect()
            ->assertSessionHas('status', 'Approved Model and all linked model records have been deleted.');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseMissing('model_applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('model_profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('model_referrals', ['id' => $referral->id]);
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
        $this->assertDatabaseMissing('course_access_requests', ['id' => $accessRequest->id]);
        $this->assertDatabaseMissing('lesson_progress', ['user_id' => $member->id]);
        $this->assertDatabaseMissing('course_enrollments', ['user_id' => $member->id]);
        $this->assertDatabaseMissing('email_campaign_deliveries', ['id' => $delivery->id]);
        $this->assertDatabaseMissing('sessions', ['id' => 'approved-delete-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'approved-delete@example.com']);
        Storage::disk('public')->assertMissing('profile-photos/approved-model.jpg');
        Storage::disk('public')->assertMissing('testimonials/approved-model.jpg');
        Storage::disk('local')->assertMissing('applications/photos/approved.jpg');
        Storage::disk('local')->assertMissing('applications/photos/referral-approved.jpg');
        Storage::disk('local')->assertMissing('verifications/'.$member->id.'/id.jpg');
        Storage::disk('local')->assertMissing('verifications/'.$member->id.'/selfie.jpg');
        Storage::disk('local')->assertMissing('course-access-proofs/'.$member->id.'/proof.jpg');
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
            ->assertSee(route('admin.models.destroy', $member), false)
            ->assertSee('confirm-onboarding-member-deletion')
            ->assertSeeText('Delete member account?')
            ->assertDontSee('return confirm(', false);
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

    public function test_admin_onboarding_page_exposes_inline_form_editor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index'))
            ->assertOk()
            ->assertSee('Edit Onboarding Form')
            ->assertSee('Custom Questions')
            ->assertSee(route('admin.onboarding.form.update'), false)
            ->assertDontSee('/admin/onboarding-form');
    }

    public function test_admin_can_update_onboarding_options_and_member_form_uses_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($admin)
            ->put(route('admin.onboarding.form.update'), [
                'form' => [
                    'option_groups' => [
                        'platforms_cam' => [
                            'label' => 'Cam Sites',
                            'help' => 'Choose every cam platform that applies.',
                            'options' => "CAM4\nDreamCam",
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.onboarding.index'));

        $definition = OnboardingFormDefinition::get();

        $this->assertContains('DreamCam', $definition['option_groups']['platforms_cam']['options']);
        $this->assertContains('AdultWork', $definition['option_groups']['platforms_cam']['archived']);

        $this->actingAs($member)
            ->get(route('member.onboarding.edit'))
            ->assertOk()
            ->assertSee('DreamCam')
            ->assertSee('Choose every cam platform that applies.')
            ->assertDontSee('AdultWork');
    }

    public function test_required_custom_onboarding_question_is_validated_and_saved(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);

        $this->actingAs($admin)
            ->put(route('admin.onboarding.form.update'), [
                'form' => [
                    'custom_fields' => [
                        [
                            'label' => 'Preferred content style',
                            'type' => 'text',
                            'help' => 'Tell us what style feels natural.',
                            'required' => '1',
                            'archived' => '0',
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.onboarding.index'));

        $this->actingAs($member)
            ->get(route('member.onboarding.edit'))
            ->assertOk()
            ->assertSee('Preferred content style')
            ->assertSee('Tell us what style feels natural.');

        $this->actingAs($member)
            ->put(route('member.onboarding.update'), $this->validOnboardingPayload())
            ->assertSessionHasErrors('custom_onboarding.custom_preferred_content_style');

        $this->actingAs($member)
            ->put(route('member.onboarding.update'), $this->validOnboardingPayload([
                'custom_onboarding' => [
                    'custom_preferred_content_style' => 'Soft glam and lifestyle.',
                ],
            ]))
            ->assertRedirect(route('member.verification.edit'));

        $profile = $member->modelProfile()->first();

        $this->assertSame('Soft glam and lifestyle.', $profile->custom_onboarding_answers['custom_preferred_content_style'] ?? null);
        $this->assertSame(OnboardingFormDefinition::get()['version'], $profile->onboarding_form_version);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSee('Preferred content style')
            ->assertSee('Soft glam and lifestyle.');
    }

    public function test_archived_custom_fields_hide_from_member_form_but_old_answers_remain_visible(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'custom_onboarding_answers' => [
                'custom_legacy_question' => 'Legacy answer',
            ],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.onboarding.form.update'), [
                'form' => [
                    'custom_fields' => [
                        [
                            'id' => 'custom_legacy_question',
                            'label' => 'Legacy question',
                            'type' => 'text',
                            'help' => '',
                            'required' => '0',
                            'archived' => '1',
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.onboarding.index'));

        $this->actingAs($member)
            ->get(route('member.onboarding.edit'))
            ->assertOk()
            ->assertDontSee('Legacy question');

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSee('Legacy question')
            ->assertSee('Legacy answer')
            ->assertSee('Archived');
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

    public function test_admin_can_manually_mark_and_unmark_model_as_fully_onboarded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'verification_status' => ModelProfile::VERIFICATION_NOT_REQUESTED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.fully-onboarded', $profile), [
                'manual_fully_onboarded_note' => 'Completed through WhatsApp.',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertTrue($profile->isManuallyFullyOnboarded());
        $this->assertTrue($profile->isFullyOnboarded());
        $this->assertSame(100, $profile->onboardingPercent());
        $this->assertSame($admin->id, $profile->manual_fully_onboarded_by);
        $this->assertSame('Completed through WhatsApp.', $profile->manual_fully_onboarded_note);
        $this->assertFalse($profile->hasCommunityChatAccess());

        $this->actingAs($admin)
            ->delete(route('admin.onboarding.fully-onboarded.remove', $profile))
            ->assertRedirect();

        $profile->refresh();

        $this->assertFalse($profile->isManuallyFullyOnboarded());
        $this->assertFalse($profile->isFullyOnboarded());
    }

    public function test_onboarding_list_has_quick_manual_fully_onboarded_action_until_model_is_fully_onboarded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index'))
            ->assertOk()
            ->assertSee('Mark Fully Onboarded')
            ->assertSee('Mark model as fully onboarded?')
            ->assertSee('This will mark the model as fully onboarded for admin lists and email campaign audiences.')
            ->assertSee('Marked as fully onboarded from the onboarding list.');

        $this->actingAs($admin)
            ->post(route('admin.onboarding.fully-onboarded', $profile))
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index'))
            ->assertOk()
            ->assertSee('Fully Onboarded')
            ->assertDontSee('Marked as fully onboarded from the onboarding list.');
    }

    public function test_admin_can_sort_onboarding_list_by_fully_onboarded_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fullyOnboarded = User::factory()->create([
            'role' => 'model',
            'name' => 'Fully Sorted',
            'email' => 'fully-sorted@example.com',
        ]);
        ModelProfile::create([
            'user_id' => $fullyOnboarded->id,
            'manual_fully_onboarded_at' => now(),
        ]);

        $notOnboarded = User::factory()->create([
            'role' => 'model',
            'name' => 'Pending Sorted',
            'email' => 'pending-sorted@example.com',
        ]);
        ModelProfile::create(['user_id' => $notOnboarded->id]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index', ['sort' => 'fully_onboarded']))
            ->assertOk()
            ->assertSee('Fully Onboarded')
            ->assertSee('Not Onboarded')
            ->assertSee('Fully Sorted')
            ->assertDontSee('Pending Sorted');

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index', ['sort' => 'not_onboarded']))
            ->assertOk()
            ->assertSee('Pending Sorted')
            ->assertDontSee('Fully Sorted');
    }

    public function test_email_campaign_audiences_include_manual_fully_onboarded_models(): void
    {
        $manuallyOnboarded = User::factory()->create([
            'role' => 'model',
            'email' => 'manual@example.com',
            'email_verified_at' => now(),
        ]);
        ModelProfile::create([
            'user_id' => $manuallyOnboarded->id,
            'manual_fully_onboarded_at' => now(),
        ]);

        $roleAssigned = User::factory()->create([
            'role' => 'model',
            'email' => 'role@example.com',
            'email_verified_at' => now(),
        ]);
        ModelProfile::create([
            'user_id' => $roleAssigned->id,
            'community_role_assigned_at' => now(),
        ]);

        $notOnboarded = User::factory()->create([
            'role' => 'model',
            'email' => 'waiting@example.com',
            'email_verified_at' => now(),
        ]);
        ModelProfile::create(['user_id' => $notOnboarded->id]);

        $dispatcher = new EmailCampaignDispatcher();

        $onboardedCampaign = EmailCampaign::create([
            'name' => 'Motivation',
            'subject' => 'Welcome',
            'body' => 'Hi {name}',
            'audience' => EmailCampaign::AUDIENCE_ONBOARDED_MODELS,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $notOnboardedCampaign = EmailCampaign::create([
            'name' => 'Promo',
            'subject' => 'Next step',
            'body' => 'Hi {name}',
            'audience' => EmailCampaign::AUDIENCE_NOT_ONBOARDED_MODELS,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $this->assertSame(
            ['manual@example.com', 'role@example.com'],
            $dispatcher->recipientQuery($onboardedCampaign)->pluck('email')->all()
        );

        $this->assertSame(
            ['waiting@example.com'],
            $dispatcher->recipientQuery($notOnboardedCampaign)->pluck('email')->all()
        );
    }

    private function configureDeliverableMailer(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.username' => 'sender@example.com',
            'mail.mailers.smtp.password' => 'test-password',
            'mail.from.address' => 'sender@example.com',
        ]);
    }

    private function validOnboardingPayload(array $overrides = []): array
    {
        return array_replace_recursive([
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
        ], $overrides);
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
