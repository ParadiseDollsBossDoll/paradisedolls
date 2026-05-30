<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\ModelProfile;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MemberDashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $profile = $user->modelProfile()->firstOrCreate([]);

        $courses = Course::query()
            ->where('is_published', true)
            ->with(['lessons' => fn ($query) => $query->publishedForMembers()->orderBy('sort_order')])
            ->withCount(['publishedLessons as lessons_count'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->all();

        $completedLessonLookup = array_fill_keys($completedLessonIds, true);
        $completedLessons = 0;
        $totalLessons = 0;

        $courseProgress = $courses->mapWithKeys(function (Course $course) use ($completedLessonLookup, &$completedLessons, &$totalLessons) {
            $total = $course->lessons->count();
            $completed = $course->lessons->filter(fn ($lesson) => isset($completedLessonLookup[$lesson->id]))->count();

            $completedLessons += $completed;
            $totalLessons += $total;

            return [$course->id => [
                'completed' => $completed,
                'total' => $total,
                'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            ]];
        });

        $overallPercent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
        $inProgressCount = $courseProgress->filter(fn ($progress) => $progress['completed'] > 0 && $progress['completed'] < $progress['total'])->count();
        $completedCoursesCount = $courseProgress->filter(fn ($progress) => $progress['total'] > 0 && $progress['completed'] === $progress['total'])->count();
        $notStartedCount = $courseProgress->filter(fn ($progress) => $progress['completed'] === 0)->count();
        $dashboardStats = [
            'overall_percent' => $overallPercent,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'in_progress_courses' => $inProgressCount,
            'completed_courses' => $completedCoursesCount,
            'not_started_courses' => $notStartedCount,
            'total_courses' => $courses->count(),
        ];

        $continueCourses = $this->continueCourses($courses, $courseProgress);
        $freshCourses = $this->freshCourses($courses, $courseProgress);
        $nextActionCourse = $continueCourses->first() ?: $freshCourses->first() ?: $courses->first();
        $onboardingPercent = $profile->onboardingPercent();
        $onboardingStatus = $profile->onboardingStatusLabel();
        $onboardingAction = $this->onboardingAction($profile);

        return view('member.dashboard', compact(
            'courses',
            'courseProgress',
            'continueCourses',
            'freshCourses',
            'nextActionCourse',
            'dashboardStats',
            'onboardingPercent',
            'onboardingStatus',
            'onboardingAction',
            'profile'
        ));
    }

    private function continueCourses(Collection $courses, Collection $courseProgress): Collection
    {
        return $courses
            ->filter(fn (Course $course) => ($courseProgress[$course->id]['completed'] ?? 0) > 0
                && ($courseProgress[$course->id]['completed'] ?? 0) < ($courseProgress[$course->id]['total'] ?? 0))
            ->values();
    }

    private function freshCourses(Collection $courses, Collection $courseProgress): Collection
    {
        return $courses
            ->filter(fn (Course $course) => ($courseProgress[$course->id]['completed'] ?? 0) === 0)
            ->take(3)
            ->values();
    }

    private function onboardingAction(ModelProfile $profile): ?array
    {
        $discordInviteUrl = $profile->community_invite_url ?: config('paradise.community_url');

        if (! $profile->hasInformationForm()) {
            return [
                'url' => route('member.onboarding.edit'),
                'label' => __('Complete information'),
                'style' => 'primary',
                'external' => false,
            ];
        }

        if ($profile->verification_status === ModelProfile::VERIFICATION_REJECTED) {
            return [
                'url' => route('member.verification.edit'),
                'label' => __('Resubmit verification'),
                'style' => 'primary',
                'external' => false,
            ];
        }

        if (! $profile->hasVerificationSubmission()) {
            return [
                'url' => route('member.verification.edit'),
                'label' => __('Complete verification'),
                'style' => 'primary',
                'external' => false,
            ];
        }

        if ($profile->verification_status === ModelProfile::VERIFICATION_SUBMITTED) {
            return [
                'url' => route('member.verification.edit'),
                'label' => __('View verification'),
                'style' => 'secondary',
                'external' => false,
            ];
        }

        if ($profile->isCommunityInvited() && ! $profile->isCommunityRoleAssigned() && $discordInviteUrl) {
            return [
                'url' => $discordInviteUrl,
                'label' => __('Open Discord invite'),
                'style' => 'primary',
                'external' => true,
            ];
        }

        if ($profile->isCommunityRoleAssigned()) {
            return [
                'url' => route('community.show'),
                'label' => __('Open Community Chat'),
                'style' => 'secondary',
                'external' => false,
            ];
        }

        if ($profile->isVerified()) {
            return [
                'url' => route('member.verification.edit'),
                'label' => __('View verification'),
                'style' => 'secondary',
                'external' => false,
            ];
        }

        return null;
    }
}
