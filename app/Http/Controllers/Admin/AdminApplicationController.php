<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MemberApplicationApprovedMail;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\ModelReferral;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $this->perPage($request);

        $applications = ModelApplication::query()
            ->latest()
            ->with([
                'reviewer:id,name',
                'profile:id,model_application_id,information_submitted_at,verification_status',
                'referral.referrer:id,name,email',
                'user:id,last_login_at',
            ])
            ->paginate($perPage)
            ->withQueryString();

        $referralLeads = ModelReferral::query()
            ->whereNull('model_application_id')
            ->with('referrer:id,name,email')
            ->latest()
            ->paginate($perPage, ['*'], 'leads_page')
            ->withQueryString();

        return view('admin.applications.index', compact('applications', 'referralLeads', 'perPage'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 20);

        return in_array($perPage, [10, 20, 50], true) ? $perPage : 20;
    }

    public function approve(ModelApplication $application): RedirectResponse
    {
        if ($application->status !== ModelApplication::STATUS_PENDING) {
            return redirect()->back()->withErrors(['status' => __('This application was already processed.')]);
        }

        if (User::where('email', $application->email)->exists()) {
            return redirect()->back()->withErrors(['email' => __('A member account with this email already exists.')]);
        }

        $temporaryPassword = Str::password(14, letters: true, numbers: true, symbols: false);

        DB::transaction(function () use ($application, $temporaryPassword): void {
            $user = User::create([
                'name' => $application->name,
                'email' => $application->email,
                'password' => Hash::make($temporaryPassword),
                'role' => 'model',
                'email_verified_at' => now(),
            ]);

            $application->forceFill([
                'status' => ModelApplication::STATUS_APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'user_id' => $user->id,
            ])->save();

            ModelProfile::create([
                'user_id' => $user->id,
                'model_application_id' => $application->id,
                'phone' => $application->phone,
            ]);

            $application->referral?->markJoined();
        });

        $application->refresh()->load('user');
        $application->user?->notify(new SystemNotification(
            title: __('Application approved'),
            body: __('Your Paradise Dolls application was approved. Complete your onboarding form to continue.'),
            actionUrl: route('member.onboarding.edit', absolute: false),
            category: 'application_approved',
        ));

        if ($configurationHint = $this->approvalMailConfigurationHint()) {
            return $this->redirectWithApprovalMailFailure(
                $application,
                $temporaryPassword,
                $configurationHint
            );
        }

        try {
            Mail::to($application->email)->sendNow(new MemberApplicationApprovedMail(
                memberName: $application->name,
                temporaryPassword: $temporaryPassword,
                loginUrl: route('login'),
                onboardingUrl: route('member.onboarding.edit'),
            ));
        } catch (Throwable $e) {
            report($e);

            return $this->redirectWithApprovalMailFailure(
                $application,
                $temporaryPassword,
                $this->approvalMailFailureHint($e)
            );
        }

        return redirect()->back()->with('status', __('Application approved. The member received the Application Approval Email with login and Model Information Form instructions.'));
    }

    public function resendApprovalEmail(ModelApplication $application): RedirectResponse
    {
        if ($application->status !== ModelApplication::STATUS_APPROVED) {
            return redirect()->back()->withErrors(['application' => __('Approval emails can only be resent for approved applications.')]);
        }

        $application->loadMissing('profile:id,model_application_id,information_submitted_at', 'user:id,name,email,email_verified_at,last_login_at');
        $member = $application->user;

        if (! $member) {
            return redirect()->back()->withErrors(['application' => __('This approved application does not have a linked member account to email.')]);
        }

        if ($member->last_login_at) {
            return redirect()->back()->withErrors(['application' => __('This member has already logged in, so the approval email and temporary password should not be resent.')]);
        }

        if ($application->profile?->hasInformationForm()) {
            return redirect()->back()->withErrors(['application' => __('This member already submitted the onboarding form, so the approval email and temporary password should not be resent.')]);
        }

        if ($configurationHint = $this->approvalMailConfigurationHint()) {
            return redirect()->back()
                ->with('warning', __('Approval email was not resent. :hint', ['hint' => $configurationHint]));
        }

        $temporaryPassword = Str::password(14, letters: true, numbers: true, symbols: false);
        $recipientEmail = $member->email ?: $application->email;

        $member->forceFill([
            'password' => Hash::make($temporaryPassword),
            'email_verified_at' => $member->email_verified_at ?: now(),
        ])->save();

        try {
            Mail::to($recipientEmail)->sendNow(new MemberApplicationApprovedMail(
                memberName: $member->name ?: $application->name,
                temporaryPassword: $temporaryPassword,
                loginUrl: route('login'),
                onboardingUrl: route('member.onboarding.edit'),
            ));
        } catch (Throwable $e) {
            report($e);

            return $this->redirectWithApprovalResendFailure(
                $recipientEmail,
                $temporaryPassword,
                $this->approvalMailFailureHint($e)
            );
        }

        $member->notify(new SystemNotification(
            title: __('Application approval email resent'),
            body: __('A new temporary password was sent to your email so you can complete your Model Information Form.'),
            actionUrl: route('member.onboarding.edit', absolute: false),
            category: 'application_approval_resent',
        ));

        return redirect()->back()->with('status', __('Approval email resent to :email. A fresh temporary password was created for the member.', [
            'email' => $recipientEmail,
        ]));
    }

    private function redirectWithApprovalMailFailure(
        ModelApplication $application,
        string $temporaryPassword,
        string $hint
    ): RedirectResponse {
        return redirect()->back()
            ->with(
                'warning',
                __('Application approved and the member account was created, but the welcome email could not be sent. :hint Until mail works, use the temporary password below.', ['hint' => $hint])
            )
            ->with('approval_fallback_email', $application->email)
            ->with('approval_fallback_password', $temporaryPassword);
    }

    private function redirectWithApprovalResendFailure(
        string $recipientEmail,
        string $temporaryPassword,
        string $hint
    ): RedirectResponse {
        return redirect()->back()
            ->with(
                'warning',
                __('Approval email could not be resent. :hint The member password was reset before the send failed; use the temporary password below if you need to contact them manually.', ['hint' => $hint])
            )
            ->with('approval_fallback_email', $recipientEmail)
            ->with('approval_fallback_password', $temporaryPassword);
    }

    private function approvalMailFailureHint(Throwable $e): string
    {
        $message = $e->getMessage();

        if (config('mail.default') === 'smtp' && str_contains($message, 'Application-specific password required')) {
            return __('Gmail rejected SMTP login. Create an App Password at Google Account > Security > App passwords and put it in MAIL_PASSWORD in .env, then run php artisan config:clear.');
        }

        if (config('mail.default') !== 'resend') {
            return __('Check your mail settings in .env and see storage/logs/laravel.log for the error details.');
        }

        if (str_contains($message, 'API key is invalid')) {
            return __('Resend rejected the request. Set RESEND_API_KEY from resend.com/api-keys in .env, run php artisan config:clear, and try again.');
        }

        if (str_contains($message, 'domain') && str_contains(strtolower($message), 'verify')) {
            return __('Your sending domain may not be verified in Resend yet. Add DNS records at resend.com/domains and set MAIL_FROM_ADDRESS to an address on that domain.');
        }

        return __('Check RESEND_API_KEY, verify your sending domain in the Resend dashboard, and see storage/logs/laravel.log for details.');
    }

    private function approvalMailConfigurationHint(): ?string
    {
        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return __('MAIL_MAILER is set to :mailer, which does not deliver email to real inboxes. For the no-API Gmail setup, set MAIL_MAILER=smtp with valid Gmail/Google Workspace SMTP credentials, run php artisan config:clear, then approve another application or contact the member manually.', [
                'mailer' => $mailer,
            ]);
        }

        if ($mailer === 'resend' && ! filled(config('services.resend.key'))) {
            return __('RESEND_API_KEY is empty. Create a key at resend.com/api-keys, add it to your .env file, run php artisan config:clear, then approve another application or contact the member manually.');
        }

        if ($mailer === 'smtp') {
            $host = trim((string) config('mail.mailers.smtp.host'));
            $port = (int) config('mail.mailers.smtp.port');

            if ($this->mailConfigValueIsMissing($host) || in_array($host, ['127.0.0.1', 'localhost'], true)) {
                return __('MAIL_HOST is still local or empty. For Gmail/Google Workspace SMTP, set MAIL_HOST=smtp.gmail.com, MAIL_PORT=465, and MAIL_SCHEME=smtps, then run php artisan config:clear.');
            }

            if ($this->mailConfigValueIsMissing(config('mail.mailers.smtp.username'))) {
                return __('MAIL_USERNAME is empty or still a placeholder. Put the Gmail/Google Workspace email address that will send approval emails in MAIL_USERNAME, then run php artisan config:clear.');
            }

            if ($this->mailConfigValueIsMissing(config('mail.mailers.smtp.password'))) {
                return __('MAIL_PASSWORD is empty or still a placeholder. Create a Google App Password and paste the 16-character app password into MAIL_PASSWORD, then run php artisan config:clear.');
            }

            if ($this->mailConfigValueIsMissing(config('mail.from.address'))) {
                return __('MAIL_FROM_ADDRESS is empty or still a placeholder. For Gmail SMTP, use the same email address as MAIL_USERNAME unless the mailbox has a configured sending alias, then run php artisan config:clear.');
            }

            if ($host === 'smtp.gmail.com' && ! in_array($port, [465, 587], true)) {
                return __('MAIL_PORT is not a Gmail SMTP port. Use MAIL_PORT=465 with MAIL_SCHEME=smtps, or MAIL_PORT=587 with MAIL_SCHEME=smtp, then run php artisan config:clear.');
            }
        }

        return null;
    }

    private function mailConfigValueIsMissing(mixed $value): bool
    {
        $value = trim((string) $value);
        $lowerValue = Str::lower($value);

        return $value === ''
            || in_array($lowerValue, ['null', 'fill_in', 'change_me', 'changeme', 'placeholder'], true)
            || str_contains($lowerValue, 'your_')
            || str_contains($lowerValue, 'your-')
            || str_contains($lowerValue, 'fill_in')
            || str_contains($lowerValue, 'change_me');
    }

    public function reject(ModelApplication $application): RedirectResponse
    {
        if ($application->status !== ModelApplication::STATUS_PENDING) {
            return redirect()->back()->withErrors(['status' => __('This application was already processed.')]);
        }

        $application->forceFill([
            'status' => ModelApplication::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ])->save();

        $application->referral?->markRejected();

        return redirect()->back()->with('status', __('Application rejected.'));
    }

    public function destroy(ModelApplication $application): RedirectResponse
    {
        if ($application->status !== ModelApplication::STATUS_REJECTED) {
            return redirect()->back()->withErrors(['application' => __('Only rejected applications can be deleted.')]);
        }

        $application->loadMissing('referral');

        DB::transaction(function () use ($application): void {
            $photoPaths = collect($application->photo_paths ?? [])
                ->merge($application->referral?->photo_paths ?? [])
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($photoPaths !== []) {
                Storage::disk('local')->delete($photoPaths);
            }

            $application->referral?->delete();
            $application->delete();
        });

        return redirect()->back()->with('status', __('Rejected application deleted.'));
    }

    public function convertReferral(ModelReferral $referral): RedirectResponse
    {
        if ($referral->model_application_id) {
            return redirect()->back()->withErrors(['referral' => __('This referral is already linked to an application.')]);
        }

        if ($referral->status === ModelReferral::STATUS_REJECTED) {
            return redirect()->back()->withErrors(['referral' => __('Rejected referrals cannot be converted.')]);
        }

        if (blank($referral->candidate_email)) {
            return redirect()->back()->withErrors(['referral' => __('A candidate email is required before this referral can be converted.')]);
        }

        DB::transaction(function () use ($referral): void {
            $application = ModelApplication::create([
                'name' => $referral->candidate_name,
                'email' => $referral->candidate_email,
                'phone' => $referral->candidate_phone,
                'message' => $referral->note,
                'experience_level' => $referral->experience_level,
                'social_handle' => $referral->candidate_social_handle,
                'age_confirmed' => $referral->consent_confirmed,
                'photo_paths' => $referral->photo_paths ?? [],
            ]);

            $referral->forceFill([
                'model_application_id' => $application->id,
                'status' => ModelReferral::STATUS_PENDING,
                'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
            ])->save();
        });

        return redirect()->back()->with('status', __('Referral converted into a pending application.'));
    }

    public function rejectReferral(ModelReferral $referral): RedirectResponse
    {
        if ($referral->model_application_id) {
            return redirect()->back()->withErrors(['referral' => __('Reject the linked application instead of the referral lead.')]);
        }

        $referral->markRejected();

        return redirect()->back()->with('status', __('Referral lead rejected.'));
    }

    public function markReferralRewardPaid(ModelReferral $referral): RedirectResponse
    {
        if ($referral->reward_status !== ModelReferral::REWARD_ELIGIBLE) {
            return redirect()->back()->withErrors(['referral' => __('Only eligible referral rewards can be marked paid.')]);
        }

        $referral->forceFill([
            'reward_status' => ModelReferral::REWARD_PAID,
            'reward_marked_paid_at' => now(),
            'reward_marked_paid_by' => auth()->id(),
        ])->save();

        return redirect()->back()->with('status', __('Referral reward marked as paid.'));
    }

    public function downloadPhoto(ModelApplication $application, int $index): StreamedResponse
    {
        $path = $application->photo_paths[$index] ?? null;

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function viewPhoto(ModelApplication $application, int $index): StreamedResponse
    {
        $path = $application->photo_paths[$index] ?? null;

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function downloadReferralPhoto(ModelReferral $referral, int $index): StreamedResponse
    {
        $path = $referral->photo_paths[$index] ?? null;

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function viewReferralPhoto(ModelReferral $referral, int $index): StreamedResponse
    {
        $path = $referral->photo_paths[$index] ?? null;

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
