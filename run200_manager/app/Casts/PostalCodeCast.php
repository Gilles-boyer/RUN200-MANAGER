<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast pour formater les codes postaux.
 * Assure un format cohérent avec padding de zéros si nécessaire.
 */
class PostalCodeCast implements CastsAttributes
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

        // Supprimer les espaces
        $cleaned = preg_replace('/\s+/', '', trim($value));

        // Pour les codes postaux français (5 chiffres), ajouter les zéros en début si nécessaire
        if (preg_match('/^[0-9]{1,5}$/', $cleaned)) {
            return str_pad($cleaned, 5, '0', STR_PAD_LEFT);
        }

        return $cleaned;
    }
}
