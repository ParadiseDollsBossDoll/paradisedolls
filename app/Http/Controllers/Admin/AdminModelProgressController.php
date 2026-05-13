<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminModelProgressController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $courses = Course::query()
            ->with(['lessons:id,course_id'])
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $modelsQuery = User::query()
            ->where('role', 'model');

        if ($search !== '') {
            $modelsQuery->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $directoryMembers = (clone $modelsQuery)
            ->orderBy('name')
            ->paginate(12, ['id', 'name', 'email', 'profile_photo_path'])
            ->withQueryString();

        $selectedMember = null;
        $requestedMemberId = (int) $request->query('member', 0);
        if ($requestedMemberId > 0) {
            $selectedMember = User::query()
                ->where('role', 'model')
                ->find($requestedMemberId, ['id', 'name', 'email', 'profile_photo_path']);
        }

        $lessonToCourse = [];
        $allLessonIds = [];
        foreach ($courses as $course) {
            foreach ($course->lessons as $lesson) {
                $lessonToCourse[$lesson->id] = $course->id;
                $allLessonIds[$lesson->id] = true;
            }
        }
        $allLessonIds = array_keys($allLessonIds);

        $usersForProgress = $directoryMembers->getCollection();
        if ($selectedMember) {
            $usersForProgress = $usersForProgress
                ->concat([$selectedMember])
                ->unique('id')
                ->values();
        }

        $completedRows = collect();
        if ($usersForProgress->isNotEmpty() && $allLessonIds !== []) {
            $completedRows = LessonProgress::query()
                ->whereIn('user_id', $usersForProgress->pluck('id')->all())
                ->whereIn('lesson_id', $allLessonIds)
                ->whereNotNull('completed_at')
                ->get(['user_id', 'lesson_id', 'completed_at']);
        }

        /** @var array<int, array<int, int>> $completedByUserCourse */
        $completedByUserCourse = [];
        $lastActivityByUser = [];
        foreach ($completedRows as $row) {
            $courseId = $lessonToCourse[$row->lesson_id] ?? null;
            if ($courseId === null) {
                continue;
            }
            $completedByUserCourse[$row->user_id][$courseId] = ($completedByUserCourse[$row->user_id][$courseId] ?? 0) + 1;

            if (
                ! isset($lastActivityByUser[$row->user_id])
                || $row->completed_at->greaterThan($lastActivityByUser[$row->user_id])
            ) {
                $lastActivityByUser[$row->user_id] = $row->completed_at;
            }
        }

        $memberCards = $directoryMembers->getCollection()
            ->map(fn (User $model): array => $this->memberProgress($model, $courses, $completedByUserCourse, $lastActivityByUser))
            ->values();

        $selectedProgress = $selectedMember
            ? $this->memberProgress($selectedMember, $courses, $completedByUserCourse, $lastActivityByUser, true)
            : null;

        $summary = $this->summary($courses, $allLessonIds);

        return view('admin.models-progress', [
            'courses' => $courses,
            'directoryMembers' => $directoryMembers,
            'memberCards' => $memberCards,
            'search' => $search,
            'selectedProgress' => $selectedProgress,
            'summary' => $summary,
        ]);
    }

    /**
     * @param  Collection<int, Course>  $courses
     * @return array<string, int>
     */
    private function summary(Collection $courses, array $allLessonIds): array
    {
        $memberCount = User::query()
            ->where('role', 'model')
            ->count();

        $completedCounts = collect();
        if ($memberCount > 0 && $allLessonIds !== []) {
            $completedCounts = LessonProgress::query()
                ->join('users', 'lesson_progress.user_id', '=', 'users.id')
                ->where('users.role', 'model')
                ->whereIn('lesson_progress.lesson_id', $allLessonIds)
                ->whereNotNull('lesson_progress.completed_at')
                ->selectRaw('lesson_progress.user_id as user_id, count(*) as completed_count')
                ->groupBy('lesson_progress.user_id')
                ->pluck('completed_count', 'user_id');
        }

        $totalLessons = (int) $courses->sum('lessons_count');

        return [
            'members' => $memberCount,
            'courses' => $courses->count(),
            'lessons' => $totalLessons,
            'average_progress' => $memberCount === 0 || $totalLessons === 0
                ? 0
                : (int) round(($completedCounts->sum() / ($memberCount * $totalLessons)) * 100),
            'active_members' => $completedCounts
                ->filter(fn ($count): bool => (int) $count > 0)
                ->count(),
        ];
    }

    /**
     * @param  Collection<int, Course>  $courses
     * @param  array<int, array<int, int>>  $completedByUserCourse
     * @param  array<int, mixed>  $lastActivityByUser
     * @return array<string, mixed>
     */
    private function memberProgress(
        User $model,
        Collection $courses,
        array $completedByUserCourse,
        array $lastActivityByUser,
        bool $includeCourses = false
    ): array {
        $completedLessons = 0;
        $totalLessons = 0;
        $completedCourses = 0;
        $inProgressCourses = 0;

        $courseProgress = $courses->map(function (Course $course) use ($model, $completedByUserCourse, &$completedLessons, &$totalLessons, &$completedCourses, &$inProgressCourses): array {
            $total = (int) $course->lessons_count;
            $completed = $completedByUserCourse[$model->id][$course->id] ?? 0;
            $percent = $total === 0 ? 0 : (int) round(($completed / $total) * 100);

            $completedLessons += $completed;
            $totalLessons += $total;

            if ($total === 0) {
                $status = 'empty';
            } elseif ($percent >= 100) {
                $status = 'complete';
                $completedCourses++;
            } elseif ($percent > 0) {
                $status = 'progress';
                $inProgressCourses++;
            } else {
                $status = 'new';
            }

            return [
                'id' => $course->id,
                'title' => $course->title,
                'platform' => $course->platform_label ?: __('Course'),
                'percent' => $percent,
                'completed' => $completed,
                'total' => $total,
                'status' => $status,
                'color' => $course->displayColor(),
            ];
        })->values();

        $progress = [
            'id' => $model->id,
            'name' => $model->name,
            'email' => $model->email,
            'initials' => $model->initials(),
            'profile_photo_url' => $model->profilePhotoUrl(),
            'overall_percent' => $totalLessons === 0 ? 0 : (int) round(($completedLessons / $totalLessons) * 100),
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'completed_courses' => $completedCourses,
            'in_progress_courses' => $inProgressCourses,
            'last_activity' => $lastActivityByUser[$model->id] ?? null,
            'search' => mb_strtolower($model->name.' '.$model->email),
        ];

        if ($includeCourses) {
            $progress['courses'] = $courseProgress;
        }

        return $progress;
    }
}
