<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminLessonController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'video_url' => ['nullable', 'string', 'max:2000'],
            'duration' => ['nullable', 'string', 'max:64'],
            'has_pdf' => ['nullable', 'boolean'],
            'pdf_url' => ['nullable', 'string', 'max:2000'],
            'presentation_url' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $course->lessons()->create([
            ...$validated,
            'has_pdf' => $request->boolean('has_pdf'),
            'sort_order' => $validated['sort_order'] ?? ($course->lessons()->max('sort_order') + 1),
        ]);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson added.'));
    }

    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'video_url' => ['nullable', 'string', 'max:2000'],
            'duration' => ['nullable', 'string', 'max:64'],
            'has_pdf' => ['nullable', 'boolean'],
            'pdf_url' => ['nullable', 'string', 'max:2000'],
            'presentation_url' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $validated['has_pdf'] = $request->boolean('has_pdf');

        $lesson->update($validated);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson updated.'));
    }

    public function destroy(Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->delete();

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson removed.'));
    }
}
