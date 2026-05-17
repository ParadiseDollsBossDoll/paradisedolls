<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $actionUrl = $notification->data['action_url'] ?? null;

        return redirect()->to($this->safeActionUrl($actionUrl, $request));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('status', __('Notifications marked as read.'));
    }

    private function safeActionUrl(?string $actionUrl, Request $request): string
    {
        if (blank($actionUrl)) {
            return route($request->user()->isAdmin() ? 'admin.dashboard' : 'member.dashboard');
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $actionUrl = trim($actionUrl);

        if (str_starts_with($actionUrl, '/')) {
            return $actionUrl;
        }

        if ($baseUrl !== '' && str_starts_with($actionUrl, $baseUrl)) {
            return $actionUrl;
        }

        return route($request->user()->isAdmin() ? 'admin.dashboard' : 'member.dashboard');
    }
}
