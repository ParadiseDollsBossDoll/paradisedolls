<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

trait ManagesLessonContentBlocks
{
    private const LESSON_DOCUMENT_MAX_KB = 102400;

    /**
     * @return array<string, array<int, mixed>>
     */
    private function lessonContentBlockRules(string $prefix): array
    {
        $countKey = $this->contentBlockCountKey($prefix);

        return [
            $prefix.'_enabled' => ['nullable', 'boolean'],
            $countKey => ['nullable', 'integer', 'min:0', 'max:60'],
            $prefix => ['nullable', 'array', 'max:60'],
            $prefix.'.*.id' => ['nullable', 'integer'],
            $prefix.'.*.block_type' => ['nullable', 'string', Rule::in(LessonContentBlock::VALID_TYPES)],
            $prefix.'.*.title' => ['nullable', 'string', 'max:255'],
            $prefix.'.*.content' => ['nullable', 'string', 'max:50000'],
            $prefix.'.*.image_path' => ['nullable', 'string', 'max:500'],
            $prefix.'.*.image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            $prefix.'.*.gallery_uploads' => ['nullable', 'array', 'max:20'],
            $prefix.'.*.gallery_uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            $prefix.'.*.gallery_captions' => ['nullable', 'string', 'max:10000'],
            $prefix.'.*.file_path' => ['nullable', 'string', 'max:500'],
            $prefix.'.*.file_upload' => $this->lessonDocumentUploadRules(),
            $prefix.'.*.slide_images' => ['nullable', 'array', 'max:200'],
            $prefix.'.*.slide_images.*' => ['nullable', 'string', 'max:500'],
            $prefix.'.*.remove_media' => ['nullable', 'boolean'],
            $prefix.'.*.button_label' => ['nullable', 'string', 'max:120'],
            $prefix.'.*.bunny_video_id' => ['nullable', 'string', 'max:64'],
            $prefix.'.*.bunny_library_id' => ['nullable', 'string', 'max:64'],
            $prefix.'.*.bunny_video_title' => ['nullable', 'string', 'max:255'],
            $prefix.'.*.bunny_thumbnail_url' => ['nullable', 'string', 'max:2000'],
            $prefix.'.*.bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
            $prefix.'.*.bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
            $prefix.'.*.duration' => ['nullable', 'string', 'max:64'],
            $prefix.'.*.presentation_url' => ['nullable', 'string', 'max:50000'],
            $prefix.'.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    private function contentBlockCountKey(string $prefix): string
    {
        return str_ends_with($prefix, '.content_blocks')
            ? substr($prefix, 0, -strlen('content_blocks')).'_content_block_count'
            : '_content_block_count';
    }

    private function lessonDocumentUploadRules(bool $required = false, array $allowedExtensions = ['pdf']): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:'.self::LESSON_DOCUMENT_MAX_KB,
            function (string $attribute, mixed $value, callable $fail) use ($allowedExtensions): void {
                if (! $value instanceof UploadedFile) {
                    return;
                }

                $extension = strtolower($value->getClientOriginalExtension() ?: $value->extension() ?: '');
                $mime = strtolower((string) $value->getMimeType());
                $allowedExtensions = array_map('strtolower', $allowedExtensions);
                $allowedMimes = [
                    'application/pdf',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/vnd.apple.keynote',
                    'application/x-iwork-keynote-sffkey',
                    'application/zip',
                    'application/x-zip-compressed',
                    'application/octet-stream',
                ];

                if (! in_array($extension, $allowedExtensions, true) || ! in_array($mime, $allowedMimes, true)) {
                    $fail(__('The :attribute field must be a file of type: :types.', ['types' => implode(', ', $allowedExtensions)]));
                }
            },
        ];
    }

    private function assertLessonContentBlockPayloadIsComplete(Request $request, ?string $lessonPrefix = null): void
    {
        if ($lessonPrefix === null) {
            $expected = $request->input('_content_block_count');
            if ($expected === null || $expected === '') {
                return;
            }

            $received = $request->input('content_blocks', []);
            $receivedCount = is_array($received) ? count($received) : 0;
            if ($receivedCount < (int) $expected) {
                throw ValidationException::withMessages([
                    'content_blocks' => __('Only :received of :total lesson flow blocks were received. No lesson content was changed.', [
                        'received' => $receivedCount,
                        'total' => (int) $expected,
                    ]),
                ]);
            }

            return;
        }

        $lessons = $request->input($lessonPrefix, []);
        if (! is_array($lessons)) {
            return;
        }

        foreach ($lessons as $index => $lesson) {
            if (! is_array($lesson) || ! array_key_exists('_content_block_count', $lesson)) {
                continue;
            }

            $expected = (int) $lesson['_content_block_count'];
            $blocks = $lesson['content_blocks'] ?? [];
            $receivedCount = is_array($blocks) ? count($blocks) : 0;

            if ($receivedCount < $expected) {
                throw ValidationException::withMessages([
                    "{$lessonPrefix}.{$index}.content_blocks" => __('Only :received of :total lesson flow blocks were received for this lesson. No course content was changed.', [
                        'received' => $receivedCount,
                        'total' => $expected,
                    ]),
                ]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function syncLessonContentBlocks(Lesson $lesson, array $blocks): void
    {
        $existingBlocks = $lesson->contentBlocks()->get()->keyBy('id');
        $syncedIds = [];

        foreach (array_values($blocks) as $index => $block) {
            $existingBlock = ! empty($block['id'])
                ? $existingBlocks->get((int) $block['id'])
                : null;
            $this->logIncomingContentBlock($lesson, $block, $index, $existingBlock);
            $blockData = $this->normalizedContentBlockData($block, $index, $existingBlock);

            if ($existingBlock !== null) {
                $existingBlock->update($blockData);
                $this->logSavedContentBlock($existingBlock->refresh());
                $syncedIds[] = $existingBlock->id;

                continue;
            }

            $createdBlock = $lesson->contentBlocks()->create($blockData);
            $this->logSavedContentBlock($createdBlock->refresh());
            $syncedIds[] = $createdBlock->id;
        }

        if ($syncedIds !== []) {
            // Remove any DB blocks that were not in the submitted list (user deleted them).
            $lesson->contentBlocks()
                ->whereNotIn('id', array_unique($syncedIds))
                ->delete();
        } elseif ($blocks === []) {
            // Caller explicitly sent an empty list — user removed every block.
            $lesson->contentBlocks()->delete();
        }
        // If $blocks was non-empty but nothing was synced (can't happen in normal flow),
        // leave existing blocks untouched rather than deleting everything.
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    private function normalizedContentBlockData(array $block, int $index, ?LessonContentBlock $existingBlock = null): array
    {
        $type = LessonContentBlock::canonicalType($block['block_type'] ?? $existingBlock?->block_type);
        // Prefer a non-empty submitted value; fall back to the existing DB record so that
        // form fields submitted as empty string never blank out saved media/video data.
        $blockValue = fn (string $key) => (array_key_exists($key, $block) && filled($block[$key]))
            ? $block[$key]
            : $existingBlock?->{$key};
        $removeMedia = filter_var($block['remove_media'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $imagePath = $type === 'image' && ! $removeMedia ? $existingBlock?->image_path : null;
        $filePath = in_array($type, ['pdf_resource', 'presentation'], true) && ! $removeMedia ? $existingBlock?->file_path : null;
        $presentationUrl = $type === 'presentation' && ! $removeMedia ? $existingBlock?->presentation_url : null;
        $settings = in_array($type, ['pdf_resource', 'presentation'], true) && ! $removeMedia
            ? ($existingBlock?->settings ?? [])
            : [];

        $imageUpload = $block['image_upload'] ?? null;
        if ($type === 'image' && $imageUpload instanceof UploadedFile) {
            $imagePath = $this->storeLessonImageBlockFile($imageUpload);
        } elseif ($type === 'image' && filled($block['image_path'] ?? null)) {
            $imagePath = $block['image_path'];
        }

        $fileUpload = $block['file_upload'] ?? null;
        if ($type === 'pdf_resource' && $fileUpload instanceof UploadedFile) {
            $filePath = $this->storeLessonBlockFile($fileUpload, 'academy/lesson-content/pdfs');
        } elseif ($type === 'pdf_resource' && filled($block['file_path'] ?? null)) {
            $filePath = $block['file_path'];
        }

        if ($type === 'presentation' && $fileUpload instanceof UploadedFile) {
            $filePath = $this->storeLessonBlockFile($fileUpload, 'academy/lesson-content/presentations');
            $presentationUrl = null;
            $settings['slide_images'] = $this->createPresentationSlideImages($filePath);
        } elseif ($type === 'presentation' && filled($block['file_path'] ?? null)) {
            $filePath = $block['file_path'];
        }

        if ($type === 'presentation' && isset($block['slide_images']) && is_array($block['slide_images'])) {
            $settings['slide_images'] = array_values(array_filter($block['slide_images']));
        }

        if ($type === 'gallery') {
            $galleryImages = $existingBlock?->settings['gallery_images'] ?? [];

            foreach ($block['gallery_uploads'] ?? [] as $galleryUpload) {
                if ($galleryUpload instanceof UploadedFile) {
                    $galleryImages[] = $this->storeLessonBlockFile($galleryUpload, 'academy/lesson-content/galleries');
                }
            }

            $settings['gallery_images'] = array_values(array_filter($galleryImages));

            if (filled($block['gallery_captions'] ?? null)) {
                $settings['gallery_captions'] = trim((string) $block['gallery_captions']);
            }
        }

        if ($type === 'presentation' && filled($block['presentation_url'] ?? null)) {
            $presentationUrl = Lesson::normalizePresentationUrl($block['presentation_url']);
        }

        if (in_array($type, ['pdf_resource', 'presentation'], true) && filled($block['button_label'] ?? null)) {
            $settings['button_label'] = trim((string) $block['button_label']);
        }

        return [
            'block_type' => $type,
            'title' => filled($block['title'] ?? null) ? trim((string) $block['title']) : null,
            'content' => filled($block['content'] ?? null) ? trim((string) $block['content']) : null,
            'image_path' => $imagePath,
            'file_path' => $filePath,
            'bunny_video_id' => $type === 'video' ? $blockValue('bunny_video_id') : null,
            'bunny_library_id' => $type === 'video' ? $blockValue('bunny_library_id') : null,
            'bunny_video_title' => $type === 'video' ? $blockValue('bunny_video_title') : null,
            'bunny_thumbnail_url' => $type === 'video' ? $blockValue('bunny_thumbnail_url') : null,
            'bunny_upload_fingerprint' => $type === 'video' ? $blockValue('bunny_upload_fingerprint') : null,
            'bunny_status' => $type === 'video' && filled($blockValue('bunny_status')) ? (int) $blockValue('bunny_status') : null,
            'duration' => $type === 'video' ? $blockValue('duration') : null,
            'presentation_url' => $presentationUrl,
            'settings' => $settings !== [] ? $settings : null,
            'sort_order' => $block['sort_order'] ?? ($index + 1),
        ];
    }

    private function storeLessonBlockFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    private function storeLessonImageBlockFile(UploadedFile $file): string
    {
        if (! function_exists('imagewebp')) {
            Log::warning('Lesson image WebP optimization unavailable; storing original image.', [
                'file_name' => $file->getClientOriginalName(),
                'missing_dependency' => 'php-gd',
            ]);

            return $this->storeLessonBlockFile($file, 'academy/lesson-content/images');
        }

        $source = match (strtolower($file->getClientOriginalExtension())) {
            'jpg', 'jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($file->getRealPath()) : false,
            'png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($file->getRealPath()) : false,
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false,
        };

        if ($source === false) {
            Log::warning('Lesson image WebP optimization failed; storing original image.', [
                'file_name' => $file->getClientOriginalName(),
            ]);

            return $this->storeLessonBlockFile($file, 'academy/lesson-content/images');
        }

        $path = 'academy/lesson-content/images/'.Str::uuid().'.webp';
        ob_start();
        imagewebp($source, null, 82);
        $contents = ob_get_clean();
        imagedestroy($source);

        if ($contents === false || $contents === '') {
            Log::warning('Lesson image WebP optimization returned empty output; storing original image.', [
                'file_name' => $file->getClientOriginalName(),
            ]);

            return $this->storeLessonBlockFile($file, 'academy/lesson-content/images');
        }

        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function createPresentationSlideImages(string $filePath): array
    {
        Log::warning('Presentation PDF to WebP conversion unavailable; original PDF preserved with safe fallback.', [
            'file_path' => $filePath,
            'missing_dependency' => 'Install Imagick with PDF support, or Poppler pdftoppm plus cwebp.',
        ]);

        return [];
    }

    private function logIncomingContentBlock(Lesson $lesson, array $block, int $index, ?LessonContentBlock $existingBlock): void
    {
        Log::debug('Lesson flow incoming block before save', [
            'lesson_id' => $lesson->id,
            'block_id' => $block['id'] ?? null,
            'incoming_type' => $block['block_type'] ?? null,
            'incoming_position' => $block['sort_order'] ?? ($index + 1),
            'incoming_content' => $block['content'] ?? null,
            'incoming_file_path' => $block['file_path'] ?? null,
            'incoming_file_url' => $block['file_url'] ?? null,
            'incoming_image_path' => $block['image_path'] ?? null,
            'incoming_video_url' => $block['video_url'] ?? null,
            'incoming_bunny_video_id' => $block['bunny_video_id'] ?? null,
            'incoming_slide_images' => $block['slide_images'] ?? null,
            'uploaded_file_exists' => ($block['file_upload'] ?? null) instanceof UploadedFile || ($block['image_upload'] ?? null) instanceof UploadedFile,
            'remove_media' => $block['remove_media'] ?? false,
            'existing_type' => $existingBlock?->block_type,
            'existing_file_path' => $existingBlock?->file_path,
            'existing_image_path' => $existingBlock?->image_path,
            'existing_bunny_video_id' => $existingBlock?->bunny_video_id,
            'existing_slide_images' => $existingBlock?->settings['slide_images'] ?? null,
        ]);
    }

    private function logSavedContentBlock(LessonContentBlock $block): void
    {
        Log::debug('Lesson flow saved block after save', [
            'lesson_id' => $block->lesson_id,
            'block_id' => $block->id,
            'final_type' => $block->block_type,
            'final_file_path' => $block->file_path,
            'final_image_path' => $block->image_path,
            'final_bunny_video_id' => $block->bunny_video_id,
            'final_slide_images' => $block->settings['slide_images'] ?? null,
        ]);
    }
}
