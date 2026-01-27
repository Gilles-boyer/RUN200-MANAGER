<?php

namespace App\Mail;

use App\Models\TechInspection;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé au pilote après validation technique.
 */
class TechInspectionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TechInspection $techInspection
    ) {
        $this->techInspection->load(['registration.race', 'registration.pilot.user', 'registration.car']);
    }

    public function envelope(): Envelope
    {
        $status = $this->techInspection->status === 'OK' ? 'validé' : 'refusé';

        /** @var \App\Models\RaceRegistration $registration */
        $registration = $this->techInspection->registration;
        /** @var \App\Models\Race $race */
        $race = $registration->race;

        return new Envelope(
            subject: 'Contrôle technique '.$status.' - '.$race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registrations.tech-inspection-completed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
