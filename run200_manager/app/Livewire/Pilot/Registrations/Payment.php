<?php

namespace App\Livewire\Pilot\Registrations;

use App\Application\Payments\UseCases\CreateStripeCheckout;
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
            ->where('status', 'pending')
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
        if ($this->hasPaidPayment) {
            $this->errorMessage = 'Cette inscription a déjà été payée.';

            return;
        }

        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            $createCheckout = app(CreateStripeCheckout::class);

            $result = $createCheckout->execute(
                $this->registration,
                Auth::user(),
                $this->registrationFee,
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
        $pending = $this->pendingPayment;

        if (! $pending || ! isset($pending->metadata['session_url'])) {
            $this->errorMessage = 'La session de paiement n’est plus disponible. Consultez Mes inscriptions pour vérifier le statut avant de réessayer.';

            return;
        }

        $this->redirect($pending->metadata['session_url']);
    }

    public function render()
    {
        return view('livewire.pilot.registrations.payment');
    }
}
