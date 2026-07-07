<?php

namespace App\Services;

use App\Jobs\SendEmailCampaignDelivery;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignDelivery;
use App\Models\EmailCampaignRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmailCampaignDispatcher
{
    public function dispatchDue(): int
    {
        $campaignIds = EmailCampaign::query()->due()->pluck('id');
        $dispatched = 0;

        foreach ($campaignIds as $campaignId) {
            if ($this->dispatch(EmailCampaign::query()->findOrFail($campaignId))) {
                $dispatched++;
            }
        }

        return $dispatched;
    }

    public function dispatch(EmailCampaign $campaign): ?EmailCampaignRun
    {
        $deliveryIds = [];

        $run = DB::transaction(function () use ($campaign, &$deliveryIds): ?EmailCampaignRun {
            $lockedCampaign = EmailCampaign::query()->lockForUpdate()->findOrFail($campaign->id);

            if (! in_array($lockedCampaign->status, [EmailCampaign::STATUS_SCHEDULED, EmailCampaign::STATUS_ACTIVE], true)
                || ! $lockedCampaign->next_send_at
                || $lockedCampaign->next_send_at->isFuture()) {
                return null;
            }

            $run = $lockedCampaign->runs()->create([
                'status' => EmailCampaignRun::STATUS_PROCESSING,
                'subject' => $lockedCampaign->subject,
                'body' => $lockedCampaign->body,
                'action_label' => $lockedCampaign->action_label,
                'action_url' => $lockedCampaign->action_url,
                'scheduled_for' => $lockedCampaign->next_send_at,
                'started_at' => now(),
            ]);

            $recipients = $this->recipientQuery($lockedCampaign)->get(['id', 'name', 'email']);

            foreach ($recipients as $recipient) {
                $delivery = $run->deliveries()->create([
                    'user_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'email' => $recipient->email,
                    'status' => EmailCampaignDelivery::STATUS_PENDING,
                ]);
                $deliveryIds[] = $delivery->id;
            }

            $run->update(['recipient_count' => $recipients->count()]);

            if ($recipients->isEmpty()) {
                $run->update([
                    'status' => EmailCampaignRun::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            }

            $lockedCampaign->update([
                'status' => $lockedCampaign->repeats()
                    ? EmailCampaign::STATUS_ACTIVE
                    : EmailCampaign::STATUS_COMPLETED,
                'next_send_at' => $lockedCampaign->repeats()
                    ? now()->addDays($lockedCampaign->repeat_every_days)
                    : null,
                'last_sent_at' => now(),
                'total_runs' => $lockedCampaign->total_runs + 1,
            ]);

            return $run;
        });

        foreach ($deliveryIds as $deliveryId) {
            SendEmailCampaignDelivery::dispatch($deliveryId);
        }

        return $run;
    }

    public function recipientQuery(EmailCampaign $campaign): Builder
    {
        return User::query()
            ->where('role', 'model')
            ->whereNotNull('email')
            ->whereNull('marketing_unsubscribed_at')
            ->when(
                $campaign->audience === EmailCampaign::AUDIENCE_ONBOARDED_MODELS,
                fn (Builder $query): Builder => $query->whereHas(
                    'modelProfile',
                    fn (Builder $profile): Builder => $profile->whereNotNull('community_role_assigned_at')
                )
            )
            ->orderBy('id');
    }
}
