<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LessonContentBlock extends Model
{
    public const TYPES = [
        'heading',
        'text',
        'image',
        'gallery',
        'video',
        'canva',
        'pdf_resource',
        'steps',
        'tips',
        'safety',
        'divider',
    ];

    public const LEGACY_TYPE_MAP = [
        'presentation' => 'canva',
        'pdf' => 'pdf_resource',
        'tip' => 'tips',
        'warning' => 'safety',
        'step' => 'steps',
    ];

    protected $fillable = [
        'lesson_id',
        'block_type',
        'title',
        'content',
        'image_path',
        'file_path',
        'bunny_video_id',
        'bunny_library_id',
        'bunny_video_title',
        'bunny_thumbnail_url',
        'bunny_upload_fingerprint',
        'bunny_status',
        'duration',
        'presentation_url',
        'settings',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public static function canonicalType(?string $type): string
    {
        $type = trim((string) $type);
        $type = self::LEGACY_TYPE_MAP[$type] ?? $type;

        return in_array($type, self::TYPES, true) ? $type : 'text';
    }

    public function flowType(): string
    {
        return self::canonicalType($this->block_type);
    }

    public function imageUrl(): ?string
    {
        return $this->publicFileUrl($this->image_path);
    }

    public function fileUrl(): ?string
    {
        return $this->publicFileUrl($this->file_path);
    }

    public function videoEmbedUrl(): ?string
    {
        if (filled($this->bunny_video_id) && filled($this->bunny_library_id)) {
            return 'https://iframe.mediadelivery.net/embed/'.$this->bunny_library_id.'/'.$this->bunny_video_id.'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
        }

        return null;
    }

    public function presentationOpenUrl(): ?string
    {
        return Lesson::normalizePresentationUrl($this->presentation_url);
    }

    public function canvaPresentationEmbedUrl(): ?string
    {
        $url = Lesson::normalizePresentationUrl($this->presentation_url);
        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = '/'.ltrim($parts['path'] ?? '', '/');
        parse_str($parts['query'] ?? '', $query);

        if ($host === 'canva.link') {
            return $url;
        }

        if (! in_array($host, ['canva.com', 'www.canva.com'], true)) {
            return null;
        }

        if (! array_key_exists('embed', $query)) {
            return null;
        }

        if (! preg_match('#^/design/([A-Za-z0-9_-]+)(?:/|$)#', $path)) {
            return null;
        }

        return $url;
    }

    public function hasRenderableContent(): bool
    {
        return match ($this->flowType()) {
            'divider' => true,
            'heading', 'text' => filled($this->title) || filled($this->content),
            'image' => filled($this->image_path),
            'gallery' => $this->galleryImageUrls() !== [],
            'video' => filled($this->bunny_video_id) && filled($this->bunny_library_id),
            'canva' => $this->presentationOpenUrl() !== null,
            'pdf_resource' => $this->fileUrl() !== null,
            'steps', 'tips', 'safety' => $this->contentLines() !== [],
            default => filled($this->title) || filled($this->content),
        };
    }

    public function contentLines(): array
    {
        if (blank($this->content)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', trim($this->content)))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function galleryImageUrls(): array
    {
        return collect($this->settings['gallery_images'] ?? [])
            ->map(fn (?string $path) => $this->publicFileUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    public function galleryCaptions(): array
    {
        $captions = $this->settings['gallery_captions'] ?? '';
        if (blank($captions)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', trim((string) $captions)))
            ->map(fn (string $line) => trim($line))
            ->values()
            ->all();
    }

    public function buttonLabel(string $fallback = 'Open Resource'): string
    {
        $label = trim((string) ($this->settings['button_label'] ?? ''));

        return $label !== '' ? $label : $fallback;
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (preg_match('/^(https?:)?\/\//', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
