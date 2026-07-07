<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MarketingPreferenceController extends Controller
{
    public function show(User $user): View
    {
        return view('marketing.email-preferences', compact('user'));
    }

    public function unsubscribe(User $user): RedirectResponse
    {
        $user->forceFill(['marketing_unsubscribed_at' => now()])->save();

        return back()->with('status', __('You have been unsubscribed from marketing emails.'));
    }
}
