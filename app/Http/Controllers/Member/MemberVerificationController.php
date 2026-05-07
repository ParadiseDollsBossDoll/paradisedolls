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
        $validated = $request->validate([
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'selfie_with_id' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'platform_codes' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $profile = $this->profile();
        $directory = 'verifications/'.$profile->user_id;

        $paths = [
            'id_document_path' => $validated['id_document']->store($directory),
            'selfie_with_id_path' => $validated['selfie_with_id']->store($directory),
            'platform_codes_path' => isset($validated['platform_codes'])
                ? $validated['platform_codes']->store($directory)
                : $profile->platform_codes_path,
        ];

        $profile->forceFill([
            ...$paths,
            'verification_status' => ModelProfile::VERIFICATION_SUBMITTED,
            'verification_submitted_at' => now(),
            'verification_reviewed_by' => null,
            'verification_reviewed_at' => null,
            'verification_notes' => null,
        ])->save();

        $profile->refresh()->load('user');
        $this->sendConfirmation($profile);

        return redirect()
            ->route('member.dashboard')
            ->with('status', __('Verification submitted. The Paradise Dolls team will review your documents privately.'));
    }

    private function profile(): ModelProfile
    {
        return auth()->user()->modelProfile()->firstOrCreate([]);
    }

    private function sendConfirmation(ModelProfile $profile): void
    {
        try {
            Mail::to($profile->user->email)->send(new VerificationSubmissionReceivedMail($profile));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
