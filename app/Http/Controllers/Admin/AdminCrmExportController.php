<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelApplication;
use App\Models\ModelProfile;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCrmExportController extends Controller
{
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
                'modelProfile.application',
                'modelProfile.verificationReviewer:id,name,email',
            ])
            ->orderBy('name')
            ->get();

        return $this->csvDownload(
            'paradise-onboarding-forms-'.now()->format('Y-m-d').'.csv',
            $this->onboardingHeaders(),
            $members->map(fn (User $member): array => $this->onboardingRow($member))
        );
    }

    public function onboardingProfile(ModelProfile $profile): StreamedResponse
    {
        $profile->loadMissing([
            'user',
            'application',
            'verificationReviewer:id,name,email',
        ]);

        $member = $profile->user;

        return $this->csvDownload(
            'paradise-onboarding-'.$this->filenameSlug($member?->name ?: 'member').'-'.now()->format('Y-m-d').'.csv',
            $this->onboardingHeaders(),
            [$this->onboardingRow($member)]
        );
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
            'Model Vibe',
            'Anything Else',
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
            $profile?->model_vibe,
            $profile?->anything_else,
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

    private function csvDownload(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $stream = fopen('php://output', 'w');

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers);

            foreach ($rows as $row) {
                fputcsv($stream, array_map(fn (mixed $value): string => $this->csvValue($value), $row));
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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

        return trim((string) $value);
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
