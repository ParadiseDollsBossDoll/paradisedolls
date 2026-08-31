<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
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

    public const WORKFLOW_IN_PROGRESS = 'in_progress';
    public const WORKFLOW_READY_FOR_REVIEW = 'ready_for_review';
    public const WORKFLOW_APPROVED = 'approved';
    public const WORKFLOW_CLOSED = 'closed';

    private const WORKFLOW_TIMEZONE = 'Europe/London';

    protected $fillable = [
        'user_id', 'period_start', 'period_end', 'status', 'submitted_at', 'reviewed_by', 'reviewed_at',
        'review_note', 'ordinary_minutes', 'break_minutes', 'night_minutes', 'weekend_minutes',
        'overtime_minutes', 'adjustment_pence', 'base_pay_pence', 'commission_pence',
        'foreign_commission_currency', 'foreign_commission_pence', 'foreign_commission_usd_pence',
        'gbp_to_usd_rate', 'gbp_to_usd_rate_date', 'gbp_to_usd_rate_fetched_at', 'gbp_to_usd_rate_provider',
        'gross_pay_pence', 'calculation_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date', 'period_end' => 'date', 'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime', 'calculation_snapshot' => 'array',
            'base_pay_pence' => 'integer', 'commission_pence' => 'integer',
            'foreign_commission_pence' => 'integer', 'foreign_commission_usd_pence' => 'integer',
            'gbp_to_usd_rate' => 'decimal:6',
            'gbp_to_usd_rate_date' => 'date', 'gbp_to_usd_rate_fetched_at' => 'datetime',
            'gross_pay_pence' => 'integer',
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
        return $this->status === self::STATUS_APPROVED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_CHANGES_REQUESTED => 'Changes requested',
            default => ucfirst($this->status),
        };
    }

    public function periodHasEnded(?CarbonInterface $moment = null): bool
    {
        $now = $moment
            ? CarbonImmutable::instance($moment)->timezone(self::WORKFLOW_TIMEZONE)
            : CarbonImmutable::now(self::WORKFLOW_TIMEZONE);
        $periodEnd = CarbonImmutable::parse(
            $this->period_end->toDateString().' 23:59:59',
            self::WORKFLOW_TIMEZONE,
        );

        return $now->greaterThan($periodEnd);
    }

    public function workflowStatus(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => self::WORKFLOW_APPROVED,
            self::STATUS_REJECTED => self::WORKFLOW_CLOSED,
            default => $this->periodHasEnded()
                ? self::WORKFLOW_READY_FOR_REVIEW
                : self::WORKFLOW_IN_PROGRESS,
        };
    }

    public function workflowStatusLabel(): string
    {
        return match ($this->workflowStatus()) {
            self::WORKFLOW_IN_PROGRESS => 'In progress',
            self::WORKFLOW_READY_FOR_REVIEW => 'Ready for review',
            self::WORKFLOW_APPROVED => 'Approved',
            self::WORKFLOW_CLOSED => 'Closed - No payroll',
        };
    }

    public function scopeWhereWorkflowStatus(Builder $query, string $status, ?CarbonInterface $moment = null): Builder
    {
        $today = ($moment
            ? CarbonImmutable::instance($moment)
            : CarbonImmutable::now('UTC'))
            ->timezone(self::WORKFLOW_TIMEZONE)
            ->toDateString();

        return match ($status) {
            self::WORKFLOW_IN_PROGRESS => $query
                ->whereNotIn('status', [self::STATUS_APPROVED, self::STATUS_REJECTED])
                ->whereDate('period_end', '>=', $today),
            self::WORKFLOW_READY_FOR_REVIEW => $query
                ->whereNotIn('status', [self::STATUS_APPROVED, self::STATUS_REJECTED])
                ->whereDate('period_end', '<', $today),
            self::WORKFLOW_APPROVED => $query->where('status', self::STATUS_APPROVED),
            self::WORKFLOW_CLOSED => $query->where('status', self::STATUS_REJECTED),
            default => $query,
        };
    }
}
