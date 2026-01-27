<?php

namespace App\Livewire\Pilot\Registrations;

use App\Models\RaceRegistration;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    protected $queryString = ['statusFilter'];

    public function render()
    {
        $user = auth()->user();
        $pilot = $user->pilot;

        $registrations = collect();

        if ($pilot) {
            $query = RaceRegistration::with(['race.season', 'car', 'payments', 'paddockSpot'])
                ->where('pilot_id', $pilot->id);

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            $registrations = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('livewire.pilot.registrations.index', [
            'registrations' => $registrations,
            'pilot' => $pilot,
        ])->layout('layouts.pilot');
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'PENDING_PAYMENT' => 'En attente de paiement',
            'PENDING_VALIDATION' => 'En attente de validation',
            'ACCEPTED' => 'Acceptée',
            'REFUSED' => 'Refusée',
            'CANCELLED' => 'Annulée',
            'TECH_CHECKED_OK' => 'Contrôle technique OK',
            'TECH_CHECKED_FAIL' => 'Contrôle technique refusé',
            'RACE_READY' => 'Prêt à courir',
            default => $status,
        };
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'PENDING_PAYMENT' => 'orange',
            'PENDING_VALIDATION' => 'yellow',
            'ACCEPTED' => 'blue',
            'REFUSED' => 'red',
            'CANCELLED' => 'gray',
            'TECH_CHECKED_OK' => 'green',
            'TECH_CHECKED_FAIL' => 'red',
            'RACE_READY' => 'green',
            default => 'gray',
        };
    }
}
