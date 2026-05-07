<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $pendingApplications = ModelApplication::where('status', ModelApplication::STATUS_PENDING)->count();
        $coursesCount = Course::count();
        $publishedCoursesCount = Course::where('is_published', true)->count();
        $modelsCount = User::where('role', 'model')->count();
        $verificationReviewCount = ModelProfile::where('verification_status', ModelProfile::VERIFICATION_SUBMITTED)->count();
        $recentApplications = ModelApplication::query()
            ->latest()
            ->take(5)
            ->get();
        $recentCourses = Course::query()
            ->withCount('lessons')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingApplications',
            'coursesCount',
            'publishedCoursesCount',
            'modelsCount',
            'verificationReviewCount',
            'recentApplications',
            'recentCourses'
        ));
    }
}
