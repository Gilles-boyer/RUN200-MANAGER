<?php

namespace App\Livewire\Pilot\Registrations;

use App\Domain\Registration\Enums\RegistrationStatus;
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

            if ($this->statusFilter && RegistrationStatus::tryFrom($this->statusFilter)) {
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
        return RegistrationStatus::tryFrom($status)?->label() ?? $status;
    }

    public function getStatusColor(string $status): string
    {
        return RegistrationStatus::tryFrom($status)?->badgeColor() ?? 'gray';
    }
}
