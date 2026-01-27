<?php

namespace App\Mail;

use App\Models\RaceRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de rappel pour les vérifications techniques (VA/VT).
 */
class TechInspectionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RaceRegistration $registration
    ) {
        $this->registration->load(['race', 'pilot.user', 'car']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : Vérifications techniques samedi à 14h - '.$this->registration->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registrations.tech-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
