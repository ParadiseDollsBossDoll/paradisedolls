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
}
