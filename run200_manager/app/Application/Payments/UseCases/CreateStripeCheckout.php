<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Activitylog\Facades\Activity;

/**
 * Use case for creating a Stripe checkout session.
 */
final class CreateStripeCheckout
{
    public function __construct(
        private StripePaymentService $stripeService
    ) {}

    /**
     * Execute the use case.
     *
     * @return array{payment: Payment, checkout_url: string}
     *
     * @throws InvalidArgumentException
     */
    public function execute(
        RaceRegistration $registration,
        User $user,
        ?int $amountCents = null,
        ?string $currency = null
    ): array {
        $amountCents = $amountCents ?? $this->stripeService->getDefaultFee();
        $currency = $currency ?? $this->stripeService->getDefaultCurrency();

        return DB::transaction(function () use ($registration, $user, $amountCents, $currency) {
            $registration = RaceRegistration::query()->lockForUpdate()->findOrFail($registration->id);

            if (! in_array($registration->status, ['PENDING_PAYMENT', 'ACCEPTED'], true)) {
                throw new InvalidArgumentException(
                    'Le paiement ne peut être effectué que pour une inscription en attente de paiement ou acceptée.'
                );
            }

            if ($registration->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Cette inscription a déjà été payée.');
            }

            if ($registration->payments()->whereIn('status', ['pending', 'processing'])->where('method', 'stripe')->exists()) {
                throw new InvalidArgumentException('Un paiement est déjà en cours. Reprenez-le depuis votre inscription ou attendez sa confirmation.');
            }

            // Create Stripe checkout session
            $session = $this->stripeService->createCheckoutSession(
                $registration,
                $amountCents,
                $currency
            );

            // Create payment record
            $payment = Payment::create([
                'race_registration_id' => $registration->id,
                'user_id' => $user->id,
                'amount' => $amountCents / 100,
                'amount_cents' => $amountCents,
                'currency' => $currency,
                'method' => 'stripe',
                'status' => 'pending',
                'stripe_session_id' => $session->id,
                'metadata' => [
                    'session_url' => $session->url,
                    'expires_at' => $session->expires_at,
                ],
            ]);

            // Log activity
            Activity::performedOn($payment)
                ->causedBy($user)
                ->withProperties([
                    'registration_id' => $registration->id,
                    'amount_cents' => $amountCents,
                    'currency' => $currency,
                    'session_id' => $session->id,
                ])
                ->log('payment.stripe.checkout_created');

            return [
                'payment' => $payment,
                'checkout_url' => $session->url,
            ];
        });
    }
}
