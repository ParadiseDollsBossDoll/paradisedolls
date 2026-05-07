<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\ChatRoom;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_course_with_zip_style_lesson_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Stripchat Pro Guide',
            'slug' => 'stripchat-pro-guide',
            'platform_label' => 'Stripchat',
            'platform_color' => '#FF3E4D',
            'short_description' => 'A premium onboarding path for new members.',
            'description' => 'Master the platform from setup to private shows.',
            'has_course_outline' => '1',
            'course_outline_url' => 'https://example.com/outline.pdf',
            'has_intro' => '1',
            'intro_title' => 'Course Orientation',
            'intro_video_url' => 'https://www.youtube.com/embed/intro',
            'intro_duration' => '04:30',
            'intro_body' => 'Start here before Lesson 1.',
            'is_published' => '1',
            'sort_order' => 1,
            'modules' => [
                [
                    'client_key' => 'module-1',
                    'title' => 'Getting Started',
                    'description' => 'Set up the foundation before the walkthrough.',
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
            'lessons' => [
                [
                    'module_key' => 'module-1',
                    'title' => 'Getting Started',
                    'body' => 'Account registration and verification.',
                    'overview' => 'Set up the account safely.',
                    'steps' => "Create the account\nVerify profile",
                    'tips' => 'Keep login details ready.',
                    'safety_notes' => 'Use approved links only.',
                    'resource_links' => 'Guide | https://example.com/resource',
                    'is_published' => '1',
                    'video_url' => 'https://www.youtube.com/embed/demo',
                    'duration' => '12:10',
                    'pdf_url' => 'https://example.com/guide.pdf',
                    'presentation_url' => 'https://www.canva.com/design/example',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $course = Course::with(['chatRoom', 'modules', 'lessons.module'])->where('slug', 'stripchat-pro-guide')->first();

        $this->assertNotNull($course);
        $this->assertInstanceOf(ChatRoom::class, $course->chatRoom);
        $this->assertSame($course->id, $course->chatRoom->course_id);
        $this->assertTrue($course->is_published);
        $this->assertSame('#FF3E4D', $course->platform_color);
        $this->assertSame('A premium onboarding path for new members.', $course->short_description);
        $this->assertTrue($course->has_course_outline);
        $this->assertSame('https://example.com/outline.pdf', $course->course_outline_url);
        $this->assertTrue($course->has_intro);
        $this->assertSame('Course Orientation', $course->intro_title);
        $this->assertSame('https://www.youtube.com/embed/intro', $course->intro_video_url);
        $this->assertSame('12:10', $course->lessons->first()->duration);
        $this->assertSame('https://example.com/guide.pdf', $course->lessons->first()->pdf_url);
        $this->assertSame('https://www.canva.com/design/example', $course->lessons->first()->presentation_url);
        $this->assertCount(1, $course->modules);
        $this->assertSame('Set up the foundation before the walkthrough.', $course->modules->first()->description);
        $this->assertSame('Getting Started', $course->lessons->first()->module->title);
        $this->assertSame('Set up the account safely.', $course->lessons->first()->overview);
        $this->assertTrue($course->lessons->first()->is_published);
    }

    public function test_admin_can_toggle_course_visibility_from_index_cards(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'title' => 'OnlyFans Blueprint',
            'slug' => 'onlyfans-blueprint',
            'platform_label' => 'OnlyFans',
            'platform_color' => '#00AFF0',
            'description' => 'A full platform walkthrough.',
            'is_published' => false,
        ]);

        $this->actingAs($admin)->patch(route('admin.courses.visibility', $course), [
            'is_published' => '1',
        ])->assertRedirect(route('admin.courses.index'));

        $this->assertTrue($course->fresh()->is_published);
    }

    public function test_admin_update_keeps_user_progress_when_lesson_ids_are_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Stripchat Pro Guide',
            'slug' => 'stripchat-pro-guide',
            'platform_label' => 'Stripchat',
            'platform_color' => '#FF3E4D',
            'description' => 'Master the platform from setup to private shows.',
            'is_published' => true,
        ]);

        $firstLesson = $course->lessons()->create([
            'title' => 'Getting Started',
            'body' => 'Account registration and verification.',
            'video_url' => 'https://www.youtube.com/embed/demo',
            'duration' => '12:10',
            'pdf_url' => 'https://example.com/guide.pdf',
            'sort_order' => 1,
        ]);

        $course->lessons()->create([
            'title' => 'Profile Polish',
            'body' => 'Improve the public profile.',
            'video_url' => 'https://www.youtube.com/embed/profile',
            'duration' => '08:00',
            'sort_order' => 2,
        ]);

        LessonProgress::create([
            'user_id' => $member->id,
            'lesson_id' => $firstLesson->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'Stripchat Pro Guide Updated',
            'slug' => 'stripchat-pro-guide',
            'platform_label' => 'Stripchat',
            'platform_color' => '#FF3E4D',
            'description' => 'Updated platform walkthrough.',
            'has_course_outline' => '0',
            'has_intro' => '0',
            'is_published' => '1',
            'sort_order' => 1,
            'lessons' => [
                [
                    'title' => 'Getting Started Updated',
                    'body' => 'Updated account registration and verification.',
                    'video_url' => 'https://www.youtube.com/embed/demo-updated',
                    'duration' => '13:00',
                    'pdf_url' => 'https://example.com/guide-updated.pdf',
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Profile Polish Updated',
                    'body' => 'Updated profile guidance.',
                    'video_url' => 'https://www.youtube.com/embed/profile-updated',
                    'duration' => '09:00',
                    'sort_order' => 2,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('lessons', [
            'id' => $firstLesson->id,
            'title' => 'Getting Started Updated',
        ]);
        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $member->id,
            'lesson_id' => $firstLesson->id,
        ]);
        $this->assertSame(2, $course->fresh()->lessons()->count());
    }

    public function test_admin_update_with_lesson_ids_can_add_lessons_without_resetting_existing_progress(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'model']);
        $course = Course::create([
            'title' => 'Private Training',
            'slug' => 'private-training',
            'platform_label' => 'OnlyFans',
            'description' => 'A full platform walkthrough.',
            'is_published' => true,
        ]);
        $module = $course->modules()->create([
            'title' => 'Core Training',
            'description' => 'Main training path.',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $firstLesson = $course->lessons()->create([
            'course_module_id' => $module->id,
            'title' => 'Setup',
            'sort_order' => 1,
        ]);
        $secondLesson = $course->lessons()->create([
            'course_module_id' => $module->id,
            'title' => 'Profile',
            'sort_order' => 2,
        ]);

        LessonProgress::create([
            'user_id' => $member->id,
            'lesson_id' => $firstLesson->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'Private Training Updated',
            'slug' => 'private-training',
            'platform_label' => 'OnlyFans',
            'description' => 'Updated full platform walkthrough.',
            'has_course_outline' => '0',
            'has_intro' => '0',
            'is_published' => '1',
            'modules' => [
                [
                    'id' => $module->id,
                    'client_key' => 'module-'.$module->id,
                    'title' => 'Core Training',
                    'description' => 'Updated main training path.',
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
            'lessons' => [
                [
                    'id' => $firstLesson->id,
                    'course_module_id' => $module->id,
                    'module_key' => 'module-'.$module->id,
                    'title' => 'Setup Updated',
                    'sort_order' => 1,
                ],
                [
                    'id' => $secondLesson->id,
                    'course_module_id' => $module->id,
                    'module_key' => 'module-'.$module->id,
                    'title' => 'Profile Updated',
                    'sort_order' => 2,
                ],
                [
                    'course_module_id' => $module->id,
                    'module_key' => 'module-'.$module->id,
                    'title' => 'Safety Review',
                    'sort_order' => 3,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('lessons', [
            'id' => $firstLesson->id,
            'title' => 'Setup Updated',
        ]);
        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $member->id,
            'lesson_id' => $firstLesson->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'title' => 'Safety Review',
        ]);
        $this->assertSame(3, $course->fresh()->lessons()->count());
    }
}
