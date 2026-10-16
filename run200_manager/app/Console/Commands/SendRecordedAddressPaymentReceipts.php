<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Payments\UseCases\ReviewRecordedPaymentReceipt;
use App\Application\Payments\UseCases\SendPaymentReceipt;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

/** Explicit, reviewed batches addressed to the current pilot account email. */
final class SendRecordedAddressPaymentReceipts extends Command
{
    protected $signature = 'payment-receipts:send-recorded-addresses
        {--payment-ids= : Comma-separated payment IDs; 1 to 20 IDs required}
        {--commit : Issue and send after reviewing the same batch}
        {--review-hash= : Exact fingerprint printed by the dry run}';

    protected $description = 'Preview or send a reviewed batch of recorded paid payments';

    public function handle(ReviewRecordedPaymentReceipt $reviewer, SendPaymentReceipt $sender): int
    {
        $ids = $this->paymentIds((string) $this->option('payment-ids'));
        if ($ids === null) {
            $this->error('Indiquez entre 1 et 20 identifiants de paiement distincts et positifs avec --payment-ids.');

            return self::FAILURE;
        }

        $commit = (bool) $this->option('commit');
        $reviewHash = $this->option('review-hash');
        if ($commit && (! app()->environment('production', 'testing')
            || ! config('payment_receipts.auto_send', false))) {
            $this->error('Campagne désactivée dans cet environnement ou par le coupe-circuit des reçus.');

            return self::FAILURE;
        }
        if ($commit && (! is_string($reviewHash) || ! preg_match('/^[a-f0-9]{64}$/', $reviewHash))) {
            $this->error('Le --commit exige l’empreinte de la simulation dans --review-hash.');

            return self::FAILURE;
        }

        $key = config('app.key');
        if (! is_string($key) || $key === '') {
            $this->error('La clé de signature de la revue est indisponible.');

            return self::FAILURE;
        }

        $rows = [];
        $held = $alreadySent = $eligible = 0;
        foreach ($ids as $id) {
            try {
                $payment = Payment::query()
                    ->with(['registration.pilot.user', 'registration.race'])
                    ->find($id);
                $receipt = PaymentReceipt::query()->where('payment_id', $id)->first();
                $reason = match (true) {
                    $payment === null => 'payment_missing',
                    $receipt?->email_status === 'sent' => 'already_sent',
                    $receipt?->email_status === 'sending' => 'smtp_outcome_uncertain',
                    default => $reviewer->holdReason($payment),
                };
                $rows[] = $this->reviewRow($id, $payment, $receipt, $reason, $reviewer);
            } catch (Throwable $e) {
                $this->error('Paiement #'.$id.' : lecture ou contrôle impossible ('.$e::class.'). Aucun envoi lancé.');

                return self::FAILURE;
            }

            if ($reason === 'already_sent') {
                $alreadySent++;
            } elseif ($reason !== null) {
                $held++;
            } else {
                $eligible++;
            }
            $this->line('Paiement #'.$id.' : '.($reason ?? 'eligible'));
        }

        $fingerprint = hash_hmac(
            'sha256',
            json_encode(['recorded-address-receipts-v1', $rows], JSON_THROW_ON_ERROR),
            $key,
        );
        $this->info('Empreinte de revue : '.$fingerprint);
        $this->info(sprintf('Lot : %d paiement(s), %d éligible(s), %d déjà envoyé(s), %d retenu(s).', count($ids), $eligible, $alreadySent, $held));

        if (! $commit) {
            return $held > 0 ? self::FAILURE : self::SUCCESS;
        }
        if (! hash_equals($fingerprint, $reviewHash)) {
            $this->error('Le lot ou les dossiers ont changé depuis la simulation. Recommencez la revue.');

            return self::FAILURE;
        }
        if ($held > 0) {
            $this->error('Le lot contient un paiement retenu ; aucun reçu ni e-mail envoyé.');

            return self::FAILURE;
        }

        $sent = 0;
        foreach ($rows as $row) {
            if ($row['reason'] === 'already_sent') {
                continue;
            }
            $id = $row['id'];
            try {
                $payment = Payment::query()->with(['registration.pilot.user', 'registration.race'])->findOrFail($id);
                $recipient = $payment->registration?->pilot?->user;
                $expectedEmail = $row['email'];
                $expectedUserId = $row['pilot_user_id'];
                $expectedSnapshotFingerprint = $row['snapshot_fingerprint'];
                if ($recipient === null || ! is_string($expectedEmail) || ! is_int($expectedUserId)
                    || ! is_string($expectedSnapshotFingerprint)) {
                    throw new InvalidArgumentException('Destinataire manquant.');
                }

                $receipt = $reviewer->issue($payment, $expectedUserId, $expectedEmail, $expectedSnapshotFingerprint);
                if (! $sender->send(
                    $receipt,
                    historical: true,
                    expectedEmail: $expectedEmail,
                    expectedUserId: $expectedUserId,
                )) {
                    $this->error('Paiement #'.$id.' : envoi non confirmé ; examiner le statut SMTP avant toute relance.');

                    return self::FAILURE;
                }
                $sent++;
            } catch (Throwable $e) {
                $this->error('Paiement #'.$id.' retenu ou en échec ('.$e::class.'). Vérifiez le statut avant relance.');

                return self::FAILURE;
            }
        }

        $this->info(sprintf('%d e-mail(s) accepté(s) par le transport ; %d déjà envoyé(s).', $sent, $alreadySent));

        return self::SUCCESS;
    }

    /** @return list<int>|null */
    private function paymentIds(string $option): ?array
    {
        $parts = array_map('trim', explode(',', $option));
        if (count($parts) < 1 || count($parts) > 20) {
            return null;
        }

        $ids = [];
        foreach ($parts as $part) {
            $id = filter_var($part, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (! is_int($id) || in_array($id, $ids, true)) {
                return null;
            }
            $ids[] = $id;
        }
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    /** @return array<string, int|string|null> */
    private function reviewRow(
        int $id,
        ?Payment $payment,
        ?PaymentReceipt $receipt,
        ?string $reason,
        ReviewRecordedPaymentReceipt $reviewer,
    ): array {
        $pilot = $payment?->registration?->pilot;
        $user = $pilot?->user;

        return [
            'id' => $id,
            'reason' => $reason,
            'payment_id' => $payment?->id,
            'registration_id' => $payment?->race_registration_id,
            'pilot_id' => $pilot?->id,
            'pilot_user_id' => $user?->id,
            'email' => $user?->email,
            'method' => $payment?->method?->value,
            'status' => $payment?->status?->value,
            'amount' => $payment?->amount,
            'amount_cents' => $payment?->amount_cents,
            'currency' => $payment?->currency,
            'refund_amount_cents' => $payment?->refund_amount_cents,
            'paid_at' => $payment?->paid_at?->toIso8601String(),
            'stripe_payment_intent_id' => $payment?->stripe_payment_intent_id,
            'receipt_id' => $receipt?->id,
            'receipt_status' => $receipt?->email_status,
            'receipt_number' => $receipt?->number,
            'snapshot_fingerprint' => $payment !== null && $reason === null
                ? $reviewer->reviewFingerprint($payment)
                : ($receipt !== null ? $reviewer->receiptFingerprint($receipt) : null),
        ];
    }
}
