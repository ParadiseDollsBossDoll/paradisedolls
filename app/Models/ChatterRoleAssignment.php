<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatterRoleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'chatter_work_role_id', 'hourly_rate_pence', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workRole(): BelongsTo
    {
        return $this->belongsTo(ChatterWorkRole::class, 'chatter_work_role_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
