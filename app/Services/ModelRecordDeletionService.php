<?php

namespace App\Services;

use App\Models\CommunityMessage;
use App\Models\EmailCampaignDelivery;
use App\Models\ModelApplication;
use App\Models\ModelReferral;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\CommunityPresence;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModelRecordDeletionService
{
    /**
     * @return array{deleted_member: bool, name: string}
     */
    public function deleteApplication(ModelApplication $application): array
    {
        $application->loadMissing(['user', 'profile.user', 'referral']);

        $member = $application->user ?: $application->profile?->user;

        if ($member) {
            return $this->deleteModel($member, $application);
        }

        $name = $application->name;

        DB::transaction(function () use ($application): void {
            $this->deleteStandaloneApplications(collect([$application]));
        });

        return [
            'deleted_member' => false,
            'name' => $name,
        ];
    }

    /**
     * @return array{deleted_member: bool, name: string}
     */
    public function deleteModel(User $user, ?ModelApplication $sourceApplication = null): array
    {
        if (! $user->isModel()) {
            throw new AuthorizationException('Only model accounts can be deleted from the member directory.');
        }

        $memberName = $user->name;

        DB::transaction(function () use ($user, $sourceApplication): void {
            $user->loadMissing([
                'modelProfile.application',
                'courseAccessRequests.proofFiles',
            ]);

            $applications = $this->linkedApplications($user, $sourceApplication);

            $this->deleteStoredFilesForModel($user, $applications);
            $this->deleteModelOwnedRows($user, $applications);
            $this->deleteStandaloneApplications($applications);

            $user->notifications()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            $user->delete();
        });

        CommunityPresence::forgetMemberDirectory();

        return [
            'deleted_member' => true,
            'name' => $memberName,
        ];
    }

    /**
     * @return Collection<int, ModelApplication>
     */
    private function linkedApplications(User $user, ?ModelApplication $sourceApplication): Collection
    {
        $applicationIds = collect([
            $sourceApplication?->id,
            $user->modelProfile?->model_application_id,
        ])->filter()->values();

        return ModelApplication::query()
            ->where(function ($query) use ($user, $applicationIds): void {
                $query->where('user_id', $user->id);

                if ($applicationIds->isNotEmpty()) {
                    $query->orWhereIn('id', $applicationIds->all());
                }
            })
            ->with('referral')
            ->get();
    }

    /**
     * @param  Collection<int, ModelApplication>  $applications
     */
    private function deleteStoredFilesForModel(User $user, Collection $applications): void
    {
        if (filled($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $profile = $user->modelProfile;
        if ($profile) {
            Storage::disk('local')->delete(array_filter([
                $profile->id_document_path,
                $profile->selfie_with_id_path,
                $profile->platform_codes_path,
            ]));
        }

        foreach ($user->courseAccessRequests as $accessRequest) {
            foreach ($accessRequest->proofFiles as $proofFile) {
                $this->deleteStoredFile($proofFile->disk, $proofFile->path);
            }
        }

        foreach ($applications as $application) {
            foreach ($application->photo_paths ?? [] as $path) {
                $this->deleteStoredFile('local', $path);
            }
        }

        $referrals = $this->linkedReferrals($user, $applications);
        foreach ($referrals as $referral) {
            foreach ($referral->photo_paths ?? [] as $path) {
                $this->deleteStoredFile('local', $path);
            }
        }

        Testimonial::query()
            ->where('submitted_by', $user->id)
            ->whereNotNull('image_path')
            ->get(['image_path'])
            ->each(fn (Testimonial $testimonial) => $this->deleteStoredFile('public', $testimonial->image_path));

        CommunityMessage::withTrashed()
            ->where('user_id', $user->id)
            ->whereNotNull('attachment')
            ->get(['attachment'])
            ->each(function (CommunityMessage $message): void {
                $attachment = is_array($message->attachment) ? $message->attachment : [];
                $this->deleteStoredFile($attachment['disk'] ?? 'local', $attachment['path'] ?? null);
            });

        Storage::disk('local')->deleteDirectory('course-access-proofs/'.$user->id);
        Storage::disk('local')->deleteDirectory('verifications/'.$user->id);
    }

    /**
     * @param  Collection<int, ModelApplication>  $applications
     */
    private function deleteModelOwnedRows(User $user, Collection $applications): void
    {
        $applicationIds = $applications->pluck('id')->all();

        $this->linkedReferrals($user, $applications)->each->delete();

        Testimonial::query()
            ->where('submitted_by', $user->id)
            ->delete();

        EmailCampaignDelivery::query()
            ->where('user_id', $user->id)
            ->delete();

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        DB::table('community_moderation_logs')
            ->where('actor_id', $user->id)
            ->orWhere('target_user_id', $user->id)
            ->delete();

        if ($applicationIds !== []) {
            DB::table('model_profiles')
                ->whereIn('model_application_id', $applicationIds)
                ->where('user_id', '!=', $user->id)
                ->update(['model_application_id' => null]);
        }
    }

    /**
     * @param  Collection<int, ModelApplication>  $applications
     * @return Collection<int, ModelReferral>
     */
    private function linkedReferrals(User $user, Collection $applications): Collection
    {
        $applicationIds = $applications->pluck('id')->filter()->values();

        return ModelReferral::query()
            ->where('referrer_id', $user->id)
            ->when($applicationIds->isNotEmpty(), function ($query) use ($applicationIds): void {
                $query->orWhereIn('model_application_id', $applicationIds->all());
            })
            ->get();
    }

    /**
     * @param  Collection<int, ModelApplication>  $applications
     */
    private function deleteStandaloneApplications(Collection $applications): void
    {
        foreach ($applications as $application) {
            $application->unsetRelation('referral');
            $application->load('referral');

            foreach ($application->photo_paths ?? [] as $path) {
                $this->deleteStoredFile('local', $path);
            }

            if ($application->referral) {
                foreach ($application->referral->photo_paths ?? [] as $path) {
                    $this->deleteStoredFile('local', $path);
                }

                $application->referral->delete();
            }

            $application->delete();
        }
    }

    private function deleteStoredFile(?string $disk, mixed $path): void
    {
        $disk = in_array($disk, ['local', 'public'], true) ? $disk : null;
        $path = trim(str_replace('\\', '/', (string) $path), '/');

        if ($disk === null || $path === '' || str_contains($path, "\0") || str_contains($path, '..')) {
            return;
        }

        Storage::disk($disk)->delete($path);
    }
}
