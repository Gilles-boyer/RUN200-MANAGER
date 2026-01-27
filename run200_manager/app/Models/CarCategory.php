<?php

namespace App\Models;

use App\Casts\UppercaseCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            // Formatage des données
            'name' => UppercaseCast::class,
        ];
    }

    /**
     * Get the cars in this category.
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    /**
     * Get the season category standings for this category.
     */
    public function seasonCategoryStandings(): HasMany
    {
        return $this->hasMany(SeasonCategoryStanding::class, 'car_category_id');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeWhereActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
