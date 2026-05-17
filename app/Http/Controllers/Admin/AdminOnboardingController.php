<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountApprovalMail;
use App\Mail\CommunityAccessMail;
use App\Mail\VerificationRequestMail;
use App\Mail\VerificationResubmissionMail;
use App\Models\Course;
use App\Models\CourseAccessRequest;
use App\Models\ModelProfile;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminOnboardingController extends Controller
{
    public function index(): View
    {
        $models = User::query()
            ->where('role', 'model')
            ->with(['courseAccessRequests.course', 'courseEnrollments', 'modelProfile.application', 'modelProfile.verificationReviewer'])
            ->orderBy('name')
            ->paginate(20);

        $publishedCourses = Course::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'slug',
                'platform_label',
                'course_access_requirements',
                'access_registration_instructions',
                'access_callback_instructions',
                'access_onboarding_instructions',
                'access_verification_instructions',
                'is_published',
            ]);

        $stageOptions = collect(ModelProfile::onboardingStageOptions())
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        $stats = [
            'members' => User::where('role', 'model')->count(),
            'information_submitted' => ModelProfile::whereNotNull('information_submitted_at')->count(),
            'verification_submitted' => ModelProfile::where('verification_status', ModelProfile::VERIFICATION_SUBMITTED)->count(),
            'verified' => ModelProfile::where('verification_status', ModelProfile::VERIFICATION_VERIFIED)->count(),
            'community_invited' => ModelProfile::whereNotNull('community_invited_at')->count(),
            'role_assigned' => ModelProfile::whereNotNull('community_role_assigned_at')->count(),
        ];

        return view('admin.onboarding.index', compact('models', 'publishedCourses', 'stageOptions', 'stats'));
    }

    public function updateStage(Request $request, ModelProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'onboarding_stage' => ['required', 'string', Rule::in(ModelProfile::onboardingStages())],
        ]);

        $profile->forceFill([
            'onboarding_stage' => $validated['onboarding_stage'],
        ])->save();

        return redirect()->back()->with('status', __('Onboarding stage updated.'));
    }

    public function updateVerificationInstructions(Request $request, ModelProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'verification_request_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $profile->forceFill([
            'verification_request_instructions' => $validated['verification_request_instructions'] ?? null,
        ])->save();

        return redirect()->back()->with('status', __('Verification instructions saved.'));
    }

    public function unlockCourse(ModelProfile $profile, Course $course): RedirectResponse
    {
        if (! $profile->isVerified()) {
            return redirect()->back()->withErrors(['profile' => __('The member must be verified before a website walkthrough can be unlocked.')]);
        }

        if (! $course->is_published) {
            return redirect()->back()->withErrors(['course' => __('Only published courses can be unlocked for members.')]);
        }

        $course->enrollments()->firstOrCreate(
            ['user_id' => $profile->user_id],
            ['enrolled_at' => now()]
        );

        CourseAccessRequest::query()->updateOrCreate([
            'course_id' => $course->id,
            'user_id' => $profile->user_id,
        ], [
            'status' => CourseAccessRequest::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => null,
        ]);

        $profile->user?->notify(new SystemNotification(
            title: __('Course access approved'),
            body: __('Kayla approved your access to :course.', ['course' => $course->title]),
            actionUrl: route('member.courses.show', $course->slug, false),
            category: 'course_access_approved',
        ));

        return redirect()->back()->with('status', __('Website walkthrough unlocked for this member.'));
    }

    public function lockCourse(ModelProfile $profile, Course $course): RedirectResponse
    {
        $course->enrollments()
            ->where('user_id', $profile->user_id)
            ->get()
            ->each(fn ($enrollment) => $enrollment->delete());

        CourseAccessRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $profile->user_id)
            ->where('status', CourseAccessRequest::STATUS_APPROVED)
            ->update([
                'status' => CourseAccessRequest::STATUS_REJECTED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => __('Access locked by admin.'),
            ]);

        return redirect()->back()->with('status', __('Website walkthrough locked for this member.'));
    }

    public function requestCourseResubmission(Request $request, ModelProfile $profile, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:5000'],
        ]);

        if ($course->enrollments()->where('user_id', $profile->user_id)->exists()) {
            return redirect()->back()->withErrors(['course' => __('Lock this course before requesting resubmission.')]);
        }

        $accessRequest = CourseAccessRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $profile->user_id)
            ->first();

        if (! $accessRequest) {
            return redirect()->back()->withErrors(['course' => __('This member has not requested access to this course yet.')]);
        }

        $accessRequest->forceFill([
            'status' => CourseAccessRequest::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ])->save();

        $profile->user?->notify(new SystemNotification(
            title: __('Course access needs resubmission'),
            body: __('Kayla requested more details for :course.', ['course' => $course->title]),
            actionUrl: route('member.courses.show', $course->slug, false),
            category: 'course_access_resubmission',
        ));

        return redirect()->back()->with('status', __('Course access resubmission requested.'));
    }

    public function requestVerification(ModelProfile $profile): RedirectResponse
    {
        if (! $profile->hasInformationForm()) {
            return redirect()->back()->withErrors(['profile' => __('The Model Information Form must be submitted before verification is requested.')]);
        }

        if ($profile->isVerified()) {
            return redirect()->back()->withErrors(['profile' => __('This member is already verified.')]);
        }

        $profile->forceFill([
            'verification_status' => ModelProfile::VERIFICATION_REQUESTED,
            'verification_notes' => null,
        ])->save();

        $profile->load('user');
        $this->sendMail($profile, new VerificationRequestMail(
            profile: $profile,
            verificationUrl: route('member.verification.edit'),
        ));

        return redirect()->back()->with('status', __('Verification Request Email sent.'));
    }

    public function verify(Request $request, ModelProfile $profile): RedirectResponse
    {
        if (! $profile->hasVerificationSubmission()) {
            return redirect()->back()->withErrors(['profile' => __('This member has not submitted verification documents yet.')]);
        }

        $validated = $request->validate([
            'verification_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $profile->forceFill([
            'verification_status' => ModelProfile::VERIFICATION_VERIFIED,
            'verification_reviewed_by' => auth()->id(),
            'verification_reviewed_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ])->save();

        $profile->load('user');
        $profile->user->notify(new SystemNotification(
            title: __('Verification approved'),
            body: __('Your verification was approved. Kayla can now unlock courses and community access for you.'),
            actionUrl: route('member.dashboard', absolute: false),
            category: 'verification_approved',
        ));
        $this->sendMail($profile, new AccountApprovalMail(
            profile: $profile,
            dashboardUrl: route('member.dashboard'),
        ));

        return redirect()->back()->with('status', __('Account Approval Email sent and member marked verified.'));
    }

    public function rejectVerification(Request $request, ModelProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'verification_notes' => ['required', 'string', 'max:5000'],
        ]);

        $profile->forceFill([
            'verification_status' => ModelProfile::VERIFICATION_REJECTED,
            'verification_reviewed_by' => auth()->id(),
            'verification_reviewed_at' => now(),
            'verification_notes' => $validated['verification_notes'],
        ])->save();

        $profile->load('user');
        $this->sendMail($profile, new VerificationResubmissionMail(
            profile: $profile,
            verificationUrl: route('member.verification.edit'),
        ));

        return redirect()->back()->with('status', __('Resubmission instructions saved and sent to the member.'));
    }

    public function communityInvite(ModelProfile $profile): RedirectResponse
    {
        if (! $profile->isVerified()) {
            return redirect()->back()->withErrors(['profile' => __('The member must be verified before Discord Community access is sent.')]);
        }

        $profile->forceFill([
            'community_invited_at' => now(),
        ])->save();

        $profile->load('user');
        $this->sendMail($profile, new CommunityAccessMail(
            profile: $profile,
            communityUrl: config('paradise.community_url'),
            roleName: config('paradise.community_role_name'),
        ));

        return redirect()->back()->with('status', __('Discord Community access email sent.'));
    }

    public function markCommunityRoleAssigned(ModelProfile $profile): RedirectResponse
    {
        if (! $profile->isVerified()) {
            return redirect()->back()->withErrors(['profile' => __('The member must be verified before community chat access can be assigned.')]);
        }

        if (! $profile->isCommunityInvited()) {
            return redirect()->back()->withErrors(['profile' => __('Send Discord Community access before marking the role assigned.')]);
        }

        $profile->forceFill([
            'community_role_assigned_at' => now(),
        ])->save();

        return redirect()->back()->with('status', __('Discord Community role assignment recorded.'));
    }

    public function downloadDocument(ModelProfile $profile, string $document): StreamedResponse
    {
        $path = $this->resolveDocumentPath($profile, $document);

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function viewDocument(ModelProfile $profile, string $document): StreamedResponse
    {
        $path = $this->resolveDocumentPath($profile, $document);

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    private function resolveDocumentPath(ModelProfile $profile, string $document): ?string
    {
        $field = match ($document) {
            'id' => 'id_document_path',
            'selfie' => 'selfie_with_id_path',
            'codes' => 'platform_codes_path',
            default => abort(404),
        };

        return $profile->{$field};
    }

    private function sendMail(ModelProfile $profile, mixed $mail): void
    {
        try {
            Mail::to($profile->user->email)->queue($mail);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
