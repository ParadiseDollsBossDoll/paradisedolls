<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class BunnyStreamClient
{
    public function listVideos(?string $search = null, int $page = 1, int $itemsPerPage = 50): array
    {
        $response = $this->request()->get($this->baseUrl().'/videos', [
            'page' => max(1, $page),
            'itemsPerPage' => min(max(1, $itemsPerPage), 100),
            'search' => $search ?? '',
            'orderBy' => 'date',
        ])->throw();

        $data = $response->json();

        return [
            'total' => (int) ($data['totalItems'] ?? 0),
            'page' => (int) ($data['currentPage'] ?? $page),
            'items' => collect($data['items'] ?? [])
                ->map(fn (array $video) => $this->normalizeVideo($video))
                ->values()
                ->all(),
        ];
    }

    public function createVideo(string $title): array
    {
        $response = $this->request()
            ->post($this->baseUrl().'/videos', [
                'title' => $title,
            ])
            ->throw();

        return $this->normalizeVideo($response->json());
    }

    public function getVideo(string $videoId): array
    {
        $response = $this->request()
            ->get($this->baseUrl().'/videos/'.$videoId)
            ->throw();

        return $this->normalizeVideo($response->json());
    }

    public function uploadAuthorization(string $videoId): array
    {
        $libraryId = $this->libraryId();
        $expiresAt = now()->addSeconds(max(3600, $this->uploadSignatureTtl()))->timestamp;
        $signature = hash('sha256', $libraryId.$this->apiKey().$expiresAt.$videoId);

        return [
            'endpoint' => 'https://video.bunnycdn.com/tusupload',
            'library_id' => $libraryId,
            'video_id' => $videoId,
            'expires_at' => $expiresAt,
            'signature' => $signature,
        ];
    }

    public function embedUrl(string $videoId, ?string $libraryId = null): string
    {
        return 'https://iframe.mediadelivery.net/embed/'.($libraryId ?: $this->libraryId()).'/'.$videoId.'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
    }

    public function thumbnailUrl(string $videoId, ?string $thumbnailFileName = null): ?string
    {
        $cdnHostname = $this->cdnHostname();
        if ($cdnHostname === null) {
            return null;
        }

        return 'https://'.Str::of($cdnHostname)->trim('/').'/'.$videoId.'/'.($thumbnailFileName ?: 'thumbnail.jpg');
    }

    public function normalizeVideo(array $video): array
    {
        $videoId = (string) ($video['guid'] ?? $video['videoId'] ?? '');
        $libraryId = (string) ($video['videoLibraryId'] ?? $this->libraryId());
        $length = (int) ($video['length'] ?? 0);
        $thumbnailFileName = $video['thumbnailFileName'] ?? null;

        return [
            'id' => $videoId,
            'library_id' => $libraryId,
            'title' => (string) ($video['title'] ?? 'Untitled Video'),
            'duration_seconds' => $length,
            'duration' => $this->formatDuration($length),
            'thumbnail_url' => $videoId !== '' ? $this->thumbnailUrl($videoId, is_string($thumbnailFileName) ? $thumbnailFileName : null) : null,
            'embed_url' => $videoId !== '' ? $this->embedUrl($videoId, $libraryId) : null,
            'status' => $video['status'] ?? null,
            'encode_progress' => $video['encodeProgress'] ?? null,
        ];
    }

    public function configured(): bool
    {
        return filled(config('services.bunny.library_id'))
            && filled(config('services.bunny.api_key'));
    }

    private function request(): PendingRequest
    {
        if (! $this->configured()) {
            throw new RuntimeException('Bunny Stream is not configured.');
        }

        return Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'AccessKey' => $this->apiKey(),
            ]);
    }

    private function baseUrl(): string
    {
        return 'https://video.bunnycdn.com/library/'.$this->libraryId();
    }

    private function libraryId(): string
    {
        return (string) config('services.bunny.library_id');
    }

    private function apiKey(): string
    {
        return (string) config('services.bunny.api_key');
    }

    private function cdnHostname(): ?string
    {
        $hostname = config('services.bunny.cdn_hostname');

        return filled($hostname) ? (string) $hostname : null;
    }

    private function uploadSignatureTtl(): int
    {
        return (int) config('services.bunny.upload_signature_ttl', 86400);
    }

    private function formatDuration(int $seconds): ?string
    {
        if ($seconds <= 0) {
            return null;
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $remainingSeconds)
            : sprintf('%d:%02d', $minutes, $remainingSeconds);
    }
}
