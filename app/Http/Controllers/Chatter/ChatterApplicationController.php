<?php

namespace App\Http\Controllers\Chatter;

use App\Http\Controllers\Controller;
use App\Models\ChatterRequest;
use App\Models\User;
use App\Services\AdminActivityNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ChatterApplicationController extends Controller
{
    public function create(): View
    {
        return view('chatter.apply', ['timezones' => $this->timezones()]);
    }

    public function store(Request $request, AdminActivityNotifier $notifier): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email:rfc', 'max:255',
                Rule::unique(User::class, 'email'),
                Rule::unique(ChatterRequest::class, 'email'),
            ],
            'timezone' => ['required', 'timezone'],
        ]);

        $chatterRequest = ChatterRequest::create($validated);

        $notifier->notify(
            title: __('New chatter account request'),
            body: __(':name requested access to the chatter time-tracking workspace.', ['name' => $chatterRequest->name]),
            actionUrl: route('admin.chatter-hours.index', ['section' => 'requests'], false),
            category: 'chatter_request',
            emailSubject: __('New chatter request: :name', ['name' => $chatterRequest->name]),
            details: ['Name' => $chatterRequest->name, 'Email' => $chatterRequest->email, 'Timezone' => $chatterRequest->timezone],
            actionLabel: __('Review chatter request'),
        );

        return redirect()->route('chatter.apply')->with('status', __('Your request was sent. An administrator will contact you after it has been reviewed.'));
    }

    /** @return array<string, string> */
    private function timezones(): array
    {
        return [
            'Europe/London' => 'United Kingdom - Europe/London',
            'America/New_York' => 'US Eastern - America/New_York',
            'America/Chicago' => 'US Central - America/Chicago',
            'America/Denver' => 'US Mountain - America/Denver',
            'America/Los_Angeles' => 'US Pacific - America/Los_Angeles',
            'Asia/Manila' => 'Philippines - Asia/Manila',
            'Asia/Bangkok' => 'Thailand - Asia/Bangkok',
            'Europe/Lisbon' => 'Portugal - Europe/Lisbon',
            'Europe/Berlin' => 'Central Europe - Europe/Berlin',
            'UTC' => 'UTC',
        ];
    }
}
