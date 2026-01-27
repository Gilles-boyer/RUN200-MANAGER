<?php

namespace App\Policies;

use App\Models\RaceRegistration;
use App\Models\User;

class RaceRegistrationPolicy
{
    /**
     * Admin peut tout faire
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Pilote peut voir ses propres inscriptions, staff peut voir toutes
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Pilote peut voir sa propre inscription, staff peut voir toutes
     */
    public function view(User $user, RaceRegistration $registration): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->pilot && $registration->pilot_id === $user->pilot->id;
    }

    /**
     * Pilote peut créer une inscription (s'inscrire à une course)
     */
    public function create(User $user): bool
    {
        return $user->isPilot() && $user->pilot !== null;
    }

    /**
     * Seul staff/admin peut modifier une inscription (validation, paddock)
     */
    public function update(User $user, RaceRegistration $registration): bool
    {
        return $user->isStaff();
    }

    /**
     * Pilote peut annuler sa propre inscription si PENDING, staff peut supprimer
     */
    public function delete(User $user, RaceRegistration $registration): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        // Pilote peut annuler uniquement si PENDING
        return $user->pilot
            && $registration->pilot_id === $user->pilot->id
            && $registration->isPending();
    }

    /**
     * Seul staff peut valider/refuser une inscription
     */
    public function validate(User $user, RaceRegistration $registration): bool
    {
        return $user->isStaff();
    }

    /**
     * Seul staff peut assigner un paddock
     */
    public function assignPaddock(User $user, RaceRegistration $registration): bool
    {
        return $user->isStaff();
    }

    /**
     * Pilote peut choisir son emplacement de paddock si inscription acceptée
     * Staff/Admin peuvent toujours assigner
     */
    public function selectPaddockSpot(User $user, RaceRegistration $registration): bool
    {
        // Staff/Admin peuvent toujours assigner
        if ($user->isStaff()) {
            return true;
        }

        // Pilote peut choisir uniquement si:
        // 1. C'est son inscription
        // 2. L'inscription est acceptée
        // 3. Il n'a pas déjà un emplacement
        return $user->pilot
            && $registration->pilot_id === $user->pilot->id
            && $registration->isAccepted();
    }

    /**
     * Pilote peut libérer son propre emplacement
     * Staff/Admin peuvent toujours libérer
     */
    public function releasePaddockSpot(User $user, RaceRegistration $registration): bool
    {
        // Staff/Admin peuvent toujours libérer
        if ($user->isStaff()) {
            return true;
        }

        // Pilote peut libérer uniquement si c'est son inscription
        return $user->pilot && $registration->pilot_id === $user->pilot->id;
    }
}
