<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberApplicationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $memberName,
        public string $temporaryPassword,
        public string $loginUrl,
        public string $onboardingUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Application approval'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.member-application-approved',
        );
    }
}
