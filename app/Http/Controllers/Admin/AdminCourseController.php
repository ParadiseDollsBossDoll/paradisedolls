<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->with('lessons:id,course_id')
            ->withCount(['lessons', 'chatMessages'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20);

        $courseStats = $courses->getCollection()->mapWithKeys(function (Course $course) {
            $lessonIds = $course->lessons->pluck('id');
            $totalLessons = $lessonIds->count();

            $started = LessonProgress::query()
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->distinct('user_id')
                ->count('user_id');

            $finished = $totalLessons === 0
                ? 0
                : LessonProgress::query()
                    ->select('user_id')
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereNotNull('completed_at')
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(DISTINCT lesson_id) >= ?', [$totalLessons])
                    ->get()
                    ->count();

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

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCourse($request, validateLessons: true);
        $validated['course']['is_published'] = $request->boolean('is_published');

        $slug = $this->uniqueSlug($validated['course']['slug'] ?? Str::slug($validated['course']['title']));

        DB::transaction(function () use ($validated, $slug): void {
            $course = Course::create([
                ...collect($validated['course'])->except('slug')->all(),
                'slug' => $slug,
            ]);

            foreach ($validated['lessons'] ?? [] as $index => $lesson) {
                $course->lessons()->create([
                    'title' => $lesson['title'],
                    'body' => $lesson['body'] ?? null,
                    'video_url' => $lesson['video_url'] ?? null,
                    'duration' => $lesson['duration'] ?? null,
                    'has_pdf' => (bool) ($lesson['has_pdf'] ?? false),
                    'pdf_url' => $lesson['pdf_url'] ?? null,
                    'presentation_url' => $lesson['presentation_url'] ?? null,
                    'sort_order' => $lesson['sort_order'] ?? ($index + 1),
                ]);
            }
        });

        return redirect()->route('admin.courses.index')->with('status', __('Course created.'));
    }

    public function edit(Course $course): View
    {
        $course->load(['lessons' => fn ($q) => $q->orderBy('sort_order')]);

        return view('admin.courses.edit', [
            'course' => $course,
            ...$this->courseDesignOptions(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $this->validateCourse($request, $course->id);
        $validated['course']['is_published'] = $request->boolean('is_published');

        $slugInput = $validated['course']['slug'] ?? null;
        $slug = $slugInput !== null && $slugInput !== ''
            ? $this->uniqueSlug(Str::slug($slugInput), $course->id)
            : $this->uniqueSlug(Str::slug($validated['course']['title']), $course->id);

        $course->update([
            ...collect($validated['course'])->except('slug')->all(),
            'slug' => $slug,
        ]);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Course updated.'));
    }

    public function visibility(Request $request, Course $course): RedirectResponse
    {
        $course->update([
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.courses.index')->with('status', __('Course visibility updated.'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', __('Course deleted.'));
    }

    private function validateCourse(Request $request, ?int $courseId = null, bool $validateLessons = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('courses', 'slug')->ignore($courseId)],
            'platform_label' => ['required', 'string', 'max:255'],
            'platform_color' => ['nullable', 'string', 'max:32', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['required', 'string', 'max:10000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];

        if ($validateLessons) {
            $rules += [
                'lessons' => ['required', 'array', 'min:1'],
                'lessons.*.title' => ['required', 'string', 'max:255'],
                'lessons.*.body' => ['nullable', 'string', 'max:50000'],
                'lessons.*.video_url' => ['nullable', 'string', 'max:2000'],
                'lessons.*.duration' => ['nullable', 'string', 'max:64'],
                'lessons.*.has_pdf' => ['nullable', 'boolean'],
                'lessons.*.pdf_url' => ['nullable', 'string', 'max:2000'],
                'lessons.*.presentation_url' => ['nullable', 'string', 'max:2000'],
                'lessons.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            ];
        }

        $validated = $request->validate($rules);

        return [
            'course' => collect($validated)->only([
                'title',
                'slug',
                'platform_label',
                'platform_color',
                'description',
                'sort_order',
            ])->all(),
            'lessons' => $validated['lessons'] ?? [],
        ];
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
}
