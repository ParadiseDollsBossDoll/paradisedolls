<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->academyPaths() as $path) {
            $this->moveBetweenStorageRoots($path, 'public', 'private');
        }
    }

    public function down(): void
    {
        foreach ($this->academyPaths() as $path) {
            $this->moveBetweenStorageRoots($path, 'private', 'public');
        }
    }

    /**
     * @return array<int, string>
     */
    private function academyPaths(): array
    {
        $paths = [];

        DB::table('courses')
            ->whereNotNull('course_outline_url')
            ->pluck('course_outline_url')
            ->each(fn ($path) => $paths[] = $path);

        DB::table('lessons')
            ->select(['lesson_banner_image', 'lesson_images'])
            ->orderBy('id')
            ->each(function (object $lesson) use (&$paths): void {
                $paths[] = $lesson->lesson_banner_image;

                $images = json_decode((string) $lesson->lesson_images, true);
                if (is_array($images)) {
                    array_push($paths, ...$images);
                }
            });

        DB::table('lesson_content_blocks')
            ->select(['image_path', 'file_path', 'settings'])
            ->orderBy('id')
            ->each(function (object $block) use (&$paths): void {
                $paths[] = $block->image_path;
                $paths[] = $block->file_path;

                $settings = json_decode((string) $block->settings, true);
                $galleryImages = is_array($settings) ? ($settings['gallery_images'] ?? []) : [];
                if (is_array($galleryImages)) {
                    array_push($paths, ...$galleryImages);
                }
            });

        return collect($paths)
            ->map(fn ($path) => $this->safeAcademyPath($path))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function moveBetweenStorageRoots(string $path, string $fromRoot, string $toRoot): void
    {
        $from = storage_path('app/'.$fromRoot.'/'.$path);
        $to = storage_path('app/'.$toRoot.'/'.$path);

        if (! File::exists($from)) {
            return;
        }

        File::ensureDirectoryExists(dirname($to));

        if (! File::exists($to)) {
            File::copy($from, $to);
        }

        File::delete($from);
    }

    private function safeAcademyPath(mixed $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path), '/');

        if ($path === '' || str_contains($path, "\0") || str_contains($path, '..')) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, '/')) {
            return null;
        }

        return str_starts_with($path, 'academy/') ? $path : null;
    }
};
