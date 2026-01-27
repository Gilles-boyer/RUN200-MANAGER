<?php

namespace App\Policies;

use App\Models\Race;
use App\Models\User;

class RacePolicy
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
     * Tout le monde peut voir les courses ouvertes
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tout le monde peut voir une course
     */
    public function view(User $user, Race $race): bool
    {
        return true;
    }

    /**
     * Seul admin/staff peut créer une course
     */
    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isAdmin();
    }

    /**
     * Seul admin/staff peut modifier une course
     */
    public function update(User $user, Race $race): bool
    {
        return $user->isStaff() || $user->isAdmin();
    }

    /**
     * Seul admin peut supprimer une course
     */
    public function delete(User $user, Race $race): bool
    {
        return $user->isAdmin();
    }
}
