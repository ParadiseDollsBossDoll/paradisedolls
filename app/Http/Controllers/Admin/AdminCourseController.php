<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesLessonContentBlocks;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\CourseCommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCourseController extends Controller
{
    use ManagesLessonContentBlocks;

    public function index(): View
    {
        $courses = Course::query()
            ->with('lessons:id,course_id')
            ->withCount(['lessons', 'chatMessages'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20);

        $lessonToCourse = [];
        $courseLessonCounts = [];
        foreach ($courses->getCollection() as $course) {
            $lessonIds = $course->lessons->pluck('id');
            $courseLessonCounts[$course->id] = $lessonIds->count();
            foreach ($lessonIds as $lessonId) {
                $lessonToCourse[$lessonId] = $course->id;
            }
        }

        $progressByCourse = [];
        if ($lessonToCourse !== []) {
            LessonProgress::query()
                ->select('lesson_id', 'user_id')
                ->whereIn('lesson_id', array_keys($lessonToCourse))
                ->whereNotNull('completed_at')
                ->each(function (LessonProgress $row) use ($lessonToCourse, &$progressByCourse): void {
                    $progressByCourse[$lessonToCourse[$row->lesson_id]][$row->user_id][$row->lesson_id] = true;
                });
        }

        $courseStats = $courses->getCollection()->mapWithKeys(function (Course $course) use ($progressByCourse, $courseLessonCounts) {
            $totalLessons = $courseLessonCounts[$course->id] ?? 0;
            $userProgress = $progressByCourse[$course->id] ?? [];
            $started = count($userProgress);
            $finished = $totalLessons === 0 ? 0 : count(array_filter(
                $userProgress,
                fn (array $lessons) => count($lessons) >= $totalLessons
            ));

            return [$course->id => [
                'started' => $started,
                'finished' => $finished,
                'messages' => $course->chat_messages_count,
            ]];
        });

        return view('admin.courses.index', compact('courses', 'courseStats'));
    }

    public function create(): View
    {
        return view('admin.courses.create', $this->courseDesignOptions());
    }

    public function store(Request $request, CourseCommunityService $community): RedirectResponse
    {
        $this->assertLessonContentBlockPayloadIsComplete($request, 'lessons');

        $validated = $this->validateCourse($request, validateLessons: true);
        $courseData = $this->normalizedCourseData($request, $validated['course']);

        $slug = $this->uniqueSlug($courseData['slug'] ?? Str::slug($courseData['title']));

        $course = null;

        DB::transaction(function () use ($validated, $courseData, $slug, &$course): void {
            $course = Course::create([
                ...collect($courseData)->except('slug')->all(),
                'slug' => $slug,
            ]);

            $moduleMap = $this->syncModules($course, $validated['modules'] ?? [], $validated['lessons'] ?? []);

            foreach ($validated['lessons'] ?? [] as $index => $lesson) {
                $createdLesson = $course->lessons()->create($this->normalizedLessonData($course, $lesson, $index, $moduleMap));

                if ($this->shouldSyncContentBlocks($lesson)) {
                    $this->syncLessonContentBlocks($createdLesson, $lesson['content_blocks'] ?? []);
                }
            }
        });

        if ($course) {
            $community->ensureForCourse($course, $request->user());

            if ($course->is_published) {
                $this->notifyModelsOfPublishedCourse($course);
            }
        }

        return redirect()->route('admin.courses.index')->with('status', __('Course created.'));
    }

    public function edit(Course $course): View
    {
        $course->load([
            'modules' => fn ($q) => $q->orderBy('sort_order'),
            'lessons' => fn ($q) => $q->with(['module', 'contentBlocks'])->orderBy('sort_order'),
        ]);

        return view('admin.courses.edit', [
            'course' => $course,
            ...$this->courseDesignOptions(),
        ]);
    }

    public function preview(Course $course): View
    {
        $course = $this->previewCoursePayload($course);
        $selectedLesson = $course->hasPreLessonMaterials()
            ? null
            : $course->lessons->first();

        return $this->previewLearningView($course, $selectedLesson);
    }

    public function previewLesson(Course $course, Lesson $lesson): View
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $course = $this->previewCoursePayload($course);
        $selectedLesson = $course->lessons->firstWhere('id', $lesson->id);

        abort_unless($selectedLesson !== null, 404);

        return $this->previewLearningView($course, $selectedLesson);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $wasPublished = (bool) $course->is_published;

        // Detect PHP max_input_vars truncation before any validation.
        // The frontend sends _lesson_count with the actual total. If fewer lessons
        // arrived than expected, the payload was silently cut off by PHP — abort
        // instead of deleting the missing lessons.
        $claimedCount = (int) $request->input('_lesson_count', 0);
        $receivedLessons = $request->input('lessons', []);
        $receivedCount = is_array($receivedLessons) ? count($receivedLessons) : 0;

        if ($claimedCount > 0 && $receivedCount < $claimedCount) {
            Log::error('Course update payload truncated by PHP max_input_vars', [
                'course_id' => $course->id,
                'claimed_lesson_count' => $claimedCount,
                'received_lesson_count' => $receivedCount,
                'max_input_vars' => ini_get('max_input_vars'),
            ]);

            return redirect()->back()->withInput()->withErrors([
                'lessons' => __(
                    'Only :received of :total lessons were received — the form was too large and PHP cut it off. '.
                    'Please contact support or increase php_value max_input_vars in .htaccess. No data was changed.',
                    ['received' => $receivedCount, 'total' => $claimedCount]
                ),
            ]);
        }

        $this->assertLessonContentBlockPayloadIsComplete($request, 'lessons');

        $validated = $this->validateCourse($request, $course->id, validateLessons: true);
        $courseData = $this->normalizedCourseData($request, $validated['course'], $course);

        $slugInput = $courseData['slug'] ?? null;
        $slug = $slugInput !== null && $slugInput !== ''
            ? $this->uniqueSlug(Str::slug($slugInput), $course->id)
            : $this->uniqueSlug(Str::slug($courseData['title']), $course->id);

        DB::transaction(function () use ($course, $validated, $courseData, $slug): void {
            $course->update([
                ...collect($courseData)->except('slug')->all(),
                'slug' => $slug,
            ]);

            $moduleMap = $this->syncModules($course, $validated['modules'] ?? [], $validated['lessons'] ?? []);

            $this->syncLessons($course, $validated['lessons'] ?? [], $moduleMap);
        });

        $course->refresh();
        if (! $wasPublished && $course->is_published) {
            $this->notifyModelsOfPublishedCourse($course);
        }

        return redirect()->route('admin.courses.index')->with('status', __('Course updated.'));
    }

    public function updateDetails(Request $request, Course $course): JsonResponse
    {
        $validated = $this->validateCourse($request, $course->id, validateLessons: false);
        $courseData = $this->normalizedCourseData($request, $validated['course'], $course);

        $slugInput = $courseData['slug'] ?? null;
        $slug = $slugInput !== null && $slugInput !== ''
            ? $this->uniqueSlug(Str::slug($slugInput), $course->id)
            : $this->uniqueSlug(Str::slug($courseData['title']), $course->id);

        $course->update([
            ...collect($courseData)->except('slug')->all(),
            'slug' => $slug,
        ]);

        return response()->json(['saved' => true, 'slug' => $course->fresh()->slug]);
    }

    public function visibility(Request $request, Course $course): RedirectResponse
    {
        $wasPublished = (bool) $course->is_published;

        $course->update([
            'is_published' => $request->boolean('is_published'),
        ]);

        if (! $wasPublished && $course->is_published) {
            $this->notifyModelsOfPublishedCourse($course);
        }

        return redirect()->route('admin.courses.index')->with('status', __('Course visibility updated.'));
    }

    public function uploadBlockFile(Request $request): JsonResponse
    {
        $type = $request->input('type');

        $fileRule = match ($type) {
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            // Presentation: PDF only — admin exports PowerPoint to PDF before uploading.
            'presentation' => ['required', 'file', 'mimes:pdf', 'max:102400'],
            default => ['required', 'file', 'mimes:pdf', 'max:20480'],
        };

        $request->validate([
            'type' => ['required', 'string', Rule::in(['image', 'pdf', 'presentation'])],
            'file' => $fileRule,
        ]);

        $directory = match ($type) {
            'image' => 'academy/lesson-content/images',
            'pdf' => 'academy/lesson-content/pdfs',
            default => 'academy/lesson-content/presentations',
        };

        $path = $request->file('file')->store($directory, 'public');
        $slideImages = $type === 'presentation'
            ? $this->createPresentationSlideImages($path)
            : [];

        return response()->json([
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'slide_images' => $slideImages,
        ]);
    }

    public function destroy(Course $course, CourseCommunityService $community): RedirectResponse
    {
        $community->archiveForCourse($course);
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', __('Course deleted.'));
    }

    private function previewCoursePayload(Course $course): Course
    {
        $course = Course::query()
            ->whereKey($course->id)
            ->with([
                'lessons' => fn ($query) => $query
                    ->with(['module', 'contentBlocks'])
                    ->orderBy('sort_order'),
                'modules' => fn ($query) => $query
                    ->with(['lessons' => fn ($lessonQuery) => $lessonQuery
                        ->with('contentBlocks')
                        ->orderBy('sort_order')])
                    ->orderBy('sort_order'),
                'chatRoom',
            ])
            ->withCount([
                'lessons as lessons_count',
                'modules as modules_count',
                'enrollments as enrolled_users_count',
            ])
            ->firstOrFail();

        $course->setRelation('lessons', $course->lessonsInModuleOrder());

        return $course;
    }

    private function previewLearningView(Course $course, ?Lesson $selectedLesson): View
    {
        $lessonIds = $course->lessons->pluck('id')->values();
        if ($selectedLesson === null && ! $course->hasPreLessonMaterials()) {
            $selectedLesson = $course->lessons->first();
        }
        $selectedIndex = $selectedLesson ? $lessonIds->search($selectedLesson->id) : false;
        $previousLesson = $selectedIndex !== false && $selectedIndex > 0
            ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex - 1])
            : null;
        $nextLesson = $selectedIndex !== false && $selectedIndex < $lessonIds->count() - 1
            ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex + 1])
            : null;

        $progress = [
            'completed' => 0,
            'total' => $lessonIds->count(),
            'percent' => 0,
            'completedLessonIds' => [],
        ];

        $moduleProgress = $course->modules->mapWithKeys(function (CourseModule $module) {
            $total = $module->lessons->count();

            return [$module->id => [
                'completed' => 0,
                'total' => $total,
                'percent' => 0,
            ]];
        });

        $messages = collect();
        $previewMode = true;
        $previewExitUrl = route('admin.courses.edit', $course);

        return view('member.courses.learn', compact(
            'course',
            'selectedLesson',
            'previousLesson',
            'nextLesson',
            'progress',
            'moduleProgress',
            'messages',
            'previewMode',
            'previewExitUrl'
        ));
    }

    private function validateCourse(Request $request, ?int $courseId = null, bool $validateLessons = false): array
    {
        $httpUrlRule = $this->httpUrlRule();
        $trustedFileReferenceRule = $this->trustedFileReferenceRule();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('courses', 'slug')->ignore($courseId)],
            'platform_label' => ['required', 'string', 'max:255'],
            'platform_color' => ['nullable', 'string', 'max:32', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['required', 'string', 'max:10000'],
            'short_description' => ['nullable', 'string', 'max:1200'],
            'thumbnail_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'course_cover_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'difficulty_level' => ['nullable', 'string', 'max:64'],
            'estimated_duration' => ['nullable', 'string', 'max:64'],
            'what_you_will_learn' => ['nullable', 'string', 'max:50000'],
            'requirements' => ['nullable', 'string', 'max:50000'],
            'course_access_requirements' => ['nullable', 'string', 'max:50000'],
            'access_registration_instructions' => ['nullable', 'string', 'max:50000'],
            'access_callback_instructions' => ['nullable', 'string', 'max:50000'],
            'access_onboarding_instructions' => ['nullable', 'string', 'max:50000'],
            'access_verification_instructions' => ['nullable', 'string', 'max:50000'],
            'has_course_outline' => ['nullable', 'boolean'],
            'course_outline_url' => ['nullable', 'string', 'max:2000', $trustedFileReferenceRule],
            'course_outline_upload' => [
                Rule::requiredIf(fn () => $request->boolean('has_course_outline') && blank($request->input('course_outline_url'))),
                'file',
                'mimes:pdf,doc,docx,ppt,pptx',
                'max:20480',
            ],
            'has_intro' => ['nullable', 'boolean'],
            'intro_title' => ['nullable', 'string', 'max:255'],
            'intro_video_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'intro_bunny_video_id' => ['nullable', 'string', 'max:64'],
            'intro_bunny_library_id' => ['nullable', 'string', 'max:64'],
            'intro_bunny_video_title' => ['nullable', 'string', 'max:255'],
            'intro_bunny_thumbnail_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
            'intro_bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
            'intro_bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
            'intro_duration' => ['nullable', 'string', 'max:64'],
            'intro_body' => ['nullable', 'string', 'max:50000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];

        if ($validateLessons) {
            $lessonIdRule = ['nullable', 'integer'];
            $moduleIdRule = ['nullable', 'integer'];
            if ($courseId !== null) {
                // Accept IDs that belong to this course OR don't exist at all (draft
                // recovery: syncLessons re-creates missing IDs as new records).
                // Only reject an ID that belongs to a *different* course.
                $lessonIdRule[] = function ($attribute, $value, $fail) use ($courseId) {
                    if (filled($value) && Lesson::where('id', (int) $value)->where('course_id', '!=', $courseId)->exists()) {
                        $fail(__('validation.exists'));
                    }
                };
                $moduleIdRule[] = function ($attribute, $value, $fail) use ($courseId) {
                    if (filled($value) && CourseModule::where('id', (int) $value)->where('course_id', '!=', $courseId)->exists()) {
                        $fail(__('validation.exists'));
                    }
                };
            }

            $rules += [
                'modules' => ['nullable', 'array'],
                'modules.*.id' => $moduleIdRule,
                'modules.*.client_key' => ['required_with:modules', 'string', 'max:80'],
                'modules.*.title' => ['required_with:modules', 'string', 'max:255'],
                'modules.*.description' => ['nullable', 'string', 'max:50000'],
                'modules.*.is_published' => ['nullable', 'boolean'],
                'modules.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
                'lessons' => ['required', 'array', 'min:1'],
                'lessons.*.id' => $lessonIdRule,
                'lessons.*.course_id' => $courseId !== null
                    ? ['nullable', 'integer', Rule::in([$courseId])]
                    : ['nullable', 'integer'],
                'lessons.*.course_module_id' => $moduleIdRule,
                'lessons.*.module_key' => ['nullable', 'string', 'max:80'],
                'lessons.*.title' => ['required', 'string', 'max:255'],
                'lessons.*.module_title' => ['nullable', 'string', 'max:255'],
                'lessons.*.body' => ['nullable', 'string', 'max:50000'],
                'lessons.*.overview' => ['nullable', 'string', 'max:50000'],
                'lessons.*.steps' => ['nullable', 'string', 'max:50000'],
                'lessons.*.tips' => ['nullable', 'string', 'max:50000'],
                'lessons.*.safety_notes' => ['nullable', 'string', 'max:50000'],
                'lessons.*.resource_links' => ['nullable', 'string', 'max:50000'],
                'lessons.*.lesson_banner_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
                'lessons.*.lesson_images_upload' => ['nullable', 'array', 'max:12'],
                'lessons.*.lesson_images_upload.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
                'lessons.*.is_published' => ['nullable', 'boolean'],
                'lessons.*.video_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
                'lessons.*.bunny_video_id' => ['nullable', 'string', 'max:64'],
                'lessons.*.bunny_library_id' => ['nullable', 'string', 'max:64'],
                'lessons.*.bunny_video_title' => ['nullable', 'string', 'max:255'],
                'lessons.*.bunny_thumbnail_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
                'lessons.*.bunny_upload_fingerprint' => ['nullable', 'string', 'max:255'],
                'lessons.*.bunny_status' => ['nullable', 'integer', 'min:0', 'max:255'],
                'lessons.*.duration' => ['nullable', 'string', 'max:64'],
                'lessons.*.has_pdf' => ['nullable', 'boolean'],
                'lessons.*.pdf_url' => ['nullable', 'string', 'max:2000', $httpUrlRule],
                'lessons.*.presentation_url' => ['nullable', 'string', 'max:50000', $httpUrlRule],
                'lessons.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            ];
            $rules += $this->lessonContentBlockRules('lessons.*.content_blocks');
        }

        $validated = $request->validate($rules);

        return [
            'course' => collect($validated)->only([
                'title',
                'slug',
                'platform_label',
                'platform_color',
                'description',
                'short_description',
                'thumbnail_url',
                'difficulty_level',
                'estimated_duration',
                'what_you_will_learn',
                'requirements',
                'course_access_requirements',
                'access_registration_instructions',
                'access_callback_instructions',
                'access_onboarding_instructions',
                'access_verification_instructions',
                'has_course_outline',
                'course_outline_url',
                'has_intro',
                'intro_title',
                'intro_video_url',
                'intro_bunny_video_id',
                'intro_bunny_library_id',
                'intro_bunny_video_title',
                'intro_bunny_thumbnail_url',
                'intro_bunny_upload_fingerprint',
                'intro_bunny_status',
                'intro_duration',
                'intro_body',
                'sort_order',
            ])->all(),
            'modules' => $validated['modules'] ?? [],
            'lessons' => $validated['lessons'] ?? [],
        ];
    }

    private function normalizedCourseData(Request $request, array $course, ?Course $existingCourse = null): array
    {
        $course += [
            'course_outline_url' => null,
            'short_description' => null,
            'thumbnail_url' => null,
            'course_cover_image' => $existingCourse?->course_cover_image,
            'difficulty_level' => null,
            'estimated_duration' => null,
            'what_you_will_learn' => null,
            'requirements' => null,
            'course_access_requirements' => null,
            'access_registration_instructions' => null,
            'access_callback_instructions' => null,
            'access_onboarding_instructions' => null,
            'access_verification_instructions' => null,
            'intro_title' => null,
            'intro_video_url' => null,
            'intro_bunny_video_id' => null,
            'intro_bunny_library_id' => null,
            'intro_bunny_video_title' => null,
            'intro_bunny_thumbnail_url' => null,
            'intro_bunny_upload_fingerprint' => null,
            'intro_bunny_status' => null,
            'intro_duration' => null,
            'intro_body' => null,
        ];

        $course['is_published'] = $request->boolean('is_published');
        $course['has_course_outline'] = $request->boolean('has_course_outline');
        $course['has_intro'] = $request->boolean('has_intro');

        $coverImage = $request->file('course_cover_image_upload');
        if ($coverImage instanceof UploadedFile) {
            $course['course_cover_image'] = $this->storePublicImage($coverImage, 'academy/course-covers');
        }

        if (! $course['has_course_outline']) {
            $course['course_outline_url'] = null;
        } else {
            $outlineUpload = $request->file('course_outline_upload');
            if ($outlineUpload instanceof UploadedFile) {
                $course['course_outline_url'] = $this->storePrivateDocument($outlineUpload, 'academy/course-outlines');
            }
        }

        if (! $course['has_intro']) {
            $course['intro_title'] = null;
            $course['intro_video_url'] = null;
            $course['intro_bunny_video_id'] = null;
            $course['intro_bunny_library_id'] = null;
            $course['intro_bunny_video_title'] = null;
            $course['intro_bunny_thumbnail_url'] = null;
            $course['intro_bunny_upload_fingerprint'] = null;
            $course['intro_bunny_status'] = null;
            $course['intro_duration'] = null;
            $course['intro_body'] = null;
        } elseif (blank($course['intro_title'])) {
            $course['intro_title'] = 'Course Orientation';
        }

        if (filled($course['intro_bunny_video_id']) && filled($course['intro_bunny_library_id'])) {
            $course['intro_video_url'] = 'https://iframe.mediadelivery.net/embed/'.$course['intro_bunny_library_id'].'/'.$course['intro_bunny_video_id'].'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
        }

        return $course;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lessons
     */
    private function syncLessons(Course $course, array $lessons, array $moduleMap = []): void
    {
        $existingLessons = $course->lessons()->get()->values();
        $existingLessonsById = $existingLessons->keyBy('id');
        $submittedIds = collect($lessons)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();
        $hasSubmittedIds = $submittedIds !== [];
        $syncedExistingIds = [];

        foreach ($lessons as $index => $lesson) {
            $existingLesson = ! empty($lesson['id'])
                ? $existingLessonsById->get((int) $lesson['id'])
                : null;

            $lessonData = $this->normalizedLessonData($course, $lesson, $index, $moduleMap, $existingLesson);

            if ($existingLesson !== null) {
                $existingLesson->update($lessonData);
                if ($this->shouldSyncContentBlocks($lesson)) {
                    $this->syncLessonContentBlocks($existingLesson, $lesson['content_blocks'] ?? []);
                }
                $syncedExistingIds[] = $existingLesson->id;

                continue;
            }

            $createdLesson = $course->lessons()->create($lessonData);
            if ($this->shouldSyncContentBlocks($lesson)) {
                $this->syncLessonContentBlocks($createdLesson, $lesson['content_blocks'] ?? []);
            }
            $syncedExistingIds[] = $createdLesson->id;
        }

        if ($hasSubmittedIds) {
            $course->lessons()
                ->whereNotIn('id', array_unique($syncedExistingIds))
                ->delete();
        }
    }

    private function normalizedLessonData(Course $course, array $lesson, int $index, array $moduleMap = [], ?Lesson $existingLesson = null): array
    {
        $lessonValue = fn (string $key) => array_key_exists($key, $lesson)
            ? $lesson[$key]
            : $existingLesson?->{$key};

        $bunnyVideoId = $lessonValue('bunny_video_id');
        $bunnyLibraryId = $lessonValue('bunny_library_id');
        $videoUrl = $lessonValue('video_url');
        $moduleId = $this->moduleIdForLesson($lesson, $moduleMap);
        $lessonVisuals = $this->normalizedLessonVisuals($lesson, $existingLesson);
        $pdfUrl = $lessonValue('pdf_url');

        if (filled($bunnyVideoId) && filled($bunnyLibraryId)) {
            $videoUrl = 'https://iframe.mediadelivery.net/embed/'.$bunnyLibraryId.'/'.$bunnyVideoId.'?autoplay=false&loop=false&muted=false&preload=true&responsive=true';
        }

        return [
            'course_id' => $course->id,
            'course_module_id' => $moduleId,
            'title' => $lesson['title'],
            'body' => $lessonValue('body'),
            'overview' => $lessonValue('overview'),
            'steps' => $lessonValue('steps'),
            'tips' => $lessonValue('tips'),
            'safety_notes' => $lessonValue('safety_notes'),
            'resource_links' => $lessonValue('resource_links'),
            'lesson_banner_image' => $lessonVisuals['lesson_banner_image'],
            'lesson_images' => $lessonVisuals['lesson_images'],
            'is_published' => array_key_exists('is_published', $lesson) ? (bool) $lesson['is_published'] : (bool) ($existingLesson?->is_published ?? true),
            'video_url' => $videoUrl,
            'bunny_video_id' => $bunnyVideoId,
            'bunny_library_id' => $bunnyLibraryId,
            'bunny_video_title' => $lessonValue('bunny_video_title'),
            'bunny_thumbnail_url' => $lessonValue('bunny_thumbnail_url'),
            'bunny_upload_fingerprint' => $lessonValue('bunny_upload_fingerprint'),
            'bunny_status' => $lessonValue('bunny_status'),
            'duration' => $lessonValue('duration'),
            'has_pdf' => array_key_exists('has_pdf', $lesson)
                ? (bool) $lesson['has_pdf']
                : (array_key_exists('pdf_url', $lesson) ? filled($pdfUrl) : (bool) ($existingLesson?->has_pdf ?? filled($pdfUrl))),
            'pdf_url' => $pdfUrl,
            'presentation_url' => Lesson::normalizePresentationUrl($lessonValue('presentation_url')),
            'sort_order' => $lesson['sort_order'] ?? $existingLesson?->sort_order ?? ($index + 1),
        ];
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

    private function storePublicImage(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    private function storePrivateImage(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    private function storePrivateDocument(UploadedFile $file, string $directory): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'file');
        $filename = Str::slug($name) ?: 'course-outline';

        return $file->storeAs($directory, $filename.'-'.Str::random(8).'.'.$extension, 'local');
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

    private function trustedFileReferenceRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (blank($value)) {
                return;
            }

            $value = trim(str_replace('\\', '/', (string) $value), '/');

            if (
                preg_match('/^https?:\/\//i', $value)
                || (str_starts_with($value, 'academy/') && ! str_contains($value, '..') && ! str_contains($value, "\0"))
            ) {
                return;
            }

            $fail(__('The :attribute must be a valid uploaded academy file or HTTP(S) URL.', ['attribute' => str_replace('_', ' ', $attribute)]));
        };
    }

    /**
     * @param  array<string, mixed>  $lesson
     */
    private function shouldSyncContentBlocks(array $lesson): bool
    {
        return array_key_exists('content_blocks', $lesson)
            || array_key_exists('_content_block_count', $lesson);
    }

    /**
     * @param  array<int, array<string, mixed>>  $modules
     * @param  array<int, array<string, mixed>>  $lessons
     * @return array<string, int>
     */
    private function syncModules(Course $course, array $modules, array $lessons): array
    {
        if ($modules === []) {
            $modules = $this->modulesFromLessons($lessons);
        }

        $existing = $course->modules()
            ->get()
            ->keyBy('id');
        $existingByTitle = $existing->keyBy(fn (CourseModule $module) => $this->moduleKey($module->title));
        $moduleMap = [];
        $usedIds = [];

        foreach (array_values($modules) as $index => $moduleData) {
            $title = $this->moduleTitle($moduleData['title'] ?? null);
            $clientKey = $moduleData['client_key'] ?? $this->moduleKey($title);
            $module = ! empty($moduleData['id'])
                ? $existing->get((int) $moduleData['id'])
                : null;
            $module ??= $existingByTitle->get($this->moduleKey($title));
            $module ??= new CourseModule(['course_id' => $course->id]);

            $module->fill([
                'course_id' => $course->id,
                'title' => $title,
                'description' => $moduleData['description'] ?? null,
                'is_published' => array_key_exists('is_published', $moduleData) ? (bool) $moduleData['is_published'] : true,
                'sort_order' => $moduleData['sort_order'] ?? ($index + 1),
            ]);
            $module->save();

            $moduleMap[$clientKey] = $module->id;
            $moduleMap[$this->moduleKey($title)] = $module->id;
            $moduleMap['id:'.$module->id] = $module->id;
            $usedIds[] = $module->id;
        }

        $course->modules()
            ->whereNotIn('id', $usedIds)
            ->update(['is_published' => false]);

        return $moduleMap;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lessons
     * @return array<int, array<string, mixed>>
     */
    private function modulesFromLessons(array $lessons): array
    {
        $modules = [];

        foreach ($lessons as $index => $lesson) {
            $title = $this->moduleTitle($lesson['module_title'] ?? null);
            $key = $this->moduleKey($title);

            $modules[$key] ??= [
                'client_key' => $lesson['module_key'] ?? $key,
                'title' => $title,
                'description' => null,
                'is_published' => true,
                'sort_order' => $index + 1,
            ];
        }

        return array_values($modules ?: [[
            'client_key' => $this->moduleKey(null),
            'title' => $this->moduleTitle(null),
            'description' => null,
            'is_published' => true,
            'sort_order' => 1,
        ]]);
    }

    /**
     * @param  array<string, mixed>  $lesson
     * @param  array<string, int>  $moduleMap
     */
    private function moduleIdForLesson(array $lesson, array $moduleMap): ?int
    {
        $moduleId = $lesson['course_module_id'] ?? null;
        if ($moduleId !== null && isset($moduleMap['id:'.(int) $moduleId])) {
            return $moduleMap['id:'.(int) $moduleId];
        }

        $moduleKey = $lesson['module_key'] ?? null;
        if (is_string($moduleKey) && isset($moduleMap[$moduleKey])) {
            return $moduleMap[$moduleKey];
        }

        return null;
    }

    private function moduleTitle(?string $title): string
    {
        $title = trim((string) $title);

        return $title !== '' ? $title : 'Core Training';
    }

    private function moduleKey(?string $title): string
    {
        return Str::lower($this->moduleTitle($title));
    }

    private function uniqueSlug(string $base, ?int $ignoreCourseId = null): string
    {
        $slug = $base !== '' ? $base : Str::random(8);
        $original = $slug;
        $i = 1;

        while (Course::where('slug', $slug)
            ->when($ignoreCourseId, fn ($q) => $q->where('id', '!=', $ignoreCourseId))
            ->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }

    private function courseDesignOptions(): array
    {
        return [
            'platformSuggestions' => [
                ['name' => 'Chaturbate', 'color' => '#FF8C00'],
                ['name' => 'Stripchat', 'color' => '#FF3E4D'],
                ['name' => 'OnlyFans', 'color' => '#00AFF0'],
                ['name' => 'Fansly', 'color' => '#9B6DFF'],
                ['name' => 'Babestation', 'color' => '#E91E8C'],
                ['name' => 'LiveJasmin', 'color' => '#FF6B35'],
                ['name' => 'BongaCams', 'color' => '#FF4444'],
                ['name' => 'Cam4', 'color' => '#22C55E'],
                ['name' => 'CamSoda', 'color' => '#06B6D4'],
                ['name' => 'MyFreeCams', 'color' => '#8B5CF6'],
                ['name' => 'Flirt4Free', 'color' => '#F472B6'],
                ['name' => 'Streamate', 'color' => '#F59E0B'],
                ['name' => 'Instagram', 'color' => '#E1306C'],
                ['name' => 'TikTok', 'color' => '#FF0050'],
            ],
            'colorSwatches' => [
                '#FF8C00',
                '#FF3E4D',
                '#E91E8C',
                '#FF6B35',
                '#00AFF0',
                '#9B6DFF',
                '#22C55E',
                '#F59E0B',
                '#06B6D4',
                '#F472B6',
                '#C9A96E',
                '#6366F1',
            ],
        ];
    }

    private function notifyModelsOfPublishedCourse(Course $course): void
    {
        User::query()
            ->where('role', 'model')
            ->each(fn (User $model) => $model->notify(new SystemNotification(
                title: __('New course available'),
                body: __(':course has been added to the academy. Open it to review Kayla access requirements and request access.', [
                    'course' => $course->title,
                ]),
                actionUrl: route('member.courses.show', $course->slug, false),
                category: 'new_course',
            )));
    }
}
