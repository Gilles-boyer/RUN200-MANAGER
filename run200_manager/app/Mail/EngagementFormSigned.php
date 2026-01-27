<?php

namespace App\Mail;

use App\Models\EngagementForm;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé au pilote après signature de la feuille d'engagement.
 */
class EngagementFormSigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EngagementForm $engagementForm
    ) {
        $this->engagementForm->load(['registration.race', 'registration.pilot.user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Feuille d\'engagement signée - '.$this->engagementForm->registration->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registrations.engagement-signed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
