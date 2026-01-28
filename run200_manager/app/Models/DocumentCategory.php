<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentCategory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_required',
        'is_multiple',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_multiple' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Documents appartenant à cette catégorie
     */
    public function documents(): HasMany
    {
        return $this->hasMany(RaceDocument::class, 'category_id');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Catégories actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Catégories obligatoires
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Tri par ordre de priorité
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // =========================================================================
    // Activity Log
    // =========================================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'is_required', 'is_multiple', 'is_active', 'sort_order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
