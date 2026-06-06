<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationSubmittedMail;
use App\Models\ModelApplication;
use App\Models\ModelReferral;
use App\Models\User;
use App\Support\CountryCallingCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class ApplyController extends Controller
{
    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('home', array_filter([
            'ref' => $request->query('ref'),
        ]))->withFragment('apply');
    }

    public function store(Request $request): RedirectResponse
    {
        $callingCodes = config('country_calling_codes', []);
        $phonePrefixLookup = CountryCallingCodes::phonePrefixLookup($callingCodes);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_country' => ['nullable', 'required_with:phone_number', 'string', Rule::in(array_keys($phonePrefixLookup))],
            'phone_number' => ['nullable', 'string', 'max:32'],
            'message' => ['nullable', 'string', 'max:5000'],
            'experience_level' => ['required', 'string', 'max:64'],
            'instagram_handle' => ['nullable', 'string', 'max:255'],
            'tiktok_handle' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'age_confirmed' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'max:32'],
        ], [
            'email.email' => __('Please enter a valid email address, like name@example.com.'),
            'phone_country.required_with' => __('Please choose a country code for your phone number.'),
            'phone_country.in' => __('Please choose a valid country code.'),
            'photos.max' => __('Please upload up to 6 photos only.'),
            'photos.*.max' => __('Each photo must be 10 MB or smaller.'),
            'photos.*.mimes' => __('Photos must be JPG, PNG, or WEBP files.'),
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
        $phone = $this->normalizePhone($validated, $phonePrefixLookup);

        $socialHandle = implode(' / ', array_filter([
            trim((string) ($validated['instagram_handle'] ?? '')),
            trim((string) ($validated['tiktok_handle'] ?? '')),
        ])) ?: null;

        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = $photo->store('applications/photos', 'local');
        }

        $application = ModelApplication::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'message' => $validated['message'] ?? null,
            'experience_level' => $validated['experience_level'],
            'social_handle' => $socialHandle,
            'age_confirmed' => true,
            'photo_paths' => $photoPaths,
        ]);

        $this->connectReferral($application, $validated, $phone, $photoPaths, $socialHandle);
        $this->notifyOnboardingTeam($application);

        return redirect()->route('home')->withFragment('apply')->with('application_sent', true);
    }

    private function normalizePhone(array $validated, array $phonePrefixLookup): ?string
    {
        $phoneNumber = trim((string) ($validated['phone_number'] ?? ''));

        if ($phoneNumber === '') {
            return null;
        }

        $country = $validated['phone_country'] ?? 'GB';
        $countryCode = $phonePrefixLookup[$country]['prefix'] ?? null;

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
            Mail::to($email)->sendNow(new ApplicationSubmittedMail(
                application: $application,
                adminUrl: route('admin.applications.index'),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function connectReferral(ModelApplication $application, array $validated, ?string $phone, array $photoPaths, ?string $socialHandle = null): void
    {
        $referralCode = trim((string) ($validated['referral_code'] ?? ''));

        if ($referralCode === '') {
            return;
        }

        $referrer = User::query()
            ->where('role', 'model')
            ->where('referral_code', $referralCode)
            ->first(['id']);

        if (! $referrer) {
            return;
        }

        $referral = ModelReferral::query()
            ->where('referrer_id', $referrer->id)
            ->whereNull('model_application_id')
            ->where('candidate_email', $validated['email'])
            ->where('status', ModelReferral::STATUS_REFERRED)
            ->latest()
            ->first();

        $payload = [
            'model_application_id' => $application->id,
            'candidate_name' => $validated['name'],
            'candidate_email' => $validated['email'],
            'candidate_phone' => $phone,
            'candidate_social_handle' => $socialHandle,
            'experience_level' => $validated['experience_level'],
            'note' => $validated['message'] ?? null,
            'consent_confirmed' => true,
            'status' => ModelReferral::STATUS_PENDING,
            'reward_status' => ModelReferral::REWARD_NOT_ELIGIBLE,
        ];

        if ($photoPaths !== []) {
            $payload['photo_paths'] = $photoPaths;
        }

        if ($referral) {
            $referral->forceFill($payload)->save();

            return;
        }

        ModelReferral::create([
            ...$payload,
            'referrer_id' => $referrer->id,
            'photo_paths' => $photoPaths,
            'source' => ModelReferral::SOURCE_APPLY_LINK,
        ]);
    }
}
