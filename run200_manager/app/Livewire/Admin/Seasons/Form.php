<?php

namespace App\Livewire\Admin\Seasons;

use App\Models\Season;
use Livewire\Component;

class Form extends Component
{
    public ?Season $season = null;

    public string $name = '';

    public string $start_date = '';

    public string $end_date = '';

    public bool $is_active = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom de la saison est obligatoire.',
        'start_date.required' => 'La date de début est obligatoire.',
        'end_date.required' => 'La date de fin est obligatoire.',
        'end_date.after' => 'La date de fin doit être après la date de début.',
    ];

    public function mount(?Season $season = null)
    {
        if ($season && $season->exists) {
            $this->season = $season;
            $this->name = $season->name;
            $this->start_date = $season->start_date->format('Y-m-d');
            $this->end_date = $season->end_date->format('Y-m-d');
            $this->is_active = $season->is_active;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->season) {
            $this->season->update($validated);
            session()->flash('success', 'Saison mise à jour avec succès.');
        } else {
            Season::create($validated);
            session()->flash('success', 'Saison créée avec succès.');
        }

        return $this->redirect(route('admin.seasons.index'));
    }

    public function render()
    {
        return view('livewire.admin.seasons.form', [
            'isEdit' => $this->season !== null && $this->season->exists,
        ])->layout('layouts.app');
    }
}
