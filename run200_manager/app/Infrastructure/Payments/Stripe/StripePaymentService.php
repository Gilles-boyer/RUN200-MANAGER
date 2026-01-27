<?php

declare(strict_types=1);

namespace App\Infrastructure\Payments\Stripe;

use App\Models\Payment;
use App\Models\RaceRegistration;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Service for handling Stripe payments.
 */
final class StripePaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));

        // Désactiver la vérification SSL en mode développement local
        // ATTENTION : À NE JAMAIS UTILISER EN PRODUCTION
        if (config('app.env') === 'local' || config('stripe.test_mode')) {
            Stripe::setVerifySslCerts(false);
        }
    }

    /**
     * Create a Stripe Checkout session for a race registration.
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(
        RaceRegistration $registration,
        int $amountCents,
        string $currency = 'EUR',
        ?array $metadata = null
    ): Session {
        /** @var \App\Models\Pilot $pilot */
        $pilot = $registration->pilot;
        /** @var \App\Models\Race $race */
        $race = $registration->race;
        /** @var \App\Models\Car $car */
        $car = $registration->car;

        $lineItemDescription = sprintf(
            'Course: %s - Voiture #%s (%s %s)',
            $race->name,
            (string) $car->race_number,
            $car->make,
            $car->model
        );

        $successUrl = url(str_replace(
            '{registration_id}',
            (string) $registration->id,
            config('stripe.success_url')
        )).'?session_id={CHECKOUT_SESSION_ID}';

        $cancelUrl = url(str_replace(
            '{registration_id}',
            (string) $registration->id,
            config('stripe.cancel_url')
        ));

        /** @var \App\Models\User|null $user */
        $user = $pilot->user;

        return Session::create([
            'payment_method_types' => config('stripe.payment_methods', ['card']),
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => 'Inscription Run200',
                            'description' => $lineItemDescription,
                        ],
                        'unit_amount' => $amountCents,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $user?->email,
            'metadata' => array_merge([
                'registration_id' => (string) $registration->id,
                'race_id' => (string) $race->id,
                'race_name' => $race->name ?? '',
                'pilot_id' => (string) $pilot->id,
                'pilot_name' => $pilot->full_name ?? $pilot->first_name.' '.$pilot->last_name,
                'car_id' => (string) $car->id,
                'car_number' => (string) $car->race_number,
            ], $metadata ?? []),
            'locale' => 'fr',
            'expires_at' => now()->addHours(24)->timestamp,
        ]);
    }

    /**
     * Retrieve a checkout session by ID.
     *
     * @throws ApiErrorException
     */
    public function retrieveSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    /**
     * Retrieve a payment intent by ID.
     *
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Refund a payment.
     *
     * @throws ApiErrorException
     */
    public function refund(
        string $paymentIntentId,
        ?int $amountCents = null,
        ?string $reason = null
    ): Refund {
        $params = [
            'payment_intent' => $paymentIntentId,
        ];

        if ($amountCents !== null) {
            $params['amount'] = $amountCents;
        }

        if ($reason !== null) {
            $params['reason'] = $reason;
        }

        return Refund::create($params);
    }

    /**
     * Construct and validate a webhook event.
     *
     * @throws \UnexpectedValueException
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('stripe.webhook_secret')
        );
    }

    /**
     * Get the default registration fee in cents.
     */
    public function getDefaultFee(): int
    {
        return (int) config('stripe.registration_fee_cents', 5000);
    }

    /**
     * Get the default currency.
     */
    public function getDefaultCurrency(): string
    {
        return config('stripe.currency', 'EUR');
    }

    /**
     * Format amount from cents to display format.
     */
    public static function formatAmount(int $cents, string $currency = 'EUR'): string
    {
        $amount = $cents / 100;

        return number_format($amount, 2, ',', ' ').' '.strtoupper($currency);
    }

    /**
     * Convert amount from decimal to cents.
     */
    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert amount from cents to decimal.
     */
    public static function fromCents(int $cents): float
    {
        return $cents / 100;
    }
}
