<?php

namespace App\Support;

use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Collection;

class MemberExperienceData
{
    public static function build(User $user): array
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->with(['lessons' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->all();

        $completedLookup = array_fill_keys($completedLessonIds, true);
        $palette = ['#C9A96E', '#FF8C00', '#FF3E4D', '#E91E8C', '#00AFF0', '#FF6B35', '#C4687A'];

        $courseCards = $courses->values()->map(function (Course $course, int $index) use ($completedLookup, $palette) {
            $lessons = $course->lessons;
            $totalLessons = $lessons->count();
            $completedLessons = $lessons->filter(fn ($lesson) => isset($completedLookup[$lesson->id]))->count();
            $percent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
            $nextLesson = $lessons->first(fn ($lesson) => ! isset($completedLookup[$lesson->id]));
            $accent = $palette[$index % count($palette)];

            return [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'platform' => $course->platform_label ?: __('Academy'),
                'description' => $course->description ?: __('Training and guided lessons for the ParadiseDollz academy.'),
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'percent' => $percent,
                'accent' => $accent,
                'status' => $percent === 100 ? 'done' : ($percent > 0 ? 'progress' : 'new'),
                'next_lesson' => $nextLesson?->title,
            ];
        });

        $totalLessons = $courseCards->sum('total_lessons');
        $completedLessons = $courseCards->sum('completed_lessons');

        return [
            'courses' => $courseCards,
            'stats' => [
                'overall_progress' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'total_courses' => $courseCards->count(),
                'in_progress_courses' => $courseCards->where('status', 'progress')->count(),
                'completed_courses' => $courseCards->where('status', 'done')->count(),
                'new_courses' => $courseCards->where('status', 'new')->count(),
            ],
        ];
    }

    public static function featuredCourses(array $experience, int $limit = 2): Collection
    {
        return collect($experience['courses'])
            ->sortByDesc(fn (array $course) => $course['status'] === 'progress' ? 2 : ($course['status'] === 'new' ? 1 : 0))
            ->sortByDesc('percent')
            ->take($limit)
            ->values();
    }
}
