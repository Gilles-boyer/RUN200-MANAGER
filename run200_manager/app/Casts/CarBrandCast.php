<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Cast pour formater les marques de voiture.
 * Normalise les marques connues et applique Title Case aux autres.
 */
class CarBrandCast implements CastsAttributes
{
    /**
     * Liste des marques avec leur formatage correct.
     * Clé = version normalisée (lowercase), Valeur = formatage correct
     */
    protected static array $knownBrands = [
        'bmw' => 'BMW',
        'vw' => 'VW',
        'volkswagen' => 'Volkswagen',
        'mercedes' => 'Mercedes',
        'mercedes-benz' => 'Mercedes-Benz',
        'audi' => 'Audi',
        'porsche' => 'Porsche',
        'ferrari' => 'Ferrari',
        'lamborghini' => 'Lamborghini',
        'maserati' => 'Maserati',
        'alfa romeo' => 'Alfa Romeo',
        'alfaromeo' => 'Alfa Romeo',
        'fiat' => 'Fiat',
        'lancia' => 'Lancia',
        'peugeot' => 'Peugeot',
        'renault' => 'Renault',
        'citroen' => 'Citroën',
        'citroën' => 'Citroën',
        'alpine' => 'Alpine',
        'toyota' => 'Toyota',
        'honda' => 'Honda',
        'nissan' => 'Nissan',
        'mazda' => 'Mazda',
        'mitsubishi' => 'Mitsubishi',
        'subaru' => 'Subaru',
        'suzuki' => 'Suzuki',
        'lexus' => 'Lexus',
        'infiniti' => 'Infiniti',
        'ford' => 'Ford',
        'chevrolet' => 'Chevrolet',
        'dodge' => 'Dodge',
        'jeep' => 'Jeep',
        'chrysler' => 'Chrysler',
        'cadillac' => 'Cadillac',
        'lincoln' => 'Lincoln',
        'gmc' => 'GMC',
        'buick' => 'Buick',
        'tesla' => 'Tesla',
        'corvette' => 'Corvette',
        'mustang' => 'Mustang',
        'shelby' => 'Shelby',
        'aston martin' => 'Aston Martin',
        'astonmartin' => 'Aston Martin',
        'bentley' => 'Bentley',
        'rolls royce' => 'Rolls-Royce',
        'rolls-royce' => 'Rolls-Royce',
        'rollsroyce' => 'Rolls-Royce',
        'jaguar' => 'Jaguar',
        'land rover' => 'Land Rover',
        'landrover' => 'Land Rover',
        'mini' => 'MINI',
        'mclaren' => 'McLaren',
        'lotus' => 'Lotus',
        'morgan' => 'Morgan',
        'tvr' => 'TVR',
        'caterham' => 'Caterham',
        'seat' => 'SEAT',
        'skoda' => 'Škoda',
        'opel' => 'Opel',
        'dacia' => 'Dacia',
        'hyundai' => 'Hyundai',
        'kia' => 'Kia',
        'genesis' => 'Genesis',
        'volvo' => 'Volvo',
        'saab' => 'Saab',
        'koenigsegg' => 'Koenigsegg',
        'pagani' => 'Pagani',
        'bugatti' => 'Bugatti',
        'ds' => 'DS',
        'ds automobiles' => 'DS Automobiles',
    ];

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

        $value = trim($value);
        $normalized = Str::lower($value);

        // Vérifier si c'est une marque connue
        if (isset(self::$knownBrands[$normalized])) {
            return self::$knownBrands[$normalized];
        }

        // Sinon, appliquer Title Case
        return collect(explode(' ', $value))
            ->map(function ($word) {
                return collect(explode('-', $word))
                    ->map(fn ($part) => Str::ucfirst(Str::lower($part)))
                    ->implode('-');
            })
            ->implode(' ');
    }
}
