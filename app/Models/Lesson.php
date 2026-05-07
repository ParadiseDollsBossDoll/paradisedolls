<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
                $url = $parts[1] ?? $parts[0];

                return [
                    'label' => $label,
                    'url' => $url,
                ];
            })
            ->filter()
            ->values()
            ->all();
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
}
