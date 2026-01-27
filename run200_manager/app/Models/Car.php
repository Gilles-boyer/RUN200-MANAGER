<?php

namespace App\Models;

use App\Casts\CarBrandCast;
use App\Casts\TitleCaseCast;
use App\Domain\Car\ValueObjects\RaceNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Car extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pilot_id',
        'car_category_id',
        'race_number',
        'make',
        'model',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'race_number' => 'integer',
            // Formatage des données
            'make' => CarBrandCast::class,
            'model' => TitleCaseCast::class,
        ];
    }

    /**
     * Get the pilot that owns the car.
     */
    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }

    /**
     * Get the category of the car.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CarCategory::class, 'car_category_id');
    }

    /**
     * Get the tech inspection history for this car.
     */
    public function techInspectionHistory(): HasMany
    {
        return $this->hasMany(CarTechInspectionHistory::class);
    }

    /**
     * Get the latest tech inspection for this car.
     */
    public function latestTechInspection()
    {
        return $this->hasOne(CarTechInspectionHistory::class)
            ->ofMany('inspected_at', 'max');
    }

    /**
     * Get the race number as ValueObject.
     */
    public function getRaceNumberAttribute(): RaceNumber
    {
        return RaceNumber::fromInt($this->attributes['race_number']);
    }

    /**
     * Get the car's full name (make model #number).
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->make} {$this->model} #{$this->attributes['race_number']}";
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'race_number',
                'make',
                'model',
                'car_category_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope a query to find by race number.
     */
    public function scopeWhereRaceNumber($query, int $number)
    {
        return $query->where('race_number', $number);
    }
}
