<?php

namespace App\Mail;

use App\Models\Pilot;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Welcome email sent when a pilot profile is created.
 */
class WelcomePilotMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pilot $pilot
    ) {
        $this->pilot->load('user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏎️ Bienvenue sur Run200 Manager !',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pilots.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
