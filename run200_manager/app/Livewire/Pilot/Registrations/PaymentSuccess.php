<?php

namespace App\Livewire\Pilot\Registrations;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\RaceRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PaymentSuccess extends Component
{
    public RaceRegistration $registration;

    public ?string $sessionId = null;

    public function mount(RaceRegistration $registration): void
    {
        $pilot = Auth::user()->pilot;
        abort_unless($pilot && $registration->pilot_id === $pilot->id, 403);

        $this->registration = $registration->load(['race', 'car']);

        $sessionId = request()->query('session_id');
        $this->sessionId = is_string($sessionId) && $sessionId !== '' && strlen($sessionId) <= 255
            ? $sessionId
            : null;
    }

    public function render()
    {
        $this->registration->refresh()->load(['race', 'car']);

        $payment = $this->sessionId
            ? $this->registration->payments()
                ->where('method', PaymentMethod::STRIPE->value)
                ->where('stripe_session_id', $this->sessionId)
                ->first()
            : null;

        $paymentState = match (true) {
            $payment?->isPaid() === true => 'confirmed',
            $payment?->isPending() === true, $payment?->isProcessing() === true => 'pending',
            $payment instanceof Payment => 'not_confirmed',
            default => 'unknown',
        };

        return view('livewire.pilot.registrations.payment-success', [
            'payment' => $payment,
            'paymentState' => $paymentState,
            'canAccessEcard' => $paymentState === 'confirmed' && $this->registration->canAccessEcard(),
        ]);
    }
}
