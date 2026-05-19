<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\CourseAccessRequestedMail;
use App\Models\Course;
use App\Models\CourseAccessRequest;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\CourseCommunityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class MemberCourseController extends Controller
{
    public function index(Request $request): View
    {
        $coursesPaginator = Course::query()
            ->where('is_published', true)
            ->with([
                'lessons' => fn ($q) => $q
                    ->publishedForMembers()
                    ->select('id', 'course_id', 'course_module_id', 'title', 'sort_order', 'is_published')
                    ->orderBy('sort_order'),
            ])
            ->withCount([
                'publishedLessons as lessons_count',
                'publishedModules as modules_count',
                'enrollments as enrolled_users_count',
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $courses = $coursesPaginator->getCollection();

        $progressPercents = Course::batchProgressPercentsForUser($request->user(), $courses);

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($lessonId) => (int) $lessonId)
            ->all();

        $courseProgress = $courses->mapWithKeys(function (Course $course) use ($completedLessonIds, $progressPercents) {
            $completed = $course->lessons->whereIn('id', $completedLessonIds)->count();
            $total = $course->lessons->count();
            $isDone = $total > 0 && $completed === $total;

            return [$course->id => [
                'completed' => $completed,
                'total' => $total,
                'percent' => $progressPercents[$course->id] ?? 0,
                'status' => $isDone ? 'completed' : ($completed > 0 ? 'in-progress' : 'new'),
            ]];
        });

        $filter = $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'new', 'in-progress', 'completed'], true)) {
            $filter = 'all';
        }

        $filteredCourses = $filter === 'all'
            ? $courses
            : $courses->filter(fn (Course $course) => ($courseProgress[$course->id]['status'] ?? 'new') === $filter)->values();

        $enrolledCourseIds = $request->user()
            ->enrolledCourses()
            ->pluck('courses.id')
            ->map(fn ($courseId) => (int) $courseId)
            ->all();

        $accessRequestsByCourse = $request->user()
            ->courseAccessRequests()
            ->whereIn('course_id', $courses->pluck('id'))
            ->get()
            ->keyBy('course_id');

        return view('member.courses.index', compact(
            'courses',
            'filteredCourses',
            'courseProgress',
            'completedLessonIds',
            'enrolledCourseIds',
            'accessRequestsByCourse',
            'filter',
            'coursesPaginator'
        ));
    }

    public function learn(Request $request, string $slug, CourseCommunityService $community): RedirectResponse
    {
        $course = $this->publishedCourse($slug);
        $profile = $request->user()->modelProfile()->first();

        if (! $profile?->isVerified()) {
            return redirect()
                ->route('member.courses.show', $course->slug)
                ->with('status', __('Verification must be approved before Kayla can unlock this course.'));
        }

        if (! $course->isEnrolledBy($request->user())) {
            return redirect()
                ->route('member.courses.show', $course->slug)
                ->with('status', __('Locked pending Kayla approval.'));
        }

        $community->joinCourse($request->user(), $course);

        $course = $this->learningCourse($slug);
        $lesson = $this->resumeLesson($course, $request->user()->id);
        $progress = $this->courseProgress($course, $request->user()->id);

        if ($course->hasPreLessonMaterials() && $progress['completed'] === 0) {
            return redirect()
                ->route('member.courses.learn.show', $course->slug)
                ->with('status', __('You are enrolled in this course.'));
        }

        if ($lesson !== null) {
            return redirect()
                ->route('member.courses.lessons.show', [$course->slug, $lesson])
                ->with('status', __('You are enrolled in this course.'));
        }

        return redirect()
            ->route('member.courses.learn.show', $course->slug)
            ->with('status', __('You are enrolled in this course.'));
    }

    public function show(Request $request, string $slug): View
    {
        $course = $this->overviewCourse($slug);
        $isEnrolled = $course->isEnrolledBy($request->user());
        $isVerified = (bool) $request->user()->modelProfile()->first()?->isVerified();
        $courseAccessRequest = $course->accessRequestFor($request->user());
        $progress = $this->courseProgress($course, $request->user()->id);
        $resumeLesson = $this->resumeLesson($course, $request->user()->id);
        $communityChannel = $course->communityChannels()
            ->where('is_archived', false)
            ->first();

        return view('member.courses.show', compact(
            'course',
            'isEnrolled',
            'isVerified',
            'courseAccessRequest',
            'progress',
            'resumeLesson',
            'communityChannel'
        ));
    }

    public function requestAccess(Request $request, string $slug): RedirectResponse
    {
        $course = $this->publishedCourse($slug);
        $profile = $request->user()->modelProfile()->first();

        if (! $profile?->isVerified()) {
            return redirect()
                ->route('member.courses.show', $course->slug)
                ->with('status', __('Verification must be approved before Kayla can review course access.'));
        }

        if ($course->isEnrolledBy($request->user())) {
            return redirect()
                ->route('member.courses.show', $course->slug)
                ->with('status', __('This course is already unlocked for you.'));
        }

        $validated = $request->validate([
            'member_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $accessRequest = CourseAccessRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($accessRequest?->isPending()) {
            $accessRequest->forceFill([
                'member_notes' => $validated['member_notes'] ?? $accessRequest->member_notes,
            ])->save();

            return redirect()
                ->route('member.courses.show', $course->slug)
                ->with('status', __('Your access request is already pending. Kayla will review it soon.'));
        }

        $accessRequest ??= new CourseAccessRequest([
            'course_id' => $course->id,
            'user_id' => $request->user()->id,
        ]);

        $accessRequest->forceFill([
            'status' => CourseAccessRequest::STATUS_PENDING,
            'member_notes' => $validated['member_notes'] ?? null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'admin_notes' => null,
        ])->save();

        $accessRequest->load(['course', 'user']);
        $this->notifyAdminOfAccessRequest($accessRequest);

        return redirect()
            ->route('member.courses.show', $course->slug)
            ->with('status', __('Access request sent. Kayla will review your course requirements and unlock it when approved.'));
    }

    public function learnShow(Request $request, string $slug): View
    {
        $course = $this->learningCourse($slug);
        $progress = $this->courseProgress($course, $request->user()->id);
        $selectedLesson = $course->hasPreLessonMaterials()
            ? null
            : $this->resumeLessonFromProgress($course, $progress['completedLessonIds']);

        return $this->learningView($request, $course, $selectedLesson, $progress);
    }

    public function lesson(Request $request, string $slug, Lesson $lesson): View
    {
        $course = $this->learningCourse($slug);

        abort_unless($lesson->course_id === $course->id && $lesson->is_published, 404);

        return $this->learningView($request, $course, $lesson);
    }

    public function community(Request $request, string $slug): View
    {
        $course = $this->overviewCourse($slug);
        $chatRoom = $course->chatRoom()->firstOrCreate([], [
            'name' => $course->title.' Community',
        ]);

        $messages = $chatRoom->messages()
            ->with('user:id,name')
            ->latest()
            ->take(100)
            ->get()
            ->sortBy('created_at');

        $progress = $this->courseProgress($course, $request->user()->id);

        return view('member.courses.community', compact('course', 'messages', 'progress'));
    }

    private function overviewCourse(string $slug): Course
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'lessons' => fn ($query) => $query
                    ->publishedForMembers()
                    ->with('module')
                    ->orderBy('sort_order'),
                'modules' => fn ($query) => $query
                    ->where('is_published', true)
                    ->with(['lessons' => fn ($lessonQuery) => $lessonQuery
                        ->publishedForMembers()
                        ->orderBy('sort_order')])
                    ->orderBy('sort_order'),
                'chatRoom',
            ])
            ->withCount([
                'publishedLessons as lessons_count',
                'publishedModules as modules_count',
                'enrollments as enrolled_users_count',
            ])
            ->firstOrFail();

        $course->setRelation('lessons', $course->lessonsInModuleOrder());

        return $course;
    }

    private function learningCourse(string $slug): Course
    {
        return $this->overviewCourse($slug);
    }

    private function publishedCourse(string $slug): Course
    {
        return Course::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
    }

    private function notifyAdminOfAccessRequest(CourseAccessRequest $accessRequest): void
    {
        User::query()
            ->where('role', 'admin')
            ->each(fn (User $admin) => $admin->notify(new SystemNotification(
                title: __('Course access requested'),
                body: __(':model requested access to :course.', [
                    'model' => $accessRequest->user->name,
                    'course' => $accessRequest->course->title,
                ]),
                actionUrl: route('admin.onboarding.index', ['model' => $accessRequest->user_id], false),
                category: 'course_access_requested',
            )));

        try {
            Mail::to(config('paradise.onboarding_email'))->queue(new CourseAccessRequestedMail(
                accessRequest: $accessRequest,
                adminUrl: route('admin.onboarding.index'),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function learningView(Request $request, Course $course, ?Lesson $selectedLesson, ?array $progress = null): View
    {
        $progress ??= $this->courseProgress($course, $request->user()->id);
        $lessonIds = $course->lessons->pluck('id')->values();
        if ($selectedLesson === null && ! $course->hasPreLessonMaterials()) {
            $selectedLesson = $course->lessons->first();
        }
        if ($selectedLesson !== null) {
            $selectedLesson = $course->lessons->firstWhere('id', $selectedLesson->id)
                ?: $selectedLesson->loadMissing('contentBlocks');
        }
        $selectedIndex = $selectedLesson ? $lessonIds->search($selectedLesson->id) : false;
        $previousLesson = $selectedIndex !== false && $selectedIndex > 0
            ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex - 1])
            : null;
        $nextLesson = $selectedIndex !== false && $selectedIndex < $lessonIds->count() - 1
            ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex + 1])
            : null;

        $moduleProgress = $course->modules->mapWithKeys(function ($module) use ($progress) {
            $moduleLessonIds = $module->lessons->pluck('id');
            $total = $moduleLessonIds->count();
            $completed = $moduleLessonIds
                ->filter(fn ($lessonId) => in_array((int) $lessonId, $progress['completedLessonIds'], true))
                ->count();

            return [$module->id => [
                'completed' => $completed,
                'total' => $total,
                'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            ]];
        });

        $messages = $course->chatRoom?->messages()
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get()
            ->sortBy('created_at') ?? collect();

        $communityChannel = $course->communityChannels()
            ->where('is_archived', false)
            ->first();

        return view('member.courses.learn', compact(
            'course',
            'selectedLesson',
            'previousLesson',
            'nextLesson',
            'progress',
            'moduleProgress',
            'messages',
            'communityChannel'
        ));
    }

    private function resumeLessonFromProgress(Course $course, array $completedLessonIds): ?Lesson
    {
        if ($course->lessons->isEmpty()) {
            return null;
        }

        return $course->lessons
            ->first(fn (Lesson $lesson) => ! in_array($lesson->id, $completedLessonIds, true))
            ?: $course->lessons->last();
    }

    private function resumeLesson(Course $course, int $userId): ?Lesson
    {
        if ($course->lessons->isEmpty()) {
            return null;
        }

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($lessonId) => (int) $lessonId)
            ->all();

        return $course->lessons
            ->first(fn (Lesson $lesson) => ! in_array($lesson->id, $completedLessonIds, true))
            ?: $course->lessons->last();
    }

    private function courseProgress(Course $course, int $userId): array
    {
        $lessonIds = $course->lessons->pluck('id');
        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->map(fn ($lessonId) => (int) $lessonId)
            ->all();

        $total = $lessonIds->count();
        $completed = count($completedLessonIds);

        return [
            'completed' => $completed,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'completedLessonIds' => $completedLessonIds,
        ];
    }
}
