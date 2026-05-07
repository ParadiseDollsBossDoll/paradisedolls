<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminLessonController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'course_module_id' => [
                'nullable',
                'integer',
                Rule::exists('course_modules', 'id')->where(fn ($query) => $query->where('course_id', $course->id)),
            ],
            'module_title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'overview' => ['nullable', 'string', 'max:50000'],
            'steps' => ['nullable', 'string', 'max:50000'],
            'tips' => ['nullable', 'string', 'max:50000'],
            'safety_notes' => ['nullable', 'string', 'max:50000'],
            'resource_links' => ['nullable', 'string', 'max:50000'],
            'is_published' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:2000'],
            'bunny_video_id' => ['nullable', 'string', 'max:64'],
            'bunny_library_id' => ['nullable', 'string', 'max:64'],
            'bunny_video_title' => ['nullable', 'string', 'max:255'],
            'bunny_thumbnail_url' => ['nullable', 'string', 'max:2000'],
            'bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
            'bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
            'duration' => ['nullable', 'string', 'max:64'],
            'has_pdf' => ['nullable', 'boolean'],
            'pdf_url' => ['nullable', 'string', 'max:2000'],
            'presentation_url' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $course->lessons()->create([
            ...$this->normalizedLessonData($validated),
            'course_module_id' => $this->moduleIdFor($course, $validated['module_title'] ?? null, $validated['course_module_id'] ?? null),
            'sort_order' => $validated['sort_order'] ?? ($course->lessons()->max('sort_order') + 1),
        ]);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson added.'));
    }

    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'course_module_id' => [
                'nullable',
                'integer',
                Rule::exists('course_modules', 'id')->where(fn ($query) => $query->where('course_id', $course->id)),
            ],
            'module_title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'overview' => ['nullable', 'string', 'max:50000'],
            'steps' => ['nullable', 'string', 'max:50000'],
            'tips' => ['nullable', 'string', 'max:50000'],
            'safety_notes' => ['nullable', 'string', 'max:50000'],
            'resource_links' => ['nullable', 'string', 'max:50000'],
            'is_published' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:2000'],
            'bunny_video_id' => ['nullable', 'string', 'max:64'],
            'bunny_library_id' => ['nullable', 'string', 'max:64'],
            'bunny_video_title' => ['nullable', 'string', 'max:255'],
            'bunny_thumbnail_url' => ['nullable', 'string', 'max:2000'],
            'bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
            'bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
            'duration' => ['nullable', 'string', 'max:64'],
            'has_pdf' => ['nullable', 'boolean'],
            'pdf_url' => ['nullable', 'string', 'max:2000'],
            'presentation_url' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $lesson->update([
            ...$this->normalizedLessonData($validated),
            'course_module_id' => $this->moduleIdFor($course, $validated['module_title'] ?? null, $validated['course_module_id'] ?? null),
        ]);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson updated.'));
    }

    public function destroy(Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->delete();

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson removed.'));
    }

    private function normalizedLessonData(array $lesson): array
    {
        $bunnyVideoId = $lesson['bunny_video_id'] ?? null;
        $bunnyLibraryId = $lesson['bunny_library_id'] ?? null;

        if (filled($bunnyVideoId) && filled($bunnyLibraryId)) {
            $lesson['video_url'] = 'https://iframe.mediadelivery.net/embed/'.$bunnyLibraryId.'/'.$bunnyVideoId.'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
        }

        unset($lesson['course_module_id'], $lesson['module_title']);

        $lesson['is_published'] = array_key_exists('is_published', $lesson) ? (bool) $lesson['is_published'] : true;
        $lesson['has_pdf'] = array_key_exists('has_pdf', $lesson) ? (bool) $lesson['has_pdf'] : filled($lesson['pdf_url'] ?? null);

        return $lesson;
    }

    private function moduleIdFor(Course $course, ?string $title, int|string|null $moduleId = null): int
    {
        if ($moduleId !== null && $moduleId !== '') {
            $existingModule = $course->modules()->whereKey((int) $moduleId)->first();
            if ($existingModule !== null) {
                return $existingModule->id;
            }
        }

        $title = trim((string) $title);
        $title = $title !== '' ? $title : 'Core Training';

        $module = $course->modules()
            ->whereRaw('LOWER(title) = ?', [strtolower($title)])
            ->first();

        if ($module !== null) {
            $module->update(['is_published' => true]);

            return $module->id;
        }

        return CourseModule::create([
            'course_id' => $course->id,
            'title' => $title,
            'is_published' => true,
            'sort_order' => ((int) $course->modules()->max('sort_order')) + 1,
        ])->id;
    }
}
