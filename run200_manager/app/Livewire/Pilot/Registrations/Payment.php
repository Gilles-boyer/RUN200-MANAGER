<?php

namespace App\Livewire\Pilot\Registrations;

use App\Application\Payments\UseCases\CreateStripeCheckout;
use App\Models\RaceRegistration;
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        /** @var \App\Models\Pilot|null $pilot */
        $pilot = $user->pilot;

        if (! $pilot || $registration->pilot_id !== $pilot->id) {
            abort(403);
        }

        // Check registration status - allow PENDING_PAYMENT (new) or ACCEPTED (for additional payments)
        if (! in_array($registration->status, ['PENDING_PAYMENT', 'ACCEPTED'])) {
            abort(403, 'Cette inscription n\'est pas en attente de paiement ou acceptée.');
        }

        $this->registration = $registration->load(['race', 'car', 'pilot', 'payments']);
    }

    #[Computed]
    public function hasPaidPayment(): bool
    {
        return $this->registration->payments->where('status', 'paid')->isNotEmpty();
    }

    #[Computed]
    public function pendingPayment()
    {
        return $this->registration->payments
            ->where('status', 'pending')
            ->where('method', 'stripe')
            ->first();
    }

    #[Computed]
    public function paidPayment()
    {
        return $this->registration->payments->where('status', 'paid')->first();
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
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->isProcessing = false;
        }
    }

    public function resumePayment(): void
    {
        $pending = $this->pendingPayment;

        if (! $pending || ! isset($pending->metadata['session_url'])) {
            $this->errorMessage = 'Impossible de reprendre le paiement.';

            return;
        }

        $this->redirect($pending->metadata['session_url']);
    }

    public function render()
    {
        return view('livewire.pilot.registrations.payment');
    }
}
