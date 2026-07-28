<?php

namespace Tests\Feature;

use App\Mail\AdminActivityAlertMail;
use App\Models\ChatterPerformanceReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ChatterPerformanceReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_submit_confidential_chatter_review_and_admin_is_notified(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $model = User::factory()->create(['role' => 'model', 'name' => 'Model Doll']);
        $chatter = User::factory()->create(['role' => 'chatter', 'name' => 'Helpful Chatter']);
        $reviewUrl = URL::temporarySignedRoute('member.chatter-reviews.create', now()->addDays(60));

        $this->actingAs($model)
            ->post($reviewUrl, $this->reviewPayload([
                'is_anonymous' => '0',
                'chatter_user_id' => $chatter->id,
                'model_name' => 'Stage Doll',
            ]))
            ->assertRedirect($reviewUrl);

        $review = ChatterPerformanceReview::query()->firstOrFail();

        $this->assertSame($model->id, $review->user_id);
        $this->assertSame($chatter->id, $review->chatter_user_id);
        $this->assertFalse($review->is_anonymous);
        $this->assertSame('Stage Doll', $review->model_name);
        $this->assertSame('Helpful Chatter', $review->chatter_name);
        $this->assertSame('4.20', (string) $review->average_score);

        $this->assertSame('chatter_performance_review', $admin->notifications()->first()?->data['category']);
        Mail::assertQueued(AdminActivityAlertMail::class, fn (AdminActivityAlertMail $mail) =>
            $mail->subjectLine === 'New chatter performance review submitted'
            && $mail->actionLabel === 'Review feedback'
        );

        $this->actingAs($admin)
            ->get(route('admin.chatter-reviews.show', $review))
            ->assertOk()
            ->assertSee('Stage Doll')
            ->assertSee('Helpful Chatter')
            ->assertSee('4.20/5');
    }

    public function test_chatter_review_form_is_private_to_logged_in_models(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = User::factory()->create(['role' => 'chatter']);
        $model = User::factory()->create(['role' => 'model']);
        $reviewUrl = URL::temporarySignedRoute('member.chatter-reviews.create', now()->addDays(60));

        $this->get(route('member.chatter-reviews.create'))
            ->assertRedirect(route('login'));

        $this->actingAs($model)
            ->get(route('member.chatter-reviews.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get($reviewUrl)
            ->assertRedirect(route('admin.models.progress'));

        $this->actingAs($chatter)
            ->get($reviewUrl)
            ->assertRedirect(route('chatter.dashboard'));

        $this->actingAs($model)
            ->get($reviewUrl)
            ->assertOk()
            ->assertSee('First Month Chatter Performance Review');
    }

    public function test_admin_can_update_management_only_review_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $model = User::factory()->create(['role' => 'model']);
        $review = ChatterPerformanceReview::create([
            ...$this->reviewPayload(),
            'user_id' => $model->id,
            'is_anonymous' => true,
            'average_score' => 4.2,
            'status' => ChatterPerformanceReview::STATUS_SUBMITTED,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.chatter-reviews.update', $review), [
                'status' => ChatterPerformanceReview::STATUS_FOLLOW_UP,
                'management_performance' => 'requires_improvement',
                'model_wishes_to_remain' => 'yes_with_improvements',
                'additional_training_required' => '1',
                'manager_notes' => 'Coach the chatter on response time.',
            ])
            ->assertRedirect(route('admin.chatter-reviews.show', $review));

        $review->refresh();

        $this->assertSame(ChatterPerformanceReview::STATUS_FOLLOW_UP, $review->status);
        $this->assertSame('requires_improvement', $review->management_performance);
        $this->assertSame('yes_with_improvements', $review->model_wishes_to_remain);
        $this->assertTrue($review->additional_training_required);
        $this->assertSame('Coach the chatter on response time.', $review->manager_notes);
        $this->assertSame($admin->id, $review->reviewed_by);
        $this->assertNotNull($review->reviewed_at);
    }

    private function reviewPayload(array $overrides = []): array
    {
        return [
            'is_anonymous' => '1',
            'chatter_user_id' => null,
            'model_name' => null,
            'chatter_name' => 'Test Chatter',
            'review_date' => '2026-07-28',
            'review_period' => 'First month',
            'communication_rating' => 5,
            'professionalism_rating' => 4,
            'friendliness_rating' => 5,
            'response_time_rating' => 3,
            'reliability_rating' => 4,
            'customer_engagement_rating' => 4,
            'sales_encouragement_rating' => 4,
            'support_motivation_rating' => 5,
            'boundary_understanding_rating' => 4,
            'overall_performance_rating' => 4,
            'sounds_like_model' => 'most_of_the_time',
            'understands_personality' => 'mostly',
            'respects_boundaries' => 'always',
            'performance_feeling' => 'small_improvements',
            'wants_to_help' => 'mostly',
            'confidence_improved' => 'yes',
            'customer_engagement_improved' => 'yes',
            'continue_working' => 'yes_with_improvements',
            'does_well' => 'Very friendly with customers.',
            'could_improve' => 'Could respond faster during busy moments.',
            'missed_opportunities' => null,
            'natural_speech' => 'Mostly understands my tone.',
            'uncomfortable_incident' => 'no',
            'uncomfortable_explanation' => null,
            'one_change' => null,
            'recognition' => 'Keeps the mood positive.',
            'anything_else' => null,
            'overall_satisfaction' => 'satisfied',
            'would_recommend' => 'yes',
            'contact_requested' => '0',
            ...$overrides,
        ];
    }
}
