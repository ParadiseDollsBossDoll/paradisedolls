<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_must_click_learn_course_before_viewing_course(): void
    {
        $member = User::factory()->create(['role' => 'model']);
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
            ->assertSee('Start Learning');

        $this->actingAs($member)
            ->get(route('member.courses.learn.show', $course->slug))
            ->assertRedirect(route('member.courses.show', $course->slug));

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

    public function test_only_enrolled_members_can_use_course_chat_and_progress(): void
    {
        $member = User::factory()->create(['role' => 'model']);
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

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

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

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('Canva Template')
            ->assertSee('https://www.canva.com/design/example', false)
            ->assertSee('https://example.com/safety-guide', false)
            ->assertSee('presentation-wrapper', false)
            ->assertSee('https://www.canva.com/design/presentation/view?embed', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertDontSee('Video will appear here');
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
            ->assertSee('Course Overview')
            ->assertSee('lg:sticky', false);
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

    public function test_empty_draft_content_blocks_do_not_replace_legacy_member_layout(): void
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

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('This fallback overview should still show.')
            ->assertSee('Legacy step still works.');
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

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('Presentation cannot be embedded')
            ->assertSee('Open Presentation')
            ->assertSee('https://www.canva.com/design/sharelink/view', false)
            ->assertDontSee('presentation-wrapper', false);
    }

    public function test_canva_smart_embed_link_renders_iframe(): void
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

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('presentation-wrapper', false)
            ->assertSee('https://canva.link/example-presentation', false)
            ->assertSee('x-on:error="presentationBlocked = true"', false);
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

        $this->actingAs($member)->post(route('member.courses.learn', $course->slug));

        $this->actingAs($member)
            ->get(route('member.courses.lessons.show', [$course->slug, $lesson]))
            ->assertOk()
            ->assertSee('Open Presentation')
            ->assertSee('https://example.com/deck', false)
            ->assertDontSee('presentation-wrapper', false);
    }
}
