<?php

namespace App\Livewire\Admin;

use App\Models\Car;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function activeSeason(): ?Season
    {
        return Season::active()->latest()->first();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total_users' => User::count(),
            'total_pilots' => Pilot::count(),
            'total_seasons' => Season::count(),
            'total_races' => Race::count(),
            'pending_registrations' => RaceRegistration::where('status', 'PENDING_VALIDATION')->count(),
            'open_races' => Race::where('status', 'OPEN')->count(),
            'total_cars' => Car::count(),
            'total_registrations' => RaceRegistration::count(),
        ];
    }

    #[Computed]
    public function registrationsEvolution(): array
    {
        // Évolution des inscriptions sur les 6 derniers mois
        // Approche agnostique de la DB (compatible SQLite et MySQL)
        $registrations = RaceRegistration::where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn ($reg) => $reg->created_at->format('Y-m'))
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        return [
            'labels' => $registrations->keys()->map(fn ($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y'))->toArray(),
            'data' => $registrations->values()->toArray(),
        ];
    }

    #[Computed]
    public function registrationsByStatus(): array
    {
        $data = RaceRegistration::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $statusLabels = [
            'PENDING_VALIDATION' => 'En attente',
            'ACCEPTED' => 'Acceptées',
            'REFUSED' => 'Refusées',
            'ADMIN_CHECKED' => 'Vérifiées',
            'TECH_CHECKED_OK' => 'Tech OK',
            'TECH_CHECKED_FAIL' => 'Tech Fail',
            'ENTRY_SCANNED' => 'Entrées',
            'BRACELET_GIVEN' => 'Bracelets',
            'PUBLISHED' => 'Publiées',
        ];

        return [
            'labels' => $data->pluck('status')->map(fn ($s) => $statusLabels[$s] ?? $s)->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    #[Computed]
    public function carsByCategory(): array
    {
        $data = Car::select('car_category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('car_category_id')
            ->with('category')
            ->get()
            ->sortByDesc('count')
            ->take(8);

        return [
            'labels' => $data->map(fn ($c) => $c->category?->name ?? 'Non défini')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    #[Computed]
    public function racesFillRate(): array
    {
        // Taux de remplissage par course (saison active)
        $races = Race::when($this->activeSeason, function ($query) {
            $query->where('season_id', $this->activeSeason->id);
        })
            ->withCount('registrations')
            ->orderBy('race_date')
            ->take(10)
            ->get();

        return [
            'labels' => $races->pluck('name')->toArray(),
            'data' => $races->pluck('registrations_count')->toArray(),
        ];
    }

    #[Computed]
    public function topPilots(): array
    {
        // Top 5 pilotes par nombre d'inscriptions
        $data = Pilot::withCount('raceRegistrations')
            ->orderByDesc('race_registrations_count')
            ->take(5)
            ->get();

        return [
            'labels' => $data->map(fn ($p) => $p->full_name)->toArray(),
            'data' => $data->pluck('race_registrations_count')->toArray(),
        ];
    }

    #[Computed]
    public function paymentStats(): array
    {
        $total = RaceRegistration::whereIn('status', ['ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'TECH_CHECKED_FAIL', 'ENTRY_SCANNED', 'BRACELET_GIVEN', 'PUBLISHED'])->count();
        $pending = RaceRegistration::where('status', 'PENDING_VALIDATION')->count();
        $refused = RaceRegistration::where('status', 'REFUSED')->count();

        return [
            'accepted' => $total,
            'pending' => $pending,
            'refused' => $refused,
            'conversion_rate' => $total + $pending + $refused > 0
                ? round(($total / ($total + $pending + $refused)) * 100, 1)
                : 0,
        ];
    }

    #[Computed]
    public function recentRaces()
    {
        return Race::with('season')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentRegistrations()
    {
        return RaceRegistration::with(['pilot', 'race', 'car'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app');
    }
}
