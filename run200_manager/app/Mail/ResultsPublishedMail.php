<?php

namespace App\Mail;

use App\Models\Race;
use App\Models\RaceResult;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email sent to participants when race results are published.
 */
class ResultsPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Race $race,
        public ?RaceResult $pilotResult = null
    ) {
        $this->race->load('season');
        $this->pilotResult?->load(['pilot.user', 'car']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏆 Résultats publiés - '.$this->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.races.results-published',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
