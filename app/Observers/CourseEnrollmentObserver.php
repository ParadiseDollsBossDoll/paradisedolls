<?php

namespace App\Observers;

use App\Models\CourseEnrollment;
use App\Models\User;
use App\Services\CourseCommunityService;

class CourseEnrollmentObserver
{
    public function __construct(private CourseCommunityService $community) {}

    public function created(CourseEnrollment $enrollment): void
    {
        $user   = User::find($enrollment->user_id);
        $course = $enrollment->course;

        if ($user && $course) {
            $this->community->joinCourse($user, $course);
        }
    }

    public function deleted(CourseEnrollment $enrollment): void
    {
        $user   = User::find($enrollment->user_id);
        $course = $enrollment->course;

        if ($user && $course) {
            $this->community->leaveCourse($user, $course);
        }
    }
}
