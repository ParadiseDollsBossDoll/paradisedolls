<?php

namespace App\Mail;

use App\Models\ModelApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ModelApplication $application,
        public string $adminUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New Paradise Dolls application'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.application-submitted',
        );
    }
}
