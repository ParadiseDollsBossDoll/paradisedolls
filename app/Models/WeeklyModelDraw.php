<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyModelDraw extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_READY = 'ready';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'title',
        'week_start',
        'week_end',
        'status',
        'qualification_threshold_cents',
        'notes',
        'recording_url',
        'drawn_at',
        'created_by',
        'completed_by',
        'winner_entry_id',
        'winning_prize_id',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'drawn_at' => 'datetime',
            'qualification_threshold_cents' => 'integer',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(WeeklyModelDrawEntry::class);
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(WeeklyModelDrawPrize::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function winnerEntry(): BelongsTo
    {
        return $this->belongsTo(WeeklyModelDrawEntry::class, 'winner_entry_id');
    }

    public function winningPrize(): BelongsTo
    {
        return $this->belongsTo(WeeklyModelDrawPrize::class, 'winning_prize_id');
    }

    public function qualifiedNames(): array
    {
        return $this->entries
            ->where('is_qualified', true)
            ->sortBy('model_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->pluck('model_name')
            ->filter()
            ->values()
            ->all();
    }
}
