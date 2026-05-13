<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\ChatRoom;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCourseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_course_with_zip_style_lesson_fields(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Stripchat Pro Guide',
            'slug' => 'stripchat-pro-guide',
            'platform_label' => 'Stripchat',
            'platform_color' => '#FF3E4D',
            'short_description' => 'A premium onboarding path for new members.',
            'description' => 'Master the platform from setup to private shows.',
            'has_course_outline' => '1',
            'course_outline_upload' => $this->fakePdfUpload('outline.pdf'),
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
                    'presentation_url' => '<iframe src="https://www.canva.com/design/example/view?embed" allowfullscreen></iframe>',
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
        $this->assertStringStartsWith('academy/course-outlines/', $course->course_outline_url);
        $this->assertStringStartsWith('outline-', $course->courseOutlineFileName());
        $this->assertStringEndsWith('.pdf', $course->courseOutlineFileName());
        Storage::disk('public')->assertExists($course->course_outline_url);
        $this->assertTrue($course->has_intro);
        $this->assertSame('Course Orientation', $course->intro_title);
        $this->assertSame('https://www.youtube.com/embed/intro', $course->intro_video_url);
        $this->assertSame('12:10', $course->lessons->first()->duration);
        $this->assertSame('https://example.com/guide.pdf', $course->lessons->first()->pdf_url);
        $this->assertSame('https://www.canva.com/design/example/view?embed', $course->lessons->first()->presentation_url);
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

    public function test_admin_can_upload_course_and_lesson_visual_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Visual Academy Guide',
            'slug' => 'visual-academy-guide',
            'platform_label' => 'General',
            'description' => 'A visual onboarding walkthrough.',
            'course_cover_image_upload' => $this->fakePngUpload('course-cover.png'),
            'has_course_outline' => '0',
            'has_intro' => '0',
            'is_published' => '1',
            'modules' => [
                [
                    'client_key' => 'module-1',
                    'title' => 'Core Training',
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
            'lessons' => [
                [
                    'module_key' => 'module-1',
                    'title' => 'Visual Setup',
                    'overview' => 'Follow the annotated examples.',
                    'lesson_banner_image_upload' => $this->fakePngUpload('lesson-banner.png'),
                    'lesson_images_upload' => [
                        $this->fakePngUpload('example-one.png'),
                        $this->fakePngUpload('example-two.png'),
                    ],
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $course = Course::with('lessons')->where('slug', 'visual-academy-guide')->firstOrFail();
        $lesson = $course->lessons->first();

        $this->assertNotNull($course->course_cover_image);
        $this->assertNotNull($lesson->lesson_banner_image);
        $this->assertCount(2, $lesson->lesson_images);

        Storage::disk('public')->assertExists($course->course_cover_image);
        Storage::disk('public')->assertExists($lesson->lesson_banner_image);
        foreach ($lesson->lesson_images as $imagePath) {
            Storage::disk('public')->assertExists($imagePath);
        }
    }

    public function test_admin_can_create_lesson_content_blocks(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Flexible Lesson Guide',
            'slug' => 'flexible-lesson-guide',
            'platform_label' => 'General',
            'description' => 'A course with flexible content blocks.',
            'has_course_outline' => '0',
            'has_intro' => '0',
            'is_published' => '1',
            'modules' => [
                [
                    'client_key' => 'module-1',
                    'title' => 'Core Training',
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
            'lessons' => [
                [
                    'module_key' => 'module-1',
                    'title' => 'Custom Flow',
                    'overview' => 'Legacy overview remains available as fallback.',
                    'content_blocks_enabled' => '1',
                    'content_blocks' => [
                        [
                            'block_type' => 'text',
                            'title' => 'Start Here',
                            'content' => 'Read this first.',
                            'sort_order' => 1,
                        ],
                        [
                            'block_type' => 'image',
                            'title' => 'Profile Example',
                            'image_upload' => $this->fakePngUpload('profile-example.png'),
                            'sort_order' => 2,
                        ],
                        [
                            'block_type' => 'video',
                            'title' => 'Walkthrough',
                            'bunny_video_id' => 'video-123',
                            'bunny_library_id' => 'library-456',
                            'bunny_video_title' => 'Block Walkthrough',
                            'duration' => '03:20',
                            'sort_order' => 3,
                        ],
                        [
                            'block_type' => 'pdf_resource',
                            'title' => 'Checklist',
                            'file_upload' => $this->fakePdfUpload('checklist.pdf'),
                            'sort_order' => 4,
                        ],
                        [
                            'block_type' => 'presentation',
                            'title' => 'Slides',
                            'file_upload' => $this->fakePdfUpload('slides.pdf'),
                            'sort_order' => 5,
                        ],
                    ],
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $course = Course::with('lessons.contentBlocks')->where('slug', 'flexible-lesson-guide')->firstOrFail();
        $blocks = $course->lessons->first()->contentBlocks;

        $this->assertSame(['text', 'image', 'video', 'pdf_resource', 'presentation'], $blocks->pluck('block_type')->all());
        $this->assertSame('Start Here', $blocks[0]->title);
        $this->assertSame('Read this first.', $blocks[0]->content);
        $this->assertNotNull($blocks[1]->image_path);
        $this->assertSame('video-123', $blocks[2]->bunny_video_id);
        $this->assertSame('library-456', $blocks[2]->bunny_library_id);
        $this->assertNotNull($blocks[3]->file_path);
        $this->assertNotNull($blocks[4]->file_path);

        Storage::disk('public')->assertExists($blocks[1]->image_path);
        Storage::disk('public')->assertExists($blocks[3]->file_path);
        Storage::disk('public')->assertExists($blocks[4]->file_path);
    }

    public function test_admin_added_empty_content_block_is_saved_as_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Draft Block Guide',
            'slug' => 'draft-block-guide',
            'platform_label' => 'General',
            'description' => 'A course with a draft block.',
            'has_course_outline' => '0',
            'has_intro' => '0',
            'is_published' => '1',
            'modules' => [
                [
                    'client_key' => 'module-1',
                    'title' => 'Core Training',
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
            'lessons' => [
                [
                    'module_key' => 'module-1',
                    'title' => 'Draft Lesson',
                    'overview' => 'Fallback content still renders for members.',
                    'content_blocks_enabled' => '1',
                    'content_blocks' => [
                        [
                            'block_type' => 'text',
                            'title' => '',
                            'content' => '',
                            'sort_order' => 1,
                        ],
                    ],
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $course = Course::with('lessons.contentBlocks')->where('slug', 'draft-block-guide')->firstOrFail();
        $block = $course->lessons->first()->contentBlocks->first();

        $this->assertNotNull($block);
        $this->assertSame('text', $block->block_type);
        $this->assertNull($block->title);
        $this->assertNull($block->content);
    }

    public function test_admin_can_preview_course_like_member_without_progress_side_effects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'title' => 'Preview Sandbox Guide',
            'slug' => 'preview-sandbox-guide',
            'platform_label' => 'General',
            'description' => 'A draft course for visual review.',
            'is_published' => false,
        ]);
        $module = $course->modules()->create([
            'title' => 'Core Training',
            'is_published' => false,
            'sort_order' => 1,
        ]);
        $lesson = $course->lessons()->create([
            'course_module_id' => $module->id,
            'title' => 'Preview Lesson',
            'lesson_banner_image' => 'https://example.com/preview-banner.jpg',
            'is_published' => false,
            'sort_order' => 1,
        ]);
        $lesson->contentBlocks()->create([
            'block_type' => 'text',
            'title' => 'Preview Flow',
            'content' => 'Admins can verify this before publishing.',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.courses.edit', $course))
            ->assertOk()
            ->assertSee('Preview Course')
            ->assertSee('Preview Lesson');

        $this->actingAs($admin)
            ->get(route('admin.courses.lessons.preview', [$course, $lesson]))
            ->assertOk()
            ->assertSee('Preview Mode')
            ->assertSee('Exit Preview')
            ->assertSee('https://example.com/preview-banner.jpg', false)
            ->assertSee('Preview Flow')
            ->assertDontSee('Mark Complete')
            ->assertDontSee('Ask In Community');

        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $course->id,
            'user_id' => $admin->id,
        ]);
        $this->assertDatabaseMissing('lesson_progress', [
            'lesson_id' => $lesson->id,
            'user_id' => $admin->id,
        ]);
    }

    private function fakePngUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );
    }

    private function fakePdfUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF"
        );
    }

    public function test_admin_update_keeps_user_progress_when_lesson_ids_are_submitted(): void
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

        $secondLesson = $course->lessons()->create([
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
                    'id' => $firstLesson->id,
                    'course_id' => $course->id,
                    'title' => 'Getting Started Updated',
                    'body' => 'Updated account registration and verification.',
                    'video_url' => 'https://www.youtube.com/embed/demo-updated',
                    'duration' => '13:00',
                    'pdf_url' => 'https://example.com/guide-updated.pdf',
                    'sort_order' => 1,
                ],
                [
                    'id' => $secondLesson->id,
                    'course_id' => $course->id,
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

    public function test_admin_update_moves_existing_lesson_using_real_course_module_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'title' => 'Module Move Course',
            'slug' => 'module-move-course',
            'platform_label' => 'General',
            'description' => 'A course with several modules.',
            'is_published' => true,
        ]);

        $modules = collect(range(1, 4))->map(fn (int $number) => $course->modules()->create([
            'title' => 'Module '.$number,
            'is_published' => true,
            'sort_order' => $number,
        ]))->values();

        $movedLesson = $course->lessons()->create([
            'course_module_id' => $modules[0]->id,
            'title' => 'Move Me',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $moduleTwoLesson = $course->lessons()->create([
            'course_module_id' => $modules[1]->id,
            'title' => 'Stay In Module 2',
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'Module Move Course',
            'slug' => 'module-move-course',
            'platform_label' => 'General',
            'description' => 'A course with several modules.',
            'has_course_outline' => '0',
            'has_intro' => '0',
            'is_published' => '1',
            'modules' => $modules->map(fn ($module) => [
                'id' => $module->id,
                'client_key' => 'module-'.$module->id,
                'title' => $module->title,
                'is_published' => '1',
                'sort_order' => $module->sort_order,
            ])->all(),
            'lessons' => [
                [
                    'id' => $movedLesson->id,
                    'course_id' => $course->id,
                    'course_module_id' => $modules[3]->id,
                    'module_key' => 'module-'.$modules[0]->id,
                    'title' => 'Move Me',
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
                [
                    'id' => $moduleTwoLesson->id,
                    'course_id' => $course->id,
                    'course_module_id' => $modules[1]->id,
                    'module_key' => 'module-'.$modules[1]->id,
                    'title' => 'Stay In Module 2',
                    'is_published' => '1',
                    'sort_order' => 2,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('lessons', [
            'id' => $movedLesson->id,
            'course_id' => $course->id,
            'course_module_id' => $modules[3]->id,
        ]);

        $course->refresh()->load('modules.lessons');

        $this->assertFalse($course->modules->firstWhere('id', $modules[0]->id)->lessons->contains('id', $movedLesson->id));
        $this->assertTrue($course->modules->firstWhere('id', $modules[3]->id)->lessons->contains('id', $movedLesson->id));
    }

    public function test_minimal_lesson_form_update_preserves_legacy_lesson_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'title' => 'Legacy Lesson Course',
            'slug' => 'legacy-lesson-course',
            'platform_label' => 'General',
            'description' => 'A course with saved legacy lesson content.',
            'is_published' => true,
        ]);
        $module = $course->modules()->create([
            'title' => 'Core Training',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $lesson = $course->lessons()->create([
            'course_module_id' => $module->id,
            'title' => 'Legacy Setup',
            'body' => 'Saved body copy.',
            'overview' => 'Saved overview copy.',
            'steps' => "Saved step one\nSaved step two",
            'tips' => 'Saved tip.',
            'safety_notes' => 'Saved safety note.',
            'resource_links' => 'Saved Resource | https://example.com/resource',
            'lesson_banner_image' => 'academy/lesson-banners/banner.png',
            'lesson_images' => ['academy/lesson-images/example.png'],
            'video_url' => 'https://iframe.mediadelivery.net/embed/654926/video-id',
            'bunny_video_id' => 'video-id',
            'bunny_library_id' => '654926',
            'bunny_video_title' => 'Saved Bunny Video',
            'bunny_thumbnail_url' => 'https://cdn.example.com/video-id/thumbnail.jpg',
            'bunny_upload_fingerprint' => 'saved.mp4:100:123',
            'bunny_status' => 4,
            'duration' => '04:12',
            'has_pdf' => true,
            'pdf_url' => 'https://example.com/guide.pdf',
            'presentation_url' => 'https://www.canva.com/design/legacy/view?embed',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'Legacy Lesson Course',
            'slug' => 'legacy-lesson-course',
            'platform_label' => 'General',
            'description' => 'A course with saved legacy lesson content.',
            'has_course_outline' => '0',
            'has_intro' => '0',
            'is_published' => '1',
            'modules' => [
                [
                    'id' => $module->id,
                    'client_key' => 'module-'.$module->id,
                    'title' => 'Core Training',
                    'is_published' => '1',
                    'sort_order' => 1,
                ],
            ],
            'lessons' => [
                [
                    'id' => $lesson->id,
                    'course_module_id' => $module->id,
                    'module_key' => 'module-'.$module->id,
                    'title' => 'Legacy Setup Updated',
                    'is_published' => '1',
                    'content_blocks_enabled' => '1',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $lesson->refresh();

        $this->assertSame('Legacy Setup Updated', $lesson->title);
        $this->assertSame('Saved body copy.', $lesson->body);
        $this->assertSame('Saved overview copy.', $lesson->overview);
        $this->assertSame("Saved step one\nSaved step two", $lesson->steps);
        $this->assertSame('Saved tip.', $lesson->tips);
        $this->assertSame('Saved safety note.', $lesson->safety_notes);
        $this->assertSame('Saved Resource | https://example.com/resource', $lesson->resource_links);
        $this->assertSame('academy/lesson-banners/banner.png', $lesson->lesson_banner_image);
        $this->assertSame(['academy/lesson-images/example.png'], $lesson->lesson_images);
        $this->assertSame('video-id', $lesson->bunny_video_id);
        $this->assertSame('https://example.com/guide.pdf', $lesson->pdf_url);
        $this->assertTrue($lesson->has_pdf);
        $this->assertSame('https://www.canva.com/design/legacy/view?embed', $lesson->presentation_url);
    }
}
