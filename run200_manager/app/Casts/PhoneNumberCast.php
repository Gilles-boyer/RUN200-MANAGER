<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast pour formater les numéros de téléphone.
 * Stocke en format normalisé (ex: +33612345678 ou 0612345678).
 */
class PhoneNumberCast implements CastsAttributes
{
    /**
     * Cast the given value (pour affichage formaté).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // Formater pour affichage (06 12 34 56 78)
        $cleaned = preg_replace('/[^0-9+]/', '', $value);

        // Format français 10 chiffres
        if (preg_match('/^0[1-9][0-9]{8}$/', $cleaned)) {
            return implode(' ', str_split($cleaned, 2));
        }

        // Format international français (+33...)
        if (preg_match('/^\+33[0-9]{9}$/', $cleaned)) {
            $national = '0'.substr($cleaned, 3);

            return implode(' ', str_split($national, 2));
        }

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

        // Supprimer tous les caractères non numériques sauf le +
        $cleaned = preg_replace('/[^0-9+]/', '', trim($value));

        // Convertir +33 en 0 pour standardiser
        if (str_starts_with($cleaned, '+33')) {
            $cleaned = '0'.substr($cleaned, 3);
        }

        // Convertir 0033 en 0
        if (str_starts_with($cleaned, '0033')) {
            $cleaned = '0'.substr($cleaned, 4);
        }

        return $cleaned;
    }
}
