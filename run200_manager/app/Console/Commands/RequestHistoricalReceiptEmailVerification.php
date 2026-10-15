<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Payments\UseCases\HistoricalReceiptEmailHash;
use App\Application\Payments\UseCases\SendVerifiedHistoricalStripeReceipts;
use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RequestHistoricalReceiptEmailVerification extends Command
{
    protected $signature = 'payment-receipts:request-verification
        {--commit : Send one confirmation-only email per unverified account}
        {--limit= : Maximum number of accounts to inspect}
        {--after-user-id=0 : Resume strictly after this account ID}';

    protected $description = 'Preview or request email verification before historical Stripe receipt delivery';

    public function handle(SendVerifiedHistoricalStripeReceipts $historicalSender): int
    {
        $limitOption = $this->option('limit');
        $afterOption = $this->option('after-user-id');
        if (($limitOption !== null && (! ctype_digit((string) $limitOption) || (int) $limitOption < 1))
            || ! ctype_digit((string) $afterOption)) {
            $this->error('Les options --limit et --after-user-id doivent être des entiers valides.');

            return self::FAILURE;
        }
        $commit = (bool) $this->option('commit');
        if ($commit && ! app()->environment('production', 'testing')) {
            $this->error('La campagne ne peut être envoyée que depuis production.');

            return self::FAILURE;
        }

        $limit = $limitOption === null ? null : (int) $limitOption;
        $scanned = $eligible = $sent = $held = $skipped = 0;
        $lastId = (int) $afterOption;

        User::query()
            ->where('id', '>', $lastId)
            ->whereNull('email_verified_at')
            ->whereHas('pilot.raceRegistrations.payments', fn ($query) => $query
                ->where('method', PaymentMethod::STRIPE->value)
                ->whereIn('status', [PaymentStatus::PAID->value, PaymentStatus::PARTIALLY_REFUNDED->value, PaymentStatus::REFUNDED->value])
                ->whereNotNull('stripe_payment_intent_id'))
            ->orderBy('id')
            ->chunkById(50, function ($users) use ($commit, $limit, $historicalSender, &$scanned, &$eligible, &$sent, &$held, &$skipped, &$lastId): bool {
                foreach ($users as $user) {
                    if ($limit !== null && $scanned >= $limit) {
                        return false;
                    }
                    $scanned++;
                    $lastId = $user->id;
                    if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                        $held++;

                        continue;
                    }
                    $emailHash = HistoricalReceiptEmailHash::of($user->email);
                    if (DB::table('payment_receipt_verification_requests')->where('user_id', $user->id)->where('email_hash', $emailHash)->exists()) {
                        $skipped++;

                        continue;
                    }
                    $eligible++;
                    if (! $commit) {
                        continue;
                    }

                    // Persist before SMTP. A crash or uncertain acceptance never retries automatically.
                    try {
                        DB::table('payment_receipt_verification_requests')->insert([
                            'user_id' => $user->id,
                            'email_hash' => $emailHash,
                            'status' => 'sending',
                            'claimed_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (QueryException) {
                        $skipped++;

                        continue;
                    }

                    try {
                        // Laravel's signed verification URL contains no receipt or payment details.
                        $user->notify(new VerifyEmail);
                    } catch (Throwable $e) {
                        Log::error('Historical receipt verification delivery uncertain', ['user_id' => $user->id, 'error_type' => $e::class]);
                        $held++;

                        continue;
                    }

                    try {
                        DB::table('payment_receipt_verification_requests')->where('user_id', $user->id)
                            ->where('email_hash', $emailHash)
                            ->where('status', 'sending')->update(['status' => 'sent', 'sent_at' => now(), 'updated_at' => now()]);
                        $sent++;
                        // If the recipient verified between SMTP acceptance and this update,
                        // the Verified listener saw an unfinished campaign. Close that race.
                        $current = $user->fresh();
                        if ($current?->email === $user->email && $current->email_verified_at !== null) {
                            $historicalSender->forUser($current);
                        }
                    } catch (Throwable $e) {
                        Log::error('Historical receipt verification delivery state uncertain', ['user_id' => $user->id, 'error_type' => $e::class]);
                        $held++;
                    }
                }

                return true;
            });

        $this->info(sprintf('%s : %d comptes examinés, %d éligibles, %d notifications acceptées, %d à vérifier, %d déjà traités. Dernier compte : #%d.',
            $commit ? 'Campagne' : 'Simulation', $scanned, $eligible, $sent, $held, $skipped, $lastId));

        return $held > 0 ? self::FAILURE : self::SUCCESS;
    }
}
