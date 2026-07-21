<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chatter_shifts')
            || ! Schema::hasTable('chatter_breaks')
            || ! Schema::hasTable('chatter_time_audits')) {
            return;
        }

        DB::table('chatter_shifts')->orderBy('id')->eachById(function (object $shift): void {
            $audits = DB::table('chatter_time_audits')
                ->where('chatter_shift_id', $shift->id)
                ->orderBy('id')
                ->get(['action', 'before', 'after']);

            $clockedInAt = null;
            $clockedOutAt = null;
            $breakPeriods = [];
            $openBreak = null;

            foreach ($audits as $audit) {
                $before = $this->payload($audit->before);
                $after = $this->payload($audit->after);

                if ($audit->action === 'clocked_in') {
                    $clockedInAt = $this->utcDateTime($after['clocked_in_at'] ?? null) ?? $clockedInAt;
                } elseif (in_array($audit->action, ['clocked_out', 'clocked_out_on_logout', 'clocked_out_on_suspension'], true)) {
                    $clockedOutAt = $this->utcDateTime($after['clocked_out_at'] ?? null) ?? $clockedOutAt;
                } elseif ($audit->action === 'shift_corrected') {
                    $clockedInAt = $this->utcDateTime($after['clocked_in_at'] ?? null) ?? $clockedInAt;
                    $clockedOutAt = $this->utcDateTime($after['clocked_out_at'] ?? null) ?? $clockedOutAt;
                } elseif ($audit->action === 'break_started') {
                    $breakPeriods[] = [
                        'started_at' => $this->utcDateTime($after['started_at'] ?? null),
                        'ended_at' => null,
                    ];
                    $openBreak = array_key_last($breakPeriods);
                } elseif (in_array($audit->action, ['break_ended', 'break_ended_on_clock_out', 'break_ended_on_logout', 'break_ended_on_suspension'], true) && $openBreak !== null) {
                    $breakPeriods[$openBreak]['ended_at'] = $this->utcDateTime($after['ended_at'] ?? null);
                    $openBreak = null;
                } elseif ($audit->action === 'break_corrected' && $breakPeriods !== []) {
                    $periodIndex = $this->matchingBreakIndex($breakPeriods, $before) ?? array_key_last($breakPeriods);
                    $breakPeriods[$periodIndex] = [
                        'started_at' => $this->utcDateTime($after['started_at'] ?? null) ?? $breakPeriods[$periodIndex]['started_at'],
                        'ended_at' => $this->utcDateTime($after['ended_at'] ?? null) ?? $breakPeriods[$periodIndex]['ended_at'],
                    ];
                }
            }

            $shiftUpdate = array_filter([
                'clocked_in_at' => $clockedInAt,
                'clocked_out_at' => $clockedOutAt,
            ], fn (?string $value): bool => $value !== null);

            if ($shiftUpdate !== []) {
                DB::table('chatter_shifts')->where('id', $shift->id)->update($shiftUpdate);
            }

            $breakRows = DB::table('chatter_breaks')
                ->where('chatter_shift_id', $shift->id)
                ->orderBy('id')
                ->get(['id']);

            foreach ($breakRows as $index => $breakRow) {
                $period = $breakPeriods[$index] ?? null;
                if (! $period || $period['started_at'] === null) {
                    continue;
                }

                $update = ['started_at' => $period['started_at']];
                if ($period['ended_at'] !== null) {
                    $update['ended_at'] = $period['ended_at'];
                }

                DB::table('chatter_breaks')->where('id', $breakRow->id)->update($update);
            }
        });
    }

    public function down(): void
    {
        // Data repair is intentionally irreversible.
    }

    private function payload(?string $value): array
    {
        $decoded = $value ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private function utcDateTime(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->utc()->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function matchingBreakIndex(array $periods, array $before): ?int
    {
        $beforeStart = $this->utcDateTime($before['started_at'] ?? null);
        $beforeEnd = $this->utcDateTime($before['ended_at'] ?? null);

        foreach ($periods as $index => $period) {
            if (($beforeEnd && $period['ended_at'] === $beforeEnd)
                || ($beforeStart && $period['started_at'] === $beforeStart)) {
                return $index;
            }
        }

        return null;
    }
};
