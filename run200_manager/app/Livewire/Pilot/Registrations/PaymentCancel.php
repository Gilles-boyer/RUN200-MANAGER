<?php

namespace App\Livewire\Pilot\Registrations;

use App\Models\RaceRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PaymentCancel extends Component
{
    public RaceRegistration $registration;

    public function mount(RaceRegistration $registration): void
    {
        $pilot = Auth::user()->pilot;
        abort_unless($pilot && $registration->pilot_id === $pilot->id, 403);

        $this->registration = $registration->load(['race', 'car', 'pilot']);
    }

    public function render()
    {
        return view('livewire.pilot.registrations.payment-cancel');
    }
}
