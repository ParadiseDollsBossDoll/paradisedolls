<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatterShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'active_user_id', 'chatter_work_role_id', 'hourly_rate_pence',
        'clocked_in_at', 'clocked_out_at', 'timezone', 'note',
    ];

    protected function casts(): array
    {
        return ['clocked_in_at' => 'datetime', 'clocked_out_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workRole(): BelongsTo
    {
        return $this->belongsTo(ChatterWorkRole::class, 'chatter_work_role_id');
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(ChatterBreak::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ChatterTimeAudit::class);
    }

    public function isOpen(): bool
    {
        return $this->clocked_out_at === null;
    }
}
