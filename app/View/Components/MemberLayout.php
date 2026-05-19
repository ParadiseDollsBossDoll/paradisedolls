<?php

namespace App\View\Components;

use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

class MemberLayout extends Component
{
    public int $layoutProgress = 0;

    public int $layoutCompletedLessons = 0;

    public int $layoutTotalLessons = 0;

    public function __construct(
        public bool $hideSidebar = false,
        public bool $player = false,
        public ?array $shellStats = null,
    ) {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $stats = $this->shellStats ?: $this->buildShellStats($user);

        $this->layoutProgress = (int) ($stats['overall_percent'] ?? $stats['overall_progress'] ?? 0);
        $this->layoutCompletedLessons = (int) ($stats['completed_lessons'] ?? 0);
        $this->layoutTotalLessons = (int) ($stats['total_lessons'] ?? 0);
    }


    public function render(): View
    {
        return view('layouts.member');
    }

    private function buildShellStats(User $user): array
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->withCount(['publishedLessons as lessons_count'])
            ->get();

        $totalLessons = (int) $courses->sum('lessons_count');
        $completedLessons = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->whereHas('lesson', fn ($query) => $query
                ->where('is_published', true)
                ->whereHas('course', fn ($courseQuery) => $courseQuery->where('is_published', true)))
            ->count();

        return [
            'overall_percent' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
        ];
    }
}
