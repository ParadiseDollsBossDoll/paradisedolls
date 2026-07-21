<?php

namespace App\Services;

use App\Models\ChatterBreak;
use App\Models\ChatterRoleAssignment;
use App\Models\ChatterShift;
use App\Models\ChatterTimeAudit;
use App\Models\ChatterWorkRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatterClockService
{
    public function __construct(private readonly ChatterPayrollService $payroll) {}

    public function clockIn(User $user, ?int $workRoleId = null): ChatterShift
    {
        return DB::transaction(function () use ($user, $workRoleId) {
            $this->lockAndAssertActive($user);
            $this->assertNoOpenShift($user);
            $assignment = $this->resolveRoleAssignment($user, $workRoleId);
            $now = now('UTC');
            $shift = ChatterShift::create([
                'user_id' => $user->id,
                'active_user_id' => $user->id,
                'chatter_work_role_id' => $assignment->chatter_work_role_id,
                'hourly_rate_pence' => $assignment->hourly_rate_pence,
                'clocked_in_at' => $now,
                'timezone' => $user->chatterProfile->timezone,
            ]);
            $this->audit($shift, $user, 'clocked_in', null, null, [
                'clocked_in_at' => $now->toIso8601String(),
                'work_role_id' => $assignment->chatter_work_role_id,
                'hourly_rate_pence' => $assignment->hourly_rate_pence,
            ]);

            return $shift;
        });
    }

    public function startBreak(User $user): ChatterBreak
    {
        return DB::transaction(function () use ($user) {
            $this->lockAndAssertActive($user);
            $shift = $this->openShift($user, true);

            if ($shift->breaks()->whereNull('ended_at')->exists()) {
                throw ValidationException::withMessages(['shift' => __('A break is already active.')]);
            }

            $now = now('UTC');
            $break = $shift->breaks()->create([
                'active_shift_id' => $shift->id,
                'started_at' => $now,
            ]);
            $this->audit($shift, $user, 'break_started', null, null, ['started_at' => $now->toIso8601String()]);

            return $break;
        });
    }

    public function endBreak(User $user): ChatterBreak
    {
        return DB::transaction(function () use ($user) {
            $this->lockAndAssertActive($user);
            $shift = $this->openShift($user, true);
            $break = $shift->breaks()->whereNull('ended_at')->lockForUpdate()->first();

            if (! $break) {
                throw ValidationException::withMessages(['shift' => __('There is no active break.')]);
            }

            $now = now('UTC');
            $break->forceFill(['ended_at' => $now, 'active_shift_id' => null])->save();
            $this->audit($shift, $user, 'break_ended', null, null, ['ended_at' => $now->toIso8601String()]);

            return $break;
        });
    }

    public function clockOut(User $user): ChatterShift
    {
        $shift = DB::transaction(function () use ($user) {
            $this->lockAndAssertActive($user);
            $shift = $this->openShift($user, true);
            $now = now('UTC');
            $activeBreak = $shift->breaks()->whereNull('ended_at')->lockForUpdate()->first();

            if ($activeBreak) {
                $activeBreak->forceFill(['ended_at' => $now, 'active_shift_id' => null])->save();
                $this->audit($shift, $user, 'break_ended_on_clock_out', null, null, ['ended_at' => $now->toIso8601String()]);
            }

            $shift->forceFill(['clocked_out_at' => $now, 'active_user_id' => null])->save();
            $this->audit($shift, $user, 'clocked_out', null, null, ['clocked_out_at' => $now->toIso8601String()]);

            return $shift->load('user');
        });

        $this->payroll->refreshPeriodsTouchedBy($shift);

        return $shift;
    }

    public function clockOutForLogout(User $user): ?ChatterShift
    {
        if (! $user->isChatter()) {
            return null;
        }

        $shift = DB::transaction(function () use ($user) {
            $shift = ChatterShift::query()
                ->where('user_id', $user->id)
                ->whereNull('clocked_out_at')
                ->with('breaks')
                ->lockForUpdate()
                ->first();

            if (! $shift) {
                return null;
            }

            $now = now('UTC');
            $activeBreak = $shift->breaks()->whereNull('ended_at')->lockForUpdate()->first();

            if ($activeBreak) {
                $activeBreak->forceFill(['ended_at' => $now, 'active_shift_id' => null])->save();
                $this->audit($shift, $user, 'break_ended_on_logout', null, null, ['ended_at' => $now->toIso8601String()]);
            }

            $shift->forceFill(['clocked_out_at' => $now, 'active_user_id' => null])->save();
            $this->audit($shift, $user, 'clocked_out_on_logout', null, null, ['clocked_out_at' => $now->toIso8601String()]);

            return $shift->load('user');
        });

        if ($shift) {
            $this->payroll->refreshPeriodsTouchedBy($shift);
        }

        return $shift;
    }

    private function lockAndAssertActive(User $user): void
    {
        $locked = User::query()->with('chatterProfile')->lockForUpdate()->findOrFail($user->id);

        if (! $locked->isChatter() || ! $locked->chatterProfile?->isActive()) {
            throw ValidationException::withMessages(['shift' => __('This chatter account is not active.')]);
        }

        $user->setRelation('chatterProfile', $locked->chatterProfile);
    }

    private function assertNoOpenShift(User $user): void
    {
        if (ChatterShift::query()->where('user_id', $user->id)->whereNull('clocked_out_at')->exists()) {
            throw ValidationException::withMessages(['shift' => __('You are already clocked in.')]);
        }
    }

    private function resolveRoleAssignment(User $user, ?int $workRoleId): ChatterRoleAssignment
    {
        $query = ChatterRoleAssignment::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('workRole', fn ($roleQuery) => $roleQuery->where('is_active', true))
            ->with('workRole');

        $assignment = $workRoleId
            ? (clone $query)->where('chatter_work_role_id', $workRoleId)->first()
            : (clone $query)->whereHas('workRole', fn ($roleQuery) => $roleQuery->where('slug', 'chatter'))->first();

        if ($workRoleId && ! $assignment) {
            throw ValidationException::withMessages(['work_role_id' => __('This work role is not assigned to your account.')]);
        }

        $assignment ??= $query->first();

        if ($assignment) {
            return $assignment;
        }

        $defaultRole = ChatterWorkRole::query()->firstOrCreate(
            ['slug' => 'chatter'],
            ['name' => 'Chatter', 'is_active' => true, 'sort_order' => 10],
        );
        $baseRatePence = (int) ($user->chatterPayRates()
            ->whereDate('effective_from', '<=', now(ChatterPayrollService::REPORTING_TIMEZONE)->toDateString())
            ->latest('effective_from')
            ->value('base_rate_pence') ?? 0);

        return ChatterRoleAssignment::query()->updateOrCreate(
            ['user_id' => $user->id, 'chatter_work_role_id' => $defaultRole->id],
            ['hourly_rate_pence' => $baseRatePence, 'is_active' => true],
        );
    }

    private function openShift(User $user, bool $lock = false): ChatterShift
    {
        $query = ChatterShift::query()->where('user_id', $user->id)->whereNull('clocked_out_at')->with('breaks');
        $shift = $lock ? $query->lockForUpdate()->first() : $query->first();

        if (! $shift) {
            throw ValidationException::withMessages(['shift' => __('You are not currently clocked in.')]);
        }

        return $shift;
    }

    private function audit(ChatterShift $shift, User $actor, string $action, ?string $reason, ?array $before, ?array $after): void
    {
        ChatterTimeAudit::create([
            'chatter_shift_id' => $shift->id,
            'actor_id' => $actor->id,
            'action' => $action,
            'reason' => $reason,
            'before' => $before,
            'after' => $after,
        ]);
    }
}
