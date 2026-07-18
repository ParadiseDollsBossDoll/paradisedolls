<?php

namespace App\Services;

use App\Models\ChatterBreak;
use App\Models\ChatterProfile;
use App\Models\ChatterShift;
use App\Models\ChatterTimeAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatterClockService
{
    public function __construct(private readonly ChatterPayrollService $payroll) {}

    public function clockIn(User $user): ChatterShift
    {
        return DB::transaction(function () use ($user) {
            $this->lockAndAssertActive($user);
            $this->assertNoOpenShift($user);
            $now = now('UTC')->startOfMinute();
            $shift = ChatterShift::create([
                'user_id' => $user->id,
                'active_user_id' => $user->id,
                'clocked_in_at' => $now,
                'timezone' => $user->chatterProfile->timezone,
            ]);
            $this->audit($shift, $user, 'clocked_in', null, null, ['clocked_in_at' => $now->toIso8601String()]);

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

            $now = now('UTC')->startOfMinute();
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

            $now = now('UTC')->startOfMinute();
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
            $now = now('UTC')->startOfMinute();
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
