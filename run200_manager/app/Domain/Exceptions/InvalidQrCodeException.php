<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'un QR code est invalide ou expiré.
 */
class InvalidQrCodeException extends DomainException
{
    public function __construct(string $reason, ?string $token = null)
    {
        parent::__construct(
            message: "QR code invalide : {$reason}",
            errorCode: 'QR_CODE_INVALID',
            userMessageKey: 'exceptions.qrcode.invalid',
            context: [
                'reason' => $reason,
                'token' => $token ? substr($token, 0, 10).'...' : null, // Token tronqué pour sécurité
            ]
        );
    }

    public static function expired(): self
    {
        return new self('Le QR code a expiré');
    }

    public static function notFound(?string $token = null): self
    {
        return new self('QR code non reconnu', $token);
    }

    public static function alreadyUsed(string $checkpoint): self
    {
        return new self("Déjà scanné au checkpoint '{$checkpoint}'");
    }

    public static function invalidStatus(string $currentStatus, string $requiredStatus): self
    {
        return new self("Statut inscription invalide. Actuel: {$currentStatus}, requis: {$requiredStatus}");
    }
}
