<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaignDelivery;
use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignDelivery;
use App\Models\ModelProfile;
use App\Models\User;
use App\Services\EmailCampaignDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AdminEmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_draft_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.email-campaigns.store'), [
            'name' => 'Weekly confidence note',
            'subject' => 'A note for {name}',
            'body' => 'Keep showing up, {name}.',
            'audience' => EmailCampaign::AUDIENCE_ALL_MODELS,
            'delivery_mode' => 'draft',
        ]);

        $campaign = EmailCampaign::query()->firstOrFail();

        $response->assertRedirect(route('admin.email-campaigns.edit', $campaign));
        $this->assertSame(EmailCampaign::STATUS_DRAFT, $campaign->status);
        $this->assertNull($campaign->next_send_at);
    }

    public function test_send_now_only_queues_eligible_models(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $eligible = User::factory()->create(['role' => 'model']);
        $unsubscribed = User::factory()->create(['role' => 'model']);
        $unsubscribed->forceFill(['marketing_unsubscribed_at' => now()])->save();

        $campaign = $this->campaign($admin);

        $this->actingAs($admin)
            ->post(route('admin.email-campaigns.send', $campaign))
            ->assertRedirect();

        $run = $campaign->runs()->firstOrFail();

        $this->assertSame(1, $run->recipient_count);
        $this->assertDatabaseHas('email_campaign_deliveries', [
            'email_campaign_run_id' => $run->id,
            'user_id' => $eligible->id,
        ]);
        $this->assertDatabaseMissing('email_campaign_deliveries', [
            'email_campaign_run_id' => $run->id,
            'user_id' => $unsubscribed->id,
        ]);
        Queue::assertPushed(SendEmailCampaignDelivery::class, 1);
    }

    public function test_onboarded_audience_excludes_models_without_completed_onboarding(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $onboarded = User::factory()->create(['role' => 'model']);
        $waiting = User::factory()->create(['role' => 'model']);
        ModelProfile::create([
            'user_id' => $onboarded->id,
            'community_role_assigned_at' => now(),
        ]);
        ModelProfile::create(['user_id' => $waiting->id]);

        $campaign = $this->campaign($admin, [
            'audience' => EmailCampaign::AUDIENCE_ONBOARDED_MODELS,
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => now(),
        ]);

        app(EmailCampaignDispatcher::class)->dispatch($campaign);

        $run = $campaign->runs()->firstOrFail();
        $this->assertSame([$onboarded->id], $run->deliveries()->pluck('user_id')->all());
    }

    public function test_delivery_job_uses_saved_run_copy_and_marks_delivery_sent(): void
    {
        Queue::fake();
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $model = User::factory()->create(['role' => 'model', 'name' => 'Kayla Doll']);
        $campaign = $this->campaign($admin, [
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => now(),
        ]);

        app(EmailCampaignDispatcher::class)->dispatch($campaign);
        $delivery = EmailCampaignDelivery::query()->firstOrFail();

        $campaign->update([
            'subject' => 'Edited after queueing',
            'body' => 'This edited copy must not be sent.',
        ]);

        (new SendEmailCampaignDelivery($delivery->id))->handle();

        $this->assertDatabaseHas('email_campaign_deliveries', [
            'id' => $delivery->id,
            'status' => EmailCampaignDelivery::STATUS_SENT,
        ]);
        Mail::assertSent(MarketingCampaignMail::class, function (MarketingCampaignMail $mail) use ($model): bool {
            return $mail->hasTo($model->email)
                && $mail->renderedSubject === 'Hello Kayla Doll'
                && str_contains($mail->renderedBody, 'Welcome Kayla Doll')
                && ! str_contains($mail->renderedBody, 'edited copy');
        });
    }

    public function test_model_can_unsubscribe_using_signed_email_link(): void
    {
        $model = User::factory()->create(['role' => 'model']);
        $url = URL::signedRoute('marketing.unsubscribe', ['user' => $model]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Unsubscribe from marketing emails');

        $this->post($url)->assertRedirect();

        $this->assertNotNull($model->refresh()->marketing_unsubscribed_at);
    }

    public function test_queued_delivery_is_skipped_if_model_unsubscribes_before_send(): void
    {
        Queue::fake();
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $model = User::factory()->create(['role' => 'model']);
        $campaign = $this->campaign($admin, [
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'next_send_at' => now(),
        ]);

        app(EmailCampaignDispatcher::class)->dispatch($campaign);
        $delivery = EmailCampaignDelivery::query()->firstOrFail();
        $model->forceFill(['marketing_unsubscribed_at' => now()])->save();

        (new SendEmailCampaignDelivery($delivery->id))->handle();

        $this->assertDatabaseHas('email_campaign_deliveries', [
            'id' => $delivery->id,
            'status' => EmailCampaignDelivery::STATUS_SKIPPED,
        ]);
        Mail::assertNothingSent();
    }

    public function test_recurring_campaign_advances_to_its_next_run(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'model']);
        $campaign = $this->campaign($admin, [
            'status' => EmailCampaign::STATUS_ACTIVE,
            'next_send_at' => now()->subMinute(),
            'repeat_every_days' => 3,
        ]);

        $count = app(EmailCampaignDispatcher::class)->dispatchDue();

        $campaign->refresh();
        $this->assertSame(1, $count);
        $this->assertSame(EmailCampaign::STATUS_ACTIVE, $campaign->status);
        $this->assertSame(1, $campaign->total_runs);
        $this->assertTrue($campaign->next_send_at->isAfter(now()->addDays(2)));
    }

    public function test_non_admin_cannot_access_email_campaigns(): void
    {
        $model = User::factory()->create(['role' => 'model']);

        $this->actingAs($model)
            ->get(route('admin.email-campaigns.index'))
            ->assertForbidden();
    }

    private function campaign(User $admin, array $attributes = []): EmailCampaign
    {
        return EmailCampaign::create([
            'created_by' => $admin->id,
            'name' => 'Welcome series',
            'subject' => 'Hello {name}',
            'body' => 'Welcome {name} to this week\'s update.',
            'audience' => EmailCampaign::AUDIENCE_ALL_MODELS,
            'status' => EmailCampaign::STATUS_DRAFT,
            ...$attributes,
        ]);
    }
}
