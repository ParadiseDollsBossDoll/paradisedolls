<?php

namespace App\Services;

use App\Mail\AdminActivityAlertMail;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AdminActivityNotifier
{
    /**
     * @param  array<string, string|null>  $details
     */
    public function notify(
        string $title,
        string $body,
        string $actionUrl,
        string $category,
        bool $sendEmail = true,
        ?string $emailSubject = null,
        array $details = [],
        string $actionLabel = 'Open in admin',
    ): void {
        User::query()
            ->where('role', 'admin')
            ->each(fn (User $admin) => $admin->notify(new SystemNotification(
                title: $title,
                body: $body,
                actionUrl: $actionUrl,
                category: $category,
            )));

        if (! $sendEmail) {
            return;
        }

        $email = config('paradise.onboarding_email');
        if (! filled($email)) {
            return;
        }

        try {
            Mail::to($email)->queue(new AdminActivityAlertMail(
                subjectLine: $emailSubject ?: $title,
                heading: $title,
                body: $body,
                actionUrl: $this->absoluteUrl($actionUrl),
                actionLabel: $actionLabel,
                details: $details,
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function absoluteUrl(string $url): string
    {
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        return url($url);
    }
}
