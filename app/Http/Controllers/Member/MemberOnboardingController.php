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
            'platformOptions'    => $this->streamingPlatformOptions(),
            'fanSiteOptions'     => $this->fanSiteOptions(),
            'socialMediaOptions' => $this->socialMediaOptions(),
            'fetishSections'     => $this->fetishSections(),
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
        $platformOptions = $this->allPlatformOptions();
        $equipmentOptions = $this->equipmentOptions();
        $workInterestOptions = $this->workInterestOptions();
        $comfortLevelOptions = $this->comfortLevelOptions();
        $payoutMethodOptions = $this->payoutMethodOptions();
        $fetishItems = $this->fetishItems();

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
            'platforms.*' => ['string', 'max:80', Rule::in($platformOptions)],
            // Basic Info extras
            'nationality'              => ['nullable', 'string', 'max:255'],
            'spoken_languages'         => ['nullable', 'string', 'max:255'],
            'social_handles'           => ['nullable', 'string', 'max:255'],
            'with_other_agency'        => ['nullable', 'string', 'max:255'],
            'hear_about_us'            => ['nullable', 'string', 'max:255'],
            // Appearance
            'height'                   => ['nullable', 'string', 'max:50'],
            'weight'                   => ['nullable', 'string', 'max:50'],
            'hair_color'               => ['nullable', 'string', 'max:100'],
            'eye_color'                => ['nullable', 'string', 'max:100'],
            'body_type'                => ['nullable', 'string', 'max:255'],
            'has_tattoos_piercings'    => ['nullable', 'string', 'max:500'],
            // Platforms
            'current_platforms'        => ['nullable', 'string', 'max:1000'],
            // Work preferences
            'work_interests'           => ['nullable', 'array', 'max:4'],
            'work_interests.*'         => ['string', 'max:100', Rule::in($workInterestOptions)],
            'comfort_levels'           => ['nullable', 'array', 'max:8'],
            'comfort_levels.*'         => ['string', 'max:100', Rule::in($comfortLevelOptions)],
            'custom_content_ok'        => ['nullable', 'string', 'in:Yes,No,Maybe'],
            'worn_items_ok'            => ['nullable', 'string', 'in:Yes,No,Maybe'],
            // Fetishes checklist
            'fetishes_checklist'       => ['nullable', 'array', 'max:'.count($fetishItems)],
            'fetishes_checklist.*'     => ['nullable', 'string', 'in:Yes,No,Sometimes'],
            // Availability
            'weekly_availability'      => ['nullable', 'string', 'max:255'],
            'availability_preference'  => ['nullable', 'string', 'max:255'],
            'has_private_space'        => ['nullable', 'string', 'in:Yes,No,Working on it'],
            // Payout
            'payout_methods'           => ['nullable', 'array', 'max:4'],
            'payout_methods.*'         => ['string', 'max:100', Rule::in($payoutMethodOptions)],
            'payout_method_other'      => ['nullable', 'string', 'max:255'],
            'payout_country'           => ['nullable', 'string', 'max:255'],
            // Extra details
            'model_vibe'               => ['nullable', 'string', 'max:1000'],
            'anything_else'            => ['nullable', 'string', 'max:2000'],
            'equipment' => ['nullable', 'array', 'max:12'],
            'equipment.*' => ['string', 'max:80', Rule::in($equipmentOptions)],
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

        $validated['platforms'] = $this->valuesFromOptions($validated['platforms'] ?? [], $platformOptions);
        $validated['equipment'] = $this->valuesFromOptions($validated['equipment'] ?? [], $equipmentOptions);
        $validated['work_interests'] = $this->valuesFromOptions($validated['work_interests'] ?? [], $workInterestOptions);
        $validated['comfort_levels'] = $this->valuesFromOptions($validated['comfort_levels'] ?? [], $comfortLevelOptions);
        $validated['payout_methods'] = $this->valuesFromOptions($validated['payout_methods'] ?? [], $payoutMethodOptions);
        $validated['fetishes_checklist'] = $this->filterFetishChecklist($validated['fetishes_checklist'] ?? []);

        if (! in_array('Other', $validated['payout_methods'], true)) {
            $validated['payout_method_other'] = null;
        }

        $profile = $this->profile();
        $profile->forceFill([
            ...$validated,
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
                'value'   => $countryCode,
                'name'    => $country['name'],
                'code'    => $country['code'],
                'dialNum' => (int) ltrim($country['code'], '+'),
                'flag'    => 'https://flagcdn.com/w40/'.strtolower($countryCode).'.png',
            ])
            ->sortBy('dialNum')
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
        return $this->streamingPlatformOptions();
    }

    private function allPlatformOptions(): array
    {
        return array_values(array_unique([
            ...$this->streamingPlatformOptions(),
            ...$this->fanSiteOptions(),
            ...$this->socialMediaOptions(),
        ]));
    }

    private function streamingPlatformOptions(): array
    {
        return [
            'Chaturbate',
            'Babestation',
            'Camsoda',
            'Stripchat',
            'LiveJasmin',
            'MyFreeCams',
            'BongaCams',
            'Flirt4Free',
            'Streamate',
            'Cam4',
            'XLoveCam',
            'XXXPanded',
        ];
    }

    private function fanSiteOptions(): array
    {
        return [
            'OnlyFans',
            'Fansly',
            'LoyalFans',
            'Playboy',
            'ChatAI',
        ];
    }

    private function socialMediaOptions(): array
    {
        return [
            'Instagram',
            'TikTok',
            'Twitter',
            'Telegram',
            'Snapchat',
        ];
    }

    private function fetishSections(): array
    {
        return [
            [
                'title' => 'Lingerie / Tease Shows',
                'items' => [
                    'Topless',
                    'Fully Nude',
                    'Pussy Play / Fingering',
                    'Toys (Solo)',
                    'Anal (Solo)',
                    'Squirting',
                    'Girl/Girl (G/G)',
                    'Boy/Girl (B/G) – must be verified',
                    'Couples Shows',
                    'Group Shows',
                    'Shower/Bath Shows',
                    'Oil Shows',
                    'Outdoor Shows',
                    'Public Shows (must be safe/legal)',
                ],
            ],
            [
                'title' => 'Fetish / Kink',
                'items' => [
                    'Foot Fetish (showing soles, toes, heels)',
                    'JOI (Jerk Off Instruction)',
                    'SPH (Small Penis Humiliation)',
                    'CEI (Cum Eating Instruction)',
                    'Domination (light)',
                    'Domination (hardcore)',
                    'Submissive (obedient, brat, etc.)',
                    'Findom (Financial Domination)',
                    'Roleplay (student/teacher, nurse, etc.)',
                    'Cosplay (costumes, fantasy characters)',
                    'Dirty Talk / Verbal Tease',
                    'Humiliation (light)',
                    'Humiliation (extreme)',
                    'Cuckold / Cuckquean Play',
                    'Giantess / Shrinking Fetish',
                    'Sissy Training',
                    'Chastity / Keyholding',
                    'Fetish Outfit Requests (latex, leather, socks, heels, etc.)',
                ],
            ],
            [
                'title' => 'Bodily / Sensation Fetishes',
                'items' => [
                    'Oil / Lotion Play',
                    'Shower / Bath Shows',
                    'Wet & Messy (e.g. food play, cream, etc.)',
                    'Squirting',
                    'Spitting (on self or POV)',
                    'Sweaty / Gym Content',
                    'Twerking / Ass Play',
                    'Nipple Play',
                    'Pussy Play / Fingering',
                    'Anal Play (solo)',
                    'Gaping',
                ],
            ],
            [
                'title' => 'Feet, Legs, & Stockings',
                'items' => [
                    'Foot Close-Ups',
                    'Shoeplay / Heels',
                    'Socks / Dirty Socks',
                    'Stockings / Pantyhose',
                    'Toe Curling / Wrinkled Soles',
                ],
            ],
            [
                'title' => 'Dom/Sub Kinks & Taboo Play',
                'note'  => 'Only if legal and site-approved',
                'items' => [
                    'Pet Play (Kitten, Puppy)',
                    'Age Play (18+ only)',
                    'Degradation',
                    'Collar / Leash Content',
                    'Role Reversal / Power Swap',
                    'Impact Play (light spanking)',
                ],
            ],
            [
                'title' => 'Clean Fetish Extras',
                'items' => [
                    'Shaving (legs, pussy, etc.)',
                    'Hair Brushing / Hair Play',
                    'Nail Fetish (polish, filing, close-ups)',
                ],
            ],
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

    private function workInterestOptions(): array
    {
        return [
            'OnlyFans Content',
            'Webcam Premium Shows',
            'Freemium Webcam',
            'All Types',
        ];
    }

    private function comfortLevelOptions(): array
    {
        return [
            'Tease / Lingerie',
            'Topless',
            'Nude',
            'Toys (Solo)',
            'Girl/Girl',
            'Fetish',
            'Anal (Solo)',
            'Domination / Roleplay',
        ];
    }

    private function payoutMethodOptions(): array
    {
        return [
            'Wise',
            'Bank Transfer',
            'Crypto',
            'Other',
        ];
    }

    private function fetishItems(): array
    {
        return collect($this->fetishSections())
            ->flatMap(fn (array $section) => $section['items'])
            ->values()
            ->all();
    }

    private function valuesFromOptions(mixed $values, array $allowedOptions): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->filter(fn ($value) => is_string($value) && in_array($value, $allowedOptions, true))
            ->unique()
            ->values()
            ->all();
    }

    private function filterFetishChecklist(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $allowedItems = array_fill_keys($this->fetishItems(), true);
        $allowedAnswers = ['Yes', 'No', 'Sometimes'];

        return collect($values)
            ->filter(fn ($answer, $item) => isset($allowedItems[$item]) && in_array($answer, $allowedAnswers, true))
            ->all();
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
