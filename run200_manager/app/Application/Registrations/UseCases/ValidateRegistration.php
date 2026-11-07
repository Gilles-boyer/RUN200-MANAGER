<?php

namespace App\Application\Registrations\UseCases;

use App\Events\RegistrationAccepted;
use App\Events\RegistrationRefused;
use App\Infrastructure\Qr\QrTokenService;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ValidateRegistration
{
    /**
     * Valider (accepter ou refuser) une inscription
     *
     * @param  string  $status  'ACCEPTED' ou 'REFUSED'
     * @param  string|null  $reason  Raison obligatoire si refus
     * @param  User|null  $user  L'utilisateur qui effectue la validation
     */
    public function execute(
        RaceRegistration $registration,
        string $status,
        ?string $reason = null,
        ?User $user = null,
        bool $allowPendingPaymentAcceptance = false
    ): RaceRegistration {
        // Vérifier que le statut est valide
        if (! in_array($status, ['ACCEPTED', 'REFUSED'])) {
            throw new InvalidArgumentException('Le statut doit être ACCEPTED ou REFUSED.');
        }

        if ($status === 'REFUSED') {
            if ($allowPendingPaymentAcceptance) {
                throw new InvalidArgumentException('L’exception d’acceptation avant paiement ne peut pas servir à refuser.');
            }

            return $this->refuse($registration, $reason ?? '', $user);
        }

        $validator = $user ?? Auth::user();

        return DB::transaction(function () use ($registration, $status, $reason, $validator, $allowPendingPaymentAcceptance) {
            $registration = RaceRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            $previousStatus = $registration->status;
            $acceptingBeforePayment = $status === 'ACCEPTED'
                && $allowPendingPaymentAcceptance
                && $registration->isPendingPayment();

            if (! $registration->isPending() && ! $acceptingBeforePayment) {
                throw new InvalidArgumentException('Seules les inscriptions en attente peuvent être validées');
            }

            $paymentConfirmed = $registration->isPaid();

            $registration->update([
                'status' => $status,
                'reason' => $reason,
                'validated_at' => now(),
                'validated_by' => $validator?->id,
            ]);

            activity()
                ->performedOn($registration)
                ->causedBy($validator)
                ->withProperties([
                    'status' => $status,
                    'reason' => $reason,
                    'previous_status' => $previousStatus,
                    'payment_confirmed_at_validation' => $paymentConfirmed,
                    'accepted_before_payment' => $acceptingBeforePayment && ! $paymentConfirmed,
                ])
                ->log($status === 'ACCEPTED' ? 'registration.accepted' : 'registration.refused');

            // Dispatch events for email notifications
            RegistrationAccepted::dispatch($registration);

            return $registration->fresh();
        });
    }

    /**
     * Accepter une inscription
     */
    public function accept(RaceRegistration $registration, ?User $user = null): RaceRegistration
    {
        return $this->execute($registration, 'ACCEPTED', null, $user);
    }

    /** Accept an unpaid registration by an explicit staff decision. */
    public function acceptBeforePayment(RaceRegistration $registration, ?User $user = null): RaceRegistration
    {
        return $this->execute($registration, 'ACCEPTED', null, $user, true);
    }

    /**
     * Refuser une inscription avec raison
     */
    public function refuse(RaceRegistration $registration, string $reason, ?User $user = null, ?array $expected = null): RaceRegistration
    {
        Gate::authorize('race_registration.validate');

        $reason = trim($reason);
        if (mb_strlen($reason) < 10) {
            throw new InvalidArgumentException('Indiquez un motif de refus d’au moins 10 caractères.');
        }

        $validator = $user ?? Auth::user();
        if ($validator?->id !== Auth::id()) {
            throw new InvalidArgumentException('L’agent de validation ne correspond pas à la session active.');
        }

        return DB::transaction(function () use ($registration, $reason, $validator, $expected) {
            $locked = RaceRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            if ($expected !== null) {
                foreach (['status', 'pilot_id', 'race_id', 'car_id'] as $field) {
                    if (array_key_exists($field, $expected) && (string) $locked->{$field} !== (string) $expected[$field]) {
                        throw new InvalidArgumentException('Le dossier a changé. Actualisez la liste avant de confirmer.');
                    }
                }
            }

            if (! in_array($locked->status, [
                'PENDING_VALIDATION', 'PENDING_PAYMENT', 'ACCEPTED',
                'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'TECH_CHECKED_FAIL',
            ], true)) {
                throw new InvalidArgumentException('Cette inscription ne peut plus être refusée à cette étape.');
            }
            if ($locked->passages()->whereHas('checkpoint', fn ($query) => $query->whereIn('code', ['ENTRY', 'BRACELET']))->exists()
                || RaceResult::query()->where('race_registration_id', $locked->id)->exists()) {
                throw new InvalidArgumentException('Une entrée ou un résultat existe déjà pour ce dossier. La correction doit être examinée par un administrateur.');
            }

            $previousStatus = $locked->status;
            $previousPaddock = $locked->paddock;
            $previousSpotId = $locked->paddock_spot_id;
            $locked->update([
                'status' => 'REFUSED',
                'reason' => $reason,
                'validated_at' => now(),
                'validated_by' => $validator?->id,
                'paddock' => null,
                'paddock_spot_id' => null,
            ]);
            app(QrTokenService::class)->revoke($locked);

            $refusalActivity = activity()->performedOn($locked)->causedBy($validator)
                ->withProperties([
                    'previous_status' => $previousStatus,
                    'new_status' => 'REFUSED',
                    'reason' => $reason,
                    'previous_paddock' => $previousPaddock,
                    'previous_paddock_spot_id' => $previousSpotId,
                    'payment_unchanged' => true,
                ])->log('registration.refused');

            DB::afterCommit(fn () => RegistrationRefused::dispatch($locked->fresh(), $refusalActivity->id));

            return $locked->fresh();
        });
    }
}
