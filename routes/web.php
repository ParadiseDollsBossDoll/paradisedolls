<?php

use App\Http\Controllers\Admin\AdminAcademyFileController;
use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminCrmExportController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLessonController;
use App\Http\Controllers\Admin\AdminModelProgressController;
use App\Http\Controllers\Admin\AdminModuleController;
use App\Http\Controllers\Admin\AdminOnboardingController;
use App\Http\Controllers\Admin\AdminReferralController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminTestimonialController;
use App\Http\Controllers\Admin\BunnyVideoController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\Community\CommunityChannelController;
use App\Http\Controllers\Community\CommunityController;
use App\Http\Controllers\Community\CommunityMessageController;
use App\Http\Controllers\Community\CommunityModerationController;
use App\Http\Controllers\Community\CommunityPresenceController;
use App\Http\Controllers\Community\MessageReactionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Member\CourseAssetController;
use App\Http\Controllers\Member\CourseChatController;
use App\Http\Controllers\Member\LessonProgressController;
use App\Http\Controllers\Member\MemberCourseController;
use App\Http\Controllers\Member\MemberDashboardController;
use App\Http\Controllers\Member\MemberOnboardingController;
use App\Http\Controllers\Member\MemberReferralController;
use App\Http\Controllers\Member\MemberTestimonialController;
use App\Http\Controllers\Member\MemberVerificationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::redirect('/about', '/our-story');

Route::view('/our-story', 'marketing.our-story')->name('our-story');
Route::view('/work-from-home', 'marketing.work-from-home')->name('work-from-home');
Route::view('/work-from-paradise', 'marketing.work-from-paradise')->name('work-from-paradise');
Route::view('/perks', 'marketing.perks')->name('perks');
Route::view('/multistreaming', 'marketing.multistreaming')->name('multistreaming');
Route::get('/success-stories', TestimonialController::class)->name('success-stories');
Route::get('/profile-photos/{user}', ProfilePhotoController::class)
    ->whereNumber('user')
    ->name('profile-photos.show');

Route::middleware('throttle:translation')->prefix('translation')->name('translation.')->group(function () {
    Route::get('/languages', [TranslationController::class, 'languages'])->name('languages');
    Route::post('/translate', [TranslationController::class, 'translate'])->name('translate');
});

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
    Route::get('/testimonials/create', [MemberTestimonialController::class, 'create'])->name('testimonials.create');
    Route::post('/testimonials', [MemberTestimonialController::class, 'store'])
        ->middleware('throttle:profile-updates')
        ->name('testimonials.store');
    Route::get('/referrals', [MemberReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referrals', [MemberReferralController::class, 'store'])
        ->middleware('throttle:profile-updates')
        ->name('referrals.store');
    Route::get('/courses', [MemberCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{slug}', [MemberCourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{slug}/learn', [MemberCourseController::class, 'learn'])
        ->middleware('throttle:member-progress')
        ->name('courses.learn');
    Route::post('/courses/{slug}/request-access', [MemberCourseController::class, 'requestAccess'])
        ->middleware('throttle:course-access-requests')
        ->name('courses.request-access');

    Route::middleware('course.enrolled')->group(function () {
        Route::get('/courses/{slug}/learn', [MemberCourseController::class, 'learnShow'])->name('courses.learn.show');
        Route::get('/courses/{slug}/lessons/{lesson}', [MemberCourseController::class, 'lesson'])->name('courses.lessons.show');
        Route::get('/courses/{slug}/outline', [CourseAssetController::class, 'outline'])->name('courses.outline');
        Route::get('/courses/{slug}/lessons/{lesson}/media/{kind}/{index?}', [CourseAssetController::class, 'lessonMedia'])
            ->whereNumber('index')
            ->name('courses.lessons.media');
        Route::get('/courses/{slug}/content-blocks/{block}/{field}/{index?}', [CourseAssetController::class, 'contentBlock'])
            ->whereNumber('index')
            ->name('courses.content-blocks.media');
        Route::get('/courses/{slug}/community', [MemberCourseController::class, 'community'])->name('courses.community');
        Route::patch('/lessons/{lesson}/progress', [LessonProgressController::class, 'update'])
            ->middleware('throttle:member-progress')
            ->name('lessons.progress');
        Route::post('/courses/{slug}/chat', [CourseChatController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('courses.chat.store');
    });
});

Route::middleware(['auth', 'verified', 'community.access', 'community.perf'])->prefix('community')->name('community.')->group(function () {
    Route::get('/', [CommunityController::class, 'show'])->name('show');
    Route::get('/channels', [CommunityChannelController::class, 'index'])->name('channels.index');
    Route::get('/channels/{channel:slug}', [CommunityController::class, 'show'])->name('channels.show');

    Route::get('/channels/{channel:slug}/messages', [CommunityMessageController::class, 'index'])->name('channels.messages.index');
    Route::post('/channels/{channel:slug}/messages', [CommunityMessageController::class, 'store'])
        ->middleware('throttle:60,1')
        ->name('channels.messages.store');
    Route::post('/channels/{channel:slug}/read', [CommunityMessageController::class, 'markRead'])->name('channels.read');

    Route::post('/messages/{message}/reactions/toggle', [MessageReactionController::class, 'toggle'])->name('messages.reactions.toggle');
    Route::delete('/messages/{message}', [CommunityMessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/messages/{message}/pin', [CommunityMessageController::class, 'pin'])->name('messages.pin');
    Route::get('/messages/{message}/attachment', [CommunityMessageController::class, 'serveAttachment'])->middleware('throttle:60,1')->name('messages.attachment');

    Route::get('/presence', [CommunityPresenceController::class, 'index'])->name('presence.index');
    Route::post('/presence/ping', [CommunityPresenceController::class, 'ping'])->name('presence.ping');
    Route::post('/presence/typing', [CommunityPresenceController::class, 'typing'])->name('presence.typing');

    Route::post('/channels', [CommunityChannelController::class, 'store'])->name('channels.store');
    Route::post('/channels/{channel:slug}/archive', [CommunityChannelController::class, 'archive'])->name('channels.archive');
    Route::patch('/channels/{channel:slug}', [CommunityChannelController::class, 'update'])->name('channels.update');
    Route::delete('/channels/{channel:slug}', [CommunityChannelController::class, 'destroy'])->name('channels.destroy');
    Route::post('/channels/{channel:slug}/restore', [CommunityChannelController::class, 'restore'])->name('channels.restore');
    Route::post('/channels/reorder', [CommunityChannelController::class, 'reorder'])->name('channels.reorder');

    Route::post('/members/{user}/timeout', [CommunityModerationController::class, 'timeout'])->name('members.timeout');
    Route::post('/timeouts/{timeout}/revoke', [CommunityModerationController::class, 'revoke'])->name('timeouts.revoke');
    Route::get('/moderation/history', [CommunityModerationController::class, 'history'])->name('moderation.history');
});

Route::middleware(['auth', 'verified'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    Route::post('/{notification}', [NotificationController::class, 'open'])->name('open');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/site-editor', [AdminSettingsController::class, 'editMarketingContent'])->name('site-editor.edit');
    Route::put('/site-editor', [AdminSettingsController::class, 'updateMarketingContent'])
        ->middleware('throttle:admin-actions')
        ->name('site-editor.update');
    Route::post('/settings/theme', [AdminSettingsController::class, 'updateTheme'])->name('settings.theme');

    // ── Autosave endpoints (higher rate limit, JSON responses) ───────────────
    // These fire on every change; keep them outside the strict throttle:admin-actions group.
    Route::middleware('throttle:120,1')->group(function () {
        // Module CRUD
        Route::post('/courses/{course}/modules', [AdminModuleController::class, 'store'])->name('courses.modules.store');
        Route::put('/courses/{course}/modules/{module}', [AdminModuleController::class, 'update'])->name('courses.modules.update');
        Route::delete('/courses/{course}/modules/{module}', [AdminModuleController::class, 'destroy'])->name('courses.modules.destroy');
        Route::patch('/courses/{course}/modules/reorder', [AdminModuleController::class, 'reorder'])->name('courses.modules.reorder');

        // Lesson autosave (JSON)
        Route::post('/courses/{course}/lessons/autosave', [AdminLessonController::class, 'autosave'])->name('courses.lessons.autosave');
        Route::put('/courses/{course}/lessons/{lesson}/autosave', [AdminLessonController::class, 'autosaveUpdate'])->name('courses.lessons.autosave.update');
        Route::patch('/courses/{course}/lessons/reorder', [AdminLessonController::class, 'reorder'])->name('courses.lessons.reorder');

        // Course details-only save (JSON, no lesson/module sync)
        Route::patch('/courses/{course}/details', [AdminCourseController::class, 'updateDetails'])->name('courses.update-details');
    });

    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/export', [AdminCrmExportController::class, 'applications'])->name('applications.export');
    Route::post('/applications/referrals/{referral}/convert', [AdminApplicationController::class, 'convertReferral'])
        ->middleware('throttle:admin-actions')
        ->name('applications.referrals.convert');
    Route::post('/applications/referrals/{referral}/reject', [AdminApplicationController::class, 'rejectReferral'])
        ->middleware('throttle:admin-actions')
        ->name('applications.referrals.reject');
    Route::post('/applications/referrals/{referral}/reward-paid', [AdminApplicationController::class, 'markReferralRewardPaid'])
        ->middleware('throttle:admin-actions')
        ->name('applications.referrals.reward-paid');
    Route::get('/applications/referrals/{referral}/photos/{index}', [AdminApplicationController::class, 'downloadReferralPhoto'])
        ->whereNumber('index')
        ->name('applications.referrals.photos.show');
    Route::get('/applications/referrals/{referral}/photos/{index}/view', [AdminApplicationController::class, 'viewReferralPhoto'])
        ->whereNumber('index')
        ->name('applications.referrals.photos.view');
    Route::get('/applications/{application}/photos/{index}', [AdminApplicationController::class, 'downloadPhoto'])
        ->whereNumber('index')
        ->name('applications.photos.show');
    Route::get('/applications/{application}/photos/{index}/view', [AdminApplicationController::class, 'viewPhoto'])
        ->whereNumber('index')
        ->name('applications.photos.view');
    Route::post('/applications/{application}/approve', [AdminApplicationController::class, 'approve'])
        ->middleware('throttle:admin-actions')
        ->name('applications.approve');
    Route::post('/applications/{application}/resend-approval-email', [AdminApplicationController::class, 'resendApprovalEmail'])
        ->middleware('throttle:admin-actions')
        ->name('applications.resend-approval-email');
    Route::post('/applications/{application}/reject', [AdminApplicationController::class, 'reject'])
        ->middleware('throttle:admin-actions')
        ->name('applications.reject');
    Route::delete('/applications/{application}', [AdminApplicationController::class, 'destroy'])
        ->middleware('throttle:admin-actions')
        ->name('applications.destroy');

    Route::get('/referrals', [AdminReferralController::class, 'index'])->name('referrals.index');

    Route::get('/models/progress', [AdminModelProgressController::class, 'index'])->name('models.progress');
    Route::patch('/models/{user}/login', [AdminModelProgressController::class, 'updateLogin'])
        ->middleware('throttle:admin-actions')
        ->name('models.login.update');
    Route::post('/models/{user}/password/generate', [AdminModelProgressController::class, 'generatePassword'])
        ->middleware('throttle:admin-actions')
        ->name('models.password.generate');
    Route::delete('/models/{user}', [AdminModelProgressController::class, 'destroy'])
        ->middleware('throttle:admin-actions')
        ->name('models.destroy');
    Route::get('/onboarding', [AdminOnboardingController::class, 'index'])->name('onboarding.index');
    Route::put('/onboarding/form', [AdminOnboardingController::class, 'updateOnboardingForm'])
        ->middleware('throttle:admin-actions')
        ->name('onboarding.form.update');
    Route::get('/onboarding/export', [AdminCrmExportController::class, 'onboarding'])->name('onboarding.export');
    Route::get('/onboarding/{profile}/export', [AdminCrmExportController::class, 'onboardingProfile'])->name('onboarding.export-profile');
    Route::get('/onboarding/{profile}', [AdminOnboardingController::class, 'show'])->name('onboarding.show');
    Route::get('/onboarding/{profile}/details', [AdminOnboardingController::class, 'details'])->name('onboarding.details');
    Route::get('/academy-files', [AdminAcademyFileController::class, 'show'])->name('academy-files.show');
    Route::get('/onboarding/{profile}/documents/{document}', [AdminOnboardingController::class, 'downloadDocument'])
        ->name('onboarding.documents.show');
    Route::get('/onboarding/{profile}/documents/{document}/view', [AdminOnboardingController::class, 'viewDocument'])
        ->name('onboarding.documents.view');
    Route::get('/onboarding/{profile}/courses/{course}/proofs/{file}', [AdminOnboardingController::class, 'downloadCourseAccessProof'])
        ->name('onboarding.courses.proofs.show');
    Route::get('/onboarding/{profile}/courses/{course}/proofs/{file}/view', [AdminOnboardingController::class, 'viewCourseAccessProof'])
        ->name('onboarding.courses.proofs.view');

    Route::middleware('throttle:admin-actions')->group(function () {
        Route::post('/onboarding/{profile}/stage', [AdminOnboardingController::class, 'updateStage'])
            ->name('onboarding.stage');
        Route::post('/onboarding/{profile}/verification-instructions', [AdminOnboardingController::class, 'updateVerificationInstructions'])
            ->name('onboarding.verification-instructions');
        Route::post('/onboarding/{profile}/courses/{course}/unlock', [AdminOnboardingController::class, 'unlockCourse'])
            ->name('onboarding.courses.unlock');
        Route::post('/onboarding/{profile}/courses/{course}/lock', [AdminOnboardingController::class, 'lockCourse'])
            ->name('onboarding.courses.lock');
        Route::post('/onboarding/{profile}/courses/{course}/resubmission', [AdminOnboardingController::class, 'requestCourseResubmission'])
            ->name('onboarding.courses.resubmission');
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
        Route::post('/testimonials/{testimonial}/approve', [AdminTestimonialController::class, 'approve'])->name('testimonials.approve');
        Route::patch('/testimonials/{testimonial}/visibility', [AdminTestimonialController::class, 'visibility'])->name('testimonials.visibility');
        Route::resource('testimonials', AdminTestimonialController::class)->except(['show']);
        Route::get('/bunny/videos', [BunnyVideoController::class, 'index'])->name('bunny.videos.index');
        Route::post('/bunny/videos/upload-intent', [BunnyVideoController::class, 'uploadIntent'])->name('bunny.videos.upload-intent');
        Route::get('/bunny/videos/{videoId}', [BunnyVideoController::class, 'show'])->name('bunny.videos.show');
        Route::patch('/courses/{course}/visibility', [AdminCourseController::class, 'visibility'])->name('courses.visibility');
        Route::patch('/courses/{course}/move', [AdminCourseController::class, 'move'])->name('courses.move');
        Route::get('/courses/{course}/preview', [AdminCourseController::class, 'preview'])->name('courses.preview');
        Route::get('/courses/{course}/lessons/{lesson}/preview', [AdminCourseController::class, 'previewLesson'])->name('courses.lessons.preview');
        Route::post('/courses/block-file', [AdminCourseController::class, 'uploadBlockFile'])->name('courses.block-file');
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
