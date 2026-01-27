<?php

namespace App\Livewire\Pilot\Registrations;

use App\Application\Payments\UseCases\CreateStripeCheckout;
use App\Models\RaceRegistration;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Checkout extends Component
{
    public RaceRegistration $registration;

    public string $errorMessage = '';

    public bool $isProcessing = false;

    public function mount(RaceRegistration $registration)
    {
        $this->registration = $registration;

        // Vérifier que l'inscription appartient au pilote connecté
        $pilot = auth()->user()->pilot;
        if (! $pilot || $registration->pilot_id !== $pilot->id) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette page.');
        }

        // Vérifier que l'inscription est en attente de paiement
        if (! $registration->isPendingPayment()) {
            if ($registration->isPaid()) {
                session()->flash('info', 'Cette inscription a déjà été payée.');

                return $this->redirect(route('pilot.registrations.index'));
            }

            session()->flash('error', 'Cette inscription n\'est pas en attente de paiement.');

            return $this->redirect(route('pilot.registrations.index'));
        }
    }

    #[Computed]
    public function race()
    {
        return $this->registration->race;
    }

    #[Computed]
    public function car()
    {
        return $this->registration->car;
    }

    #[Computed]
    public function pilot()
    {
        return $this->registration->pilot;
    }

    #[Computed]
    public function registrationFee()
    {
        // Le tarif peut être défini par course ou utiliser le tarif par défaut
        $feeCents = $this->race->entry_fee_cents ?? config('stripe.registration_fee_cents', 5000);

        return number_format($feeCents / 100, 2, ',', ' ').' €';
    }

    #[Computed]
    public function registrationFeeCents()
    {
        return $this->race->entry_fee_cents ?? config('stripe.registration_fee_cents', 5000);
    }

    public function proceedToPayment(CreateStripeCheckout $createStripeCheckout)
    {
        $this->errorMessage = '';
        $this->isProcessing = true;

        try {
            $result = $createStripeCheckout->execute(
                $this->registration,
                auth()->user(),
                $this->registrationFeeCents
            );

            // Rediriger vers Stripe Checkout
            return $this->redirect($result['checkout_url']);

        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->isProcessing = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Une erreur est survenue lors de la création du paiement. Veuillez réessayer.';
            $this->isProcessing = false;
            report($e);
        }
    }

    public function cancelRegistration()
    {
        $this->registration->cancel('Annulé par le pilote avant paiement');

        session()->flash('info', 'Votre inscription a été annulée.');

        return $this->redirect(route('pilot.races.index'));
    }

    public function render()
    {
        return view('livewire.pilot.registrations.checkout')
            ->layout('layouts.pilot');
    }
}
