<?php

namespace App\Application\Registrations\UseCases;

use App\Models\Car;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubmitRegistration
{
    /**
     * Execute the registration submission.
     *
     * @param  Race  $race  The race to register for
     * @param  Pilot  $pilot  The pilot registering
     * @param  Car  $car  The car to use
     * @param  bool  $requiresPayment  Whether online payment is required (true for pilot self-registration)
     */
    public function execute(Race $race, Pilot $pilot, Car $car, bool $requiresPayment = true): RaceRegistration
    {
        // Vérifier que la course est ouverte
        if (! $race->isOpen()) {
            throw new InvalidArgumentException('La course n\'est pas ouverte aux inscriptions.');
        }

        // Vérifier que la voiture appartient au pilote
        if ($car->pilot_id !== $pilot->id) {
            throw new InvalidArgumentException('Cette voiture ne vous appartient pas.');
        }

        // Note: Un pilote PEUT inscrire plusieurs de ses voitures sur la même course
        // Seule contrainte: une même voiture ne peut pas être inscrite deux fois

        // Vérifier que la voiture n'est pas déjà inscrite
        $existingCarRegistration = RaceRegistration::where('race_id', $race->id)
            ->where('car_id', $car->id)
            ->whereNotIn('status', ['CANCELLED', 'REFUSED'])
            ->exists();

        if ($existingCarRegistration) {
            throw new InvalidArgumentException('Cette voiture est déjà inscrite à cette course.');
        }

        return DB::transaction(function () use ($race, $pilot, $car, $requiresPayment) {
            // Statut initial selon le mode d'inscription
            // - PENDING_PAYMENT : inscription en ligne, nécessite paiement Stripe
            // - PENDING_VALIDATION : inscription sur circuit avec paiement manuel déjà reçu
            $status = $requiresPayment ? 'PENDING_PAYMENT' : 'PENDING_VALIDATION';

            $registration = RaceRegistration::create([
                'race_id' => $race->id,
                'pilot_id' => $pilot->id,
                'car_id' => $car->id,
                'status' => $status,
            ]);

            activity()
                ->performedOn($registration)
                ->causedBy(Auth::user())
                ->withProperties([
                    'race_id' => $race->id,
                    'pilot_id' => $pilot->id,
                    'car_id' => $car->id,
                    'requires_payment' => $requiresPayment,
                ])
                ->log('registration.created');

            // Dispatch event for email notification
            \App\Events\RegistrationCreated::dispatch($registration);

            return $registration;
        });
    }
}
