<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserSessionService
{
    public function revokeAll(User $user): void
    {
        $this->sessionsFor($user)?->delete();
        $this->terminateRealtimeConnections($user);
    }

    public function revokeOthers(User $user, ?string $currentSessionId): void
    {
        $query = $this->sessionsFor($user);

        if ($query) {
            if (filled($currentSessionId)) {
                $query->where('id', '!=', $currentSessionId);
            }

            $query->delete();
        }

        $this->terminateRealtimeConnections($user);
    }

    private function sessionsFor(User $user): Builder
    {
        return DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getKey());
    }

    private function terminateRealtimeConnections(User $user): void
    {
        try {
            $broadcaster = app(BroadcastManager::class)->connection();

            if ($broadcaster instanceof PusherBroadcaster) {
                $broadcaster->getPusher()->terminateUserConnections((string) $user->getKey());
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
