<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyModelDrawPrize extends Model
{
    protected $fillable = [
        'weekly_model_draw_id',
        'name',
        'value_cents',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'value_cents' => 'integer',
        ];
    }

    public function draw(): BelongsTo
    {
        return $this->belongsTo(WeeklyModelDraw::class, 'weekly_model_draw_id');
    }
}
