<?php

namespace App\Mail;

use App\Models\ModelProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ModelProfile $profile,
        public string $communityUrl,
        public string $roleName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Community access'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.community-access',
        );
    }
}
