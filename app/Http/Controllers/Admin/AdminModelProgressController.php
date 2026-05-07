<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\View\View;

class AdminModelProgressController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->with(['lessons:id,course_id'])
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $models = User::query()
            ->where('role', 'model')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $lessonToCourse = [];
        $allLessonIds = [];
        foreach ($courses as $course) {
            foreach ($course->lessons as $lesson) {
                $lessonToCourse[$lesson->id] = $course->id;
                $allLessonIds[$lesson->id] = true;
            }
        }
        $allLessonIds = array_keys($allLessonIds);

        $matrix = [];

        if ($models->isEmpty() || $allLessonIds === []) {
            foreach ($models as $model) {
                foreach ($courses as $course) {
                    $matrix[$model->id][$course->id] = 0;
                }
            }

            return view('admin.models-progress', compact('models', 'courses', 'matrix'));
        }

        $completedRows = LessonProgress::query()
            ->whereIn('user_id', $models->pluck('id')->all())
            ->whereIn('lesson_id', $allLessonIds)
            ->whereNotNull('completed_at')
            ->get(['user_id', 'lesson_id']);

        /** @var array<int, array<int, int>> $completedByUserCourse */
        $completedByUserCourse = [];
        foreach ($completedRows as $row) {
            $courseId = $lessonToCourse[$row->lesson_id] ?? null;
            if ($courseId === null) {
                continue;
            }
            $completedByUserCourse[$row->user_id][$courseId] = ($completedByUserCourse[$row->user_id][$courseId] ?? 0) + 1;
        }

        foreach ($models as $model) {
            foreach ($courses as $course) {
                $total = (int) $course->lessons_count;
                $completed = $completedByUserCourse[$model->id][$course->id] ?? 0;
                $matrix[$model->id][$course->id] = $total === 0 ? 0 : (int) round(($completed / $total) * 100);
            }
        }

        return view('admin.models-progress', compact('models', 'courses', 'matrix'));
    }
}
