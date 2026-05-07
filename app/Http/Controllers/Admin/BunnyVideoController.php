<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\BunnyStreamClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BunnyVideoController extends Controller
{
    public function __construct(private readonly BunnyStreamClient $bunny)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($this->bunny->listVideos(
                $validated['search'] ?? null,
                (int) ($validated['page'] ?? 1),
            ));
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function uploadIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'file_size' => ['nullable', 'integer', 'min:1'],
            'fingerprint' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['fingerprint'])) {
            $existingLesson = Lesson::query()
                ->where('bunny_upload_fingerprint', $validated['fingerprint'])
                ->whereNotNull('bunny_video_id')
                ->latest()
                ->first();

            if ($existingLesson !== null) {
                $video = $existingLesson->bunnyVideoPayload();

                return response()->json([
                    'duplicate' => true,
                    'video' => $video,
                    'upload' => null,
                ]);
            }

            $existingCourseIntro = Course::query()
                ->where('intro_bunny_upload_fingerprint', $validated['fingerprint'])
                ->whereNotNull('intro_bunny_video_id')
                ->latest()
                ->first();

            if ($existingCourseIntro !== null) {
                return response()->json([
                    'duplicate' => true,
                    'video' => $existingCourseIntro->introBunnyVideoPayload(),
                    'upload' => null,
                ]);
            }
        }

        try {
            $video = $this->bunny->createVideo($validated['title']);

            return response()->json([
                'duplicate' => false,
                'video' => $video,
                'upload' => $this->bunny->uploadAuthorization($video['id']),
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(string $videoId): JsonResponse
    {
        abort_unless(Str::isUuid($videoId), 404);

        try {
            return response()->json([
                'video' => $this->bunny->getVideo($videoId),
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
