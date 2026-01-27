<?php

namespace App\Policies;

use App\Models\Pilot;
use App\Models\User;

class PilotPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pilot $pilot): bool
    {
        return $user->id === $pilot->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isPilot();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pilot $pilot): bool
    {
        return $user->id === $pilot->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pilot $pilot): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pilot $pilot): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pilot $pilot): bool
    {
        return $user->isAdmin();
    }
}
