<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checkpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'required_permission',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function passages(): HasMany
    {
        return $this->hasMany(CheckpointPassage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the previous checkpoint in order
     */
    public function previousCheckpoint(): ?Checkpoint
    {
        return static::where('sort_order', '<', $this->sort_order)
            ->where('is_active', true)
            ->orderBy('sort_order', 'desc')
            ->first();
    }

    /**
     * Check if a user has permission to scan this checkpoint
     */
    public function userCanScan(User $user): bool
    {
        if (empty($this->required_permission)) {
            return true;
        }

        return $user->hasPermissionTo($this->required_permission);
    }
}
