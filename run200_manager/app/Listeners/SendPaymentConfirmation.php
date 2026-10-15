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
        $payment = $event->payment;

        try {
            $receipt = $this->issuer->issue($payment);
        } catch (Throwable $e) {
            Log::error('Payment receipt issuance failed', [
                'payment_id' => $payment->id,
                'error_type' => $e::class,
            ]);

            return;
        }

        $this->sender->send($receipt);
    }
}
