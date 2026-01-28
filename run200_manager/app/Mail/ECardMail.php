<?php

namespace App\Mail;

use App\Infrastructure\Qr\QrTokenService;
use App\Models\Payment;
use App\Models\RaceRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-Card email with QR code sent after payment is confirmed.
 * This serves as the pilot's entry pass for the race.
 */
class ECardMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $qrCodeDataUri;

    public string $qrToken;

    public function __construct(
        public Payment $payment,
        public RaceRegistration $registration
    ) {
        $this->registration->load(['race.season', 'pilot.user', 'car.category']);

        // Generate QR code for the E-Card
        $qrService = new QrTokenService();
        $this->qrToken = $qrService->getOrGenerateToken($this->registration);
        $this->qrCodeDataUri = $qrService->generateQrCodeDataUri($this->qrToken, 250);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎫 Votre E-Carte d\'accès - '.$this->registration->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registrations.e-card',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
