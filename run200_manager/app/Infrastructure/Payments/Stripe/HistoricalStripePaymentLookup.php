<?php

declare(strict_types=1);

namespace App\Infrastructure\Payments\Stripe;

use Stripe\PaymentIntent;
use Stripe\StripeClient;

class HistoricalStripePaymentLookup
{
    public function retrieve(string $paymentIntentId): PaymentIntent
    {
        return (new StripeClient((string) config('stripe.secret')))
            ->paymentIntents->retrieve($paymentIntentId, ['expand' => ['latest_charge']]);
    }
}
