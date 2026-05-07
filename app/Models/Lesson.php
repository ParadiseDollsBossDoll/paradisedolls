<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'body',
        'video_url',
        'duration',
        'has_pdf',
        'pdf_url',
        'presentation_url',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'has_pdf' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function isCompletedBy(User $user): bool
    {
        return LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $this->id)
            ->whereNotNull('completed_at')
            ->exists();
    }
}
