<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterBreak extends Model
{
    use HasFactory;

    protected $fillable = ['chatter_shift_id', 'active_shift_id', 'started_at', 'ended_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ChatterShift::class, 'chatter_shift_id');
    }
}
