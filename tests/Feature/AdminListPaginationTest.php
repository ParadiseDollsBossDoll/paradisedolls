<?php

namespace Tests\Feature;

use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ModelReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_onboarding_list_honors_the_selected_page_size(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(12)->create(['role' => 'model']);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index', ['per_page' => 10]))
            ->assertOk()
            ->assertViewHas('perPage', 10)
            ->assertViewHas('models', fn ($models) => $models->perPage() === 10
                && $models->total() === 12
                && $models->count() === 10);
    }

    public function test_admin_onboarding_list_can_search_and_sort_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $olderMember = User::factory()->create([
            'name' => 'Zara Model',
            'email' => 'zara@example.com',
            'role' => 'model',
            'created_at' => now()->subDays(10),
        ]);
        $newerMember = User::factory()->create([
            'name' => 'Bella Model',
            'email' => 'bella@example.com',
            'role' => 'model',
            'created_at' => now()->subDays(9),
        ]);

        $olderApplication = ModelApplication::create([
            'name' => 'Zara Application',
            'email' => 'zara@example.com',
            'age_confirmed' => true,
        ]);
        $olderApplication->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $olderMember->id,
            'created_at' => now()->subDays(8),
        ])->save();

        $newerApplication = ModelApplication::create([
            'name' => 'Bella Application',
            'email' => 'bella@example.com',
            'age_confirmed' => true,
        ]);
        $newerApplication->forceFill([
            'status' => ModelApplication::STATUS_APPROVED,
            'user_id' => $newerMember->id,
            'created_at' => now()->subDay(),
        ])->save();

        ModelProfile::create([
            'user_id' => $olderMember->id,
            'model_application_id' => $olderApplication->id,
            'legal_name' => 'Amanda Legal',
            'stage_name' => 'Velvet Spark',
        ]);
        ModelProfile::create([
            'user_id' => $newerMember->id,
            'model_application_id' => $newerApplication->id,
            'legal_name' => 'Bella Legal',
            'stage_name' => 'Breeze',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index', ['search' => 'Velvet']))
            ->assertOk()
            ->assertSee('Search members')
            ->assertSee('Applied')
            ->assertViewHas('search', 'Velvet')
            ->assertViewHas('models', fn ($models) => $models->total() === 1
                && $models->first()->is($olderMember));

        $this->actingAs($admin)
            ->get(route('admin.onboarding.index', [
                'sort' => 'application_date',
                'direction' => 'desc',
            ]))
            ->assertOk()
            ->assertViewHas('sort', 'application_date')
            ->assertViewHas('direction', 'desc')
            ->assertViewHas('models', fn ($models) => $models->getCollection()->pluck('id')->take(2)->all() === [
                $newerMember->id,
                $olderMember->id,
            ]);
    }

    public function test_admin_applications_and_referral_leads_are_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $referrer = User::factory()->create(['role' => 'model']);

        foreach (range(1, 12) as $index) {
            ModelApplication::create([
                'name' => "Applicant {$index}",
                'email' => "applicant{$index}@example.com",
            ]);

            ModelReferral::create([
                'referrer_id' => $referrer->id,
                'candidate_name' => "Referral {$index}",
                'candidate_email' => "referral{$index}@example.com",
                'source' => ModelReferral::SOURCE_MEMBER_FORM,
                'status' => ModelReferral::STATUS_REFERRED,
                'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.applications.index', ['per_page' => 10]))
            ->assertOk()
            ->assertViewHas('perPage', 10)
            ->assertViewHas('applications', fn ($applications) => $applications->perPage() === 10
                && $applications->total() === 12
                && $applications->count() === 10)
            ->assertViewHas('referralLeads', fn ($leads) => $leads->perPage() === 10
                && $leads->total() === 12
                && $leads->count() === 10);
    }

    public function test_admin_referral_lists_honor_the_selected_page_size(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(12)->create(['role' => 'model']);

        $this->actingAs($admin)
            ->get(route('admin.referrals.index', ['per_page' => 10]))
            ->assertOk()
            ->assertViewHas('perPage', 10)
            ->assertViewHas('referrers', fn ($referrers) => $referrers->perPage() === 10
                && $referrers->total() === 12
                && $referrers->count() === 10)
            ->assertViewHas('recentReferrals', fn ($referrals) => $referrals->perPage() === 10);
    }
}
