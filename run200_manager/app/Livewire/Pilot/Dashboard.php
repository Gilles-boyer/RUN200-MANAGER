<?php

namespace App\Livewire\Pilot;

use App\Models\Race;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $pilot = $user->pilot;

        // Vérifier si le profil pilote existe
        $hasProfile = $pilot !== null;

        // Statistiques
        $stats = [
            'cars_count' => $pilot ? $pilot->cars()->count() : 0,
            'registrations_count' => $pilot ? $pilot->raceRegistrations()->count() : 0,
            'is_active_season' => $pilot ? $pilot->is_active_season : false,
        ];

        // Vérifier la complétude du profil avec la nouvelle méthode
        $profileComplete = $hasProfile && $pilot->isProfileComplete();
        $profileCompletionPercentage = $hasProfile ? $pilot->getProfileCompletionPercentage() : 0;
        $missingFields = $hasProfile ? $pilot->getMissingFields() : [];

        // Vérifier si le pilote peut s'inscrire aux courses
        $canRegisterForRace = $hasProfile && $pilot->canRegisterForRace();
        $registrationBlockingReasons = $hasProfile ? $pilot->getRegistrationBlockingReasons() : [];

        // Courses ouvertes aux inscriptions
        $openRaces = Race::with('season')
            ->where('status', 'OPEN')
            ->whereHas('season', fn ($q) => $q->where('is_active', true))
            ->orderBy('race_date')
            ->take(3)
            ->get();

        // Inscriptions récentes du pilote
        $recentRegistrations = $pilot
            ? $pilot->raceRegistrations()
                ->with(['race.season', 'car.category', 'payments'])
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get()
            : collect();

        return view('livewire.pilot.dashboard', [
            'pilot' => $pilot,
            'hasProfile' => $hasProfile,
            'profileComplete' => $profileComplete,
            'profileCompletionPercentage' => $profileCompletionPercentage,
            'missingFields' => $missingFields,
            'canRegisterForRace' => $canRegisterForRace,
            'registrationBlockingReasons' => $registrationBlockingReasons,
            'stats' => $stats,
            'openRaces' => $openRaces,
            'recentRegistrations' => $recentRegistrations,
        ])->layout('layouts.pilot');
    }
}
