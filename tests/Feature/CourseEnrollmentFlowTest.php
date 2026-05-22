<?php

namespace Tests\Feature;

use App\Mail\CourseAccessRequestedMail;
use App\Models\Course;
use App\Models\CourseAccessRequest;
use App\Models\CourseAccessRequestFile;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseEnrollmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_must_be_unlocked_before_viewing_course(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Stripchat Pro Guide',
            'slug' => 'stripchat-pro-guide',
            'platform_label' => 'Stripchat',
            'description' => 'Master the platform.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Getting Started',
            'sort_order' => 1,
        ]);

        $this->actingAs($member)
            ->get(route('member.courses.show', $course->slug))
            ->assertOk()
            ->assertSee('Locked pending Kayla approval');

        $this->actingAs($member)
            ->get(route('member.courses.learn.show', $course->slug))
            ->assertRedirect(route('member.courses.show', $course->slug));

        $this->actingAs($member)
            ->post(route('member.courses.learn', $course->slug))
            ->assertRedirect(route('member.courses.show', $course->slug));

        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);

        $course->enrollments()->create([
            'user_id' => $member->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.learn', $course->slug))
            ->assertRedirect(route('member.courses.lessons.show', [$course->slug, $lesson]));

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.learn', $course->slug))
            ->assertRedirect(route('member.courses.lessons.show', [$course->slug, $lesson]));

        $this->assertSame(1, $course->enrollments()->where('user_id', $member->id)->count());
    }

    public function test_verified_member_can_request_course_access_and_admin_can_unlock_it(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Platform Callback Guide',
            'slug' => 'platform-callback-guide',
            'platform_label' => 'Stripchat',
            'description' => 'A platform walkthrough with approval requirements.',
            'course_access_requirements' => "Submit your platform QR code.\nFinish Kayla's callback.",
            'access_registration_instructions' => 'Register your platform account before the call.',
            'access_callback_instructions' => 'Kayla will call you and explain the QR code flow.',
            'access_onboarding_instructions' => 'Follow the setup checklist Kayla gives you.',
            'access_verification_instructions' => 'Send QR screenshots quickly because they can expire.',
            'is_published' => true,
        ]);

        $this->actingAs($member)
            ->get(route('member.courses.show', $course->slug))
            ->assertOk()
            ->assertSee('Website Verification Process')
            ->assertSee('Registration phase')
            ->assertSee('Register your platform account before the call.')
            ->assertSee('Callback phase')
            ->assertSee('Kayla will call you and explain the QR code flow.')
            ->assertSee('Course Access Review')
            ->assertSee('Review Requirements')
            ->assertSee('role="dialog"', false)
            ->assertSee('x-show="accessModalOpen"', false)
            ->assertSee('Access Requirements From Kayla')
            ->assertSee('Submit your platform QR code.')
            ->assertSee('Course proof files')
            ->assertSee('Upload screenshots or proof Kayla requested for this specific course.')
            ->assertDontSee('Upload Platform Codes')
            ->assertDontSee('member/verification', false)
            ->assertSee('Tell Kayla what QR/code verification steps you completed for this course.')
            ->assertSeeInOrder([
                'Website Verification Process',
                'Registration phase',
                'Callback phase',
                'Verification phase',
                'Access Requirements From Kayla',
                'Request Course Access',
            ])
            ->assertSee('Request Access');

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'I submitted the QR code after my callback.',
            ])
            ->assertRedirect(route('member.courses.show', $course->slug));

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'Adding one more detail while Kayla reviews.',
            ])
            ->assertRedirect(route('member.courses.show', $course->slug));

        $this->assertDatabaseHas('course_access_requests', [
            'course_id' => $course->id,
            'user_id' => $member->id,
            'status' => CourseAccessRequest::STATUS_PENDING,
            'member_notes' => 'Adding one more detail while Kayla reviews.',
        ]);
        $accessRequest = CourseAccessRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $member->id)
            ->firstOrFail();
        $reviewUrl = route('admin.onboarding.show', [
            'profile' => $profile,
            'course_request' => $accessRequest->id,
        ], false);
        $this->assertSame(1, $admin->notifications()->where('type', \App\Notifications\SystemNotification::class)->count());
        $adminNotification = $admin->notifications()->first();
        $this->assertNotNull($adminNotification);
        $this->assertSame('course_access_requested', $adminNotification->data['category']);
        $this->assertSame('Course access requested', $adminNotification->data['title']);
        $this->assertSame($reviewUrl, $adminNotification->data['action_url']);

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Course access requested');
        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
        Mail::assertQueued(CourseAccessRequestedMail::class, fn (CourseAccessRequestedMail $mail) => $mail->accessRequest->course_id === $course->id
            && $mail->accessRequest->user_id === $member->id);
        Mail::assertQueued(CourseAccessRequestedMail::class, 1);

        $this->actingAs($member)
            ->get(route('member.courses.show', $course->slug))
            ->assertOk()
            ->assertSee('Access request sent');

        $this->actingAs($admin)
            ->post(route('notifications.open', $adminNotification))
            ->assertRedirect($reviewUrl);

        $this->assertNotNull($adminNotification->fresh()->read_at);

        $this->actingAs($admin)
            ->get($reviewUrl)
            ->assertOk()
            ->assertSee('requestOpen: true', false)
            ->assertSee('Course Access Review');

        $admin->notify(new \App\Notifications\SystemNotification(
            title: 'Legacy course access requested',
            body: 'Old notification format.',
            actionUrl: route('admin.onboarding.index', ['model' => $member->id], false),
            category: 'course_access_requested',
        ));
        $legacyNotification = $admin->notifications()
            ->get()
            ->first(fn ($notification) => ($notification->data['title'] ?? null) === 'Legacy course access requested');
        $this->assertNotNull($legacyNotification);

        $this->actingAs($admin)
            ->post(route('notifications.open', $legacyNotification))
            ->assertRedirect($reviewUrl);

        $this->actingAs($admin)
            ->post(route('admin.onboarding.courses.unlock', [$profile, $course]))
            ->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('course_access_requests', [
            'course_id' => $course->id,
            'user_id' => $member->id,
            'status' => CourseAccessRequest::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
        ]);

        $memberNotification = $member->notifications()
            ->get()
            ->first(fn ($notification) => ($notification->data['category'] ?? null) === 'course_access_approved');
        $this->assertNotNull($memberNotification);
        $this->assertSame('Course access approved', $memberNotification->data['title']);
    }

    public function test_verified_member_can_submit_course_access_proof_files(): void
    {
        Storage::fake('local');
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'QR Proof Guide',
            'slug' => 'qr-proof-guide',
            'platform_label' => 'Stripchat',
            'description' => 'Upload course-specific proof.',
            'course_access_requirements' => 'Upload QR proof for this course.',
            'is_published' => true,
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'I completed the QR registration.',
                'proof_files' => [
                    UploadedFile::fake()->create('qr-proof.png', 100, 'image/png'),
                    UploadedFile::fake()->create('registration-proof.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('member.courses.show', $course->slug));

        $accessRequest = CourseAccessRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $member->id)
            ->firstOrFail();

        $this->assertDatabaseHas('course_access_requests', [
            'id' => $accessRequest->id,
            'status' => CourseAccessRequest::STATUS_PENDING,
            'member_notes' => 'I completed the QR registration.',
        ]);
        $this->assertSame(2, $accessRequest->proofFiles()->count());

        $proofFile = $accessRequest->proofFiles()->where('original_name', 'qr-proof.png')->firstOrFail();
        $this->assertStringStartsWith('course-access-proofs/'.$member->id.'/'.$course->id.'/', $proofFile->path);
        Storage::disk('local')->assertExists($proofFile->path);

        $this->actingAs($member)
            ->get(route('member.courses.show', $course->slug))
            ->assertOk()
            ->assertSee('Uploaded course proof')
            ->assertSee('qr-proof.png')
            ->assertSee('registration-proof.pdf');

        $this->actingAs($admin)
            ->get(route('admin.onboarding.show', $profile))
            ->assertOk()
            ->assertSee('Course proof files')
            ->assertSee('qr-proof.png')
            ->assertSee('registration-proof.pdf');

        $this->actingAs($admin)
            ->get(route('admin.onboarding.courses.proofs.view', [$profile, $course, $proofFile]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.onboarding.courses.proofs.show', [$profile, $course, $proofFile]))
            ->assertOk();

        Mail::assertQueued(CourseAccessRequestedMail::class, fn (CourseAccessRequestedMail $mail) => $mail->accessRequest->proofFiles->pluck('original_name')->contains('qr-proof.png'));
    }

    public function test_pending_course_access_request_can_receive_more_proof_files(): void
    {
        Storage::fake('local');
        Mail::fake();

        User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Pending Proof Guide',
            'slug' => 'pending-proof-guide',
            'platform_label' => 'General',
            'description' => 'Add more proof.',
            'is_published' => true,
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'Initial request.',
            ])
            ->assertRedirect(route('member.courses.show', $course->slug));

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'Adding screenshot.',
                'proof_files' => [
                    UploadedFile::fake()->create('extra-proof.jpg', 100, 'image/jpeg'),
                ],
            ])
            ->assertRedirect(route('member.courses.show', $course->slug));

        $accessRequest = CourseAccessRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $member->id)
            ->firstOrFail();

        $this->assertSame(CourseAccessRequest::STATUS_PENDING, $accessRequest->status);
        $this->assertSame('Adding screenshot.', $accessRequest->member_notes);
        $this->assertSame(1, $accessRequest->proofFiles()->count());
        Mail::assertQueued(CourseAccessRequestedMail::class, 2);
    }

    public function test_rejected_course_access_request_can_be_resubmitted_with_proof_files(): void
    {
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Resubmission Proof Guide',
            'slug' => 'resubmission-proof-guide',
            'platform_label' => 'General',
            'description' => 'Resubmit proof.',
            'is_published' => true,
        ]);
        CourseAccessRequest::create([
            'course_id' => $course->id,
            'user_id' => $member->id,
            'status' => CourseAccessRequest::STATUS_REJECTED,
            'member_notes' => 'Old note.',
            'admin_notes' => 'Upload the missing screenshot.',
            'reviewed_by' => User::factory()->create(['role' => 'admin'])->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'Uploaded the missing screenshot.',
                'proof_files' => [
                    UploadedFile::fake()->create('missing-screenshot.webp', 100, 'image/webp'),
                ],
            ])
            ->assertRedirect(route('member.courses.show', $course->slug));

        $accessRequest = CourseAccessRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $member->id)
            ->firstOrFail();

        $this->assertSame(CourseAccessRequest::STATUS_PENDING, $accessRequest->status);
        $this->assertSame('Uploaded the missing screenshot.', $accessRequest->member_notes);
        $this->assertNull($accessRequest->reviewed_by);
        $this->assertNull($accessRequest->reviewed_at);
        $this->assertNull($accessRequest->admin_notes);
        $this->assertSame(1, $accessRequest->proofFiles()->count());
    }

    public function test_course_access_proof_upload_rejects_invalid_files(): void
    {
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Invalid Proof Guide',
            'slug' => 'invalid-proof-guide',
            'platform_label' => 'General',
            'description' => 'Reject invalid proof.',
            'is_published' => true,
        ]);

        $this->actingAs($member)
            ->from(route('member.courses.show', $course->slug))
            ->post(route('member.courses.request-access', $course->slug), [
                'proof_files' => [
                    UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
                ],
            ])
            ->assertRedirect(route('member.courses.show', $course->slug))
            ->assertSessionHasErrors('proof_files.0');

        $this->assertDatabaseMissing('course_access_requests', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_course_access_proof_upload_rejects_oversized_files(): void
    {
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Large Proof Guide',
            'slug' => 'large-proof-guide',
            'platform_label' => 'General',
            'description' => 'Reject oversized proof.',
            'is_published' => true,
        ]);

        $this->actingAs($member)
            ->from(route('member.courses.show', $course->slug))
            ->post(route('member.courses.request-access', $course->slug), [
                'proof_files' => [
                    UploadedFile::fake()->create('large-proof.pdf', 10241, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('member.courses.show', $course->slug))
            ->assertSessionHasErrors('proof_files.0');

        $this->assertDatabaseMissing('course_access_requests', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_unverified_member_cannot_submit_course_access_proof_files(): void
    {
        Storage::fake('local');

        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Unverified Proof Guide',
            'slug' => 'unverified-proof-guide',
            'platform_label' => 'General',
            'description' => 'Block unverified proof.',
            'is_published' => true,
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.request-access', $course->slug), [
                'member_notes' => 'Here is my proof.',
                'proof_files' => [
                    UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('member.courses.show', $course->slug))
            ->assertSessionHas('status', 'Verification must be approved before Kayla can review course access.');

        $this->assertDatabaseMissing('course_access_requests', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseCount('course_access_request_files', 0);
    }

    public function test_course_access_proof_files_are_scoped_to_the_correct_profile_and_course(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $otherMember = User::factory()->create(['role' => 'model']);
        $profile = ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $otherProfile = ModelProfile::create([
            'user_id' => $otherMember->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/2/id.jpg',
            'selfie_with_id_path' => 'verifications/2/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Scoped Proof Guide',
            'slug' => 'scoped-proof-guide',
            'platform_label' => 'General',
            'description' => 'Scoped proof.',
            'is_published' => true,
        ]);
        $otherCourse = Course::create([
            'title' => 'Other Proof Guide',
            'slug' => 'other-proof-guide',
            'platform_label' => 'General',
            'description' => 'Other proof.',
            'is_published' => true,
        ]);
        $accessRequest = CourseAccessRequest::create([
            'course_id' => $course->id,
            'user_id' => $member->id,
            'status' => CourseAccessRequest::STATUS_PENDING,
        ]);
        Storage::disk('local')->put('course-access-proofs/'.$member->id.'/'.$course->id.'/proof.png', 'proof');
        $proofFile = $accessRequest->proofFiles()->create([
            'disk' => 'local',
            'path' => 'course-access-proofs/'.$member->id.'/'.$course->id.'/proof.png',
            'original_name' => 'proof.png',
            'mime_type' => 'image/png',
            'size' => 5,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.courses.proofs.view', [$profile, $course, $proofFile]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.onboarding.courses.proofs.view', [$otherProfile, $course, $proofFile]))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.onboarding.courses.proofs.view', [$profile, $otherCourse, $proofFile]))
            ->assertNotFound();

        $this->actingAs($otherMember)
            ->get(route('admin.onboarding.courses.proofs.view', [$profile, $course, $proofFile]))
            ->assertForbidden();
    }

    public function test_only_enrolled_members_can_use_course_chat_and_progress(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'OnlyFans Blueprint',
            'slug' => 'onlyfans-blueprint',
            'platform_label' => 'OnlyFans',
            'description' => 'A full platform walkthrough.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Getting Started',
            'sort_order' => 1,
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.chat.store', $course->slug), [
                'body' => 'Hello community',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('member.lessons.progress', $lesson), [
                'completed' => '1',
            ])
            ->assertForbidden();

        $course->enrollments()->create([
            'user_id' => $member->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('member.courses.chat.store', $course->slug), [
                'body' => 'Hello community',
            ])
            ->assertRedirect();

        $this->actingAs($member)
            ->patch(route('member.lessons.progress', $lesson), [
                'completed' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('course_chat_messages', [
            'course_id' => $course->id,
            'user_id' => $member->id,
            'body' => 'Hello community',
        ]);
        $this->assertDatabaseHas('lesson_progress', [
            'lesson_id' => $lesson->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_unverified_enrolled_member_cannot_access_course_learning_routes(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $member->id,
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/1/id.jpg',
            'selfie_with_id_path' => 'verifications/1/selfie.jpg',
        ]);
        $course = Course::create([
            'title' => 'Locked Verification Guide',
            'slug' => 'locked-verification-guide',
            'platform_label' => 'General',
            'description' => 'A course that still needs global verification.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Private Lesson',
            'sort_order' => 1,
        ]);
        $course->enrollments()->create([
            'user_id' => $member->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertRedirect(route('member.courses.show', $course->slug));

        $this->actingAs($member)
            ->patch(route('member.lessons.progress', $lesson), [
                'completed' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('member.courses.chat.store', $course->slug), [
                'body' => 'Should not post.',
            ])
            ->assertForbidden();
    }

    public function test_lesson_resources_render_without_pdf_or_video_placeholder(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Resource Guide',
            'slug' => 'resource-guide',
            'platform_label' => 'General',
            'description' => 'A reading lesson with links.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Reading Lesson',
            'overview' => 'Read this lesson carefully.',
            'resource_links' => "Canva Template | https://www.canva.com/design/example\nhttps://example.com/safety-guide",
            'presentation_url' => 'https://www.canva.com/design/presentation/view?embed',
            'sort_order' => 1,
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('Canva Template')
            ->assertSee('https://www.canva.com/design/example', false)
            ->assertSee('https://example.com/safety-guide', false)
            ->assertDontSee('presentation-wrapper', false)
            ->assertSee('https://www.canva.com/design/presentation/view?embed', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertDontSee('Video will appear here');
    }

    public function test_course_outline_and_intro_render_before_lessons_in_learning_flow(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('academy/course-outlines/outline.pdf', "%PDF-1.4\n");

        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Guided Start Course',
            'slug' => 'guided-start-course',
            'platform_label' => 'General',
            'description' => 'A course with pre-lesson materials.',
            'has_course_outline' => true,
            'course_outline_url' => 'academy/course-outlines/outline.pdf',
            'has_intro' => true,
            'intro_title' => 'Course Orientation',
            'intro_body' => 'Welcome before lessons.',
            'intro_bunny_video_id' => 'intro-video',
            'intro_bunny_library_id' => '654926',
            'is_published' => true,
        ]);
        $module = $course->modules()->create([
            'title' => 'Module 1',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $lesson = $course->lessons()->create([
            'course_module_id' => $module->id,
            'title' => 'First Lesson',
            'overview' => 'The first normal lesson.',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)
            ->post(route('member.courses.learn', $course->slug))
            ->assertRedirect(route('member.courses.learn.show', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.learn.show', $course->slug))
            ->assertOk()
            ->assertSee('Course Outline / PDF Guide')
            ->assertSee('Open Guide')
            ->assertSee('Course Orientation')
            ->assertSee('Module 1')
            ->assertDontSee('Mark Complete');

        $this->actingAs($member)
            ->get(route('member.courses.outline', $course->slug))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($member)
            ->get(route('member.courses.learn.show', [$course->slug, 'item' => 'intro']))
            ->assertOk()
            ->assertSee('Course Orientation')
            ->assertSee('Welcome before lessons.')
            ->assertSee('https://iframe.mediadelivery.net/embed/654926/intro-video', false)
            ->assertDontSee('Mark Complete');

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSeeInOrder([
                'Start Here',
                'Course Outline',
                'Course Orientation',
                'Module 1',
                'First Lesson',
            ]);
    }

    public function test_learning_pages_hide_main_member_sidebar(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Focused Learning Guide',
            'slug' => 'focused-learning-guide',
            'platform_label' => 'General',
            'description' => 'A focused course learning mode.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Focused Lesson',
            'overview' => 'Use the learning mode.',
            'sort_order' => 1,
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)
            ->get(route('member.courses.show', $course->slug))
            ->assertOk()
            ->assertSee('data-member-sidebar="main"', false);

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertDontSee('data-member-sidebar="main"', false)
            ->assertSee('Course Outline')
            ->assertSee('Focused Lesson')
            ->assertSee('elysian-topbar--course', false);
    }

    public function test_lesson_content_blocks_render_in_admin_order(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Content Block Guide',
            'slug' => 'content-block-guide',
            'platform_label' => 'General',
            'description' => 'A lesson built from ordered blocks.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Flexible Lesson',
            'overview' => 'Legacy overview should not render when blocks exist.',
            'resource_links' => 'Support Link | https://example.com/support',
            'lesson_banner_image' => 'https://example.com/banner.jpg',
            'sort_order' => 1,
        ]);
        $lesson->contentBlocks()->createMany([
            [
                'block_type' => 'heading',
                'title' => 'Opening Context',
                'content' => 'This heading starts the flow.',
                'sort_order' => 1,
            ],
            [
                'block_type' => 'text',
                'title' => 'Reading Context',
                'content' => 'This text starts the lesson.',
                'sort_order' => 2,
            ],
            [
                'block_type' => 'image',
                'title' => 'Example Screenshot',
                'image_path' => 'https://example.com/screenshot.jpg',
                'sort_order' => 3,
            ],
            [
                'block_type' => 'steps',
                'title' => 'Step Sequence',
                'content' => "Do the first action.\nDo the second action.",
                'sort_order' => 4,
            ],
            [
                'block_type' => 'video',
                'title' => 'Watch Walkthrough',
                'bunny_video_id' => 'video-abc',
                'bunny_library_id' => 'library-def',
                'sort_order' => 5,
            ],
            [
                'block_type' => 'tips',
                'title' => 'Premium Tip',
                'content' => 'Keep this in mind.',
                'sort_order' => 6,
            ],
            [
                'block_type' => 'canva',
                'title' => 'Lesson Slides',
                'presentation_url' => 'https://www.canva.com/design/blockdeck/view?embed',
                'sort_order' => 7,
            ],
            [
                'block_type' => 'pdf_resource',
                'title' => 'Download Checklist',
                'file_path' => 'https://example.com/checklist.pdf',
                'sort_order' => 8,
            ],
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSeeInOrder([
                'Opening Context',
                'Reading Context',
                'Example Screenshot',
                'Step Sequence',
                'Watch Walkthrough',
                'Premium Tip',
                'Lesson Slides',
                'Download Checklist',
            ])
            ->assertSee('https://iframe.mediadelivery.net/embed/library-def/video-abc', false)
            ->assertSee('https://www.canva.com/design/blockdeck/view?embed', false)
            ->assertSee('https://example.com/checklist.pdf', false)
            ->assertSee('https://example.com/banner.jpg', false)
            ->assertDontSee('Support Link')
            ->assertDontSee('Legacy overview should not render when blocks exist.');
    }

    public function test_empty_draft_content_blocks_still_suppress_legacy_member_layout(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Draft Member Guide',
            'slug' => 'draft-member-guide',
            'platform_label' => 'General',
            'description' => 'A lesson with a draft-only block.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Draft Lesson',
            'overview' => 'This fallback overview should still show.',
            'steps' => 'Legacy step still works.',
            'sort_order' => 1,
        ]);
        $lesson->contentBlocks()->create([
            'block_type' => 'text',
            'sort_order' => 1,
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertDontSee('This fallback overview should still show.')
            ->assertDontSee('Legacy step still works.');
    }

    public function test_canva_share_link_uses_open_button_without_guessing_embed_url(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Canva Share Guide',
            'slug' => 'canva-share-guide',
            'platform_label' => 'General',
            'description' => 'A reading lesson with a Canva share link.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Canva Share Link',
            'overview' => 'Open the presentation if Canva blocks embedding.',
            'presentation_url' => 'https://www.canva.com/design/sharelink/view',
            'sort_order' => 1,
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('Lesson Slides')
            ->assertSee('>Open</a>', false)
            ->assertSee('https://www.canva.com/design/sharelink/view', false)
            ->assertDontSee('presentation-wrapper', false);
    }

    public function test_canva_smart_embed_link_uses_open_button_without_iframe(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Canva Smart Embed Guide',
            'slug' => 'canva-smart-embed-guide',
            'platform_label' => 'General',
            'description' => 'A lesson with a Canva smart embed link.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'Canva Smart Embed',
            'overview' => 'View the embedded presentation.',
            'presentation_url' => 'https://canva.link/example-presentation',
            'sort_order' => 1,
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('>Open</a>', false)
            ->assertSee('https://canva.link/example-presentation', false)
            ->assertDontSee('presentation-wrapper', false)
            ->assertDontSee('x-on:error="presentationBlocked = true"', false);
    }

    public function test_non_canva_presentation_url_uses_open_button_without_iframe(): void
    {
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'External Deck Guide',
            'slug' => 'external-deck-guide',
            'platform_label' => 'General',
            'description' => 'A reading lesson with an external deck.',
            'is_published' => true,
        ]);
        $lesson = $course->lessons()->create([
            'title' => 'External Presentation',
            'overview' => 'Open the linked deck.',
            'presentation_url' => 'https://example.com/deck',
            'sort_order' => 1,
        ]);

        $this->unlockCourseFor($member, $course);

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('>Open</a>', false)
            ->assertSee('https://example.com/deck', false)
            ->assertDontSee('presentation-wrapper', false);
    }

    private function unlockCourseFor(User $member, Course $course): void
    {
        ModelProfile::query()->updateOrCreate([
            'user_id' => $member->id,
        ], [
            'information_submitted_at' => now(),
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_submitted_at' => now(),
            'id_document_path' => 'verifications/'.$member->id.'/id.jpg',
            'selfie_with_id_path' => 'verifications/'.$member->id.'/selfie.jpg',
        ]);

        $course->enrollments()->firstOrCreate(
            ['user_id' => $member->id],
            ['enrolled_at' => now()]
        );
    }
}
