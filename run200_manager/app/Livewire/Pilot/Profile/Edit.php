<?php

namespace App\Livewire\Pilot\Profile;

use App\Models\Pilot;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $pilot;

    public $first_name;

    public $last_name;

    public $license_number;

    public $birth_date;

    public $birth_place;

    public $phone;

    public $permit_number;

    public $permit_date;

    public $address;

    public $city;

    public $postal_code;

    public $photo;

    public $is_minor = false;

    public $guardian_first_name;

    public $guardian_last_name;

    public $guardian_license_number;

    public $emergency_contact_name;

    public $emergency_contact_phone;

    public function mount()
    {
        $this->pilot = auth()->user()->pilot;

        if ($this->pilot) {
            $this->first_name = $this->pilot->first_name;
            $this->last_name = $this->pilot->last_name;
            $this->license_number = $this->pilot->license?->toString() ?? '';
            $this->birth_date = $this->pilot->birth_date?->format('Y-m-d');
            $this->birth_place = $this->pilot->birth_place;
            $this->phone = $this->pilot->phone;
            $this->permit_number = $this->pilot->permit_number;
            $this->permit_date = $this->pilot->permit_date?->format('Y-m-d');
            $this->address = $this->pilot->address;
            $this->city = $this->pilot->city;
            $this->postal_code = $this->pilot->postal_code;
            $this->is_minor = $this->pilot->is_minor;
            $this->guardian_first_name = $this->pilot->guardian_first_name;
            $this->guardian_last_name = $this->pilot->guardian_last_name;
            $this->guardian_license_number = $this->pilot->guardian_license_number;
            $this->emergency_contact_name = $this->pilot->emergency_contact_name;
            $this->emergency_contact_phone = $this->pilot->emergency_contact_phone;
        }
    }

    /**
     * Get the profile completion status for display.
     */
    public function getProfileCompletionProperty(): array
    {
        if (! $this->pilot) {
            return ['percentage' => 0, 'missing' => Pilot::requiredFields()];
        }

        return [
            'percentage' => $this->pilot->getProfileCompletionPercentage(),
            'missing' => $this->pilot->getMissingFields(),
        ];
    }

    public function save()
    {
        $validatedData = $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'license_number' => 'required|string|min:1|max:6|regex:/^[0-9]+$/',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'permit_number' => 'nullable|string|max:50',
            'permit_date' => 'nullable|date',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_minor' => 'boolean',
            'guardian_first_name' => 'nullable|required_if:is_minor,true|string|max:255',
            'guardian_last_name' => 'nullable|required_if:is_minor,true|string|max:255',
            'guardian_license_number' => 'nullable|string|min:1|max:6|regex:/^[0-9]+$/',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'license_number.required' => 'Le numéro de licence est obligatoire.',
            'license_number.regex' => 'Le numéro de licence doit contenir uniquement des chiffres.',
            'birth_date.required' => 'La date de naissance est obligatoire.',
            'birth_place.required' => 'Le lieu de naissance est obligatoire.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire.',
            'city.required' => 'La ville est obligatoire.',
            'postal_code.required' => 'Le code postal est obligatoire.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'La photo doit être au format jpg, jpeg, png ou webp.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'emergency_contact_name.required' => 'Le nom du contact d\'urgence est obligatoire.',
            'emergency_contact_phone.required' => 'Le téléphone du contact d\'urgence est obligatoire.',
        ]);

        // Valider licence unique
        $licenseExists = Pilot::where('license_number', $this->license_number)
            ->when($this->pilot, fn ($q) => $q->where('id', '!=', $this->pilot->id))
            ->exists();

        if ($licenseExists) {
            $this->addError('license_number', 'Ce numéro de licence est déjà utilisé.');

            return;
        }

        // Note: La photo est optionnelle à la création mais le profil sera marqué comme incomplet
        // jusqu'à ce qu'une photo soit uploadée

        // Upload photo
        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('pilots', 'public');
        }

        // Créer ou mettre à jour
        $pilotData = [
            'user_id' => auth()->id(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'license_number' => $this->license_number,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'phone' => $this->phone,
            'permit_number' => $this->permit_number,
            'permit_date' => $this->permit_date,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'is_minor' => $this->is_minor,
            'guardian_first_name' => $this->is_minor ? $this->guardian_first_name : null,
            'guardian_last_name' => $this->is_minor ? $this->guardian_last_name : null,
            'guardian_license_number' => $this->is_minor ? $this->guardian_license_number : null,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
        ];

        if ($photoPath) {
            $pilotData['photo_path'] = $photoPath;
        }

        if ($this->pilot) {
            $this->pilot->update($pilotData);
            $message = 'Profil mis à jour avec succès';
        } else {
            Pilot::create($pilotData);
            $message = 'Profil créé avec succès';
        }

        session()->flash('success', $message);

        return redirect()->route('pilot.profile.show');
    }

    public function render()
    {
        return view('livewire.pilot.profile.edit')->layout('layouts.pilot');
    }
}
