<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CommunityPresence
{
    private const CACHE_TTL_MINUTES = 5;

    private const ONLINE_WINDOW_SECONDS = 150;

    private const TYPING_WINDOW_SECONDS = 6;

    private const DIRECTORY_CACHE_KEY = 'community:member-directory';

    private const DIRECTORY_CACHE_SECONDS = 300;

    public static function ping(User $user): void
    {
        self::store()->put(self::cacheKey($user->id), now()->timestamp, now()->addMinutes(self::CACHE_TTL_MINUTES));
    }

    public static function payloadFor(User $viewer, ?int $channelId = null, bool $summaryOnly = false): array
    {
        $online = [];
        $offline = [];

        $users = self::memberDirectory()
            ->sortBy(fn (User $user) => ($user->id === $viewer->id ? '0-' : '1-').mb_strtolower($user->name))
            ->values();
        $presenceKeys = $users
            ->mapWithKeys(fn (User $user) => [$user->id => self::cacheKey($user->id)])
            ->all();
        $lastSeen = self::store()->many(array_values($presenceKeys));
        $onlineThreshold = now()->subSeconds(self::ONLINE_WINDOW_SECONDS)->timestamp;

        foreach ($users as $user) {
            $cachedLastSeen = $lastSeen[$presenceKeys[$user->id]] ?? null;
            $isOnline = $user->id === $viewer->id
                || (is_numeric($cachedLastSeen) && (int) $cachedLastSeen >= $onlineThreshold);
            $payload = $user->toCommunityMemberArray($isOnline, $viewer->id === $user->id);

            if ($isOnline) {
                $online[] = $payload;
            } elseif (! $summaryOnly) {
                $offline[] = $payload;
            }
        }

        $offlineCount = max(0, $users->count() - count($online));

        return [
            'online'        => $online,
            'offline'       => $summaryOnly ? [] : array_slice($offline, 0, 50),
            'offline_count' => $offlineCount,
            'total'         => count($online) + $offlineCount,
            'typing'        => $channelId ? self::typingUsersFor($viewer, $channelId) : [],
        ];
    }

    public static function setTyping(User $user, int $channelId, bool $typing): void
    {
        $cacheKey = self::typingCacheKey($channelId, $user->id);

        if (! $typing) {
            self::store()->forget($cacheKey);

            return;
        }

        self::store()->put(
            $cacheKey,
            [
                'user_id' => $user->id,
                'channel_id' => $channelId,
                'updated_at' => now()->timestamp,
            ],
            now()->addSeconds(self::TYPING_WINDOW_SECONDS)
        );
    }

    public static function typingUsersFor(User $viewer, int $channelId): array
    {
        $members = self::memberDirectory()->where('id', '!=', $viewer->id)->values();
        $typingKeys = $members
            ->mapWithKeys(fn (User $user) => [$user->id => self::typingCacheKey($channelId, $user->id)])
            ->all();
        $typingPayloads = self::store()->many(array_values($typingKeys));
        $typingThreshold = now()->subSeconds(self::TYPING_WINDOW_SECONDS)->timestamp;

        return $members
            ->filter(function (User $user) use ($typingKeys, $typingPayloads, $typingThreshold) {
                $payload = $typingPayloads[$typingKeys[$user->id]] ?? null;

                return is_array($payload)
                    && (int) ($payload['updated_at'] ?? 0) >= $typingThreshold;
            })
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'initials' => $user->initials(),
            ])
            ->values()
            ->all();
    }

    public static function isOnline(User $user): bool
    {
        $lastSeen = self::store()->get(self::cacheKey($user->id));

        return is_numeric($lastSeen) && (int) $lastSeen >= now()->subSeconds(self::ONLINE_WINDOW_SECONDS)->timestamp;
    }

    private static function cacheKey(int $userId): string
    {
        return "community:presence:{$userId}";
    }

    private static function memberDirectory()
    {
        return self::store()->remember(
            self::DIRECTORY_CACHE_KEY,
            now()->addSeconds(self::DIRECTORY_CACHE_SECONDS),
            fn () => User::query()
                ->whereIn('role', ['admin', 'moderator', 'model'])
                ->get(['id', 'name', 'role'])
        );
    }

    private static function store()
    {
        return Cache::store(config('community.performance.presence_cache_store', config('cache.default')));
    }

    private static function typingCacheKey(int $channelId, int $userId): string
    {
        return "community:typing:{$channelId}:{$userId}";
    }
}
