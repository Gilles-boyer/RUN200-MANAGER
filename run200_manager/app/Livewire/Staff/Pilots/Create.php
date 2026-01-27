<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Pilots;

use App\Models\Pilot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
{
    // Pilot fields
    public string $first_name = '';

    public string $last_name = '';

    public string $birth_date = '';

    public string $birth_place = '';

    public string $license_number = '';

    public string $phone = '';

    public string $address = '';

    public string $city = '';

    public string $postal_code = '';

    public string $emergency_contact_name = '';

    public string $emergency_contact_phone = '';

    public string $medical_certificate_date = '';

    public string $notes = '';

    public string $permit_number = '';

    public string $permit_date = '';

    // User fields (optional)
    public bool $createUserAccount = false;

    public string $email = '';

    protected function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'birth_place' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:6', 'unique:pilots,license_number'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'medical_certificate_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'permit_number' => ['nullable', 'string', 'max:50'],
            'permit_date' => ['nullable', 'date'],
        ];

        if ($this->createUserAccount) {
            $rules['email'] = ['required', 'email', 'unique:users,email'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'birth_date.required' => 'La date de naissance est obligatoire.',
            'birth_place.required' => 'Le lieu de naissance est obligatoire.',
            'license_number.required' => 'Le numéro de licence est obligatoire.',
            'license_number.unique' => 'Ce numéro de licence est déjà utilisé.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire.',
            'email.required' => 'L\'email est obligatoire pour créer un compte.',
            'email.unique' => 'Cet email est déjà utilisé.',
        ];
    }

    public function generateLicenseNumber(): void
    {
        // Generate a unique 6-digit license number
        do {
            $number = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (Pilot::where('license_number', $number)->exists());

        $this->license_number = $number;
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $userId = null;

            // Create user account if requested
            if ($this->createUserAccount && $this->email) {
                $temporaryPassword = Str::random(12);

                $user = User::create([
                    'name' => $this->first_name.' '.$this->last_name,
                    'email' => $this->email,
                    'password' => Hash::make($temporaryPassword),
                ]);

                $user->assignRole('PILOTE');
                $userId = $user->id;

                // Store temp password in session for display
                session()->flash('temp_password', $temporaryPassword);
            }

            // Create pilot
            $pilot = Pilot::create([
                'user_id' => $userId,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'birth_date' => $this->birth_date,
                'birth_place' => $this->birth_place,
                'license_number' => $this->license_number,
                'phone' => $this->phone,
                'address' => $this->address,
                'city' => $this->city ?: null,
                'postal_code' => $this->postal_code ?: null,
                'emergency_contact_name' => $this->emergency_contact_name ?: null,
                'emergency_contact_phone' => $this->emergency_contact_phone ?: null,
                'medical_certificate_date' => $this->medical_certificate_date ?: null,
                'notes' => $this->notes ?: null,
                'is_minor' => $this->isMinor(),
                'permit_number' => $this->permit_number ?: null,
                'permit_date' => $this->permit_date ?: null,
            ]);

            activity()
                ->performedOn($pilot)
                ->causedBy(auth()->user())
                ->withProperties([
                    'pilot_name' => $pilot->full_name,
                    'license_number' => $pilot->license_number,
                    'created_by_staff' => true,
                ])
                ->log('Pilote créé par le staff');

            DB::commit();

            session()->flash('success', 'Pilote créé avec succès.');

            $this->redirect(route('staff.pilots.edit', $pilot), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création: '.$e->getMessage());
        }
    }

    private function isMinor(): bool
    {
        if (! $this->birth_date) {
            return false;
        }

        return \Carbon\Carbon::parse($this->birth_date)->age < 18;
    }

    public function render()
    {
        return view('livewire.staff.pilots.create')
            ->layout('layouts.app');
    }
}
