<?php

namespace Tests\Feature;

use App\Models\Course;
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
            'description' => 'Master the platform from setup to private shows.',
            'is_published' => '1',
            'sort_order' => 1,
            'lessons' => [
                [
                    'title' => 'Getting Started',
                    'body' => 'Account registration and verification.',
                    'video_url' => 'https://www.youtube.com/embed/demo',
                    'duration' => '12:10',
                    'has_pdf' => '1',
                    'pdf_url' => 'https://example.com/guide.pdf',
                    'presentation_url' => 'https://www.canva.com/design/example',
                    'sort_order' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.courses.index'));

        $course = Course::with('lessons')->where('slug', 'stripchat-pro-guide')->first();

        $this->assertNotNull($course);
        $this->assertTrue($course->is_published);
        $this->assertSame('#FF3E4D', $course->platform_color);
        $this->assertSame('12:10', $course->lessons->first()->duration);
        $this->assertTrue($course->lessons->first()->has_pdf);
        $this->assertSame('https://example.com/guide.pdf', $course->lessons->first()->pdf_url);
        $this->assertSame('https://www.canva.com/design/example', $course->lessons->first()->presentation_url);
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
}
