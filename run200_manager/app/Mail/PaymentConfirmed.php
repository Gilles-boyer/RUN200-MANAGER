<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé au pilote après paiement confirmé.
 */
class PaymentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {
        $this->payment->load(['registration.race', 'registration.pilot.user', 'registration.car']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Paiement confirmé - '.$this->payment->registration->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registrations.payment-confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
