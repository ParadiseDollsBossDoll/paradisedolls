<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterTimeAudit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['chatter_shift_id', 'chatter_timesheet_id', 'actor_id', 'action', 'reason', 'before', 'after'];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ChatterShift::class, 'chatter_shift_id');
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(ChatterTimesheet::class, 'chatter_timesheet_id');
    }
}
