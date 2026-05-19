<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\VerificationSubmissionReceivedMail;
use App\Models\ModelProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class MemberVerificationController extends Controller
{
    public function edit(): View
    {
        return view('member.verification.edit', [
            'profile' => $this->profile(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profile();

        if (! $profile->hasInformationForm()) {
            return redirect()
                ->route('member.onboarding.edit')
                ->withErrors(['profile' => __('Submit the Model Information Form before uploading verification documents.')]);
        }

        $validated = $request->validate([
            'id_document' => [$profile->id_document_path ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'selfie_with_id' => [$profile->selfie_with_id_path ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'platform_codes' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        if (! $request->hasFile('id_document') && ! $request->hasFile('selfie_with_id') && ! $request->hasFile('platform_codes')) {
            return redirect()
                ->back()
                ->withErrors(['platform_codes' => __('Upload at least one verification file before submitting.')])
                ->withInput();
        }

        $directory = 'verifications/'.$profile->user_id;
        $uploadedIdentityDocuments = $request->hasFile('id_document') || $request->hasFile('selfie_with_id');
        $keepVerificationApproval = $profile->isVerified()
            && ! $uploadedIdentityDocuments
            && $request->hasFile('platform_codes');

        $paths = [
            'id_document_path' => isset($validated['id_document'])
                ? $validated['id_document']->store($directory)
                : $profile->id_document_path,
            'selfie_with_id_path' => isset($validated['selfie_with_id'])
                ? $validated['selfie_with_id']->store($directory)
                : $profile->selfie_with_id_path,
            'platform_codes_path' => isset($validated['platform_codes'])
                ? $validated['platform_codes']->store($directory)
                : $profile->platform_codes_path,
        ];

        $profile->forceFill([
            ...$paths,
            'verification_status' => $keepVerificationApproval ? $profile->verification_status : ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'verification_reviewed_by' => $keepVerificationApproval ? $profile->verification_reviewed_by : null,
            'verification_reviewed_at' => $keepVerificationApproval ? $profile->verification_reviewed_at : null,
            'verification_notes' => $keepVerificationApproval ? $profile->verification_notes : null,
        ])->save();

        $profile->refresh()->load('user');
        $this->sendConfirmation($profile);

        return redirect()
            ->route('member.dashboard')
            ->with('status', $keepVerificationApproval
                ? __('Platform code proof uploaded. Kayla can review it from Admin Onboarding.')
                : __('Verification submitted. The Paradise Dolls team will review your documents privately.'));
    }

    private function profile(): ModelProfile
    {
        return auth()->user()->modelProfile()->firstOrCreate([]);
    }

    private function sendConfirmation(ModelProfile $profile): void
    {
        try {
            Mail::to($profile->user->email)->queue(new VerificationSubmissionReceivedMail($profile));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
