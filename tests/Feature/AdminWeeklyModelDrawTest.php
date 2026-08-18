<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyModelDraw;
use App\Models\WeeklyModelDrawEntry;
use App\Models\WeeklyModelDrawPrize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWeeklyModelDrawTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_prepare_export_and_complete_a_weekly_model_draw(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $model = User::factory()->create([
            'role' => 'model',
            'name' => 'Luna Star',
            'email' => 'luna@example.com',
        ]);
        User::factory()->create([
            'role' => 'model',
            'name' => 'Mia Rose',
            'email' => 'mia@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.weekly-draws.store'), [
                'title' => 'August Week 3 Draw',
                'week_start' => '2026-08-10',
                'week_end' => '2026-08-16',
                'qualification_threshold' => '100.00',
                'notes' => 'OBS spin after weekly recap.',
            ])
            ->assertRedirect();

        $draw = WeeklyModelDraw::query()->where('title', 'August Week 3 Draw')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.weekly-draws.entries.store', $draw), [
                'model_id' => $model->id,
                'earnings' => '125.50',
                'is_qualified' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.weekly-draws.entries.import', $draw), [
                'import_text' => "Name,email,earnings,qualified\nMia Rose,mia@example.com,80.00,no\nImported Winner,imported@example.com,150.00,yes",
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('weekly_model_draw_entries', [
            'weekly_model_draw_id' => $draw->id,
            'user_id' => $model->id,
            'model_name' => 'Luna Star',
            'earnings_cents' => 12550,
            'is_qualified' => true,
        ]);
        $this->assertDatabaseHas('weekly_model_draw_entries', [
            'weekly_model_draw_id' => $draw->id,
            'model_name' => 'Mia Rose',
            'earnings_cents' => 8000,
            'is_qualified' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.weekly-draws.prizes.store', $draw), [
                'name' => 'Cash Bonus',
                'value' => '50.00',
            ])
            ->assertSessionHasNoErrors();

        $export = $this->actingAs($admin)->get(route('admin.weekly-draws.qualified.export', $draw));
        $export->assertOk();
        $export->assertSeeText("Imported Winner\nLuna Star", false);
        $export->assertDontSeeText('Mia Rose');

        $winner = WeeklyModelDrawEntry::query()->where('model_name', 'Imported Winner')->firstOrFail();
        $prize = WeeklyModelDrawPrize::query()->where('name', 'Cash Bonus')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.weekly-draws.complete', $draw), [
                'winner_entry_id' => $winner->id,
                'winning_prize_id' => $prize->id,
                'recording_url' => 'https://example.com/recordings/draw-week-3',
                'drawn_at' => '2026-08-17T20:00',
            ])
            ->assertSessionHasNoErrors();

        $draw->refresh();
        $this->assertSame(WeeklyModelDraw::STATUS_COMPLETED, $draw->status);
        $this->assertSame($winner->id, $draw->winner_entry_id);
        $this->assertSame($prize->id, $draw->winning_prize_id);
        $this->assertSame('https://example.com/recordings/draw-week-3', $draw->recording_url);
    }

    public function test_models_cannot_access_admin_weekly_draws(): void
    {
        $model = User::factory()->create(['role' => 'model']);

        $this->actingAs($model)
            ->get(route('admin.weekly-draws.index'))
            ->assertForbidden();
    }
}
