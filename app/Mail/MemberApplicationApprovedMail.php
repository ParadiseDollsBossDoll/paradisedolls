<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberApplicationApprovedMail extends Mailable implements ShouldQueue
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
            subject: __('Amazing news — your Paradise Dolls application is approved!'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.member-application-approved',
        );
    }
}
