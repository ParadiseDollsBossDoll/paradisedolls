<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatterInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $chatter, public string $setupUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Your Paradise Dolls chatter workspace is ready'));
    }

    public function content(): Content
    {
        return new Content(html: 'emails.chatter-invitation');
    }
}
