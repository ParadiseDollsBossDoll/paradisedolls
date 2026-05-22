<?php

namespace App\Mail;

use App\Models\CourseAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseAccessRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CourseAccessRequest $accessRequest,
        public string $adminUrl,
    ) {
        $this->accessRequest->loadMissing('course', 'proofFiles', 'user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Course access request: :course', [
                'course' => $this->accessRequest->course->title,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.course-access-requested',
        );
    }
}
