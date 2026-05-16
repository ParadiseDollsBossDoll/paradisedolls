<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\ModelInformationSubmittedMail;
use App\Models\ModelProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MemberOnboardingController extends Controller
{
    public function edit(): View
    {
        $profile = $this->profile();
        $callingCodes = config('country_calling_codes', []);
        $phoneInput = $this->splitPhone($profile->phone, $profile->country, $callingCodes);
        $emergencyContactPhoneInput = $this->splitPhone($profile->emergency_contact_phone, $profile->country, $callingCodes);

        return view('member.onboarding.edit', [
            'profile' => $profile,
            'platformOptions' => $this->platformOptions(),
            'equipmentOptions' => $this->equipmentOptions(),
            'phoneCountries' => $this->phoneCountries($callingCodes),
            'selectedPhoneCountry' => $phoneInput['country'],
            'phoneNumber' => $phoneInput['number'],
            'selectedEmergencyContactPhoneCountry' => $emergencyContactPhoneInput['country'],
            'emergencyContactPhoneNumber' => $emergencyContactPhoneInput['number'],
            'countryOptions' => $this->countryOptions($callingCodes),
            'timezoneOptions' => timezone_identifiers_list(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $callingCodes = config('country_calling_codes', []);

        $validated = $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'stage_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->format('Y-m-d')],
            'phone_country' => ['nullable', 'required_with:phone_number', 'string', Rule::in(array_keys($callingCodes))],
            'phone_number' => ['nullable', 'required_without:phone', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'timezone' => ['nullable', 'string', 'max:120'],
            'platforms' => ['nullable', 'array', 'max:12'],
            'platforms.*' => ['string', 'max:80'],
            'equipment' => ['nullable', 'array', 'max:12'],
            'equipment.*' => ['string', 'max:80'],
            'availability' => ['required', 'string', 'max:5000'],
            'goals' => ['required', 'string', 'max:5000'],
            'experience_notes' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone_country' => ['nullable', 'required_with:emergency_contact_phone_number', 'string', Rule::in(array_keys($callingCodes))],
            'emergency_contact_phone_number' => ['nullable', 'string', 'max:32'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'discord_username' => ['nullable', 'string', 'max:255'],
            'discord_user_id' => ['nullable', 'string', 'max:255'],
        ], [
            'phone_country.required_with' => __('Please choose a country code for your phone number.'),
            'phone_country.in' => __('Please choose a valid country code.'),
            'phone_number.required_without' => __('Please enter your phone number.'),
            'emergency_contact_phone_country.required_with' => __('Please choose a country code for your emergency contact phone number.'),
            'emergency_contact_phone_country.in' => __('Please choose a valid country code for your emergency contact phone number.'),
        ]);

        $this->validatePhoneInput($validated);
        $this->validatePhoneInput(
            $validated,
            numberKey: 'emergency_contact_phone_number',
            legacyKey: 'emergency_contact_phone',
            required: false
        );

        $validated['phone'] = $this->normalizePhone($validated, $callingCodes);
        $validated['emergency_contact_phone'] = $this->normalizePhone(
            $validated,
            $callingCodes,
            countryKey: 'emergency_contact_phone_country',
            numberKey: 'emergency_contact_phone_number',
            legacyKey: 'emergency_contact_phone'
        );
        unset(
            $validated['phone_country'],
            $validated['phone_number'],
            $validated['emergency_contact_phone_country'],
            $validated['emergency_contact_phone_number']
        );

        $profile = $this->profile();
        $profile->forceFill([
            ...$validated,
            'platforms' => $validated['platforms'] ?? [],
            'equipment' => $validated['equipment'] ?? [],
            'information_submitted_at' => $profile->information_submitted_at ?? now(),
        ])->save();

        $profile->refresh()->load('user');
        $this->sendConfirmation($profile);

        return redirect()
            ->route('member.verification.edit')
            ->with('status', __('Model Information Form submitted. Please complete your verification documents next.'));
    }

    private function profile(): ModelProfile
    {
        return auth()->user()->modelProfile()->firstOrCreate([]);
    }

    private function phoneCountries(array $callingCodes): array
    {
        return collect($callingCodes)
            ->map(fn (array $country, string $countryCode) => [
                'value' => $countryCode,
                'name' => $country['name'],
                'code' => $country['code'],
                'flag' => 'https://flagcdn.com/w40/'.strtolower($countryCode).'.png',
            ])
            ->values()
            ->all();
    }

    private function countryOptions(array $callingCodes): array
    {
        return collect($callingCodes)
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function splitPhone(?string $phone, ?string $countryName, array $callingCodes): array
    {
        $defaultCountry = $this->countryCodeForName($countryName, $callingCodes) ?? 'PH';
        $phone = trim((string) $phone);

        if ($phone === '') {
            return [
                'country' => $defaultCountry,
                'number' => '',
            ];
        }

        if (! str_starts_with($phone, '+')) {
            return [
                'country' => $defaultCountry,
                'number' => $phone,
            ];
        }

        $digits = preg_replace('/\D+/', '', $phone);
        $preferredCountry = $this->countryCodeForName($countryName, $callingCodes);

        if ($preferredCountry) {
            $preferredDigits = preg_replace('/\D+/', '', $callingCodes[$preferredCountry]['code']);

            if (str_starts_with($digits, $preferredDigits)) {
                return [
                    'country' => $preferredCountry,
                    'number' => substr($digits, strlen($preferredDigits)),
                ];
            }
        }

        $countriesByCodeLength = collect($callingCodes)
            ->map(fn (array $country, string $countryCode) => [
                'country' => $countryCode,
                'digits' => preg_replace('/\D+/', '', $country['code']),
            ])
            ->sortByDesc(fn (array $country) => strlen($country['digits']));

        foreach ($countriesByCodeLength as $country) {
            if (str_starts_with($digits, $country['digits'])) {
                return [
                    'country' => $country['country'],
                    'number' => substr($digits, strlen($country['digits'])),
                ];
            }
        }

        return [
            'country' => $defaultCountry,
            'number' => $phone,
        ];
    }

    private function countryCodeForName(?string $countryName, array $callingCodes): ?string
    {
        if (! $countryName) {
            return null;
        }

        foreach ($callingCodes as $countryCode => $country) {
            if (strcasecmp($country['name'], $countryName) === 0) {
                return $countryCode;
            }
        }

        return null;
    }

    private function validatePhoneInput(
        array $validated,
        string $numberKey = 'phone_number',
        string $legacyKey = 'phone',
        bool $required = true
    ): void
    {
        $phoneNumber = trim((string) ($validated[$numberKey] ?? ''));
        $legacyPhone = trim((string) ($validated[$legacyKey] ?? ''));

        if ($phoneNumber === '') {
            if ($required && $legacyPhone === '') {
                throw ValidationException::withMessages([
                    $numberKey => __('Please enter your phone number.'),
                ]);
            }

            return;
        }

        if (! preg_match('/^[0-9\s().-]+$/', $phoneNumber)) {
            throw ValidationException::withMessages([
                $numberKey => __('Use digits, spaces, dashes, or parentheses for your phone number.'),
            ]);
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber);

        if (strlen($digits) < 6 || strlen($digits) > 15) {
            throw ValidationException::withMessages([
                $numberKey => __('Enter a valid phone number with 6 to 15 digits after the country code.'),
            ]);
        }
    }

    private function normalizePhone(
        array $validated,
        array $callingCodes,
        string $countryKey = 'phone_country',
        string $numberKey = 'phone_number',
        string $legacyKey = 'phone'
    ): ?string
    {
        $phoneNumber = trim((string) ($validated[$numberKey] ?? ''));

        if ($phoneNumber === '') {
            $legacyPhone = trim((string) ($validated[$legacyKey] ?? ''));

            return $legacyPhone === '' ? null : $legacyPhone;
        }

        $country = $validated[$countryKey] ?? 'PH';
        $countryCode = $callingCodes[$country]['code'] ?? '';

        return $countryCode.preg_replace('/\D+/', '', $phoneNumber);
    }

    private function platformOptions(): array
    {
        return [
            'Chaturbate',
            'Stripchat',
            'OnlyFans',
            'Fansly',
            'Babestation',
            'LiveJasmin',
            'BongaCams',
            'Cam4',
            'CamSoda',
            'MyFreeCams',
            'Flirt4Free',
            'Streamate',
            'TikTok',
            'Instagram',
        ];
    }

    private function equipmentOptions(): array
    {
        return [
            'Phone',
            'Laptop',
            'Desktop PC',
            'Webcam',
            'Ring light',
            'Softbox lighting',
            'Microphone',
            'Stable internet',
            'Private room',
        ];
    }

    private function sendConfirmation(ModelProfile $profile): void
    {
        try {
            Mail::to($profile->user->email)->queue(new ModelInformationSubmittedMail($profile));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
