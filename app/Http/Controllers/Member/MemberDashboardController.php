<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\View\View;

class MemberDashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $profile = $user->modelProfile()->firstOrCreate([]);

        $courses = Course::query()
            ->where('is_published', true)
            ->with(['lessons' => fn ($query) => $query->orderBy('sort_order')])
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->all();

        $totalLessons = $courses->sum('lessons_count');
        $completedLessons = $courses
            ->flatMap->lessons
            ->whereIn('id', $completedLessonIds)
            ->count();

        $courseProgress = $courses->mapWithKeys(function (Course $course) use ($completedLessonIds) {
            $completed = $course->lessons->whereIn('id', $completedLessonIds)->count();
            $total = $course->lessons->count();

            return [$course->id => [
                'completed' => $completed,
                'total' => $total,
                'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            ]];
        });

        $overallPercent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
        $inProgressCount = $courseProgress->filter(fn ($progress) => $progress['completed'] > 0 && $progress['completed'] < $progress['total'])->count();
        $completedCoursesCount = $courseProgress->filter(fn ($progress) => $progress['total'] > 0 && $progress['completed'] === $progress['total'])->count();
        $notStartedCount = $courseProgress->filter(fn ($progress) => $progress['completed'] === 0)->count();

        return view('member.dashboard', compact(
            'courses',
            'courseProgress',
            'overallPercent',
            'completedLessons',
            'totalLessons',
            'inProgressCount',
            'completedCoursesCount',
            'notStartedCount',
            'profile'
        ));
    }
}
