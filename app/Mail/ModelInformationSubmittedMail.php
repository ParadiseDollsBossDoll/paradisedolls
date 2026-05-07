<?php

namespace App\Mail;

use App\Models\ModelProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ModelInformationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ModelProfile $profile,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Model information received'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.model-information-submitted',
        );
    }
}
