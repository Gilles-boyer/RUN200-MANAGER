<?php

namespace App\Mail;

use App\Models\Race;
use App\Models\RaceRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email sent 3 days before the race as a reminder.
 */
class RaceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Race $race,
        public RaceRegistration $registration
    ) {
        $this->race->load('season');
        $this->registration->load(['pilot.user', 'car']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📅 J-3 : Rappel course - '.$this->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.races.reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
