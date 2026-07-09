<?php

namespace App\Services;

use App\Models\ModelApplication;
use App\Models\User;
use Illuminate\Support\Collection;

class ModelEmailSyncService
{
    /**
     * @return Collection<int, ModelApplication>
     */
    public function linkedApplications(User $user): Collection
    {
        if (! $user->isModel()) {
            return collect();
        }

        $profileApplicationId = $user->relationLoaded('modelProfile')
            ? $user->modelProfile?->model_application_id
            : $user->modelProfile()->value('model_application_id');

        return ModelApplication::query()
            ->where(function ($query) use ($user, $profileApplicationId): void {
                $query->where('user_id', $user->id);

                if ($profileApplicationId) {
                    $query->orWhere('id', $profileApplicationId);
                }
            })
            ->with('referral')
            ->get();
    }

    public function emailIsUsedByAnotherApplication(User $user, string $email): bool
    {
        if (! $user->isModel()) {
            return false;
        }

        $linkedApplicationIds = $this->linkedApplications($user)
            ->pluck('id')
            ->filter()
            ->values();

        return ModelApplication::query()
            ->where('email', $email)
            ->when($linkedApplicationIds->isNotEmpty(), function ($query) use ($linkedApplicationIds): void {
                $query->whereNotIn('id', $linkedApplicationIds->all());
            })
            ->exists();
    }

    public function emailIsUsedByAnotherStandaloneApplication(ModelApplication $application, string $email): bool
    {
        return ModelApplication::query()
            ->where('email', $email)
            ->whereKeyNot($application->id)
            ->exists();
    }

    public function syncLinkedApplications(User $user, string $email): void
    {
        foreach ($this->linkedApplications($user) as $application) {
            $application->forceFill(['email' => $email])->save();

            if ($application->referral) {
                $application->referral->forceFill(['candidate_email' => $email])->save();
            }
        }
    }
}
