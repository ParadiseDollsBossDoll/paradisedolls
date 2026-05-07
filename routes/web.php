<?php

use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLessonController;
use App\Http\Controllers\Admin\AdminModelProgressController;
use App\Http\Controllers\Admin\AdminOnboardingController;
use App\Http\Controllers\Admin\AdminTestimonialController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Member\CourseChatController;
use App\Http\Controllers\Member\LessonProgressController;
use App\Http\Controllers\Member\MemberCourseController;
use App\Http\Controllers\Member\MemberDashboardController;
use App\Http\Controllers\Member\MemberOnboardingController;
use App\Http\Controllers\Member\MemberVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::redirect('/about', '/our-story');

Route::view('/our-story', 'marketing.our-story')->name('our-story');
Route::view('/work-from-home', 'marketing.work-from-home')->name('work-from-home');
Route::view('/work-from-paradise', 'marketing.work-from-paradise')->name('work-from-paradise');
Route::view('/perks', 'marketing.perks')->name('perks');
Route::view('/multistreaming', 'marketing.multistreaming')->name('multistreaming');
Route::get('/success-stories', TestimonialController::class)->name('success-stories');

Route::get('/apply', [ApplyController::class, 'create'])->name('apply');
Route::post('/apply', [ApplyController::class, 'store'])
    ->middleware('throttle:apply-submissions')
    ->name('apply.store');

Route::get('/dashboard', function () {
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('member.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'model'])->prefix('member')->name('member.')->group(function () {
    Route::get('/', MemberDashboardController::class)->name('dashboard');
    Route::get('/onboarding', [MemberOnboardingController::class, 'edit'])->name('onboarding.edit');
    Route::put('/onboarding', [MemberOnboardingController::class, 'update'])
        ->middleware('throttle:profile-updates')
        ->name('onboarding.update');
    Route::get('/verification', [MemberVerificationController::class, 'edit'])->name('verification.edit');
    Route::post('/verification', [MemberVerificationController::class, 'store'])
        ->middleware('throttle:profile-updates')
        ->name('verification.store');
    Route::get('/courses', [MemberCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{slug}', [MemberCourseController::class, 'show'])->name('courses.show');
    Route::patch('/lessons/{lesson}/progress', [LessonProgressController::class, 'update'])
        ->middleware('throttle:member-progress')
        ->name('lessons.progress');
    Route::post('/courses/{slug}/chat', [CourseChatController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('courses.chat.store');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}/photos/{index}', [AdminApplicationController::class, 'downloadPhoto'])
        ->whereNumber('index')
        ->name('applications.photos.show');
    Route::post('/applications/{application}/approve', [AdminApplicationController::class, 'approve'])
        ->middleware('throttle:admin-actions')
        ->name('applications.approve');
    Route::post('/applications/{application}/reject', [AdminApplicationController::class, 'reject'])
        ->middleware('throttle:admin-actions')
        ->name('applications.reject');

    Route::get('/models/progress', [AdminModelProgressController::class, 'index'])->name('models.progress');
    Route::get('/onboarding', [AdminOnboardingController::class, 'index'])->name('onboarding.index');
    Route::get('/onboarding/{profile}/documents/{document}', [AdminOnboardingController::class, 'downloadDocument'])
        ->name('onboarding.documents.show');

    Route::middleware('throttle:admin-actions')->group(function () {
        Route::post('/onboarding/{profile}/request-verification', [AdminOnboardingController::class, 'requestVerification'])
            ->name('onboarding.request-verification');
        Route::post('/onboarding/{profile}/verify', [AdminOnboardingController::class, 'verify'])
            ->name('onboarding.verify');
        Route::post('/onboarding/{profile}/reject-verification', [AdminOnboardingController::class, 'rejectVerification'])
            ->name('onboarding.reject-verification');
        Route::post('/onboarding/{profile}/community-invite', [AdminOnboardingController::class, 'communityInvite'])
            ->name('onboarding.community-invite');
        Route::post('/onboarding/{profile}/community-role-assigned', [AdminOnboardingController::class, 'markCommunityRoleAssigned'])
            ->name('onboarding.community-role-assigned');
        Route::patch('/testimonials/{testimonial}/visibility', [AdminTestimonialController::class, 'visibility'])->name('testimonials.visibility');
        Route::resource('testimonials', AdminTestimonialController::class)->except(['show']);
        Route::patch('/courses/{course}/visibility', [AdminCourseController::class, 'visibility'])->name('courses.visibility');
        Route::resource('courses', AdminCourseController::class)->except(['show']);

        Route::post('/courses/{course}/lessons', [AdminLessonController::class, 'store'])->name('courses.lessons.store');
        Route::put('/courses/{course}/lessons/{lesson}', [AdminLessonController::class, 'update'])->name('courses.lessons.update');
        Route::delete('/courses/{course}/lessons/{lesson}', [AdminLessonController::class, 'destroy'])->name('courses.lessons.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:profile-updates')
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:profile-updates')
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
