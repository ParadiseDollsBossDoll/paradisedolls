<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterModelAssignment extends Model
{
    protected $fillable = [
        'chatter_id',
        'model_id',
        'assigned_by',
        'assigned_at',
        'ended_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function chatter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chatter_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(User::class, 'model_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
