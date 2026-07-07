<?php

namespace App\Jobs;

use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaignDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendEmailCampaignDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = EmailCampaignDelivery::query()
            ->with(['run', 'user'])
            ->find($this->deliveryId);

        if (! $delivery || $delivery->status === EmailCampaignDelivery::STATUS_SENT) {
            return;
        }

        if (! $delivery->user || $delivery->user->marketing_unsubscribed_at) {
            $delivery->update([
                'status' => EmailCampaignDelivery::STATUS_SKIPPED,
                'failure_message' => null,
            ]);
            $delivery->run->refreshProgress();

            return;
        }

        $delivery->update([
            'status' => EmailCampaignDelivery::STATUS_PROCESSING,
            'failure_message' => null,
        ]);

        $unsubscribeUrl = URL::signedRoute('marketing.unsubscribe', ['user' => $delivery->user_id]);

        Mail::to($delivery->email, $delivery->recipient_name)->send(new MarketingCampaignMail(
            campaignRun: $delivery->run,
            recipientName: $delivery->recipient_name,
            unsubscribeUrl: $unsubscribeUrl,
        ));

        $delivery->update([
            'status' => EmailCampaignDelivery::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $delivery->run->refreshProgress();
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = EmailCampaignDelivery::query()->find($this->deliveryId);

        if (! $delivery || $delivery->status === EmailCampaignDelivery::STATUS_SENT) {
            return;
        }

        $delivery->update([
            'status' => EmailCampaignDelivery::STATUS_FAILED,
            'failure_message' => mb_substr($exception?->getMessage() ?? 'Delivery failed.', 0, 2000),
        ]);

        $delivery->run?->refreshProgress();
    }
}
