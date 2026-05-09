<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationSubmittedMail;
use App\Models\ModelApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class ApplyController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('home')->withFragment('apply');
    }

    public function store(Request $request): RedirectResponse
    {
        $callingCodes = config('country_calling_codes', []);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_country' => ['nullable', 'required_with:phone_number', 'string', Rule::in(array_keys($callingCodes))],
            'phone_number' => ['nullable', 'string', 'max:32'],
            'message' => ['nullable', 'string', 'max:5000'],
            'experience_level' => ['required', 'string', 'max:64'],
            'social_handle' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'age_confirmed' => ['accepted'],
        ], [
            'email.email' => __('Please enter a valid email address, like name@example.com.'),
            'phone_country.required_with' => __('Please choose a country code for your phone number.'),
            'phone_country.in' => __('Please choose a valid country code.'),
        ]);

        $validator->after(function ($validator) use ($request): void {
            $phoneNumber = trim((string) $request->input('phone_number', ''));

            if ($phoneNumber === '') {
                return;
            }

            if (! preg_match('/^[0-9\s().-]+$/', $phoneNumber)) {
                $validator->errors()->add(
                    'phone_number',
                    __('Use digits, spaces, dashes, or parentheses for your phone number.')
                );

                return;
            }

            $digits = preg_replace('/\D+/', '', $phoneNumber);
            $digitCount = strlen($digits);

            if ($digitCount < 6 || $digitCount > 15) {
                $validator->errors()->add(
                    'phone_number',
                    __('Enter a valid phone number with 6 to 15 digits after the country code.')
                );
            }
        });

        $validated = $validator->validate();
        $phone = $this->normalizePhone($validated, $callingCodes);

        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = $photo->store('applications/photos');
        }

        $application = ModelApplication::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'message' => $validated['message'] ?? null,
            'experience_level' => $validated['experience_level'],
            'social_handle' => $validated['social_handle'] ?? null,
            'age_confirmed' => true,
            'photo_paths' => $photoPaths,
        ]);

        $this->notifyOnboardingTeam($application);

        return redirect()->route('home')->withFragment('apply')->with('application_sent', true);
    }

    private function normalizePhone(array $validated, array $callingCodes): ?string
    {
        $phoneNumber = trim((string) ($validated['phone_number'] ?? ''));

        if ($phoneNumber === '') {
            return null;
        }

        $country = $validated['phone_country'] ?? 'PH';
        $countryCode = $callingCodes[$country]['code'] ?? null;

        if (! $countryCode) {
            return null;
        }

        return $countryCode.preg_replace('/\D+/', '', $phoneNumber);
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
