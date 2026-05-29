<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseAccessRequest;
use App\Models\LessonProgress;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMemberProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_member_directory_without_default_selection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'Neljhan Redondo',
            'email' => 'neljhan@example.com',
            'role' => 'model',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.models.progress'))
            ->assertOk()
            ->assertSee('Member Progress')
            ->assertSee('All Members')
            ->assertSee('Search members')
            ->assertSee('Neljhan Redondo')
            ->assertDontSee('Selected Member')
            ->assertDontSee('Course Breakdown');
    }

    public function test_admin_can_view_selected_member_progress_from_the_directory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Neljhan Redondo',
            'email' => 'neljhan@example.com',
            'role' => 'model',
        ]);

        $course = Course::create([
            'title' => 'Chaturbate Blueprint',
            'slug' => 'chaturbate-blueprint',
            'platform_label' => 'Chaturbate',
            'is_published' => true,
        ]);

        $firstLesson = $course->lessons()->create([
            'title' => 'Profile Setup',
            'is_published' => true,
        ]);

        $course->lessons()->create([
            'title' => 'First Stream',
            'is_published' => true,
        ]);

        LessonProgress::create([
            'user_id' => $member->id,
            'lesson_id' => $firstLesson->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.models.progress', ['member' => $member->id]))
            ->assertOk()
            ->assertSee('Member Progress')
            ->assertSee('All Members')
            ->assertSee('Selected Member')
            ->assertSee('role="dialog"', false)
            ->assertSee('Close progress modal')
            ->assertSee('Search members')
            ->assertSee('Course Breakdown')
            ->assertSee('Neljhan Redondo')
            ->assertSee('Chaturbate Blueprint')
            ->assertSee('50%')
            ->assertSee('1 / 2')
            ->assertSeeInOrder(['All Members', 'Selected Member']);
    }

    public function test_admin_can_delete_model_member_from_progress_directory(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Test Model',
            'email' => 'model-to-delete@example.com',
            'role' => 'model',
            'profile_photo_path' => 'profile-photos/member.jpg',
        ]);

        Storage::disk('public')->put('profile-photos/member.jpg', 'avatar');

        $application = new ModelApplication([
            'name' => $member->name,
            'email' => $member->email,
            'phone' => '555-0100',
            'photo_paths' => ['applications/photos/member.jpg'],
        ]);
        $application->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $member->id,
        ])->save();

        Storage::disk('local')->put('applications/photos/member.jpg', 'application photo');
        Storage::disk('local')->put('verifications/'.$member->id.'/id.jpg', 'id');
        Storage::disk('local')->put('verifications/'.$member->id.'/selfie.jpg', 'selfie');
        Storage::disk('local')->put('verifications/'.$member->id.'/codes.jpg', 'codes');

        ModelProfile::create([
            'user_id' => $member->id,
            'model_application_id' => $application->id,
            'id_document_path' => 'verifications/'.$member->id.'/id.jpg',
            'selfie_with_id_path' => 'verifications/'.$member->id.'/selfie.jpg',
            'platform_codes_path' => 'verifications/'.$member->id.'/codes.jpg',
        ]);

        $course = Course::create([
            'title' => 'Stripchat Blueprint',
            'slug' => 'stripchat-blueprint',
            'platform_label' => 'Stripchat',
            'is_published' => true,
        ]);

        $lesson = $course->lessons()->create([
            'title' => 'Profile Setup',
            'is_published' => true,
        ]);

        LessonProgress::create([
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        $accessRequest = CourseAccessRequest::create([
            'course_id' => $course->id,
            'user_id' => $member->id,
            'status' => CourseAccessRequest::STATUS_PENDING,
            'member_notes' => 'Proof attached.',
        ]);

        $proofPath = 'course-access-proofs/'.$member->id.'/'.$course->id.'/proof.png';
        Storage::disk('local')->put($proofPath, 'proof');
        $accessRequest->proofFiles()->create([
            'disk' => 'local',
            'path' => $proofPath,
            'original_name' => 'proof.png',
            'mime_type' => 'image/png',
            'size' => 1234,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.models.destroy', $member), [
                'confirm_member_delete' => '1',
            ])
            ->assertRedirect(route('admin.models.progress'))
            ->assertSessionHas('status', 'Test Model has been deleted from the system.');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseMissing('model_profiles', ['user_id' => $member->id]);
        $this->assertDatabaseMissing('model_applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('lesson_progress', ['user_id' => $member->id]);
        $this->assertDatabaseMissing('course_access_requests', ['id' => $accessRequest->id]);
        $this->assertDatabaseMissing('course_access_request_files', ['course_access_request_id' => $accessRequest->id]);

        Storage::disk('public')->assertMissing('profile-photos/member.jpg');
        Storage::disk('local')->assertMissing('applications/photos/member.jpg');
        Storage::disk('local')->assertMissing('verifications/'.$member->id.'/id.jpg');
        Storage::disk('local')->assertMissing('verifications/'.$member->id.'/selfie.jpg');
        Storage::disk('local')->assertMissing('verifications/'.$member->id.'/codes.jpg');
        Storage::disk('local')->assertMissing($proofPath);
    }

    public function test_admin_cannot_delete_non_model_accounts_from_member_directory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('admin.models.destroy', $otherAdmin), [
                'confirm_member_delete' => '1',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_member_progress_directory_is_paginated_for_larger_model_lists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (range(1, 13) as $index) {
            User::factory()->create([
                'name' => 'Model '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'email' => 'model'.$index.'@example.com',
                'role' => 'model',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.models.progress'))
            ->assertOk()
            ->assertSee('Showing 1-12 of 13')
            ->assertSee('Page 1 of 2')
            ->assertSee('Model 01')
            ->assertDontSee('Model 13');

        $this->actingAs($admin)
            ->get(route('admin.models.progress', ['page' => 2]))
            ->assertOk()
            ->assertSee('Showing 13-13 of 13')
            ->assertSee('Page 2 of 2')
            ->assertSee('Model 13');
    }
}
