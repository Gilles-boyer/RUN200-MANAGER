<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Mail\PaymentConfirmed as PaymentConfirmedMail;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendPaymentReceipt
{
    public function send(PaymentReceipt $receipt, bool $historical = false): bool
    {
        // Production delivery must be explicit; tests use Mail::fake().
        if (! app()->environment('production', 'testing')) {
            return false;
        }

        $statuses = $historical ? ['not_requested', 'pending', 'failed'] : ['pending', 'failed'];
        $payment = Payment::query()->with('registration.pilot.user')->find($receipt->payment_id);
        $deliveryEmail = $payment?->registration?->pilot?->user?->email;
        if ($payment === null || ! filter_var($deliveryEmail, FILTER_VALIDATE_EMAIL)) {
            PaymentReceipt::query()->whereKey($receipt->id)
                ->whereIn('email_status', $statuses)
                ->update([
                    'email_status' => 'failed',
                    'email_last_error' => 'recipient_unavailable',
                    'delivery_email' => null,
                ]);

            return false;
        }

        $claimed = PaymentReceipt::query()->whereKey($receipt->id)
            ->whereIn('email_status', $statuses)
            ->update([
                'email_status' => 'sending',
                'email_last_error' => null,
                'delivery_email' => $deliveryEmail,
            ]);
        if ($claimed === 0) {
            return false;
        }

        $receipt->refresh();

        try {
            Mail::to($deliveryEmail)
                ->send(new PaymentConfirmedMail($payment, $receipt, $historical));
            $receipt->update(['email_status' => 'sent', 'email_sent_at' => now()]);

            return true;
        } catch (Throwable $e) {
            $receipt->update(['email_status' => 'failed', 'email_last_error' => class_basename($e)]);
            Log::error('Payment receipt email failed', [
                'receipt_id' => $receipt->id,
                'error_type' => $e::class,
            ]);

            return false;
        }
    }
}
