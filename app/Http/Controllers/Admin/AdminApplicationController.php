<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MemberApplicationApprovedMail;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
    public function index(): View
    {
        $applications = ModelApplication::query()
            ->latest()
            ->with(['reviewer:id,name', 'profile:id,model_application_id,information_submitted_at,verification_status'])
            ->paginate(20);

        return view('admin.applications.index', compact('applications'));
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
        });

        if (config('mail.default') === 'resend' && ! filled(config('services.resend.key'))) {
            return $this->redirectWithApprovalMailFailure(
                $application,
                $temporaryPassword,
                __('RESEND_API_KEY is empty. Create a key at resend.com/api-keys, add it to your .env file, run php artisan config:clear, then approve another application or contact the member manually.')
            );
        }

        if (config('mail.default') === 'smtp' && ! filled(config('mail.mailers.smtp.password'))) {
            return $this->redirectWithApprovalMailFailure(
                $application,
                $temporaryPassword,
                __('MAIL_PASSWORD is empty. For Gmail you must use an App Password from Google Account > Security > App passwords. Paste it into MAIL_PASSWORD in .env, run php artisan config:clear, then try again.')
            );
        }

        try {
            Mail::to($application->email)->send(new MemberApplicationApprovedMail(
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

        return redirect()->back()->with('status', __('Application rejected.'));
    }

    public function downloadPhoto(ModelApplication $application, int $index): StreamedResponse
    {
        $path = $application->photo_paths[$index] ?? null;

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }
}
