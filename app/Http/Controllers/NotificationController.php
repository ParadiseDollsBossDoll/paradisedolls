<?php

namespace App\Http\Controllers;

use App\Models\CourseAccessRequest;
use App\Models\ModelProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_type === $request->user()::class
                && (int) $notification->notifiable_id === (int) $request->user()->id,
            403
        );

        $notification->markAsRead();
        Cache::forget('notification_bell_'.$request->user()->id);

        $actionUrl = $notification->data['action_url'] ?? null;
        $actionUrl = $this->courseAccessReviewUrlFromNotification($notification, $actionUrl) ?? $actionUrl;

        return redirect()->to($this->safeActionUrl($actionUrl, $request));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);
        Cache::forget('notification_bell_'.$request->user()->id);

        return redirect()->back()->with('status', __('Notifications marked as read.'));
    }

    private function safeActionUrl(?string $actionUrl, Request $request): string
    {
        if (blank($actionUrl)) {
            return route($request->user()->dashboardRouteName());
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $actionUrl = trim($actionUrl);

        if ($profileUrl = $this->legacyOnboardingProfileUrl($actionUrl)) {
            return $profileUrl;
        }

        if (
            str_contains($actionUrl, "\0")
            || str_contains($actionUrl, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $actionUrl)
            || str_starts_with($actionUrl, '//')
        ) {
            return route($request->user()->dashboardRouteName());
        }

        if (str_starts_with($actionUrl, '/') && ! str_starts_with($actionUrl, '//')) {
            return $actionUrl;
        }

        $baseParts = parse_url($baseUrl);
        $actionParts = parse_url($actionUrl);

        if (
            $baseParts !== false
            && $actionParts !== false
            && strtolower($actionParts['scheme'] ?? '') === strtolower($baseParts['scheme'] ?? '')
            && strtolower($actionParts['host'] ?? '') === strtolower($baseParts['host'] ?? '')
            && (($actionParts['port'] ?? null) === ($baseParts['port'] ?? null))
        ) {
            return $actionUrl;
        }

        return route($request->user()->dashboardRouteName());
    }

    private function legacyOnboardingProfileUrl(string $actionUrl): ?string
    {
        $parts = parse_url($actionUrl);

        if ($parts === false) {
            return null;
        }

        $path = '/'.ltrim((string) ($parts['path'] ?? ''), '/');

        if ($path !== '/admin/onboarding') {
            return null;
        }

        parse_str((string) ($parts['query'] ?? ''), $query);

        $modelId = filter_var($query['model'] ?? null, FILTER_VALIDATE_INT);

        if (! $modelId) {
            return null;
        }

        $profile = ModelProfile::query()
            ->where('user_id', $modelId)
            ->first(['id']);

        return $profile
            ? route('admin.onboarding.show', $profile, false)
            : null;
    }

    private function courseAccessReviewUrlFromNotification(DatabaseNotification $notification, ?string $actionUrl): ?string
    {
        if (
            blank($actionUrl)
            || ($notification->data['category'] ?? null) !== 'course_access_requested'
        ) {
            return null;
        }

        $parts = parse_url(trim($actionUrl));

        if ($parts === false) {
            return null;
        }

        parse_str((string) ($parts['query'] ?? ''), $query);

        if (filter_var($query['course_request'] ?? null, FILTER_VALIDATE_INT)) {
            return null;
        }

        $profile = $this->profileFromOnboardingActionUrl($actionUrl);

        if (! $profile) {
            return null;
        }

        $accessRequest = CourseAccessRequest::query()
            ->where('user_id', $profile->user_id)
            ->latest('updated_at')
            ->latest('id')
            ->first(['id']);

        return $accessRequest
            ? route('admin.onboarding.show', [
                'profile' => $profile,
                'course_request' => $accessRequest->id,
            ], false)
            : null;
    }

    private function profileFromOnboardingActionUrl(string $actionUrl): ?ModelProfile
    {
        $parts = parse_url($actionUrl);

        if ($parts === false) {
            return null;
        }

        $path = '/'.ltrim((string) ($parts['path'] ?? ''), '/');

        if (preg_match('#^/admin/onboarding/(\d+)$#', $path, $matches) === 1) {
            return ModelProfile::query()->find((int) $matches[1], ['id', 'user_id']);
        }

        if ($path !== '/admin/onboarding') {
            return null;
        }

        parse_str((string) ($parts['query'] ?? ''), $query);

        $modelId = filter_var($query['model'] ?? null, FILTER_VALIDATE_INT);

        if (! $modelId) {
            return null;
        }

        return ModelProfile::query()
            ->where('user_id', $modelId)
            ->first(['id', 'user_id']);
    }
}
