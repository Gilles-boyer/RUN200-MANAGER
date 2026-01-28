<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Mail\ECardMail;
use App\Models\RaceRegistration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Sends E-Card email with QR code after payment confirmation.
 */
class SendECardAfterPayment implements ShouldQueue
{
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;

        /** @var RaceRegistration|null $registration */
        $registration = $payment->registration;

        if ($registration && $registration->pilot && $registration->pilot->user) {
            Mail::to($registration->pilot->user->email)
                ->send(new ECardMail($payment, $registration));
        }
    }
}
