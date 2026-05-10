<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\CourseCommunityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberCourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->with([
                'lessons' => fn ($q) => $q
                    ->publishedForMembers()
                    ->select('id', 'course_id', 'course_module_id', 'title', 'sort_order', 'is_published')
                    ->orderBy('sort_order'),
            ])
            ->withCount([
                'publishedLessons as lessons_count',
                'publishedModules as modules_count',
                'enrollments as enrolled_users_count',
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $progressPercents = Course::batchProgressPercentsForUser($request->user(), $courses);

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $request->user()->id)
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

        $enrolledCourseIds = $request->user()
            ->enrolledCourses()
            ->pluck('courses.id')
            ->map(fn ($courseId) => (int) $courseId)
            ->all();

        return view('member.courses.index', compact(
            'courses',
            'filteredCourses',
            'courseProgress',
            'completedLessonIds',
            'enrolledCourseIds',
            'filter'
        ));
    }

    public function learn(Request $request, string $slug, CourseCommunityService $community): RedirectResponse
    {
        $course = $this->publishedCourse($slug);

        DB::transaction(function () use ($course, $request): void {
            $course->chatRoom()->firstOrCreate([], [
                'name' => $course->title.' Community',
            ]);

            $course->enrollments()->firstOrCreate(
                ['user_id' => $request->user()->id],
                ['enrolled_at' => now()]
            );
        });

        $community->ensureForCourse($course);

        $course = $this->learningCourse($slug);
        $lesson = $this->resumeLesson($course, $request->user()->id);

        if ($lesson !== null) {
            return redirect()
                ->route('member.courses.lessons.show', [$course->slug, $lesson])
                ->with('status', __('You are enrolled in this course.'));
        }

        return redirect()
            ->route('member.courses.learn.show', $course->slug)
            ->with('status', __('You are enrolled in this course.'));
    }

    public function show(Request $request, string $slug): View
    {
        $course = $this->overviewCourse($slug);
        $isEnrolled = $course->isEnrolledBy($request->user());
        $progress = $this->courseProgress($course, $request->user()->id);
        $resumeLesson = $this->resumeLesson($course, $request->user()->id);
        $communityChannel = $course->communityChannels()
            ->where('is_archived', false)
            ->first();

        return view('member.courses.show', compact(
            'course',
            'isEnrolled',
            'progress',
            'resumeLesson',
            'communityChannel'
        ));
    }

    public function learnShow(Request $request, string $slug): View
    {
        $course = $this->learningCourse($slug);
        $selectedLesson = $this->resumeLesson($course, $request->user()->id);

        return $this->learningView($request, $course, $selectedLesson);
    }

    public function lesson(Request $request, string $slug, Lesson $lesson): View
    {
        $course = $this->learningCourse($slug);

        abort_unless($lesson->course_id === $course->id && $lesson->is_published, 404);

        return $this->learningView($request, $course, $lesson);
    }

    public function community(Request $request, string $slug): View
    {
        $course = $this->overviewCourse($slug);
        $chatRoom = $course->chatRoom()->firstOrCreate([], [
            'name' => $course->title.' Community',
        ]);

        $messages = $chatRoom->messages()
            ->with('user:id,name')
            ->latest()
            ->take(100)
            ->get()
            ->sortBy('created_at');

        $progress = $this->courseProgress($course, $request->user()->id);

        return view('member.courses.community', compact('course', 'messages', 'progress'));
    }

    private function overviewCourse(string $slug): Course
    {
        return Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'lessons' => fn ($query) => $query
                    ->publishedForMembers()
                    ->with(['module', 'contentBlocks'])
                    ->orderBy('sort_order'),
                'modules' => fn ($query) => $query
                    ->where('is_published', true)
                    ->with(['lessons' => fn ($lessonQuery) => $lessonQuery
                        ->publishedForMembers()
                        ->with('contentBlocks')
                        ->orderBy('sort_order')])
                    ->orderBy('sort_order'),
                'chatRoom',
            ])
            ->withCount([
                'publishedLessons as lessons_count',
                'publishedModules as modules_count',
                'enrollments as enrolled_users_count',
            ])
            ->firstOrFail();
    }

    private function learningCourse(string $slug): Course
    {
        return $this->overviewCourse($slug);
    }

    private function publishedCourse(string $slug): Course
    {
        return Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
    }

    private function learningView(Request $request, Course $course, ?Lesson $selectedLesson): View
    {
        $progress = $this->courseProgress($course, $request->user()->id);
        $lessonIds = $course->lessons->pluck('id')->values();
        $selectedLesson ??= $course->lessons->first();
        if ($selectedLesson !== null) {
            $selectedLesson = $course->lessons->firstWhere('id', $selectedLesson->id)
                ?: $selectedLesson->loadMissing('contentBlocks');
        }
        $selectedIndex = $selectedLesson ? $lessonIds->search($selectedLesson->id) : false;
        $previousLesson = $selectedIndex !== false && $selectedIndex > 0
            ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex - 1])
            : null;
        $nextLesson = $selectedIndex !== false && $selectedIndex < $lessonIds->count() - 1
            ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex + 1])
            : null;

        $moduleProgress = $course->modules->mapWithKeys(function ($module) use ($progress) {
            $moduleLessonIds = $module->lessons->pluck('id');
            $total = $moduleLessonIds->count();
            $completed = $moduleLessonIds
                ->filter(fn ($lessonId) => in_array((int) $lessonId, $progress['completedLessonIds'], true))
                ->count();

            return [$module->id => [
                'completed' => $completed,
                'total' => $total,
                'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            ]];
        });

        $messages = $course->chatRoom?->messages()
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get()
            ->sortBy('created_at') ?? collect();

        $communityChannel = $course->communityChannels()
            ->where('is_archived', false)
            ->first();

        return view('member.courses.learn', compact(
            'course',
            'selectedLesson',
            'previousLesson',
            'nextLesson',
            'progress',
            'moduleProgress',
            'messages',
            'communityChannel'
        ));
    }

    private function resumeLesson(Course $course, int $userId): ?Lesson
    {
        if ($course->lessons->isEmpty()) {
            return null;
        }

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($lessonId) => (int) $lessonId)
            ->all();

        return $course->lessons
            ->first(fn (Lesson $lesson) => ! in_array($lesson->id, $completedLessonIds, true))
            ?: $course->lessons->last();
    }

    private function courseProgress(Course $course, int $userId): array
    {
        $lessonIds = $course->lessons->pluck('id');
        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($lessonId) => (int) $lessonId)
            ->all();

        $total = $lessonIds->count();
        $completed = count($completedLessonIds);

        return [
            'completed' => $completed,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'completedLessonIds' => $completedLessonIds,
        ];
    }
}
