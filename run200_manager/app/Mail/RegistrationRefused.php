<?php

namespace App\Mail;

use App\Models\RaceRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé au pilote si son inscription est refusée.
 */
class RegistrationRefused extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RaceRegistration $registration
    ) {
        $this->registration->load(['race', 'pilot.user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inscription refusée - '.$this->registration->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registrations.refused',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
