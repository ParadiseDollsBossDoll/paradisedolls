<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_publish_success_story(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'name' => 'Success Member',
            'headline' => 'Built confidence online',
            'quote' => 'The support system made the opportunity feel achievable.',
            'location' => 'Remote',
            'result_label' => 'Confidence',
            'image_url' => 'https://example.com/story.jpg',
            'is_published' => '1',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', [
            'name' => 'Success Member',
            'is_published' => true,
        ]);

        $this->get(route('success-stories'))
            ->assertOk()
            ->assertSee('Built confidence online');
    }

    public function test_homepage_only_shows_published_success_stories(): void
    {
        Testimonial::create([
            'name' => 'Published Member',
            'headline' => 'Visible win',
            'quote' => 'A published testimonial.',
            'is_published' => true,
        ]);

        Testimonial::create([
            'name' => 'Draft Member',
            'headline' => 'Hidden win',
            'quote' => 'A draft testimonial.',
            'is_published' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Visible win')
            ->assertDontSee('Hidden win');
    }
}
