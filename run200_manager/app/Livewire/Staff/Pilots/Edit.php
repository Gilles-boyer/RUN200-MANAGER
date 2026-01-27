<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Pilots;

use App\Models\Car;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    public Pilot $pilot;

    public string $first_name = '';

    public string $last_name = '';

    public string $birth_date = '';

    public string $license_number = '';

    public ?string $phone = '';

    public ?string $address = '';

    public ?string $city = '';

    public ?string $postal_code = '';

    public ?string $emergency_contact_name = '';

    public ?string $emergency_contact_phone = '';

    public ?string $medical_certificate_date = '';

    public ?string $notes = '';

    public ?string $permit_number = '';

    public ?string $permit_date = '';

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'medical_certificate_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'permit_number' => ['nullable', 'string', 'max:50'],
            'permit_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Get the pilot's cars with their categories eager-loaded.
     *
     * @return Collection<int, Car>
     */
    #[Computed]
    public function pilotCars(): Collection
    {
        return Car::with('category')
            ->where('pilot_id', $this->pilot->id)
            ->get();
    }

    public function mount(Pilot $pilot): void
    {
        $this->pilot = $pilot;
        $this->loadPilotData();
    }

    private function loadPilotData(): void
    {
        $this->first_name = $this->pilot->first_name ?? '';
        $this->last_name = $this->pilot->last_name ?? '';
        $this->birth_date = $this->pilot->birth_date?->format('Y-m-d') ?? '';
        $this->license_number = (string) ($this->pilot->license_number ?? '');
        $this->phone = $this->pilot->phone ?? '';
        $this->address = $this->pilot->address ?? '';
        $this->city = $this->pilot->city ?? '';
        $this->postal_code = $this->pilot->postal_code ?? '';
        $this->emergency_contact_name = $this->pilot->emergency_contact_name ?? '';
        $this->emergency_contact_phone = $this->pilot->emergency_contact_phone ?? '';
        $this->medical_certificate_date = $this->pilot->medical_certificate_date?->format('Y-m-d') ?? '';
        $this->notes = $this->pilot->notes ?? '';
        $this->permit_number = $this->pilot->permit_number ?? '';
        $this->permit_date = $this->pilot->permit_date?->format('Y-m-d') ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $originalData = $this->pilot->only([
            'first_name', 'last_name', 'birth_date', 'license_number',
            'phone', 'address', 'city', 'postal_code',
            'emergency_contact_name', 'emergency_contact_phone',
            'medical_certificate_date', 'notes', 'permit_number', 'permit_date',
        ]);

        $this->pilot->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birth_date' => $this->birth_date,
            'license_number' => $this->license_number ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'postal_code' => $this->postal_code ?: null,
            'emergency_contact_name' => $this->emergency_contact_name ?: null,
            'emergency_contact_phone' => $this->emergency_contact_phone ?: null,
            'medical_certificate_date' => $this->medical_certificate_date ?: null,
            'notes' => $this->notes ?: null,
            'permit_number' => $this->permit_number ?: null,
            'permit_date' => $this->permit_date ?: null,
        ]);

        activity()
            ->performedOn($this->pilot)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $originalData,
                'new' => $this->pilot->fresh()->only([
                    'first_name', 'last_name', 'birth_date', 'license_number',
                    'phone', 'address', 'city', 'postal_code',
                    'emergency_contact_name', 'emergency_contact_phone',
                    'medical_certificate_date', 'notes', 'permit_number', 'permit_date',
                ]),
            ])
            ->log('Pilote modifié par staff');

        session()->flash('success', 'Les informations du pilote ont été mises à jour.');

        $this->redirect(route('staff.pilots.index'), navigate: true);
    }

    public function render()
    {
        $registrations = RaceRegistration::with(['race.season', 'car.category', 'passages.checkpoint'])
            ->where('pilot_id', $this->pilot->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.staff.pilots.edit', [
            'registrations' => $registrations,
        ])->layout('layouts.app');
    }
}
