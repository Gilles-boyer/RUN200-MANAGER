<?php

namespace App\Livewire\Pilot\Registrations;

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

        $this->registration = $registration->load(['race', 'car', 'pilot', 'payments']);
        $this->sessionId = request()->query('session_id');
    }

    public function render()
    {
        $payment = $this->registration->payments()
            ->where('stripe_session_id', $this->sessionId)
            ->first();

        return view('livewire.pilot.registrations.payment-success', [
            'payment' => $payment,
        ]);
    }
}
