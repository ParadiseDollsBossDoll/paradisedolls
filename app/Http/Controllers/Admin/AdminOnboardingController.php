<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountApprovalMail;
use App\Mail\CommunityAccessMail;
use App\Mail\VerificationRequestMail;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->with(['modelProfile.application', 'modelProfile.verificationReviewer'])
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'members' => User::where('role', 'model')->count(),
            'information_submitted' => ModelProfile::whereNotNull('information_submitted_at')->count(),
            'verification_submitted' => ModelProfile::where('verification_status', ModelProfile::VERIFICATION_SUBMITTED)->count(),
            'verified' => ModelProfile::where('verification_status', ModelProfile::VERIFICATION_VERIFIED)->count(),
            'community_invited' => ModelProfile::whereNotNull('community_invited_at')->count(),
            'role_assigned' => ModelProfile::whereNotNull('community_role_assigned_at')->count(),
        ];

        return view('admin.onboarding.index', compact('models', 'stats'));
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

        return redirect()->back()->with('status', __('Verification marked for resubmission.'));
    }

    public function communityInvite(ModelProfile $profile): RedirectResponse
    {
        if (! $profile->isVerified()) {
            return redirect()->back()->withErrors(['profile' => __('The member must be verified before community access is sent.')]);
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

        return redirect()->back()->with('status', __('Community Access Email sent.'));
    }

    public function markCommunityRoleAssigned(ModelProfile $profile): RedirectResponse
    {
        if (! $profile->isCommunityInvited()) {
            return redirect()->back()->withErrors(['profile' => __('Send community access before marking the role assigned.')]);
        }

        $profile->forceFill([
            'community_role_assigned_at' => now(),
        ])->save();

        return redirect()->back()->with('status', __('Community role assignment recorded.'));
    }

    public function downloadDocument(ModelProfile $profile, string $document): StreamedResponse
    {
        $field = match ($document) {
            'id' => 'id_document_path',
            'selfie' => 'selfie_with_id_path',
            'codes' => 'platform_codes_path',
            default => abort(404),
        };

        $path = $profile->{$field};

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    private function sendMail(ModelProfile $profile, mixed $mail): void
    {
        try {
            Mail::to($profile->user->email)->send($mail);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
