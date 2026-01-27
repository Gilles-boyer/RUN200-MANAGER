<?php

namespace App\Livewire\Admin\Races;

use App\Models\Race;
use App\Models\Season;
use Livewire\Component;

class Form extends Component
{
    public ?Race $race = null;

    public ?int $season_id = null;

    public string $name = '';

    public string $race_date = '';

    public string $location = '';

    public string $status = 'DRAFT';

    public ?string $entry_fee = null; // En euros (ex: "50.00")

    protected function rules()
    {
        return [
            'season_id' => 'required|exists:seasons,id',
            'name' => 'required|string|max:255',
            'race_date' => 'required|date',
            'location' => 'required|string|max:255',
            'status' => 'required|in:DRAFT,OPEN,CLOSED,COMPLETED,CANCELLED',
            'entry_fee' => 'nullable|numeric|min:0|max:9999.99',
        ];
    }

    protected $messages = [
        'season_id.required' => 'La saison est obligatoire.',
        'name.required' => 'Le nom de la course est obligatoire.',
        'race_date.required' => 'La date de la course est obligatoire.',
        'location.required' => 'Le lieu est obligatoire.',
        'entry_fee.numeric' => 'Le prix d\'inscription doit être un nombre.',
        'entry_fee.min' => 'Le prix d\'inscription ne peut pas être négatif.',
    ];

    public function mount(?Race $race = null)
    {
        if ($race && $race->exists) {
            $this->race = $race;
            $this->season_id = $race->season_id;
            $this->name = $race->name;
            $this->race_date = $race->race_date->format('Y-m-d');
            $this->location = $race->location;
            $this->status = $race->status;
            // Convertir les centimes en euros pour l'affichage
            $this->entry_fee = $race->getRawOriginal('entry_fee_cents')
                ? number_format($race->getRawOriginal('entry_fee_cents') / 100, 2, '.', '')
                : null;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        // Convertir le prix en centimes
        $data = [
            'season_id' => $validated['season_id'],
            'name' => $validated['name'],
            'race_date' => $validated['race_date'],
            'location' => $validated['location'],
            'status' => $validated['status'],
            'entry_fee_cents' => $validated['entry_fee'] !== null && $validated['entry_fee'] !== ''
                ? (int) round((float) $validated['entry_fee'] * 100)
                : null,
        ];

        if ($this->race) {
            $this->race->update($data);
            session()->flash('success', 'Course mise à jour avec succès.');
        } else {
            Race::create($data);
            session()->flash('success', 'Course créée avec succès.');
        }

        return $this->redirect(route('admin.races.index'));
    }

    public function render()
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();

        return view('livewire.admin.races.form', [
            'seasons' => $seasons,
            'isEdit' => $this->race !== null && $this->race->exists,
        ])->layout('layouts.app');
    }
}
