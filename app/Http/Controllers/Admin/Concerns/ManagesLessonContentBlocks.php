<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

trait ManagesLessonContentBlocks
{
    /**
     * @return array<string, array<int, mixed>>
     */
    private function lessonContentBlockRules(string $prefix): array
    {
        return [
            $prefix.'_enabled' => ['nullable', 'boolean'],
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
            $prefix.'.*.file_upload' => ['nullable', 'file', 'mimes:pdf,ppt,pptx,key', 'max:20480'],
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
            $blockData = $this->normalizedContentBlockData($block, $index, $existingBlock);

            if ($existingBlock !== null) {
                $existingBlock->update($blockData);
                $syncedIds[] = $existingBlock->id;

                continue;
            }

            $createdBlock = $lesson->contentBlocks()->create($blockData);
            $syncedIds[] = $createdBlock->id;
        }

        $lesson->contentBlocks()
            ->when($syncedIds !== [], fn ($query) => $query->whereNotIn('id', array_unique($syncedIds)))
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    private function normalizedContentBlockData(array $block, int $index, ?LessonContentBlock $existingBlock = null): array
    {
        $type = LessonContentBlock::canonicalType($block['block_type'] ?? null);

        $imagePath = $type === 'image' ? $existingBlock?->image_path : null;
        $filePath = in_array($type, ['pdf_resource', 'presentation'], true) ? $existingBlock?->file_path : null;
        $presentationUrl = $type === 'presentation' ? $existingBlock?->presentation_url : null;
        $settings = in_array($type, ['pdf_resource', 'presentation'], true)
            ? ($existingBlock?->settings ?? [])
            : [];

        $imageUpload = $block['image_upload'] ?? null;
        if ($type === 'image' && $imageUpload instanceof UploadedFile) {
            $imagePath = $this->storeLessonBlockFile($imageUpload, 'academy/lesson-content/images');
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
        } elseif ($type === 'presentation' && filled($block['file_path'] ?? null)) {
            $filePath = $block['file_path'];
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
            'bunny_video_id' => $type === 'video' ? ($block['bunny_video_id'] ?? null) : null,
            'bunny_library_id' => $type === 'video' ? ($block['bunny_library_id'] ?? null) : null,
            'bunny_video_title' => $type === 'video' ? ($block['bunny_video_title'] ?? null) : null,
            'bunny_thumbnail_url' => $type === 'video' ? ($block['bunny_thumbnail_url'] ?? null) : null,
            'bunny_upload_fingerprint' => $type === 'video' ? ($block['bunny_upload_fingerprint'] ?? null) : null,
            'bunny_status' => $type === 'video' && filled($block['bunny_status'] ?? null) ? (int) $block['bunny_status'] : null,
            'duration' => $type === 'video' ? ($block['duration'] ?? null) : null,
            'presentation_url' => $presentationUrl,
            'settings' => $settings !== [] ? $settings : null,
            'sort_order' => $block['sort_order'] ?? ($index + 1),
        ];
    }

    private function storeLessonBlockFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }
}
