<?php

namespace App\Livewire\Pilot\Registrations;

use App\Application\Payments\UseCases\CreateStripeCheckout;
use App\Application\Payments\UseCases\ReconcileStripeCheckout;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;
use App\Support\UserFacingError;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.pilot')]
class Payment extends Component
{
    public RaceRegistration $registration;

    public bool $isProcessing = false;

    public ?string $errorMessage = null;

    public ?string $noticeMessage = null;

    public bool $canRetryExpiredPayment = false;

    public function mount(RaceRegistration $registration): void
    {
        // Check authorization
        /** @var User $user */
        $user = Auth::user();
        /** @var Pilot|null $pilot */
        $pilot = $user->pilot;

        if (! $pilot || $registration->pilot_id !== $pilot->id) {
            abort(403);
        }

        // An already paid registration may still be awaiting organiser validation.
        if (! in_array($registration->status, ['PENDING_PAYMENT', 'ACCEPTED'], true) && ! $registration->isPaid()) {
            session()->flash('warning', 'Cette inscription ne peut plus être payée dans son état actuel. Consultez son statut dans Mes inscriptions.');

            $this->redirect(route('pilot.registrations.index'));

            return;
        }

        $this->registration = $registration->load(['race', 'car', 'pilot', 'payments']);
    }

    #[Computed]
    public function hasPaidPayment(): bool
    {
        return $this->registration->payments()->where('status', 'paid')->exists();
    }

    #[Computed]
    public function pendingPayment()
    {
        return $this->registration->payments()
            ->whereIn('status', ['pending', 'processing'])
            ->where('method', 'stripe')
            ->latest()
            ->first();
    }

    #[Computed]
    public function paidPayment()
    {
        return $this->registration->payments()->where('status', 'paid')->latest()->first();
    }

    #[Computed]
    public function registrationFee(): int
    {
        // Utiliser le prix de la course (inclut le prix par défaut si non défini)
        return $this->registration->race->entry_fee_cents;
    }

    #[Computed]
    public function formattedFee(): string
    {
        return $this->registration->race->formatted_entry_fee;
    }

    public function initiateStripePayment(): void
    {
        $registration = $this->ownedRegistration();

        if ($registration->payments()->where('status', 'paid')->exists()) {
            $this->errorMessage = 'Cette inscription a déjà été payée.';

            return;
        }

        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->noticeMessage = null;
        $this->canRetryExpiredPayment = false;

        try {
            $createCheckout = app(CreateStripeCheckout::class);

            $result = $createCheckout->execute(
                $registration,
                Auth::user(),
                $registration->race->entry_fee_cents,
                config('stripe.currency', 'EUR')
            );

            // Redirect to Stripe Checkout
            $this->redirect($result['checkout_url']);
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->isProcessing = false;
        } catch (\Exception $e) {
            $this->errorMessage = UserFacingError::message($e, 'Impossible de lancer le paiement.');
            $this->isProcessing = false;
        }
    }

    public function resumePayment(): void
    {
        $registration = $this->ownedRegistration();
        $this->errorMessage = null;
        $this->noticeMessage = null;
        $this->canRetryExpiredPayment = false;

        if ($registration->payments()->where('status', 'paid')->exists()) {
            $this->refreshRegistration();
            $this->noticeMessage = 'Votre paiement est enregistré.';

            return;
        }

        if (! in_array($registration->status, ['PENDING_PAYMENT', 'ACCEPTED'], true)) {
            $this->refreshRegistration();
            if ($this->hasPaidPayment) {
                $this->noticeMessage = 'Votre paiement est enregistré.';
            } else {
                $this->errorMessage = 'Cette inscription ne peut plus être payée dans son état actuel. Contactez l’organisation pour vérifier la suite.';
            }

            return;
        }

        $this->isProcessing = true;

        $pending = $registration->payments()
            ->whereIn('status', ['pending', 'processing'])
            ->where('method', 'stripe')
            ->latest()
            ->first();

        if (! $pending) {
            $this->refreshRegistration();
            if ($this->hasPaidPayment) {
                $this->noticeMessage = 'Votre paiement est enregistré.';
            } else {
                $this->errorMessage = 'Cette tentative n’est plus en attente. Vérifiez le statut affiché avant de réessayer.';
            }
            $this->isProcessing = false;

            return;
        }

        try {
            $result = app(ReconcileStripeCheckout::class)->execute($pending, $registration);

            if ($result['state'] === 'open') {
                if ($registration->payments()->where('status', 'paid')->exists()) {
                    $this->refreshRegistration();
                    $this->noticeMessage = 'Votre paiement est enregistré.';

                    return;
                }

                $this->redirect($result['url']);

                return;
            }

            $this->refreshRegistration();

            if ($result['state'] === 'expired') {
                $this->canRetryExpiredPayment = in_array($this->registration->status, ['PENDING_PAYMENT', 'ACCEPTED'], true);
                $this->noticeMessage = $this->canRetryExpiredPayment
                    ? 'Cette tentative a expiré sans paiement. Vous pouvez réessayer.'
                    : 'Cette tentative a expiré sans paiement. Contactez l’organisation pour la suite de votre inscription.';
            } elseif ($result['state'] === 'paid') {
                $this->noticeMessage = 'Votre paiement est enregistré.';
            } elseif ($result['state'] === 'confirmation_pending') {
                $this->noticeMessage = 'Stripe indique que ce paiement a été effectué. Sa confirmation dans RUN200 est en cours de vérification par l’organisation. Ne relancez pas le paiement.';
            } else {
                $this->errorMessage = 'Nous vérifions votre paiement. Réessayez plus tard ou contactez l’organisation. Ne lancez pas un second paiement.';
            }
        } catch (\Throwable $e) {
            $this->errorMessage = UserFacingError::message($e, 'Impossible de vérifier ce paiement pour le moment. Aucun nouveau paiement n’a été lancé.');
        } finally {
            $this->isProcessing = false;
        }
    }

    private function ownedRegistration(): RaceRegistration
    {
        $registration = RaceRegistration::findOrFail($this->registration->id);
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user?->pilot || $registration->pilot_id !== $user->pilot->id) {
            abort(403);
        }

        return $registration;
    }

    private function refreshRegistration(): void
    {
        $this->registration = RaceRegistration::with(['race', 'car', 'pilot', 'payments'])
            ->findOrFail($this->registration->id);
        unset($this->pendingPayment, $this->paidPayment, $this->hasPaidPayment);
    }

    public function render()
    {
        return view('livewire.pilot.registrations.payment');
    }
}
