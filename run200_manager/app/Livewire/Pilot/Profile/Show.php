<?php

namespace App\Livewire\Pilot\Profile;

use Livewire\Component;

class Show extends Component
{
    public function mount()
    {
        $user = auth()->user();
        $pilot = $user?->pilot;

        if (! $pilot) {
            return redirect()->route('pilot.profile.edit')
                ->with('info', 'Veuillez créer votre profil pilote');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $pilot = $user->pilot;

        return view('livewire.pilot.profile.show', [
            'pilot' => $pilot,
        ])->layout('layouts.pilot');
    }
}
