<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $course = $lesson->course;
        abort_unless($course->is_published && $lesson->is_published, 404);

        $completed = $request->boolean('completed');

        $progress = LessonProgress::firstOrNew([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->completed_at = $completed ? now() : null;
        $progress->save();

        if ($completed) {
            $course->load([
                'lessons' => fn ($query) => $query
                    ->publishedForMembers()
                    ->orderBy('sort_order'),
                'modules' => fn ($query) => $query
                    ->where('is_published', true)
                    ->with(['lessons' => fn ($lessonQuery) => $lessonQuery
                        ->publishedForMembers()
                        ->orderBy('sort_order')])
                    ->orderBy('sort_order'),
            ]);
            $course->setRelation('lessons', $course->lessonsInModuleOrder());

            $lessonIds = $course->lessons->pluck('id')->values();
            $selectedIndex = $lessonIds->search($lesson->id);
            $nextLesson = $selectedIndex !== false && $selectedIndex < $lessonIds->count() - 1
                ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex + 1])
                : null;

            if ($nextLesson !== null) {
                return redirect()
                    ->route('member.courses.lessons.show', [$course->slug, $nextLesson])
                    ->with('status', __('Lesson completed.'));
            }

            return redirect()->back()->with('status', __('Course completed.'));
        }

        return redirect()->back()->with('status', __('Progress updated.'));
    }
}
