<?php

namespace App\Mail;

use App\Models\RaceNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email personnalisé pour une notification de course.
 */
class CustomRaceNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RaceNotification $notification,
        public string $pilotName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-race-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
