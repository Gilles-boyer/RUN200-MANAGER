<?php

namespace App\Console\Commands;

use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Application\Payments\UseCases\SendPaymentReceipt;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

class BackfillPaymentReceipts extends Command
{
    protected $signature = 'payment-receipts:backfill
        {--commit : Create missing receipts and send historical emails; without this option only inspect data}
        {--limit= : Maximum number of payments to inspect}
        {--after-id=0 : Resume strictly after this payment ID}';

    protected $description = 'Audit, then issue and send ordinary historical payment receipts';

    public function handle(IssuePaymentReceipt $issuer, SendPaymentReceipt $sender): int
    {
        $limitOption = $this->option('limit');
        if ($limitOption !== null && (! ctype_digit((string) $limitOption) || (int) $limitOption < 1)) {
            $this->error('L’option --limit doit être un entier positif.');

            return self::FAILURE;
        }

        $limit = $limitOption === null ? null : (int) $limitOption;
        $afterIdOption = $this->option('after-id');
        if (! ctype_digit((string) $afterIdOption)) {
            $this->error('L’option --after-id doit être un entier positif ou zéro.');

            return self::FAILURE;
        }
        $afterId = (int) $afterIdOption;
        $commit = (bool) $this->option('commit');
        $scanned = $existing = $ready = $created = $sent = $alreadySent = $invalid = $failed = $missingEmail = $held = 0;
        $lastId = $afterId;

        if ($commit && ! app()->environment('production', 'testing')) {
            $this->error('Envoi historique désactivé dans cet environnement. Utilisez la simulation.');

            return self::FAILURE;
        }

        Payment::query()
            ->where('id', '>', $afterId)
            ->whereIn('status', [
                PaymentStatus::PAID->value,
                PaymentStatus::PARTIALLY_REFUNDED->value,
                PaymentStatus::REFUNDED->value,
            ])
            ->with(['receipt', 'registration.race', 'registration.pilot.user'])
            ->orderBy('id')
            ->chunkById(100, function ($payments) use (
                $issuer, $sender, $limit, $commit, &$scanned, &$existing, &$ready,
                &$created, &$sent, &$alreadySent, &$invalid, &$failed, &$missingEmail, &$held, &$lastId
            ): bool {
                foreach ($payments as $payment) {
                    if ($limit !== null && $scanned >= $limit) {
                        return false;
                    }
                    $scanned++;
                    $lastId = $payment->id;

                    $receipt = $payment->receipt;
                    if ($receipt !== null && ! $receipt instanceof PaymentReceipt) {
                        throw new \LogicException('Unexpected receipt relation type.');
                    }
                    if ($receipt !== null) {
                        $existing++;
                    }

                    try {
                        if ($receipt === null) {
                            $issuer->snapshot($payment);
                        }
                        $ready++;

                        if ($receipt?->email_status === 'sent') {
                            $alreadySent++;

                            continue;
                        }

                        if ($receipt?->email_status === 'sending') {
                            $held++;
                            $this->warn('Paiement #'.$payment->id.' : envoi en cours, rapprochement manuel requis avant relance.');

                            continue;
                        }

                        $currentEmail = $payment->registration?->pilot?->user?->email;
                        if (! filter_var($currentEmail, FILTER_VALIDATE_EMAIL)) {
                            $missingEmail++;
                            $this->warn('Paiement #'.$payment->id.' : adresse actuelle du pilote absente ou invalide.');

                            // Do not freeze a known-bad address in a new receipt.
                            continue;
                        }
                        if ($receipt !== null && $receipt->payer_email !== $currentEmail) {
                            $this->warn('Paiement #'.$payment->id.' : adresse actuelle différente du reçu ; livraison à l’adresse actuelle.');
                        }

                        if ($commit) {
                            if ($receipt === null) {
                                $receipt = $issuer->issue($payment, historical: true);
                                $created++;
                            }
                            if ($sender->send($receipt, historical: true)) {
                                $sent++;
                            } else {
                                $failed++;
                                $this->warn('Paiement #'.$payment->id.' : envoi non confirmé.');
                            }
                        }
                    } catch (InvalidArgumentException $e) {
                        $invalid++;
                        $this->warn('Paiement #'.$payment->id.' ignoré : '.$e->getMessage());
                    } catch (Throwable $e) {
                        $invalid++;
                        $this->error('Paiement #'.$payment->id.' en échec ('.$e::class.').');
                    }
                }

                return true;
            });

        $this->info(sprintf(
            '%s : %d examiné(s), %d reçu(s) existant(s), %d donnée(s) valide(s), %d créé(s), %d email(s) envoyé(s), %d déjà envoyé(s), %d adresse(s) invalide(s), %d envoi(s) en cours, %d échec(s) envoi, %d anomalie(s).',
            $commit ? 'Émission et envoi' : 'Simulation',
            $scanned,
            $existing,
            $ready,
            $created,
            $sent,
            $alreadySent,
            $missingEmail,
            $held,
            $failed,
            $invalid
        ));
        $this->line('Dernier paiement examiné : #'.$lastId.' ; lot suivant avec --after-id='.$lastId.'.');
        if (($invalid + $failed + $held + $missingEmail) > 0) {
            $this->warn('Corrigez les anomalies puis relancez ce même lot avant de passer au curseur suivant.');
        }

        return ($invalid + $failed + $held + $missingEmail) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
