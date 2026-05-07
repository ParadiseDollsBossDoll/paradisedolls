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

        if ($request->user()?->enrolledCourses()->whereKey($course->id)->exists()) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            return redirect()
                ->route('member.courses.show', $course->slug)
                ->with('status', __('Click Start Learning first to enter this course.'));
        }

        abort(403, __('You must be enrolled in this course to access its community.'));
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
