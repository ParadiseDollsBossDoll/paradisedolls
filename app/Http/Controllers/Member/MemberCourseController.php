<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberCourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->with(['lessons' => fn ($q) => $q->select('id', 'course_id', 'title', 'sort_order')->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->withCount('lessons')
            ->get();

        $progressPercents = Course::batchProgressPercentsForUser(auth()->user(), $courses);

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($lessonId) => (int) $lessonId)
            ->all();

        $courseProgress = $courses->mapWithKeys(function (Course $course) use ($completedLessonIds, $progressPercents) {
            $completed = $course->lessons->whereIn('id', $completedLessonIds)->count();
            $total = $course->lessons->count();
            $isDone = $total > 0 && $completed === $total;

            return [$course->id => [
                'completed' => $completed,
                'total' => $total,
                'percent' => $progressPercents[$course->id] ?? 0,
                'status' => $isDone ? 'completed' : ($completed > 0 ? 'in-progress' : 'new'),
            ]];
        });

        $filter = $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'new', 'in-progress', 'completed'], true)) {
            $filter = 'all';
        }

        $filteredCourses = $filter === 'all'
            ? $courses
            : $courses->filter(fn (Course $course) => ($courseProgress[$course->id]['status'] ?? 'new') === $filter)->values();

        return view('member.courses.index', compact(
            'courses',
            'filteredCourses',
            'courseProgress',
            'completedLessonIds',
            'filter'
        ));
    }

    public function show(string $slug): View
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $course->load(['lessons' => fn ($q) => $q->orderBy('sort_order')]);

        $messages = $course->chatMessages()
            ->with('user:id,name')
            ->latest()
            ->take(100)
            ->get()
            ->sortBy('created_at');

        $percent = $course->progressPercentFor(auth()->user());

        return view('member.courses.show', compact('course', 'messages', 'percent'));
    }
}
