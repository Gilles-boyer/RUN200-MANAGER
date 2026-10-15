<?php

namespace App\Listeners;

use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Application\Payments\UseCases\SendPaymentReceipt;
use App\Events\PaymentConfirmed;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPaymentConfirmation
{
    public function __construct(private IssuePaymentReceipt $issuer, private SendPaymentReceipt $sender) {}

    public function handle(PaymentConfirmed $event): void
    {
        if (! config('payment_receipts.auto_send', false)) {
            return;
        }

        $payment = $event->payment;
        if (! $payment->isPaid()
            || ! ($payment->wasRecentlyCreated || $payment->wasChanged('status'))) {
            // This listener handles a fresh payment transition only. Historical
            // records are controlled by the separate Stripe verification flow.
            return;
        }

        try {
            $receipt = $this->issuer->issue($payment);
        } catch (Throwable $e) {
            Log::error('Payment receipt issuance failed', [
                'payment_id' => $payment->id,
                'error_type' => $e::class,
            ]);

            return;
        }

        $recipient = $payment->registration?->pilot?->user;
        if ($recipient?->email_verified_at === null) {
            // The PDF remains available to its owner, but no receipt is emailed yet.
            return;
        }

        $this->sender->send(
            $receipt,
            requireVerifiedEmail: true,
            expectedEmail: $recipient->email,
        );
    }
}
