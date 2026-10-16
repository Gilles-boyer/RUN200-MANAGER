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
    public function send(
        PaymentReceipt $receipt,
        bool $historical = false,
        bool $requireVerifiedEmail = false,
        ?string $expectedEmail = null,
        ?int $expectedUserId = null,
    ): bool {
        // Production delivery must be explicit; tests use Mail::fake().
        if (! app()->environment('production', 'testing')) {
            return false;
        }

        $statuses = $historical ? ['not_requested', 'pending', 'failed'] : ['pending', 'failed'];
        try {
            $payment = Payment::query()->with('registration.pilot.user')->find($receipt->payment_id);
        } catch (Throwable $e) {
            $this->logDeliveryFailure($receipt, $e, 'Payment receipt recipient lookup failed');

            return false;
        }
        $recipient = $payment?->registration?->pilot?->user;
        $deliveryEmail = $recipient?->email;
        if (! filter_var($deliveryEmail, FILTER_VALIDATE_EMAIL)) {
            $this->recordRecipientFailure($receipt, $statuses, 'recipient_unavailable');

            return false;
        }
        if (($expectedEmail !== null && $deliveryEmail !== $expectedEmail)
            || ($expectedUserId !== null && $recipient->id !== $expectedUserId)) {
            $this->recordRecipientFailure($receipt, $statuses, 'recipient_changed');

            return false;
        }
        if ($requireVerifiedEmail && $recipient->email_verified_at === null) {
            $this->recordRecipientFailure($receipt, $statuses, 'recipient_unverified_or_changed');

            return false;
        }

        try {
            $claimed = PaymentReceipt::query()->whereKey($receipt->id)
                ->whereIn('email_status', $statuses)
                ->update([
                    'email_status' => 'sending',
                    'email_last_error' => null,
                    'delivery_email' => $deliveryEmail,
                ]);
        } catch (Throwable $e) {
            $this->logDeliveryFailure($receipt, $e, 'Payment receipt claim failed before email');

            return false;
        }
        if ($claimed === 0) {
            return false;
        }

        try {
            $receipt->refresh();
            if ($requireVerifiedEmail || $expectedEmail !== null || $expectedUserId !== null) {
                // Re-read after claiming: the command's precheck and the first lookup may be stale.
                $payment = Payment::query()->with('registration.pilot.user')->find($receipt->payment_id);
                $recipient = $payment?->registration?->pilot?->user;
                if ($recipient === null
                    || $recipient->email !== $deliveryEmail
                    || ($expectedEmail !== null && $recipient->email !== $expectedEmail)
                    || ($expectedUserId !== null && $recipient->id !== $expectedUserId)
                    || ($requireVerifiedEmail && $recipient->email_verified_at === null)) {
                    $this->recordRecipientFailure($receipt, ['sending'], 'recipient_changed_or_unverified');

                    return false;
                }
            }
        } catch (Throwable $e) {
            // No SMTP call has started, but the claim may have committed. Keep it for review.
            $this->logDeliveryFailure($receipt, $e, 'Payment receipt final recipient check failed');

            return false;
        }

        try {
            Mail::to($deliveryEmail)
                ->send(new PaymentConfirmedMail($payment, $receipt, $historical));
        } catch (Throwable $e) {
            // SMTP acceptance may be unknown. Never turn this into an automatic retry.
            $this->logDeliveryFailure($receipt, $e, 'Payment receipt email outcome unknown; reconcile sending status');

            return false;
        }

        try {
            if (! $receipt->update(['email_status' => 'sent', 'email_sent_at' => now()])) {
                Log::error('Payment receipt sent status could not be recorded; reconcile sending status', [
                    'receipt_id' => $receipt->id,
                ]);

                return false;
            }
        } catch (Throwable $e) {
            $this->logDeliveryFailure($receipt, $e, 'Payment receipt sent status could not be recorded; reconcile sending status');

            return false;
        }

        return true;
    }

    /** @param array<int, string> $statuses */
    private function recordRecipientFailure(PaymentReceipt $receipt, array $statuses, string $reason): void
    {
        try {
            PaymentReceipt::query()->whereKey($receipt->id)
                ->whereIn('email_status', $statuses)
                ->update([
                    'email_status' => 'failed',
                    'email_last_error' => $reason,
                    'delivery_email' => null,
                ]);
        } catch (Throwable $e) {
            $this->logDeliveryFailure($receipt, $e, 'Payment receipt recipient failure could not be recorded');
        }
    }

    private function logDeliveryFailure(PaymentReceipt $receipt, Throwable $error, string $message): void
    {
        Log::error($message, [
            'receipt_id' => $receipt->id,
            'error_type' => $error::class,
        ]);
    }
}
