<?php

namespace Tests\Feature;

use App\Mail\AdminActivityAlertMail;
use App\Models\ChatterModelAssignment;
use App\Models\ChatterModelReview;
use App\Models\ChatterPayRate;
use App\Models\ChatterProfile;
use App\Models\ChatterTimesheet;
use App\Models\User;
use App\Services\ChatterPayrollService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ChatterModelReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_chatter_submits_weekly_model_review_and_admin_can_read_it(): void
    {
        Mail::fake();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-28 12:00:00', 'Europe/London'));

        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->activeChatter(['name' => 'Review Chatter']);
        $model = $this->grantCommunityAccess(User::factory()->create(['role' => 'model', 'name' => 'Model Doll']));
        $this->assignModel($chatter, $model);

        $this->actingAs($chatter)
            ->get(route('chatter.model-reviews.create', ['model_id' => $model->id]))
            ->assertOk()
            ->assertSee('Weekly Chatter Model Review Form')
            ->assertSee('Model Doll');

        $this->actingAs($chatter)
            ->post(route('chatter.model-reviews.store'), $this->reviewPayload([
                'model_id' => $model->id,
                'week_ending' => '2026-08-02',
            ]))
            ->assertRedirect(route('chatter.model-reviews.index'));

        $review = ChatterModelReview::query()->firstOrFail();

        $this->assertSame($chatter->id, $review->chatter_id);
        $this->assertSame($model->id, $review->model_id);
        $this->assertSame('2026-08-02', $review->week_ending->toDateString());
        $this->assertSame(5, $review->overall_rating);
        $this->assertSame('Worked well and followed guidance.', $review->went_well);
        $this->assertTrue($review->declaration_accepted);

        $this->assertSame('chatter_model_review', $admin->notifications()->first()?->data['category']);
        Mail::assertQueued(AdminActivityAlertMail::class, fn (AdminActivityAlertMail $mail) =>
            $mail->subjectLine === 'New weekly chatter model review submitted'
            && $mail->actionLabel === 'Review submission'
        );

        $this->actingAs($admin)
            ->get(route('admin.chatter-model-reviews.index'))
            ->assertOk()
            ->assertSee('Review Chatter')
            ->assertSee('Model Doll');

        $this->actingAs($admin)
            ->get(route('admin.chatter-model-reviews.show', $review))
            ->assertOk()
            ->assertSee('Review Chatter')
            ->assertSee('Model Doll')
            ->assertSee('5/5')
            ->assertSee('Strong customer energy.');
    }

    public function test_chatter_cannot_submit_two_reviews_for_same_model_and_week(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-28 12:00:00', 'Europe/London'));

        $chatter = $this->activeChatter();
        $model = $this->grantCommunityAccess(User::factory()->create(['role' => 'model']));
        $this->assignModel($chatter, $model);

        $payload = $this->reviewPayload([
            'model_id' => $model->id,
            'week_ending' => '2026-08-02',
        ]);

        $this->actingAs($chatter)
            ->post(route('chatter.model-reviews.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->actingAs($chatter)
            ->from(route('chatter.model-reviews.create', ['model_id' => $model->id]))
            ->post(route('chatter.model-reviews.store'), $payload)
            ->assertRedirect(route('chatter.model-reviews.create', ['model_id' => $model->id]))
            ->assertSessionHasErrors('model_id');

        $this->assertSame(1, ChatterModelReview::count());
    }

    public function test_admin_cannot_approve_payroll_until_required_weekly_model_reviews_are_complete(): void
    {
        Mail::fake();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-27 10:00:00', ChatterPayrollService::REPORTING_TIMEZONE));

        $admin = User::factory()->create(['role' => 'admin']);
        $chatter = $this->activeChatter();
        $model = $this->grantCommunityAccess(User::factory()->create(['role' => 'model']));
        $this->assignModel($chatter, $model);
        $timesheet = ChatterTimesheet::create([
            'user_id' => $chatter->id,
            'period_start' => '2026-07-20',
            'period_end' => '2026-07-26',
            'status' => ChatterTimesheet::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.chatter-hours.timesheets.review', $timesheet), ['decision' => 'approve'])
            ->assertSessionHasErrors('timesheet');

        $this->actingAs($chatter)
            ->post(route('chatter.model-reviews.store'), $this->reviewPayload([
                'model_id' => $model->id,
                'week_ending' => '2026-07-26',
            ]))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.chatter-hours.timesheets.review', $timesheet), ['decision' => 'approve'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertSame(ChatterTimesheet::STATUS_APPROVED, $timesheet->fresh()->status);
    }

    public function test_chatter_review_dashboard_only_shows_assigned_models(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-28 12:00:00', 'Europe/London'));

        $chatter = $this->activeChatter();
        $assigned = $this->grantCommunityAccess(User::factory()->create(['role' => 'model', 'name' => 'Assigned Model']));
        $other = $this->grantCommunityAccess(User::factory()->create(['role' => 'model', 'name' => 'Other Model']));
        $this->assignModel($chatter, $assigned);

        $this->actingAs($chatter)
            ->get(route('chatter.model-reviews.index'))
            ->assertOk()
            ->assertSee('Assigned Model')
            ->assertSee('Assigned Models')
            ->assertDontSee('Other Model');
    }

    public function test_chatter_cannot_review_unassigned_model(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-28 12:00:00', 'Europe/London'));

        $chatter = $this->activeChatter();
        $model = $this->grantCommunityAccess(User::factory()->create(['role' => 'model']));

        $this->actingAs($chatter)
            ->post(route('chatter.model-reviews.store'), $this->reviewPayload([
                'model_id' => $model->id,
            ]))
            ->assertSessionHasErrors('model_id');

        $this->assertSame(0, ChatterModelReview::count());
    }

    private function activeChatter(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'chatter',
            'name' => 'Weekly Chatter',
        ], $overrides));

        ChatterProfile::create([
            'user_id' => $user->id,
            'timezone' => 'Europe/London',
            'employment_status' => ChatterProfile::STATUS_ACTIVE,
            'started_at' => now(),
        ]);

        ChatterPayRate::create([
            'user_id' => $user->id,
            'base_rate_pence' => 300,
            'overtime_threshold_minutes' => 2400,
            'overtime_multiplier_bps' => 15000,
            'night_premium_bps' => 10000,
            'weekend_premium_bps' => 10000,
            'night_starts_at' => '22:00',
            'night_ends_at' => '06:00',
            'effective_from' => '2026-01-01',
        ]);

        return $user->refresh();
    }

    private function reviewPayload(array $overrides = []): array
    {
        return [
            'model_id' => null,
            'week_ending' => '2026-08-02',
            'overall_rating' => 5,
            'overall_rating_explanation' => 'Strong customer energy.',
            'energy_motivation' => 'excellent',
            'energy_comments' => 'Stayed positive throughout the week.',
            'effort_towards_earnings' => 'yes',
            'effort_comments' => 'Promoted goals and private shows.',
            'communication_teamwork' => 'excellent',
            'communication_comments' => 'Communicated clearly before shifts.',
            'followed_guidance' => 'always',
            'guidance_examples' => 'Followed all weekly guidance.',
            'went_well' => 'Worked well and followed guidance.',
            'could_improve' => 'Could send more updates after streams.',
            'shift_issues' => '0',
            'shift_issues_explanation' => null,
            'security_concerns' => '0',
            'security_concerns_explanation' => null,
            'authorised_actions' => 'Reported one abusive customer.',
            'performance_strategies' => 'Tip goals worked well.',
            'customer_feedback' => 'Customers responded positively.',
            'coaching_recommendations' => 'Keep consistent posting.',
            'recognition' => '1',
            'recognition_reason' => 'Excellent attitude.',
            'additional_comments' => 'No further notes.',
            'declaration_accepted' => '1',
            'signature' => 'Weekly Chatter',
            ...$overrides,
        ];
    }

    private function assignModel(User $chatter, User $model): void
    {
        ChatterModelAssignment::create([
            'chatter_id' => $chatter->id,
            'model_id' => $model->id,
            'assigned_at' => now(),
        ]);
    }
}
