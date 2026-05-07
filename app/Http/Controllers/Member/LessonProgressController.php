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
        abort_unless($course->is_published, 404);

        $completed = $request->boolean('completed');

        $progress = LessonProgress::firstOrNew([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->completed_at = $completed ? now() : null;
        $progress->save();

        return redirect()->back()->with('status', __('Progress updated.'));
    }
}
