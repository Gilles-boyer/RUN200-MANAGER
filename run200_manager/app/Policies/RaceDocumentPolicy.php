<?php

namespace App\Policies;

use App\Models\RaceDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy pour la gestion des documents de course
 */
class RaceDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des documents
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    /**
     * Détermine si l'utilisateur peut voir un document spécifique
     */
    public function view(User $user, RaceDocument $document): bool
    {
        // Admin et staff peuvent voir tous les documents
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        // Les pilotes ne peuvent voir que les documents publiés et publics
        if ($user->isPilot()) {
            return $document->isPublished() && $document->isPubliclyVisible();
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut créer un document
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Détermine si l'utilisateur peut modifier un document
     */
    public function update(User $user, RaceDocument $document): bool
    {
        return $user->isAdmin();
    }

    /**
     * Détermine si l'utilisateur peut supprimer un document
     */
    public function delete(User $user, RaceDocument $document): bool
    {
        // Seul l'admin peut supprimer, et uniquement les brouillons
        return $user->isAdmin() && $document->canBeDeleted();
    }

    /**
     * Détermine si l'utilisateur peut publier un document
     */
    public function publish(User $user, RaceDocument $document): bool
    {
        return $user->isAdmin() && $document->canBePublished();
    }

    /**
     * Détermine si l'utilisateur peut archiver un document
     */
    public function archive(User $user, RaceDocument $document): bool
    {
        return $user->isAdmin() && $document->canBeArchived();
    }

    /**
     * Détermine si l'utilisateur peut uploader une nouvelle version
     */
    public function uploadVersion(User $user, RaceDocument $document): bool
    {
        return $user->isAdmin();
    }
}
