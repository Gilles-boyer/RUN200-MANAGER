<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Mail\PaymentConfirmed as PaymentConfirmedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmation implements ShouldQueue
{
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;

        if ($payment->registration && $payment->registration->pilot && $payment->registration->pilot->user) {
            Mail::to($payment->registration->pilot->user->email)
                ->send(new PaymentConfirmedMail($payment));
        }
    }
}
