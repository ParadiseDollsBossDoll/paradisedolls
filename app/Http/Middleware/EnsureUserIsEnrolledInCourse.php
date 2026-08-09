<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Lesson;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEnrolledInCourse
{
    public function handle(Request $request, Closure $next): Response
    {
        $course = $this->courseFromRoute($request);

        if (! $course || ! $course->is_published) {
            abort(404);
        }

        $user = $request->user();

        if ($user?->enrolledCourses()->whereKey($course->id)->exists()) {
            return $next($request);
        }

        if (! $user?->isChatter() && ! $user?->modelProfile()->first()?->isVerified()) {
            return $this->deny($request, $course, __('Verification must be approved before Kayla can unlock this course.'));
        }

        return $this->deny($request, $course, __('Locked pending Kayla approval.'));
    }

    private function deny(Request $request, Course $course, string $message): Response
    {
        if ($request->isMethod('GET')) {
            $route = $request->user()?->isChatter()
                ? 'chatter.courses.index'
                : 'member.courses.show';

            return redirect()
                ->route($route, $request->user()?->isChatter() ? [] : $course->slug)
                ->with('status', $message);
        }

        abort(403, $message);
    }

    private function courseFromRoute(Request $request): ?Course
    {
        $course = $request->route('course');
        if ($course instanceof Course) {
            return $course;
        }

        $lesson = $request->route('lesson');
        if ($lesson instanceof Lesson) {
            return $lesson->course;
        }

        $slug = $request->route('slug');
        if (is_string($slug) && $slug !== '') {
            return Course::query()
                ->where('slug', $slug)
                ->first();
        }

        return null;
    }
}
