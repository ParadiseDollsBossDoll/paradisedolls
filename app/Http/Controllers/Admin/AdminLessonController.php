<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesLessonContentBlocks;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class AdminLessonController extends Controller
{
    use ManagesLessonContentBlocks;

    public function store(Request $request, Course $course): RedirectResponse
    {
        $httpUrlRule = $this->httpUrlRule();

        $rules = [
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
            'lesson_banner_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'lesson_images_upload' => ['nullable', 'array', 'max:12'],
            'lesson_images_upload.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'is_published' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'bunny_video_id' => ['nullable', 'string', 'max:64'],
            'bunny_library_id' => ['nullable', 'string', 'max:64'],
            'bunny_video_title' => ['nullable', 'string', 'max:255'],
            'bunny_thumbnail_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
            'bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
            'duration' => ['nullable', 'string', 'max:64'],
            'has_pdf' => ['nullable', 'boolean'],
            'pdf_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'presentation_url' => ['nullable', 'string', 'max:50000', $httpUrlRule],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
        $rules += $this->lessonContentBlockRules('content_blocks');

        $validated = $request->validate($rules);

        $lesson = $course->lessons()->create([
            ...$this->normalizedLessonData($validated),
            'course_module_id' => $this->moduleIdFor($course, $validated['module_title'] ?? null, $validated['course_module_id'] ?? null),
            'sort_order' => $validated['sort_order'] ?? ($course->lessons()->max('sort_order') + 1),
        ]);

        if ($this->shouldSyncContentBlocks($validated)) {
            $this->syncLessonContentBlocks($lesson, $validated['content_blocks'] ?? []);
        }

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson added.'));
    }

    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $httpUrlRule = $this->httpUrlRule();

        $rules = [
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
            'lesson_banner_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'lesson_images_upload' => ['nullable', 'array', 'max:12'],
            'lesson_images_upload.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'is_published' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'bunny_video_id' => ['nullable', 'string', 'max:64'],
            'bunny_library_id' => ['nullable', 'string', 'max:64'],
            'bunny_video_title' => ['nullable', 'string', 'max:255'],
            'bunny_thumbnail_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
            'bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
            'duration' => ['nullable', 'string', 'max:64'],
            'has_pdf' => ['nullable', 'boolean'],
            'pdf_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'presentation_url' => ['nullable', 'string', 'max:50000', $httpUrlRule],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
        $rules += $this->lessonContentBlockRules('content_blocks');

        $validated = $request->validate($rules);

        $lesson->update([
            ...$this->normalizedLessonData($validated, $lesson),
            'course_module_id' => $this->moduleIdFor($course, $validated['module_title'] ?? null, $validated['course_module_id'] ?? null),
        ]);

        if ($this->shouldSyncContentBlocks($validated)) {
            $this->syncLessonContentBlocks($lesson, $validated['content_blocks'] ?? []);
        }

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson updated.'));
    }

    public function destroy(Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->delete();

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson removed.'));
    }

    // ── Autosave JSON endpoints ─────────────────────────────────────────────

    public function autosave(Request $request, Course $course): JsonResponse
    {
        $rules = $this->lessonValidationRules($course);
        $rules += $this->lessonContentBlockRules('content_blocks');
        $validated = $request->validate($rules);

        $lesson = $course->lessons()->create([
            ...$this->normalizedLessonData($validated),
            'course_module_id' => $this->moduleIdFor(
                $course,
                $validated['module_title'] ?? null,
                $validated['course_module_id'] ?? null
            ),
            'sort_order' => $validated['sort_order'] ?? ($course->lessons()->max('sort_order') + 1),
        ]);

        if ($this->shouldSyncContentBlocks($validated)) {
            $this->syncLessonContentBlocks($lesson, $validated['content_blocks'] ?? []);
        }

        return response()->json([
            'id' => $lesson->id,
            'title' => $lesson->title,
            'course_module_id' => $lesson->course_module_id,
            'sort_order' => $lesson->sort_order,
            'saved' => true,
        ], 201);
    }

    public function autosaveUpdate(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $rules = $this->lessonValidationRules($course);
        $rules += $this->lessonContentBlockRules('content_blocks');
        $validated = $request->validate($rules);

        $lesson->update([
            ...$this->normalizedLessonData($validated, $lesson),
            'course_module_id' => $this->moduleIdFor(
                $course,
                $validated['module_title'] ?? null,
                $validated['course_module_id'] ?? null
            ),
        ]);

        if ($this->shouldSyncContentBlocks($validated)) {
            $this->syncLessonContentBlocks($lesson, $validated['content_blocks'] ?? []);
        }

        return response()->json([
            'id' => $lesson->id,
            'title' => $lesson->title,
            'course_module_id' => $lesson->course_module_id,
            'sort_order' => $lesson->sort_order,
            'saved' => true,
        ]);
    }

    private function lessonValidationRules(Course $course): array
    {
        $httpUrlRule = $this->httpUrlRule();

        return [
            'title' => ['required', 'string', 'max:255'],
            'course_module_id' => [
                'nullable',
                'integer',
                Rule::exists('course_modules', 'id')->where(fn ($q) => $q->where('course_id', $course->id)),
            ],
            'module_title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'overview' => ['nullable', 'string', 'max:50000'],
            'steps' => ['nullable', 'string', 'max:50000'],
            'tips' => ['nullable', 'string', 'max:50000'],
            'safety_notes' => ['nullable', 'string', 'max:50000'],
            'resource_links' => ['nullable', 'string', 'max:50000'],
            'lesson_banner_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'lesson_images_upload' => ['nullable', 'array', 'max:12'],
            'lesson_images_upload.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'is_published' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'bunny_video_id' => ['nullable', 'string', 'max:64'],
            'bunny_library_id' => ['nullable', 'string', 'max:64'],
            'bunny_video_title' => ['nullable', 'string', 'max:255'],
            'bunny_thumbnail_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
            'bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
            'duration' => ['nullable', 'string', 'max:64'],
            'has_pdf' => ['nullable', 'boolean'],
            'pdf_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'presentation_url' => ['nullable', 'string', 'max:50000', $httpUrlRule],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    private function normalizedLessonData(array $lesson, ?Lesson $existingLesson = null): array
    {
        $bunnyVideoId = $lesson['bunny_video_id'] ?? null;
        $bunnyLibraryId = $lesson['bunny_library_id'] ?? null;
        $lessonVisuals = $this->normalizedLessonVisuals($lesson, $existingLesson);

        if (filled($bunnyVideoId) && filled($bunnyLibraryId)) {
            $lesson['video_url'] = 'https://iframe.mediadelivery.net/embed/'.$bunnyLibraryId.'/'.$bunnyVideoId.'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
        }

        unset(
            $lesson['course_module_id'],
            $lesson['module_title'],
            $lesson['lesson_banner_image_upload'],
            $lesson['lesson_images_upload'],
            $lesson['content_blocks_enabled'],
            $lesson['content_blocks']
        );

        $lesson['is_published'] = array_key_exists('is_published', $lesson) ? (bool) $lesson['is_published'] : (bool) ($existingLesson?->is_published ?? true);
        $lesson['has_pdf'] = array_key_exists('has_pdf', $lesson)
            ? (bool) $lesson['has_pdf']
            : (array_key_exists('pdf_url', $lesson) ? filled($lesson['pdf_url'] ?? null) : (bool) ($existingLesson?->has_pdf ?? false));
        $lesson['lesson_banner_image'] = $lessonVisuals['lesson_banner_image'];
        $lesson['lesson_images'] = $lessonVisuals['lesson_images'];

        if (array_key_exists('presentation_url', $lesson)) {
            $lesson['presentation_url'] = Lesson::normalizePresentationUrl($lesson['presentation_url']);
        }

        return $lesson;
    }

    private function normalizedLessonVisuals(array $lesson, ?Lesson $existingLesson = null): array
    {
        $bannerImage = $existingLesson?->lesson_banner_image;
        $galleryImages = $existingLesson?->lesson_images ?? [];

        $bannerUpload = $lesson['lesson_banner_image_upload'] ?? null;
        if ($bannerUpload instanceof UploadedFile) {
            $bannerImage = $this->storePrivateImage($bannerUpload, 'academy/lesson-banners');
        }

        foreach ($lesson['lesson_images_upload'] ?? [] as $galleryUpload) {
            if ($galleryUpload instanceof UploadedFile) {
                $galleryImages[] = $this->storePrivateImage($galleryUpload, 'academy/lesson-images');
            }
        }

        return [
            'lesson_banner_image' => $bannerImage,
            'lesson_images' => array_values(array_filter($galleryImages)),
        ];
    }

    private function storePrivateImage(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    private function httpUrlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (blank($value)) {
                return;
            }

            if (Lesson::normalizePresentationUrl((string) $value) === null) {
                $fail(__('The :attribute must be a valid HTTP or HTTPS URL.', ['attribute' => str_replace('_', ' ', $attribute)]));
            }
        };
    }

    /**
     * @param  array<string, mixed>  $lesson
     */
    private function shouldSyncContentBlocks(array $lesson): bool
    {
        return array_key_exists('content_blocks_enabled', $lesson)
            || array_key_exists('content_blocks', $lesson);
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
