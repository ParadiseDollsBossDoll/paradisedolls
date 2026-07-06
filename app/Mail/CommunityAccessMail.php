<?php

namespace App\Mail;

use App\Models\ModelProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityAccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ModelProfile $profile,
        public string $communityUrl,
        public string $roleName,
        public string $whatsappCommunityUrl = 'https://chat.whatsapp.com/JEdgajsEUnuL1v4Hei9xn9?mode=gi_t',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Welcome to the Paradise Dolls Community!'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.community-access',
        );
    }
}
