<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyModelDrawEntry extends Model
{
    protected $fillable = [
        'weekly_model_draw_id',
        'user_id',
        'model_name',
        'model_email',
        'earnings_cents',
        'is_qualified',
        'qualification_note',
    ];

    protected function casts(): array
    {
        return [
            'earnings_cents' => 'integer',
            'is_qualified' => 'boolean',
        ];
    }

    public function draw(): BelongsTo
    {
        return $this->belongsTo(WeeklyModelDraw::class, 'weekly_model_draw_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
