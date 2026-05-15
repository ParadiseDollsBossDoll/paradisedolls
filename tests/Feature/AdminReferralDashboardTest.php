<?php

namespace Tests\Feature;

use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReferralDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_referral_counts_by_model(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $referrer = User::factory()->create([
            'name' => 'Neljhan Redondo',
            'email' => 'neljhan@example.com',
            'role' => 'model',
            'referral_code' => 'PDNELJHAN',
        ]);
        $modelWithoutReferrals = User::factory()->create([
            'name' => 'No Referral Model',
            'email' => 'no-referrals@example.com',
            'role' => 'model',
        ]);

        ModelReferral::create([
            'referrer_id' => $referrer->id,
            'candidate_name' => 'Lead Candidate',
            'candidate_email' => 'lead@example.com',
            'experience_level' => 'beginner',
            'photo_paths' => ['referrals/photos/lead.png'],
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_MEMBER_FORM,
            'status' => ModelReferral::STATUS_REFERRED,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ]);

        ModelReferral::create([
            'referrer_id' => $referrer->id,
            'candidate_name' => 'Pending Candidate',
            'candidate_email' => 'pending@example.com',
            'experience_level' => 'beginner',
            'photo_paths' => ['referrals/photos/pending.png'],
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
            'status' => ModelReferral::STATUS_PENDING,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ]);

        ModelReferral::create([
            'referrer_id' => $referrer->id,
            'candidate_name' => 'Reward Candidate',
            'candidate_email' => 'reward@example.com',
            'experience_level' => 'beginner',
            'photo_paths' => ['referrals/photos/reward.png'],
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
            'status' => ModelReferral::STATUS_JOINED,
            'reward_status' => ModelReferral::REWARD_ELIGIBLE,
            'joined_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.referrals.index'))
            ->assertOk()
            ->assertSee('Referral Counts By Model')
            ->assertSee('Neljhan Redondo')
            ->assertSee('PDNELJHAN')
            ->assertSee('No Referral Model')
            ->assertSee('Rewards Due')
            ->assertSee('Recent Referrals')
            ->assertSee('Reward Candidate')
            ->assertSee('Mark Paid');

        $this->assertSame(0, $modelWithoutReferrals->modelReferrals()->count());
    }

    public function test_admin_can_filter_referral_dashboard_to_models_with_referrals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $referrer = User::factory()->create([
            'name' => 'Active Referrer',
            'role' => 'model',
        ]);
        User::factory()->create([
            'name' => 'Quiet Model',
            'role' => 'model',
        ]);

        ModelReferral::create([
            'referrer_id' => $referrer->id,
            'candidate_name' => 'Filtered Candidate',
            'candidate_email' => 'filtered@example.com',
            'experience_level' => 'beginner',
            'photo_paths' => ['referrals/photos/filtered.png'],
            'consent_confirmed' => true,
            'source' => ModelReferral::SOURCE_MEMBER_FORM,
            'status' => ModelReferral::STATUS_REFERRED,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.referrals.index', ['with_referrals' => 1]))
            ->assertOk()
            ->assertSee('Active Referrer')
            ->assertDontSee('Quiet Model');
    }
}
