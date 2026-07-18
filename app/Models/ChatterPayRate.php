<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterPayRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'base_rate_pence', 'overtime_threshold_minutes', 'overtime_multiplier_bps',
        'night_premium_bps', 'weekend_premium_bps', 'night_starts_at', 'night_ends_at',
        'effective_from', 'created_by',
    ];

    protected function casts(): array
    {
        return ['effective_from' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
