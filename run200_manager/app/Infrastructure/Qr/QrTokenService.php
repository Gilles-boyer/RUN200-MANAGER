<?php

namespace App\Infrastructure\Qr;

use App\Models\QrToken;
use App\Models\RaceRegistration;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class QrTokenService
{
    /**
     * Generate a new QR token for a registration
     * Returns the plain token (to be encoded in QR)
     */
    public function generate(RaceRegistration $registration, ?\DateTimeInterface $expiresAt = null): string
    {
        // Generate a random 64 character token
        $plainToken = Str::random(64);

        // Store the SHA256 hash in DB (never store plain token)
        $tokenHash = hash('sha256', $plainToken);

        // Delete existing token if any
        $registration->qrToken()->delete();

        // Create new token
        QrToken::create([
            'race_registration_id' => $registration->id,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return $plainToken;
    }

    /**
     * Validate a plain token and return the associated registration
     * Returns null if token is invalid or expired
     */
    public function validate(string $plainToken): ?RaceRegistration
    {
        $tokenHash = hash('sha256', $plainToken);

        /** @var QrToken|null $qrToken */
        $qrToken = QrToken::where('token_hash', $tokenHash)->first();

        if (! $qrToken) {
            return null;
        }

        if ($qrToken->isExpired()) {
            return null;
        }

        /** @var RaceRegistration|null */
        return $qrToken->registration;
    }

    /**
     * Generate QR code SVG for a token
     */
    public function generateQrCodeSvg(string $plainToken, int $size = 300): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($plainToken);
    }

    /**
     * Generate QR code as base64 data URI
     */
    public function generateQrCodeDataUri(string $plainToken, int $size = 300): string
    {
        $svg = $this->generateQrCodeSvg($plainToken, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Get or generate token for a registration
     */
    public function getOrGenerateToken(RaceRegistration $registration): string
    {
        // Check if token already exists
        /** @var QrToken|null $existingToken */
        $existingToken = $registration->qrToken;

        if ($existingToken && $existingToken->isValid()) {
            // We can't retrieve the plain token from hash
            // So we regenerate if needed for display
            // In production, you might store encrypted token or regenerate always
            return $this->generate($registration);
        }

        return $this->generate($registration);
    }

    /**
     * Revoke (delete) token for a registration
     */
    public function revoke(RaceRegistration $registration): bool
    {
        return (bool) $registration->qrToken()->delete();
    }

    /**
     * Check if a registration has a valid token
     */
    public function hasValidToken(RaceRegistration $registration): bool
    {
        /** @var QrToken|null $token */
        $token = $registration->qrToken;

        return $token !== null && $token->isValid();
    }
}
