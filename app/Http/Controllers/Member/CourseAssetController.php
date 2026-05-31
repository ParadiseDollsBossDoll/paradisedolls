<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseAssetController extends Controller
{
    public function outline(string $slug): StreamedResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $path = $this->safeAcademyPath($course->course_outline_url);

        abort_unless($course->has_course_outline && $path, 404);

        return $this->localResponse($path);
    }

    public function lessonMedia(string $slug, Lesson $lesson, string $kind, ?int $index = null): StreamedResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        abort_unless((int) $lesson->course_id === (int) $course->id, 404);
        abort_unless($lesson->is_published, 404);

        $path = match ($kind) {
            'banner' => $lesson->lesson_banner_image,
            'image' => $lesson->lesson_images[$index ?? -1] ?? null,
            default => null,
        };

        $path = $this->safeAcademyPath($path);

        abort_unless($path, 404);

        return $this->localResponse($path);
    }

    public function contentBlock(string $slug, LessonContentBlock $block, string $field, ?int $index = null): StreamedResponse
    {
        $block->loadMissing('lesson.course');

        abort_unless(
            $block->lesson?->course?->slug === $slug
            && $block->lesson->course->is_published
            && $block->lesson->is_published,
            404
        );

        $path = match ($field) {
            'image' => $block->image_path,
            'file' => $block->file_path,
            'gallery' => is_array($block->settings) ? ($block->settings['gallery_images'][$index ?? -1] ?? null) : null,
            default => null,
        };

        $path = $this->safeAcademyPath($path);

        abort_unless($path, 404);

        return $this->localResponse($path);
    }

    private function localResponse(string $path): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function safeAcademyPath(mixed $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path), '/');

        if ($path === '' || str_contains($path, "\0") || str_contains($path, '..')) {
            return null;
        }

        return str_starts_with($path, 'academy/') ? $path : null;
    }
}
