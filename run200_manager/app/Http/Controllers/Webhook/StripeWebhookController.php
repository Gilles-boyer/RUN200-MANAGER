<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Application\Payments\UseCases\HandleStripeWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

/**
 * Controller for handling Stripe webhook events.
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private HandleStripeWebhook $handleWebhook
    ) {}

    /**
     * Handle incoming Stripe webhook.
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! $signature) {
            Log::warning('Stripe webhook missing signature header');

            return response('Missing signature', 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                config('stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);

            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook invalid signature', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
            'data_object_id' => $event->data->object->id ?? null,
        ]);

        // Check idempotency - prevent duplicate processing
        // But only for events that store the event_id (checkout.session.completed, payment_intent.succeeded)
        $eventTypesWithIdempotency = ['checkout.session.completed', 'payment_intent.succeeded'];

        if (in_array($event->type, $eventTypesWithIdempotency)) {
            if (\App\Models\Payment::where('stripe_event_id', $event->id)->exists()) {
                Log::info('Stripe webhook already processed', ['event_id' => $event->id]);

                return response('Event already processed', 200);
            }
        }

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleWebhook->handleCheckoutCompleted(
                    $event->data->object->toArray(),
                    $event->id
                ),
                'checkout.session.expired' => $this->handleWebhook->handleCheckoutExpired(
                    $event->data->object->toArray()
                ),
                'payment_intent.succeeded' => $this->handleWebhook->handlePaymentIntentSucceeded(
                    $event->data->object->toArray(),
                    $event->id
                ),
                'payment_intent.payment_failed' => $this->handleWebhook->handlePaymentFailed(
                    $event->data->object->toArray()
                ),
                'charge.refunded' => $this->handleWebhook->handleRefund(
                    $event->data->object->toArray()
                ),
                default => Log::info('Unhandled Stripe event type', ['type' => $event->type]),
            };
        } catch (\Exception $e) {
            Log::error('Stripe webhook handler error', [
                'type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 200 to prevent Stripe from retrying
            // (we've logged the error for manual investigation)
        }

        return response('Webhook handled', 200);
    }
}
