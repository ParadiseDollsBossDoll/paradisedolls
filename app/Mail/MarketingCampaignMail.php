<?php

namespace App\Mail;

use App\Models\EmailCampaignRun;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $renderedSubject;

    public string $renderedBody;

    public function __construct(
        public EmailCampaignRun $campaignRun,
        public string $recipientName,
        public string $unsubscribeUrl,
    ) {
        $this->renderedSubject = str_replace('{name}', $recipientName, $campaignRun->subject);
        $this->renderedBody = str_replace('{name}', $recipientName, $campaignRun->body);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->renderedSubject);
    }

    public function content(): Content
    {
        return new Content(html: 'emails.marketing-campaign');
    }
}
