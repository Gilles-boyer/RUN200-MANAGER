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
 * Email sent to registered pilots when a race is cancelled.
 */
class RaceCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Race $race,
        public RaceRegistration $registration,
        public ?string $cancellationReason = null
    ) {
        $this->race->load('season');
        $this->registration->load(['pilot.user', 'car']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Course annulée - '.$this->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.races.cancelled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
