<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'un paiement échoue.
 */
class PaymentFailedException extends DomainException
{
    public function __construct(
        string $reason,
        ?string $transactionId = null,
        ?string $gateway = null,
        ?string $gatewayError = null
    ) {
        parent::__construct(
            message: "Le paiement a échoué : {$reason}".($gatewayError ? " ({$gatewayError})" : ''),
            errorCode: 'PAYMENT_FAILED',
            userMessageKey: 'exceptions.payment.failed',
            context: [
                'reason' => $reason,
                'transaction_id' => $transactionId,
                'gateway' => $gateway,
                'gateway_error' => $gatewayError,
            ]
        );
    }

    public static function withReason(string $reason): self
    {
        return new self($reason);
    }

    public static function fromGateway(string $gateway, string $gatewayError, ?string $transactionId = null): self
    {
        return new self(
            reason: 'Erreur passerelle de paiement',
            transactionId: $transactionId,
            gateway: $gateway,
            gatewayError: $gatewayError
        );
    }
}
