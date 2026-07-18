<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatterTimesheet extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id', 'period_start', 'period_end', 'status', 'submitted_at', 'reviewed_by', 'reviewed_at',
        'review_note', 'ordinary_minutes', 'break_minutes', 'night_minutes', 'weekend_minutes',
        'overtime_minutes', 'adjustment_pence', 'gross_pay_pence', 'calculation_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date', 'period_end' => 'date', 'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime', 'calculation_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(ChatterPayAdjustment::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ChatterTimeAudit::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_APPROVED], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_CHANGES_REQUESTED => 'Changes requested',
            default => ucfirst($this->status),
        };
    }
}
