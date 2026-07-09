<?php

namespace Tests\Feature;

use App\Mail\AdminActivityAlertMail;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonialManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_publish_success_story(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'name' => 'Success Member',
            'display_handle' => '@successdoll',
            'quote' => 'The support system made the opportunity feel achievable.',
            'result_label' => 'Confidence',
            'photo' => UploadedFile::fake()->createWithContent(
                'story.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAFgwJ/luzEGwAAAABJRU5ErkJggg==')
            ),
            'is_published' => '1',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', [
            'name' => 'Success Member',
            'display_handle' => 'successdoll',
            'headline' => 'Confidence',
            'is_published' => true,
            'approved_by' => $admin->id,
        ]);

        $testimonial = Testimonial::query()->where('name', 'Success Member')->firstOrFail();

        $this->assertNotNull($testimonial->image_path);
        $this->assertNull($testimonial->image_url);
        Storage::disk('public')->assertExists($testimonial->image_path);

        $this->get(route('success-stories'))
            ->assertOk()
            ->assertSee('@successdoll')
            ->assertSee('The support system made the opportunity feel achievable.')
            ->assertSee('#Confidence');
    }

    public function test_model_submitted_testimonial_needs_admin_approval_before_public_display(): void
    {
        Mail::fake();
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/model.jpg', 'avatar');

        $model = User::factory()->create([
            'role' => 'model',
            'profile_photo_path' => 'profile-photos/model.jpg',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($model)->post(route('member.testimonials.store'), [
            'name' => 'Model Member',
            'display_handle' => '@neljhanredondo',
            'quote' => 'Training and support helped me feel ready.',
            'result_label' => 'Confidence',
        ])->assertRedirect(route('member.testimonials.create'));

        $testimonial = Testimonial::query()->where('quote', 'Training and support helped me feel ready.')->firstOrFail();

        $this->get(route('member.testimonials.create'))
            ->assertOk()
            ->assertSee('Training and support helped me feel ready.')
            ->assertSee('Pending review');

        $this->assertFalse($testimonial->is_published);
        $this->assertSame($model->id, $testimonial->submitted_by);
        $this->assertSame('neljhanredondo', $testimonial->display_handle);
        $this->assertSame('Confidence', $testimonial->headline);
        $this->assertNull($testimonial->approved_by);
        $this->assertNull($testimonial->approved_at);
        $this->assertNull($testimonial->image_path);

        $notification = $admin->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('testimonial_submitted', $notification->data['category']);
        Mail::assertQueued(AdminActivityAlertMail::class, fn (AdminActivityAlertMail $mail) =>
            $mail->subjectLine === 'New testimonial awaiting review from '.$model->name
            && $mail->actionLabel === 'Review testimonial'
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Training and support helped me feel ready.');

        $this->actingAs($admin)->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertSee('Training and support helped me feel ready.')
            ->assertSee(route('profile-photos.show', $model, absolute: false), false)
            ->assertSee('Approve');

        $this->actingAs($admin)->post(route('admin.testimonials.approve', $testimonial))
            ->assertRedirect(route('admin.testimonials.index'));

        $testimonial->refresh();

        $this->assertTrue($testimonial->is_published);
        $this->assertSame($admin->id, $testimonial->approved_by);
        $this->assertNotNull($testimonial->approved_at);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Training and support helped me feel ready.')
            ->assertSee('#Confidence')
            ->assertSee('@neljhanredondo')
            ->assertSee(route('profile-photos.show', $model, absolute: false), false);
    }

    public function test_homepage_only_shows_approved_success_stories(): void
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
            ->assertSeeText('Success Stories')
            ->assertSee('A published testimonial.')
            ->assertDontSee('Visible win')
            ->assertDontSee('A draft testimonial.')
            ->assertDontSeeText('Testimonials & Success Stories');
    }

    public function test_homepage_does_not_show_placeholder_reviews_when_there_are_no_real_stories(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('New Member')
            ->assertDontSee('@newmember')
            ->assertDontSee('#SupportSystem');
    }

    public function test_success_stories_hero_uses_the_community_copy(): void
    {
        Testimonial::create([
            'name' => 'Long Story Member',
            'headline' => 'Long win',
            'quote' => str_repeat('This is a longer success story from a real member. ', 20),
            'is_published' => true,
        ]);

        $this->get(route('success-stories'))
            ->assertOk()
            ->assertSeeText('PARADISE DOLLS COMMUNITY')
            ->assertSeeText('Success Stories')
            ->assertSeeText('Real Stories from Our Paradise Dolls')
            ->assertSeeText('Behind every success is a woman who had the courage to take the first step.')
            ->assertSeeText('Every Paradise Doll’s journey is unique')
            ->assertSeeText('success is about more than reaching your goals')
            ->assertSeeText('Your story starts with a single step')
            ->assertSee('pd-success-story-scroll', false)
            ->assertDontSee('Long win')
            ->assertDontSeeText('Community Testimonials');
    }
}
