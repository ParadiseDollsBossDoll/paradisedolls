<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationSubmittedMail;
use App\Models\ModelApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ApplyController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('home')->withFragment('apply');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:5000'],
            'experience_level' => ['required', 'string', 'max:64'],
            'social_handle' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'age_confirmed' => ['accepted'],
        ]);

        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = $photo->store('applications/photos');
        }

        $application = ModelApplication::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'experience_level' => $validated['experience_level'],
            'social_handle' => $validated['social_handle'] ?? null,
            'age_confirmed' => true,
            'photo_paths' => $photoPaths,
        ]);

        $this->notifyOnboardingTeam($application);

        return redirect()->route('home')->withFragment('apply')->with('application_sent', true);
    }

    private function notifyOnboardingTeam(ModelApplication $application): void
    {
        $email = config('paradise.onboarding_email');

        if (! filled($email)) {
            return;
        }

        try {
            Mail::to($email)->send(new ApplicationSubmittedMail(
                application: $application,
                adminUrl: route('admin.applications.index'),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
