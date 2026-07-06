<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\User;
use App\Support\DesignedXlsxWorkbook;
use App\Support\OnboardingFormDefinition;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCrmExportController extends Controller
{
    private const STYLE_TITLE = 1;
    private const STYLE_SUBTITLE = 2;
    private const STYLE_GREEN_BANNER = 3;
    private const STYLE_BLUE_BANNER = 4;
    private const STYLE_SECTION = 5;
    private const STYLE_LABEL = 6;
    private const STYLE_VALUE = 7;
    private const STYLE_VALUE_ALT = 8;
    private const STYLE_TABLE_HEADER = 9;
    private const STYLE_SUCCESS = 10;
    private const STYLE_SUCCESS_ALT = 11;
    private const STYLE_WARNING = 12;
    private const STYLE_ACCENT_LABEL = 13;
    private const STYLE_CENTER = 14;
    private const STYLE_WRAP = 15;
    private const STYLE_GOOD = 16;
    private const STYLE_BAD = 17;
    private const STYLE_KPI_BLUE = 18;
    private const STYLE_KPI_GREEN = 19;
    private const STYLE_KPI_PURPLE = 20;
    private const STYLE_KPI_ORANGE = 21;
    private const STYLE_RED_SECTION = 22;
    private const STYLE_BAD_LEFT = 23;

    public function applications(): StreamedResponse
    {
        $applications = ModelApplication::query()
            ->with([
                'reviewer:id,name,email',
                'user:id,name,email',
                'referral.referrer:id,name,email',
            ])
            ->latest()
            ->get();

        $headers = [
            'Application ID',
            'Status',
            'Name',
            'Email',
            'Phone',
            'Experience Level',
            'Social Handle',
            'Age Confirmed',
            'Message',
            'Submitted At',
            'Reviewed At',
            'Reviewed By',
            'Member Account Email',
            'Referrer Name',
            'Referrer Email',
            'Referral Status',
            'Referral Reward Status',
            'Photo Count',
            'Photo URLs',
            'Created At',
            'Updated At',
        ];

        $rows = $applications->map(fn (ModelApplication $application): array => [
            $application->id,
            $application->status,
            $application->name,
            $application->email,
            $application->phone,
            $application->experience_level,
            $application->social_handle,
            $this->yesNo($application->age_confirmed),
            $application->message,
            $this->dateTime($application->created_at),
            $this->dateTime($application->reviewed_at),
            $application->reviewer?->name,
            $application->user?->email,
            $application->referral?->referrer?->name,
            $application->referral?->referrer?->email,
            $application->referral?->status,
            $application->referral?->reward_status,
            count($application->photo_paths ?? []),
            $this->applicationPhotoUrls($application),
            $this->dateTime($application->created_at),
            $this->dateTime($application->updated_at),
        ]);

        return $this->csvDownload(
            'paradise-applications-'.now()->format('Y-m-d').'.csv',
            $headers,
            $rows
        );
    }

    public function onboarding(): StreamedResponse
    {
        $members = User::query()
            ->where('role', 'model')
            ->with([
                'modelProfile.application.reviewer:id,name,email',
                'modelProfile.verificationReviewer:id,name,email',
            ])
            ->orderBy('name')
            ->get();

        return $this->xlsxDownload(
            'paradise-onboarding-all-models-'.now()->format('Y-m-d').'.xlsx',
            $this->allModelsWorkbook($members)
        );
    }

    public function onboardingProfile(ModelProfile $profile): StreamedResponse
    {
        $profile->loadMissing([
            'user',
            'application.reviewer:id,name,email',
            'verificationReviewer:id,name,email',
        ]);

        $member = $profile->user;

        abort_unless($member, 404);

        return $this->xlsxDownload(
            'paradise-onboarding-'.$this->filenameSlug($member->name).'-designed.xlsx',
            $this->singleProfileWorkbook($profile, $member)
        );
    }

    private function allModelsWorkbook($members): DesignedXlsxWorkbook
    {
        $members = $members->values();

        return new DesignedXlsxWorkbook([
            $this->modelDirectorySheet($members),
            $this->verificationTrackerSheet($members),
            $this->workPreferencesSheet($members),
            $this->onboardingStatusSheet($members),
        ]);
    }

    private function modelDirectorySheet($members): array
    {
        $total = $members->count();
        $approved = $members->filter(fn (User $member): bool => $member->modelProfile?->application?->status === ModelApplication::STATUS_APPROVED)->count();
        $verified = $members->filter(fn (User $member): bool => $member->modelProfile?->isVerified())->count();
        $pending = max(0, $total - $approved);

        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F338} PARADISE DOLLS \u{2014} MODEL DIRECTORY", self::STYLE_TITLE)], 34),
            $this->sheetRow(2, [$this->cell(1, 'All registered models  |  Total: '.$total.'  |  Export date: '.now()->format('Y-m-d'), self::STYLE_SUBTITLE)], 16),
            $this->sheetRow(3, [
                $this->cell(1, 'Total Models', self::STYLE_KPI_BLUE),
                $this->cell(4, 'Approved', self::STYLE_KPI_GREEN),
                $this->cell(7, 'Verified', self::STYLE_KPI_PURPLE),
                $this->cell(10, 'Pending', self::STYLE_KPI_ORANGE),
            ], 16),
            $this->sheetRow(4, [
                $this->cell(1, $total, self::STYLE_KPI_BLUE),
                $this->cell(4, $approved, self::STYLE_KPI_GREEN),
                $this->cell(7, $verified, self::STYLE_KPI_PURPLE),
                $this->cell(10, $pending, self::STYLE_KPI_ORANGE),
            ], 26),
            $this->sheetRow(6, $this->cellsFromValues([
                '#',
                'Name',
                'Stage Name',
                'Email',
                'Country',
                'DOB',
                'Height',
                'Weight',
                'Platforms',
                'Availability',
                'App Status',
                'Verification',
                'Joined At',
            ], self::STYLE_TABLE_HEADER), 22),
        ];

        foreach ($members as $index => $member) {
            $profile = $member->modelProfile;
            $application = $profile?->application;
            $row = 7 + $index;
            $styles = [
                self::STYLE_CENTER,
                self::STYLE_ACCENT_LABEL,
                self::STYLE_VALUE,
                self::STYLE_VALUE,
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                self::STYLE_WRAP,
                self::STYLE_CENTER,
                $this->statusStyle($application?->status),
                $this->verificationStyle($profile),
                self::STYLE_CENTER,
            ];

            $rows[] = $this->sheetRow($row, $this->cellsFromValues([
                $index + 1,
                $member->name,
                $profile?->stage_name,
                $member->email,
                $profile?->country,
                $profile?->date_of_birth?->format('Y-m-d'),
                $profile?->height,
                $profile?->weight,
                $this->listValue($profile?->platforms),
                $this->availabilitySummary($profile),
                $this->applicationStatusLabel($application?->status),
                $this->verificationStatusLabel($profile),
                $this->dateTime($member->created_at),
            ], $styles), 22);
        }

        return [
            'name' => "\u{1F4CB} Model Directory",
            'freezeRow' => 6,
            'columns' => [4, 20, 14, 26, 14, 13, 10, 10, 44, 20, 16, 16, 21],
            'rows' => $rows,
            'merges' => [
                'A1:M1',
                'A2:M2',
                'A3:C3',
                'D3:F3',
                'G3:I3',
                'J3:M3',
                'A4:C4',
                'D4:F4',
                'G4:I4',
                'J4:M4',
            ],
        ];
    }

    private function verificationTrackerSheet($members): array
    {
        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F512} VERIFICATION TRACKER", self::STYLE_TITLE)], 34),
            $this->sheetRow(2, [$this->cell(1, 'Document status and verification review for all models', self::STYLE_SUBTITLE)], 16),
            $this->sheetRow(3, $this->cellsFromValues([
                '#',
                'Name',
                'ID Uploaded',
                'Selfie Uploaded',
                'Platform Codes',
                'Verification Status',
                'Submitted At',
                'Reviewed At',
                'Reviewed By',
                'Notes',
            ], self::STYLE_TABLE_HEADER), 22),
        ];

        foreach ($members as $index => $member) {
            $profile = $member->modelProfile;
            $rows[] = $this->sheetRow(4 + $index, $this->cellsFromValues([
                $index + 1,
                $member->name,
                $this->checkIcon(filled($profile?->id_document_path)),
                $this->checkIcon(filled($profile?->selfie_with_id_path)),
                $this->checkIcon(filled($profile?->platform_codes_path)),
                $this->verificationStatusLabel($profile),
                $this->dateTime($profile?->verification_submitted_at),
                $this->dateTime($profile?->verification_reviewed_at),
                $profile?->verificationReviewer?->name,
                $profile?->verification_notes,
            ], [
                self::STYLE_CENTER,
                self::STYLE_ACCENT_LABEL,
                filled($profile?->id_document_path) ? self::STYLE_GOOD : self::STYLE_BAD,
                filled($profile?->selfie_with_id_path) ? self::STYLE_GOOD : self::STYLE_BAD,
                filled($profile?->platform_codes_path) ? self::STYLE_GOOD : self::STYLE_BAD,
                $this->verificationStyle($profile),
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                self::STYLE_VALUE,
                self::STYLE_WRAP,
            ]), 22);
        }

        return [
            'name' => "\u{2705} Verification Tracker",
            'freezeRow' => 3,
            'columns' => [4, 20, 14, 15, 15, 19, 21, 21, 18, 40],
            'rows' => $rows,
            'merges' => ['A1:J1', 'A2:J2'],
        ];
    }

    private function workPreferencesSheet($members): array
    {
        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F4BC} WORK PREFERENCES & AVAILABILITY", self::STYLE_TITLE)], 34),
            $this->sheetRow(2, [$this->cell(1, 'Platform presence, content comfort levels, and payout info for all models', self::STYLE_SUBTITLE)], 16),
            $this->sheetRow(3, $this->cellsFromValues([
                '#',
                'Name',
                'Stage Name',
                'Platforms',
                'Work Interests',
                'Comfort Levels',
                'Custom Content',
                'Worn Items',
                'Hrs/Week',
                'Pref Time',
                'Private Space',
                'Payout Methods',
                'Equipment',
                'Custom Answers',
            ], self::STYLE_TABLE_HEADER), 22),
        ];

        foreach ($members as $index => $member) {
            $profile = $member->modelProfile;
            $rows[] = $this->sheetRow(4 + $index, $this->cellsFromValues([
                $index + 1,
                $member->name,
                $profile?->stage_name,
                $this->listValue($profile?->platforms),
                $this->listValue($profile?->work_interests),
                $this->listValue($profile?->comfort_levels),
                $this->checkIcon($profile?->custom_content_ok === 'Yes'),
                $this->checkIcon($profile?->worn_items_ok === 'Yes'),
                $profile?->weekly_availability,
                $profile?->availability_preference,
                $this->checkIcon($profile?->has_private_space === 'Yes'),
                $this->payoutSummary($profile),
                $this->listValue($profile?->equipment),
                $this->customAnswersSummary($profile),
            ], [
                self::STYLE_CENTER,
                self::STYLE_ACCENT_LABEL,
                self::STYLE_VALUE,
                self::STYLE_WRAP,
                self::STYLE_WRAP,
                self::STYLE_WRAP,
                $profile?->custom_content_ok === 'Yes' ? self::STYLE_GOOD : self::STYLE_WARNING,
                $profile?->worn_items_ok === 'Yes' ? self::STYLE_GOOD : self::STYLE_WARNING,
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                $profile?->has_private_space === 'Yes' ? self::STYLE_GOOD : self::STYLE_WARNING,
                self::STYLE_WRAP,
                self::STYLE_WRAP,
                self::STYLE_WRAP,
            ]), 30);
        }

        return [
            'name' => "\u{1F4BC} Work Preferences",
            'freezeRow' => 3,
            'columns' => [4, 20, 14, 42, 24, 26, 15, 13, 13, 14, 16, 26, 24, 36],
            'rows' => $rows,
            'merges' => ['A1:N1', 'A2:N2'],
        ];
    }

    private function onboardingStatusSheet($members): array
    {
        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F4CA} ONBOARDING STATUS OVERVIEW", self::STYLE_TITLE)], 34),
            $this->sheetRow(2, [$this->cell(1, 'Stage tracking, community access, and application results for all models', self::STYLE_SUBTITLE)], 16),
            $this->sheetRow(3, $this->cellsFromValues([
                '#',
                'Name',
                'Email',
                'Onboarding Stage',
                'Info Submitted At',
                'Community Invited',
                'Community URL',
                'Discord Username',
                'Application ID',
                'App Status',
            ], self::STYLE_TABLE_HEADER), 22),
        ];

        foreach ($members as $index => $member) {
            $profile = $member->modelProfile;
            $application = $profile?->application;
            $rows[] = $this->sheetRow(4 + $index, $this->cellsFromValues([
                $index + 1,
                $member->name,
                $member->email,
                $profile?->onboardingStageLabel(),
                $this->dateTime($profile?->information_submitted_at),
                $this->dateTime($profile?->community_invited_at),
                $profile?->community_invite_url,
                $profile?->discord_username,
                $application?->id,
                $this->applicationStatusLabel($application?->status),
            ], [
                self::STYLE_CENTER,
                self::STYLE_ACCENT_LABEL,
                self::STYLE_VALUE,
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                self::STYLE_WRAP,
                self::STYLE_CENTER,
                self::STYLE_CENTER,
                $this->statusStyle($application?->status),
            ]), 22);
        }

        return [
            'name' => "\u{1F4CA} Onboarding Status",
            'freezeRow' => 3,
            'columns' => [4, 20, 28, 18, 21, 21, 44, 20, 14, 16],
            'rows' => $rows,
            'merges' => ['A1:J1', 'A2:J2'],
        ];
    }

    private function singleProfileWorkbook(ModelProfile $profile, User $member): DesignedXlsxWorkbook
    {
        return new DesignedXlsxWorkbook([
            $this->profileOverviewSheet($profile, $member),
            $this->profileVerificationSheet($profile, $member),
            $this->profileContentChecklistSheet($profile),
            $this->profileSummaryDashboardSheet($profile, $member),
        ]);
    }

    private function profileOverviewSheet(ModelProfile $profile, User $member): array
    {
        $application = $profile->application;
        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F338} PARADISE DOLLS \u{2014} MODEL ONBOARDING PROFILE", self::STYLE_TITLE)], 36),
            $this->sheetRow(2, [$this->cell(1, 'Generated: '.now()->format('Y-m-d').'  |  Application ID: '.$this->valueOrDash($application?->id).'  |  Status: '.strtoupper($application?->status ?? 'unknown'), self::STYLE_SUBTITLE)], 18),
            $this->sheetRow(3, [
                $this->cell(1, $this->applicationStatusLabel($application?->status, 'APPLICATION STATUS: '), $this->statusStyle($application?->status)),
                $this->cell(5, $this->verificationStatusLabel($profile, 'VERIFICATION: '), $this->verificationStyle($profile)),
            ], 24),
        ];
        $merges = ['A1:H1', 'A2:H2', 'A3:D3', 'E3:H3'];
        $row = 4;

        $this->addProfileSection($rows, $merges, $row, "\u{1F464}  PERSONAL INFORMATION");
        foreach ([
            ['Full Name', $member->name],
            ['Legal Name', $profile->legal_name],
            ['Stage Name', $profile->stage_name],
            ['Date of Birth', $profile->date_of_birth?->format('Y-m-d')],
            ['Email', $member->email],
            ['Phone', $profile->phone],
            ['Country', $profile->country],
            ['City', $profile->city],
            ['Nationality', $profile->nationality],
            ['Languages', $profile->spoken_languages],
            ['Social Handles', $profile->social_handles],
            ['How Found Us', $profile->hear_about_us],
            ['With Other Agency', $profile->with_other_agency],
        ] as $field) {
            $this->addProfileField($rows, $merges, $row, $field[0], $field[1]);
        }

        $row++;
        $this->addProfileSection($rows, $merges, $row, "\u{1F4CF}  PHYSICAL ATTRIBUTES");
        foreach ([
            ['Height', $profile->height],
            ['Weight', $profile->weight],
            ['Hair Color', $profile->hair_color],
            ['Eye Color', $profile->eye_color],
            ['Body Type', $profile->body_type],
            ['Tattoos/Piercings', $profile->has_tattoos_piercings],
        ] as $field) {
            $this->addProfileField($rows, $merges, $row, $field[0], $field[1]);
        }

        $row++;
        $this->addProfileSection($rows, $merges, $row, "\u{1F4BC}  WORK & AVAILABILITY");
        foreach ([
            ['Platforms', $this->listValue($profile->platforms)],
            ['Current Platforms', $profile->current_platforms],
            ['Work Interests', $this->listValue($profile->work_interests)],
            ['Comfort Levels', $this->listValue($profile->comfort_levels)],
            ['Custom Content', $profile->custom_content_ok],
            ['Worn Items OK', $profile->worn_items_ok],
            ['Hrs/Week Available', $profile->weekly_availability],
            ['Availability Pref', $profile->availability_preference],
            ['Availability', $profile->availability],
            ['Private Space', $profile->has_private_space],
            ['Equipment', $this->listValue($profile->equipment)],
            ['Goals', $profile->goals],
            ['Model Vibe', $profile->model_vibe],
            ['Experience Notes', $profile->experience_notes],
            ['Anything Else', $profile->anything_else],
        ] as $field) {
            $this->addProfileField($rows, $merges, $row, $field[0], $field[1]);
        }

        $customAnswers = $this->customAnswersForProfile($profile);
        if ($customAnswers !== []) {
            $row++;
            $this->addProfileSection($rows, $merges, $row, "\u{1F4DD}  CUSTOM ONBOARDING ANSWERS");

            foreach ($customAnswers as $answer) {
                $label = $answer['label'].($answer['archived'] ? ' (Archived)' : '');
                $this->addProfileField($rows, $merges, $row, $label, $answer['answer']);
            }
        }

        $row++;
        $this->addProfileSection($rows, $merges, $row, "\u{1F4B3}  PAYOUT INFORMATION");
        foreach ([
            ['Payout Methods', $this->payoutSummary($profile)],
            ['Payout Country', $profile->payout_country],
            ['Name on Account', $profile->payout_account_name],
            ['Name of Bank', $profile->payout_bank_name],
            ['Sort Code', $profile->payout_sort_code],
            ['Account Number', $profile->payout_account_number],
            ['IBAN', $profile->payout_iban],
        ] as $field) {
            $this->addProfileField($rows, $merges, $row, $field[0], $field[1]);
        }

        $row++;
        $this->addProfileSection($rows, $merges, $row, "\u{1F4AC}  CONTACT & DISCORD");
        foreach ([
            ['Discord Username', $profile->discord_username],
            ['Discord User ID', $profile->discord_user_id],
            ['Emergency Contact', trim((string) $profile->emergency_contact_name.' '.(string) $profile->emergency_contact_phone)],
        ] as $field) {
            $this->addProfileField($rows, $merges, $row, $field[0], $field[1]);
        }

        return [
            'name' => "\u{1F464} Profile Overview",
            'freezeRow' => 3,
            'columns' => [22, 18, 18, 18, 18, 18, 18, 18],
            'rows' => $rows,
            'merges' => $merges,
        ];
    }

    private function profileVerificationSheet(ModelProfile $profile, User $member): array
    {
        $application = $profile->application;
        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F512} VERIFICATION & ONBOARDING STATUS", self::STYLE_TITLE)], 36),
            $this->sheetRow(2, [$this->cell(1, 'Identity verification, document checks, and community access', self::STYLE_SUBTITLE)], 18),
            $this->sheetRow(3, [$this->cell(1, '  '.$this->icon('clipboard').'  ONBOARDING CHECKLIST', self::STYLE_SECTION)], 20),
            $this->sheetRow(4, $this->cellsFromValues(['Step', 'Status', 'Date / Detail', 'Reviewed By', 'Notes'], self::STYLE_TABLE_HEADER), 22),
        ];

        $steps = [
            ['1. Account Created', true, $this->dateTime($member->created_at), '', ''],
            ['2. Form Submitted', $profile->hasInformationForm(), $this->dateTime($profile->information_submitted_at), '', ''],
            ['3. ID Uploaded', filled($profile->id_document_path), $this->dateTime($profile->verification_submitted_at), '', ''],
            ['4. Selfie with ID', filled($profile->selfie_with_id_path), $this->dateTime($profile->verification_submitted_at), '', ''],
            ['5. Platform Codes', filled($profile->platform_codes_path), '', '', filled($profile->platform_codes_path) ? '' : 'Pending upload'],
            ['6. Verification Review', $profile->isVerified(), $this->dateTime($profile->verification_reviewed_at), $profile->verificationReviewer?->name, $profile->verification_notes],
            ['7. Discord Invited', filled($profile->community_invited_at), $this->dateTime($profile->community_invited_at), '', $profile->community_invite_url],
            ['8. Application Approved', $application?->status === ModelApplication::STATUS_APPROVED, $this->dateTime($application?->reviewed_at), $application?->reviewer?->name, ''],
        ];

        foreach ($steps as $index => $step) {
            [$label, $complete, $date, $reviewer, $notes] = $step;
            $rows[] = $this->sheetRow(5 + $index, $this->cellsFromValues([
                $label,
                $complete ? "\u{2705} Complete" : "\u{26A0}\u{FE0F} Not Yet",
                $date,
                $reviewer,
                $notes,
            ], [
                $index % 2 === 0 ? self::STYLE_VALUE : self::STYLE_VALUE_ALT,
                $complete ? self::STYLE_GOOD : self::STYLE_WARNING,
                $index % 2 === 0 ? self::STYLE_VALUE : self::STYLE_VALUE_ALT,
                $index % 2 === 0 ? self::STYLE_VALUE : self::STYLE_VALUE_ALT,
                $index % 2 === 0 ? self::STYLE_WRAP : self::STYLE_VALUE_ALT,
            ]), 20);
        }

        $start = 15;
        $rows[] = $this->sheetRow(14, [$this->cell(1, '  '.$this->icon('document').'  DOCUMENT UPLOADS', self::STYLE_SECTION)], 20);
        $rows[] = $this->sheetRow($start, $this->cellsFromValues(['Document', 'Uploaded', 'Status'], self::STYLE_TABLE_HEADER), 20);
        foreach ([
            ['Government ID', filled($profile->id_document_path)],
            ['Selfie with ID', filled($profile->selfie_with_id_path)],
            ['Platform Codes', filled($profile->platform_codes_path)],
        ] as $index => $document) {
            [$label, $uploaded] = $document;
            $rows[] = $this->sheetRow($start + 1 + $index, $this->cellsFromValues([
                $label,
                $uploaded ? 'Yes' : 'No',
                $uploaded ? "\u{2705} Uploaded" : "\u{274C} Missing",
            ], [
                $index % 2 === 0 ? self::STYLE_VALUE : self::STYLE_VALUE_ALT,
                $index % 2 === 0 ? self::STYLE_CENTER : self::STYLE_VALUE_ALT,
                $uploaded ? self::STYLE_GOOD : self::STYLE_BAD,
            ]), 18);
        }

        return [
            'name' => "\u{2705} Verification",
            'freezeRow' => 3,
            'columns' => [28, 18, 22, 18, 44],
            'rows' => $rows,
            'merges' => ['A1:H1', 'A2:H2', 'A3:E3', 'A14:E14'],
        ];
    }

    private function profileContentChecklistSheet(ModelProfile $profile): array
    {
        [$approved, $declined] = $this->splitChecklist($profile->fetishes_checklist ?? []);
        $total = count($approved) + count($declined);
        $approvedPercent = $total > 0 ? round((count($approved) / $total) * 100) : 0;
        $declinedPercent = $total > 0 ? 100 - $approvedPercent : 0;
        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F3AD} CONTENT & FETISH CHECKLIST", self::STYLE_TITLE)], 36),
            $this->sheetRow(2, [$this->cell(1, "Model's self-selected content preferences and comfort indicators", self::STYLE_SUBTITLE)], 18),
            $this->sheetRow(3, [
                $this->cell(1, "\u{2705}  Approved: ".count($approved).' categories', self::STYLE_GREEN_BANNER),
                $this->cell(4, "\u{274C}  Declined: ".count($declined).' categories', self::STYLE_RED_SECTION),
            ], 22),
            $this->sheetRow(5, [
                $this->cell(1, '  '."\u{2705}  APPROVED / CONDITIONAL CONTENT", self::STYLE_SECTION),
                $this->cell(4, '  '."\u{274C}  DECLINED CONTENT", self::STYLE_RED_SECTION),
            ], 20),
            $this->sheetRow(6, $this->cellsFromValues(['#', 'Category', 'Status', '#', 'Category', 'Status'], self::STYLE_TABLE_HEADER), 18),
        ];

        $rowCount = max(1, count($approved), count($declined));

        for ($index = 0; $index < $rowCount; $index++) {
            $approvedItem = $approved[$index] ?? null;
            $declinedItem = $declined[$index] ?? null;
            $rows[] = $this->sheetRow(7 + $index, [
                $this->cell(1, $approvedItem ? $index + 1 : '', $index % 2 === 0 ? self::STYLE_CENTER : self::STYLE_VALUE_ALT),
                $this->cell(2, $approvedItem['label'] ?? ($total === 0 ? 'No checklist responses yet' : ''), $index % 2 === 0 ? self::STYLE_VALUE : self::STYLE_VALUE_ALT),
                $this->cell(3, $approvedItem ? $this->checklistAnswerLabel($approvedItem['answer']) : '', $approvedItem ? ($approvedItem['answer'] === 'Sometimes' ? self::STYLE_WARNING : self::STYLE_GOOD) : self::STYLE_VALUE),
                $this->cell(4, $declinedItem ? $index + 1 : '', $index % 2 === 0 ? self::STYLE_CENTER : self::STYLE_VALUE_ALT),
                $this->cell(5, $declinedItem['label'] ?? '', $index % 2 === 0 ? self::STYLE_VALUE : self::STYLE_VALUE_ALT),
                $this->cell(6, $declinedItem ? "\u{274C} NO" : '', $declinedItem ? self::STYLE_BAD : self::STYLE_VALUE),
            ], 18);
        }

        return [
            'name' => "\u{1F3AD} Content Checklist",
            'freezeRow' => 3,
            'columns' => [5, 38, 14, 5, 38, 14],
            'rows' => $rows,
            'merges' => ['A1:H1', 'A2:H2', 'A3:C3', 'D3:F3', 'A5:C5', 'D5:F5'],
        ];
    }

    private function profileSummaryDashboardSheet(ModelProfile $profile, User $member): array
    {
        $application = $profile->application;
        [$approved, $declined] = $this->splitChecklist($profile->fetishes_checklist ?? []);
        $totalChecklist = count($approved) + count($declined);
        $approvedPercent = $totalChecklist > 0 ? round((count($approved) / $totalChecklist) * 100) : 0;
        $declinedPercent = $totalChecklist > 0 ? 100 - $approvedPercent : 0;

        $rows = [
            $this->sheetRow(1, [$this->cell(1, "\u{1F4CA} ONBOARDING SUMMARY DASHBOARD", self::STYLE_TITLE)], 36),
            $this->sheetRow(2, [$this->cell(1, 'Quick-glance stats and key info for agency review', self::STYLE_SUBTITLE)], 18),
            $this->sheetRow(3, [
                $this->cell(1, 'Application ID', self::STYLE_KPI_BLUE),
                $this->cell(3, 'App Status', self::STYLE_KPI_GREEN),
                $this->cell(5, 'Verification', self::STYLE_KPI_PURPLE),
                $this->cell(7, 'Hrs/Week', self::STYLE_KPI_ORANGE),
            ], 16),
            $this->sheetRow(4, [
                $this->cell(1, $this->valueOrDash($application?->id), self::STYLE_KPI_BLUE),
                $this->cell(3, strtoupper($application?->status ?? $this->dash()), self::STYLE_KPI_GREEN),
                $this->cell(5, strtoupper($profile->verification_status ?? $this->dash()), self::STYLE_KPI_PURPLE),
                $this->cell(7, $this->valueOrDash($profile->weekly_availability), self::STYLE_KPI_ORANGE),
            ], 28),
            $this->sheetRow(6, [$this->cell(1, '  '.$this->icon('clipboard').'  KEY PROFILE SUMMARY', self::STYLE_SECTION)], 20),
        ];
        $merges = [
            'A1:H1',
            'A2:H2',
            'A3:B3',
            'C3:D3',
            'E3:F3',
            'G3:H3',
            'A4:B4',
            'C4:D4',
            'E4:F4',
            'G4:H4',
            'A6:H6',
        ];
        $row = 7;

        foreach ([
            ['Name', $member->name],
            ['Stage Name', $profile->stage_name],
            ['Date of Birth', $profile->date_of_birth?->format('Y-m-d')],
            ['Country', $profile->country],
            ['Nationality', $profile->nationality],
            ['Phone', $profile->phone],
            ['Email', $member->email],
            ['Height / Weight', trim((string) $profile->height.' / '.(string) $profile->weight, ' /')],
            ['Hair / Eyes', trim((string) $profile->hair_color.' / '.(string) $profile->eye_color, ' /')],
            ['Body Type', $profile->body_type],
            ['Platforms', $this->listValue($profile->platforms)],
            ['Payout Methods', $this->payoutSummary($profile)],
            ['Equipment', $this->listValue($profile->equipment)],
            ['Availability', $this->availabilitySummary($profile)],
            ['Goals', $profile->goals],
            ['Joined', $this->dateTime($member->created_at)],
            ['Discord', trim((string) $profile->discord_username.' '.($profile->discord_user_id ? '('.$profile->discord_user_id.')' : ''))],
            ['Community URL', $profile->community_invite_url],
        ] as $field) {
            $this->addProfileField($rows, $merges, $row, $field[0], $field[1]);
        }

        $row++;
        $rows[] = $this->sheetRow($row, [$this->cell(1, '  '."\u{1F3AD}  CONTENT CHECKLIST BREAKDOWN", self::STYLE_SECTION)], 20);
        $merges[] = 'A'.$row.':H'.$row;
        $row++;
        $rows[] = $this->sheetRow($row, $this->cellsFromValues(['Category', 'Result', 'Category', 'Result', 'Category', 'Result', 'Category', 'Result'], self::STYLE_TABLE_HEADER), 20);
        $row++;
        $rows[] = $this->sheetRow($row, [
            $this->cell(1, "\u{2705} Approved: ".count($approved).' out of '.$totalChecklist.' categories ('.$approvedPercent.'%)', self::STYLE_GOOD),
            $this->cell(5, "\u{274C} Declined: ".count($declined).' out of '.$totalChecklist.' categories ('.$declinedPercent.'%)', self::STYLE_BAD),
        ], 22);
        $merges[] = 'A'.$row.':D'.$row;
        $merges[] = 'E'.$row.':H'.$row;

        return [
            'name' => "\u{1F4CA} Summary Dashboard",
            'freezeRow' => 3,
            'columns' => [22, 18, 14, 14, 14, 14, 14, 14],
            'rows' => $rows,
            'merges' => $merges,
        ];
    }

    private function onboardingHeaders(): array
    {
        $headers = [
            'User ID',
            'Profile ID',
            'Name',
            'Email',
            'Joined At',
            'Legal Name',
            'Stage Name',
            'Date Of Birth',
            'Phone',
            'Country',
            'City',
            'Timezone',
            'Nationality',
            'Spoken Languages',
            'Social Handles',
            'With Other Agency',
            'How Found Us',
            'Height',
            'Weight',
            'Hair Color',
            'Eye Color',
            'Body Type',
            'Tattoos Piercings',
            'Platforms',
            'Current Platforms',
            'Fetishes Checklist',
            'Work Interests',
            'Comfort Levels',
            'Custom Content OK',
            'Worn Items OK',
            'Weekly Availability',
            'Availability Preference',
            'Private Space',
            'Payout Methods',
            'Payout Method Other',
            'Payout Country',
            'Name on Account',
            'Name of Bank',
            'Sort Code',
            'Account Number',
            'IBAN',
            'Model Vibe',
            'Anything Else',
            'Custom Onboarding Answers',
            'Onboarding Form Version',
            'Equipment',
            'Availability',
            'Goals',
            'Experience Notes',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Discord Username',
            'Discord User ID',
            'Onboarding Stage',
            'Onboarding Stage Label',
            'Information Submitted At',
            'Verification Status',
            'Verification Status Label',
            'Verification Submitted At',
            'Verification Reviewed At',
            'Verification Reviewed By',
            'Verification Notes',
            'Verification Request Instructions',
            'ID Document Uploaded',
            'Selfie With ID Uploaded',
            'Platform Codes Uploaded',
            'Community Invited At',
            'Community Invite URL',
            'Community Role Assigned At',
            'Application ID',
            'Application Status',
        ];

        return $headers;
    }

    private function onboardingRow(?User $member): array
    {
        $profile = $member?->modelProfile;

        return [
            $member?->id,
            $profile?->id,
            $member?->name,
            $member?->email,
            $this->dateTime($member?->created_at),
            $profile?->legal_name,
            $profile?->stage_name,
            $profile?->date_of_birth?->format('Y-m-d'),
            $profile?->phone,
            $profile?->country,
            $profile?->city,
            $profile?->timezone,
            $profile?->nationality,
            $profile?->spoken_languages,
            $profile?->social_handles,
            $profile?->with_other_agency,
            $profile?->hear_about_us,
            $profile?->height,
            $profile?->weight,
            $profile?->hair_color,
            $profile?->eye_color,
            $profile?->body_type,
            $profile?->has_tattoos_piercings,
            $this->listValue($profile?->platforms),
            $profile?->current_platforms,
            $this->listValue($profile?->fetishes_checklist),
            $this->listValue($profile?->work_interests),
            $this->listValue($profile?->comfort_levels),
            $profile?->custom_content_ok,
            $profile?->worn_items_ok,
            $profile?->weekly_availability,
            $profile?->availability_preference,
            $profile?->has_private_space,
            $this->listValue($profile?->payout_methods),
            $profile?->payout_method_other,
            $profile?->payout_country,
            $profile?->payout_account_name,
            $profile?->payout_bank_name,
            $profile?->payout_sort_code,
            $profile?->payout_account_number,
            $profile?->payout_iban,
            $profile?->model_vibe,
            $profile?->anything_else,
            $this->customAnswersSummary($profile),
            $profile?->onboarding_form_version,
            $this->listValue($profile?->equipment),
            $profile?->availability,
            $profile?->goals,
            $profile?->experience_notes,
            $profile?->emergency_contact_name,
            $profile?->emergency_contact_phone,
            $profile?->discord_username,
            $profile?->discord_user_id,
            $profile?->onboarding_stage,
            $profile?->onboardingStageLabel(),
            $this->dateTime($profile?->information_submitted_at),
            $profile?->verification_status,
            $profile?->verificationStatusLabel(),
            $this->dateTime($profile?->verification_submitted_at),
            $this->dateTime($profile?->verification_reviewed_at),
            $profile?->verificationReviewer?->name,
            $profile?->verification_notes,
            $profile?->verification_request_instructions,
            $this->yesNo(filled($profile?->id_document_path)),
            $this->yesNo(filled($profile?->selfie_with_id_path)),
            $this->yesNo(filled($profile?->platform_codes_path)),
            $this->dateTime($profile?->community_invited_at),
            $profile?->community_invite_url,
            $this->dateTime($profile?->community_role_assigned_at),
            $profile?->application?->id,
            $profile?->application?->status,
        ];
    }

    private function customAnswersSummary(?ModelProfile $profile): string
    {
        $answers = $this->customAnswersForProfile($profile);

        if ($answers === []) {
            return '';
        }

        return collect($answers)
            ->map(fn (array $answer): string => $answer['label'].': '.$answer['answer'].($answer['archived'] ? ' (archived field)' : ''))
            ->implode("\n");
    }

    private function customAnswersForProfile(?ModelProfile $profile): array
    {
        if (! $profile || empty($profile->custom_onboarding_answers)) {
            return [];
        }

        return OnboardingFormDefinition::customAnswersForDisplay(
            OnboardingFormDefinition::get(),
            $profile->custom_onboarding_answers ?? []
        );
    }

    private function xlsxDownload(string $filename, DesignedXlsxWorkbook $workbook): StreamedResponse
    {
        $contents = $workbook->toBinary();

        return response()->streamDownload(function () use ($contents): void {
            echo $contents;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function sheetRow(int $row, array $cells, ?int $height = null): array
    {
        return [
            'r' => $row,
            'height' => $height,
            'cells' => $cells,
        ];
    }

    private function cell(int $column, mixed $value, int $style = self::STYLE_VALUE): array
    {
        return [
            'col' => $column,
            'value' => $value,
            'style' => $style,
        ];
    }

    private function cellsFromValues(array $values, int|array $styles, int $startColumn = 1): array
    {
        return collect($values)
            ->map(function (mixed $value, int $index) use ($styles, $startColumn): array {
                $style = is_array($styles) ? ($styles[$index] ?? self::STYLE_VALUE) : $styles;

                return $this->cell($startColumn + $index, $value, $style);
            })
            ->all();
    }

    private function addProfileSection(array &$rows, array &$merges, int &$row, string $title): void
    {
        $rows[] = $this->sheetRow($row, [$this->cell(1, '  '.$title, self::STYLE_SECTION)], 20);
        $merges[] = 'A'.$row.':H'.$row;
        $row++;
    }

    private function addProfileField(array &$rows, array &$merges, int &$row, string $label, mixed $value): void
    {
        $rows[] = $this->sheetRow($row, [
            $this->cell(1, $label, self::STYLE_LABEL),
            $this->cell(2, $this->valueOrDash($value), $row % 2 === 0 ? self::STYLE_VALUE_ALT : self::STYLE_VALUE),
        ], 18);
        $merges[] = 'B'.$row.':H'.$row;
        $row++;
    }

    private function splitChecklist(array $checklist): array
    {
        $approved = [];
        $declined = [];

        foreach ($checklist as $label => $answer) {
            if (! is_string($label) || ! filled($label) || ! is_string($answer) || ! filled($answer)) {
                continue;
            }

            $item = [
                'label' => $label,
                'answer' => $answer,
            ];

            if ($answer === 'No') {
                $declined[] = $item;
            } else {
                $approved[] = $item;
            }
        }

        return [$approved, $declined];
    }

    private function checklistAnswerLabel(string $answer): string
    {
        return match ($answer) {
            'Sometimes' => "\u{26A0}\u{FE0F} SOMETIMES",
            'Yes' => "\u{2705} YES",
            default => $answer,
        };
    }

    private function applicationStatusLabel(?string $status, string $prefix = ''): string
    {
        return $prefix.match ($status) {
            ModelApplication::STATUS_APPROVED => "\u{2705} Approved",
            ModelApplication::STATUS_REJECTED => "\u{274C} Rejected",
            ModelApplication::STATUS_PENDING => "\u{26A0}\u{FE0F} Pending",
            default => $this->dash(),
        };
    }

    private function verificationStatusLabel(?ModelProfile $profile, string $prefix = ''): string
    {
        return $prefix.match ($profile?->verification_status) {
            ModelProfile::VERIFICATION_VERIFIED => "\u{2705} Verified",
            ModelProfile::VERIFICATION_SUBMITTED => "\u{26A0}\u{FE0F} Submitted",
            ModelProfile::VERIFICATION_REJECTED => "\u{274C} Needs resubmission",
            ModelProfile::VERIFICATION_REQUESTED => "\u{26A0}\u{FE0F} Requested",
            default => "\u{26A0}\u{FE0F} Not requested",
        };
    }

    private function statusStyle(?string $status): int
    {
        return match ($status) {
            ModelApplication::STATUS_APPROVED => self::STYLE_GOOD,
            ModelApplication::STATUS_REJECTED => self::STYLE_BAD,
            default => self::STYLE_WARNING,
        };
    }

    private function verificationStyle(?ModelProfile $profile): int
    {
        return match ($profile?->verification_status) {
            ModelProfile::VERIFICATION_VERIFIED => self::STYLE_GOOD,
            ModelProfile::VERIFICATION_REJECTED => self::STYLE_BAD,
            default => self::STYLE_WARNING,
        };
    }

    private function checkIcon(bool $value): string
    {
        return $value ? "\u{2705}" : "\u{274C}";
    }

    private function availabilitySummary(?ModelProfile $profile): string
    {
        $parts = collect([
            $profile?->weekly_availability,
            $profile?->availability_preference,
        ])->filter(fn (mixed $value): bool => filled($value))->values();

        if ($parts->isNotEmpty()) {
            return $parts->implode(' - ');
        }

        return $this->csvValue($profile?->availability);
    }

    private function payoutSummary(?ModelProfile $profile): string
    {
        $methods = $this->listValue($profile?->payout_methods);

        if (filled($profile?->payout_method_other)) {
            $methods = trim($methods.'; '.$profile->payout_method_other, '; ');
        }

        return $methods;
    }

    private function valueOrDash(mixed $value): string
    {
        $value = $this->csvValue($value);

        return $value !== '' ? $value : $this->dash();
    }

    private function dash(): string
    {
        return "\u{2014}";
    }

    private function icon(string $name): string
    {
        return match ($name) {
            'clipboard' => "\u{1F4CB}",
            'document' => "\u{1F4C4}",
            default => '',
        };
    }

    private function csvDownload(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $stream = fopen('php://output', 'w');

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, array_map(fn (mixed $value): string => $this->csvValue($value), $headers));

            foreach ($rows as $row) {
                fputcsv($stream, array_map(fn (mixed $value): string => $this->csvValue($value), $row));
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function applicationPhotoUrls(ModelApplication $application): string
    {
        return collect($application->photo_paths ?? [])
            ->keys()
            ->map(fn (int $index): string => route('admin.applications.photos.view', [$application, $index]))
            ->implode('; ');
    }

    private function listValue(mixed $value): string
    {
        if (! is_array($value)) {
            return $this->csvValue($value);
        }

        if (array_is_list($value)) {
            return collect($value)
                ->filter(fn (mixed $item): bool => filled($item))
                ->map(fn (mixed $item): string => $this->csvValue($item))
                ->implode('; ');
        }

        return collect($value)
            ->filter(fn (mixed $answer, mixed $label): bool => filled($label) || filled($answer))
            ->map(fn (mixed $answer, mixed $label): string => $this->csvValue($label).': '.$this->csvValue($answer))
            ->implode('; ');
    }

    private function csvValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $this->yesNo($value);
        }

        return $this->spreadsheetSafeValue(trim((string) $value));
    }

    private function spreadsheetSafeValue(string $value): string
    {
        return preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }

    private function dateTime(mixed $value): string
    {
        return $value ? $value->toDateTimeString() : '';
    }

    private function filenameSlug(string $value): string
    {
        $slug = str($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();

        return $slug !== '' ? $slug : 'member';
    }

    private function yesNo(?bool $value): string
    {
        return $value === null ? '' : ($value ? 'Yes' : 'No');
    }
}
