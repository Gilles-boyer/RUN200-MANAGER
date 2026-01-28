<?php

namespace App\Mail;

use App\Models\Race;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email sent to all active pilots when a race opens for registration.
 */
class RaceOpenedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Race $race,
        public string $pilotName
    ) {
        $this->race->load('season');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏁 Inscriptions ouvertes - '.$this->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.races.opened',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
