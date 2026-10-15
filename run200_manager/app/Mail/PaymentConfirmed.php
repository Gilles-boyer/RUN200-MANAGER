<?php

namespace App\Mail;

use App\Infrastructure\Pdf\PaymentReceiptPdfService;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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
        public Payment $payment,
        public PaymentReceipt $receipt,
        public bool $historical = false
    ) {
        $this->payment->load(['registration.race', 'registration.pilot.user', 'registration.car']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ($this->historical ? 'Votre reçu de paiement – ' : 'Paiement confirmé - ').$this->receipt->race_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->historical
                ? 'emails.registrations.payment-receipt-historical'
                : 'emails.registrations.payment-confirmed',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn (): string => (new PaymentReceiptPdfService)->bytes($this->receipt),
                'recu-'.$this->receipt->number.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
