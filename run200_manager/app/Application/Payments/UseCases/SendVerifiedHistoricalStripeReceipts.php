<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendVerifiedHistoricalStripeReceipts
{
    public function __construct(
        private VerifyHistoricalStripePayment $verifier,
        private IssuePaymentReceipt $issuer,
        private SendPaymentReceipt $sender,
    ) {}

    /** @return array{examined:int,sent:int,held:int,already_sent:int} */
    public function forUser(User $user): array
    {
        $result = ['examined' => 0, 'sent' => 0, 'held' => 0, 'already_sent' => 0];
        $user = $user->fresh();
        if ($user === null || $user->email_verified_at === null || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return $result;
        }

        // A campaign notification must have been accepted for this exact address.
        $request = DB::table('payment_receipt_verification_requests')
            ->where('user_id', $user->id)
            ->where('status', 'sent')
            ->where('email_hash', HistoricalReceiptEmailHash::of($user->email))
            ->first();
        if ($request === null || $request->sent_at === null) {
            return $result;
        }

        Payment::query()
            ->where('method', PaymentMethod::STRIPE->value)
            ->whereIn('status', [PaymentStatus::PAID->value, PaymentStatus::PARTIALLY_REFUNDED->value, PaymentStatus::REFUNDED->value])
            ->whereHas('registration.pilot', fn ($query) => $query->where('user_id', $user->id))
            ->with(['receipt', 'registration.pilot.user', 'registration.race'])
            ->orderBy('id')
            ->chunkById(50, function ($payments) use ($user, &$result): void {
                foreach ($payments as $payment) {
                    $result['examined']++;
                    if ($payment->receipt?->email_status === 'sent') {
                        $result['already_sent']++;

                        continue;
                    }
                    // SMTP outcome may be unknown; this state requires human reconciliation.
                    if ($payment->receipt?->email_status === 'sending') {
                        $result['held']++;

                        continue;
                    }

                    try {
                        $reason = $this->verifier->holdReason($payment);
                        if ($reason !== null) {
                            Log::warning('Historical Stripe receipt held', ['payment_id' => $payment->id, 'reason' => $reason]);
                            $result['held']++;

                            continue;
                        }

                        // Re-read under a lock after the remote call. A refund or reassignment
                        // arriving during Stripe verification must not produce a stale receipt.
                        $receipt = DB::transaction(function () use ($payment, $user): ?PaymentReceipt {
                            $locked = Payment::query()->with(['receipt', 'registration.pilot.user', 'registration.race'])
                                ->lockForUpdate()->findOrFail($payment->id);
                            if ($locked->status !== $payment->status
                                || $locked->amount_cents !== $payment->amount_cents
                                || $locked->refund_amount_cents !== $payment->refund_amount_cents
                                || $locked->currency !== $payment->currency
                                || $locked->stripe_payment_intent_id !== $payment->stripe_payment_intent_id
                                || $locked->race_registration_id !== $payment->race_registration_id
                                || $locked->user_id !== $payment->user_id
                                || $locked->user_id !== $user->id
                                || $locked->registration?->pilot_id !== $payment->registration?->pilot_id
                                || $locked->registration?->pilot?->user_id !== $payment->registration?->pilot?->user_id
                                || $locked->registration?->pilot?->user_id !== $user->id) {
                                return null;
                            }
                            $existing = $locked->receipt;
                            if ($existing instanceof PaymentReceipt) {
                                if ($existing->amount_cents !== $locked->amount_cents
                                    || $existing->payment_status_at_issue !== $locked->status->value
                                    || $existing->refund_amount_cents_at_issue !== (int) ($locked->refund_amount_cents ?? 0)) {
                                    return null;
                                }

                                return $existing;
                            }

                            return $this->issuer->issue($locked, historical: true);
                        });
                        if ($receipt === null) {
                            $result['held']++;

                            continue;
                        }
                        if ($this->sender->send($receipt, historical: true, requireVerifiedEmail: true, expectedEmail: $user->email)) {
                            $result['sent']++;
                        } else {
                            $result['held']++;
                        }
                    } catch (Throwable $e) {
                        Log::error('Historical Stripe receipt failed', ['payment_id' => $payment->id, 'error_type' => $e::class]);
                        $result['held']++;
                    }
                }
            });

        return $result;
    }
}
