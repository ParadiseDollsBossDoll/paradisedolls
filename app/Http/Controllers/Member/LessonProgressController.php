<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\AdminActivityNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonProgressController extends Controller
{
    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $course = $lesson->course;
        abort_unless($course->is_published && $lesson->is_published, 404);

        $completed = $request->boolean('completed');

        $progress = LessonProgress::firstOrNew([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id,
        ]);
        $wasCompleted = $progress->exists && $progress->completed_at !== null;

        $progress->completed_at = $completed ? now() : null;
        $progress->save();

        if ($completed) {
            if (! $wasCompleted && $this->isDosAndDontsLesson($lesson)) {
                $this->notifyAdminOfDosAndDontsCompletion($request, $lesson);
            }

            $course->load([
                'lessons' => fn ($query) => $query
                    ->publishedForMembers()
                    ->orderBy('sort_order'),
                'modules' => fn ($query) => $query
                    ->where('is_published', true)
                    ->with(['lessons' => fn ($lessonQuery) => $lessonQuery
                        ->publishedForMembers()
                        ->orderBy('sort_order')])
                    ->orderBy('sort_order'),
            ]);
            $course->setRelation('lessons', $course->lessonsInModuleOrder());

            $lessonIds = $course->lessons->pluck('id')->values();
            $selectedIndex = $lessonIds->search($lesson->id);
            $nextLesson = $selectedIndex !== false && $selectedIndex < $lessonIds->count() - 1
                ? $course->lessons->firstWhere('id', $lessonIds[$selectedIndex + 1])
                : null;

            if ($nextLesson !== null) {
                return redirect()
                    ->route('member.courses.lessons.show', [$course->slug, $nextLesson])
                    ->with('status', __('Lesson completed.'));
            }

            return redirect()->back()->with('status', __('Course completed.'));
        }

        return redirect()->back()->with('status', __('Progress updated.'));
    }

    private function isDosAndDontsLesson(Lesson $lesson): bool
    {
        $lesson->loadMissing('course', 'module');

        $text = Str::lower(implode(' ', array_filter([
            $lesson->title,
            $lesson->module?->title,
            $lesson->course?->title,
        ])));

        $hasDo = Str::contains($text, ['do\'s', 'dos', 'do and', 'do &']);
        $hasDont = Str::contains($text, ['don\'t', 'dont', 'donts', 'do not']);

        return $hasDo && $hasDont;
    }

    private function notifyAdminOfDosAndDontsCompletion(Request $request, Lesson $lesson): void
    {
        $lesson->loadMissing('course');
        $profile = $request->user()->modelProfile()->first();
        $actionUrl = $profile
            ? route('admin.onboarding.show', ['profile' => $profile], false)
            : route('admin.models.progress', ['member' => $request->user()->id], false);

        app(AdminActivityNotifier::class)->notify(
            title: __('Do\'s & Don\'ts completed'),
            body: __(':name completed the Do\'s & Don\'ts section for :course.', [
                'name' => $request->user()->name,
                'course' => $lesson->course?->title ?: __('a course'),
            ]),
            actionUrl: $actionUrl,
            category: 'dos_donts_completed',
            emailSubject: __('Do\'s & Don\'ts completed: :name', ['name' => $request->user()->name]),
            details: [
                __('Member') => $request->user()->name,
                __('Email') => $request->user()->email,
                __('Course') => $lesson->course?->title,
                __('Lesson') => $lesson->title,
            ],
            actionLabel: __('Review member progress'),
        );
    }
}
