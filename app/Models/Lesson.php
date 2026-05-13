<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'course_module_id',
        'title',
        'body',
        'overview',
        'steps',
        'tips',
        'safety_notes',
        'resource_links',
        'lesson_banner_image',
        'lesson_images',
        'is_published',
        'video_url',
        'bunny_video_id',
        'bunny_library_id',
        'bunny_video_title',
        'bunny_thumbnail_url',
        'bunny_upload_fingerprint',
        'bunny_status',
        'duration',
        'has_pdf',
        'pdf_url',
        'presentation_url',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'has_pdf' => 'boolean',
            'lesson_images' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function scopePublishedForMembers(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('course_module_id')
                    ->orWhereHas('module', fn (Builder $module) => $module->where('is_published', true));
            });
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function contentBlocks(): HasMany
    {
        return $this->hasMany(LessonContentBlock::class)->orderBy('sort_order');
    }

    public function videoEmbedUrl(): ?string
    {
        if (filled($this->bunny_video_id) && filled($this->bunny_library_id)) {
            return 'https://iframe.mediadelivery.net/embed/'.$this->bunny_library_id.'/'.$this->bunny_video_id.'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
        }

        return $this->video_url;
    }

    public function bunnyVideoPayload(): array
    {
        return [
            'id' => $this->bunny_video_id,
            'library_id' => $this->bunny_library_id,
            'title' => $this->bunny_video_title ?: $this->title,
            'duration' => $this->duration,
            'duration_seconds' => null,
            'thumbnail_url' => $this->bunny_thumbnail_url,
            'embed_url' => $this->videoEmbedUrl(),
            'status' => $this->bunny_status,
            'encode_progress' => null,
        ];
    }

    public function isCompletedBy(User $user): bool
    {
        return LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $this->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public function stepItems(): array
    {
        return $this->linesFromText($this->steps);
    }

    public function tipItems(): array
    {
        return $this->linesFromText($this->tips);
    }

    public function safetyItems(): array
    {
        return $this->linesFromText($this->safety_notes);
    }

    public function resourceItems(): array
    {
        if (blank($this->resource_links)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', trim($this->resource_links)))
            ->map(function (string $line): ?array {
                $line = trim($line);
                if ($line === '') {
                    return null;
                }

                $parts = array_map('trim', explode('|', $line, 2));
                $label = $parts[0];
                $url = self::normalizedHttpUrl($parts[1] ?? $parts[0]);

                if ($url === null) {
                    return null;
                }

                return [
                    'label' => $label !== '' ? $label : $url,
                    'url' => $url,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function presentationOpenUrl(): ?string
    {
        return self::normalizePresentationUrl($this->presentation_url);
    }

    public function canvaPresentationEmbedUrl(): ?string
    {
        return null;
    }

    public static function normalizePresentationUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/<iframe\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $value, $matches)) {
            $value = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
        }

        return self::normalizedHttpUrl($value);
    }

    public function lessonBannerImageUrl(): ?string
    {
        return $this->publicImageUrl($this->lesson_banner_image);
    }

    public function lessonImageUrls(): array
    {
        return collect($this->lesson_images ?? [])
            ->map(fn (?string $path) => $this->publicImageUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    private static function normalizedHttpUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, 'www.') || preg_match('/^canva\.(com|link)\//i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        if ($parts === false || blank($parts['scheme'] ?? null) || blank($parts['host'] ?? null)) {
            return null;
        }

        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    private function linesFromText(?string $text): array
    {
        if (blank($text)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', trim($text)))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function publicImageUrl(?string $path): ?string
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
