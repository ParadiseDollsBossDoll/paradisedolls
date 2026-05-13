<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
