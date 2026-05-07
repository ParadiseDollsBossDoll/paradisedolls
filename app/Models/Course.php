<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'platform_label',
        'platform_color',
        'description',
        'short_description',
        'thumbnail_url',
        'difficulty_level',
        'estimated_duration',
        'what_you_will_learn',
        'requirements',
        'has_course_outline',
        'course_outline_url',
        'has_intro',
        'intro_title',
        'intro_video_url',
        'intro_bunny_video_id',
        'intro_bunny_library_id',
        'intro_bunny_video_title',
        'intro_bunny_thumbnail_url',
        'intro_bunny_upload_fingerprint',
        'intro_bunny_status',
        'intro_duration',
        'intro_body',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'has_course_outline' => 'boolean',
            'has_intro' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Course $course): void {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });

        static::created(function (Course $course): void {
            $course->chatRoom()->firstOrCreate([], [
                'name' => $course->title.' Community',
            ]);
        });
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function publishedLessons(): HasMany
    {
        return $this->hasMany(Lesson::class)
            ->publishedForMembers()
            ->orderBy('sort_order');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    public function publishedModules(): HasMany
    {
        return $this->hasMany(CourseModule::class)
            ->where('is_published', true)
            ->orderBy('sort_order');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(CourseChatMessage::class)->orderBy('created_at');
    }

    public function chatRoom(): HasOne
    {
        return $this->hasOne(ChatRoom::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    public function isEnrolledBy(User $user): bool
    {
        return $this->enrollments()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function introVideoEmbedUrl(): ?string
    {
        if (filled($this->intro_bunny_video_id) && filled($this->intro_bunny_library_id)) {
            return 'https://iframe.mediadelivery.net/embed/'.$this->intro_bunny_library_id.'/'.$this->intro_bunny_video_id.'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
        }

        return $this->intro_video_url;
    }

    public function introBunnyVideoPayload(): array
    {
        return [
            'id' => $this->intro_bunny_video_id,
            'library_id' => $this->intro_bunny_library_id,
            'title' => $this->intro_bunny_video_title ?: ($this->intro_title ?: $this->title),
            'duration' => $this->intro_duration,
            'duration_seconds' => null,
            'thumbnail_url' => $this->intro_bunny_thumbnail_url,
            'embed_url' => $this->introVideoEmbedUrl(),
            'status' => $this->intro_bunny_status,
            'encode_progress' => null,
        ];
    }

    public function progressPercentFor(User $user): int
    {
        $total = $this->publishedLessons()->count();
        if ($total === 0) {
            return 0;
        }

        $lessonIds = $this->publishedLessons()->pluck('id');

        $completed = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->count();

        return (int) round(($completed / $total) * 100);
    }

    public function enrollmentFor(User $user): ?CourseEnrollment
    {
        return $this->enrollments()
            ->where('user_id', $user->id)
            ->first();
    }

    public function overviewImageUrl(): ?string
    {
        return $this->thumbnail_url
            ?: $this->intro_bunny_thumbnail_url
            ?: $this->lessons->firstWhere('bunny_thumbnail_url')?->bunny_thumbnail_url;
    }

    public function learningPoints(): array
    {
        return $this->linesFromText($this->what_you_will_learn);
    }

    public function requirementItems(): array
    {
        return $this->linesFromText($this->requirements);
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

    public function displayPlatform(): string
    {
        return $this->platform_label ?: 'General';
    }

    public function displayColor(): string
    {
        return $this->platform_color ?: match (strtolower(trim($this->displayPlatform()))) {
            'chaturbate' => '#FF8C00',
            'stripchat' => '#FF3E4D',
            'babestation' => '#E91E8C',
            'livejasmin' => '#FF6B35',
            'onlyfans' => '#00AFF0',
            'fansly' => '#9B6DFF',
            'bongacams' => '#FF4444',
            'cam4' => '#22C55E',
            'camsoda' => '#06B6D4',
            'myfreecams', 'mfc' => '#8B5CF6',
            'flirt4free' => '#F472B6',
            'streamate' => '#F59E0B',
            'instagram' => '#E1306C',
            'tiktok' => '#FF0050',
            default => '#C9A96E',
        };
    }

    public function displayColorBackground(float $alpha = 0.13): string
    {
        $hex = ltrim($this->displayColor(), '#');

        if (strlen($hex) !== 6) {
            return 'rgba(201,169,110,'.$alpha.')';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r},{$g},{$b},{$alpha})";
    }

    /**
     * Batch completion percents for one member across many courses (avoids N+1 queries).
     *
     * @param  iterable<int, Course>  $courses  Published courses with lessons eager-loaded and `lessons_count`.
     * @return array<int, int> course id => 0-100
     */
    public static function batchProgressPercentsForUser(User $user, iterable $courses): array
    {
        $coursesCollection = Collection::make($courses);
        if ($coursesCollection->isEmpty()) {
            return [];
        }

        $lessonToCourse = [];
        $allLessonIds = [];
        foreach ($coursesCollection as $course) {
            foreach ($course->lessons->where('is_published', true) as $lesson) {
                $lessonToCourse[$lesson->id] = $course->id;
                $allLessonIds[$lesson->id] = true;
            }
        }
        $allLessonIds = array_keys($allLessonIds);

        $result = [];
        foreach ($coursesCollection as $course) {
            $result[$course->id] = 0;
        }

        if ($allLessonIds === []) {
            return $result;
        }

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $allLessonIds)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        $completedCountByCourse = [];
        foreach ($completedLessonIds as $lessonId) {
            $courseId = $lessonToCourse[$lessonId] ?? null;
            if ($courseId !== null) {
                $completedCountByCourse[$courseId] = ($completedCountByCourse[$courseId] ?? 0) + 1;
            }
        }

        foreach ($coursesCollection as $course) {
            $total = (int) ($course->lessons_count ?? $course->lessons->where('is_published', true)->count());
            $completed = $completedCountByCourse[$course->id] ?? 0;
            $result[$course->id] = $total === 0 ? 0 : (int) round(($completed / $total) * 100);
        }

        return $result;
    }
}
