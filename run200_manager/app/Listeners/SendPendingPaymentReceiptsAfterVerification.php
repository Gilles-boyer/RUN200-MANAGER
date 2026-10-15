<?php

namespace App\Listeners;

use App\Application\Payments\UseCases\SendPaymentReceipt;
use App\Application\Payments\UseCases\SendVerifiedHistoricalStripeReceipts;
use App\Models\PaymentReceipt;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendPendingPaymentReceiptsAfterVerification
{
    public function __construct(
        private SendPaymentReceipt $sender,
        private SendVerifiedHistoricalStripeReceipts $historical,
    ) {}

    public function handle(Verified $event): void
    {
        if (! config('payment_receipts.auto_send', false)) {
            return;
        }

        $user = $event->user;
        if (! $user instanceof User || ! $user->hasVerifiedEmail()) {
            return;
        }

        $email = $user->email;

        try {
            PaymentReceipt::query()
                ->where('email_status', 'pending')
                ->whereHas('payment.registration.pilot', fn ($query) => $query->where('user_id', $user->id))
                ->orderBy('id')
                ->chunkById(100, function ($receipts) use ($email): void {
                    foreach ($receipts as $receipt) {
                        $this->sender->send(
                            $receipt,
                            requireVerifiedEmail: true,
                            expectedEmail: $email,
                        );
                    }
                });
        } catch (Throwable $e) {
            Log::error('Pending payment receipts could not be resumed after email verification', [
                'user_id' => $user->id,
                'error_type' => $e::class,
            ]);
        }

        try {
            $this->historical->forVerifiedEvent($event);
        } catch (Throwable $e) {
            Log::error('Historical Stripe receipts could not be resumed after email verification', [
                'user_id' => $user->id,
                'error_type' => $e::class,
            ]);
        }
    }
}
