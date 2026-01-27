<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Cast pour stocker les valeurs en Title Case (première lettre majuscule).
 * Utilisé pour les prénoms, lieux de naissance, etc.
 */
class TitleCaseCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // Nettoyage et formatage
        $value = trim($value);

        // Gestion des prénoms/noms composés (Jean-Pierre, Marie-Claire)
        return collect(explode(' ', $value))
            ->map(function ($word) {
                // Gérer les mots avec tirets (Jean-Pierre)
                return collect(explode('-', $word))
                    ->map(fn ($part) => Str::ucfirst(Str::lower($part)))
                    ->implode('-');
            })
            ->implode(' ');
    }
}
