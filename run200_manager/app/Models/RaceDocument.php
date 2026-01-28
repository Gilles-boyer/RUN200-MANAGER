<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RaceDocument extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'race_id',
        'category_id',
        'slug',
        'title',
        'description',
        'status',
        'visibility',
        'current_version',
        'sort_order',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'current_version' => 'integer',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    // =========================================================================
    // Boot
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function (RaceDocument $document) {
            if (empty($document->slug)) {
                $document->slug = (string) Str::uuid();
            }
        });
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Course associée
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /**
     * Catégorie du document
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    /**
     * Utilisateur ayant publié le document
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Toutes les versions du document
     */
    public function versions(): HasMany
    {
        return $this->hasMany(RaceDocumentVersion::class, 'document_id')->orderByDesc('version');
    }

    /**
     * Version courante (la plus récente)
     */
    public function currentVersionFile(): HasOne
    {
        return $this->hasOne(RaceDocumentVersion::class, 'document_id')
            ->where('version', $this->current_version);
    }

    /**
     * Dernière version uploadée
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(RaceDocumentVersion::class, 'document_id')->latestOfMany('version');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Documents publiés uniquement
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'PUBLISHED');
    }

    /**
     * Documents en brouillon
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    /**
     * Documents archivés
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'ARCHIVED');
    }

    /**
     * Documents publics (visibles sans auth)
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'PUBLIC');
    }

    /**
     * Documents pour une course spécifique
     */
    public function scopeForRace($query, int $raceId)
    {
        return $query->where('race_id', $raceId);
    }

    /**
     * Tri par catégorie puis ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // =========================================================================
    // Status Helpers
    // =========================================================================

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function isArchived(): bool
    {
        return $this->status === 'ARCHIVED';
    }

    public function isPubliclyVisible(): bool
    {
        return $this->visibility === 'PUBLIC';
    }

    /**
     * Vérifie si le document peut être publié
     */
    public function canBePublished(): bool
    {
        return $this->isDraft() && $this->versions()->exists();
    }

    /**
     * Vérifie si le document peut être archivé
     */
    public function canBeArchived(): bool
    {
        return $this->isPublished();
    }

    /**
     * Vérifie si le document peut être supprimé
     */
    public function canBeDeleted(): bool
    {
        return $this->isDraft();
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * URL publique du document
     */
    public function getPublicUrlAttribute(): string
    {
        return route('board.view', $this->slug);
    }

    /**
     * URL de téléchargement
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('board.download', $this->slug);
    }

    /**
     * Taille formatée du fichier courant
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        $version = $this->latestVersion;

        if (! $version) {
            return null;
        }

        $bytes = $version->file_size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' Mo';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' Ko';
        }

        return $bytes.' octets';
    }

    // =========================================================================
    // Actions
    // =========================================================================

    /**
     * Publier le document
     */
    public function publish(User $user): bool
    {
        if (! $this->canBePublished()) {
            return false;
        }

        return $this->update([
            'status' => 'PUBLISHED',
            'published_at' => now(),
            'published_by' => $user->id,
        ]);
    }

    /**
     * Archiver le document
     */
    public function archive(): bool
    {
        if (! $this->canBeArchived()) {
            return false;
        }

        return $this->update([
            'status' => 'ARCHIVED',
        ]);
    }

    /**
     * Remettre en brouillon (dépublier)
     */
    public function unpublish(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        return $this->update([
            'status' => 'DRAFT',
            'published_at' => null,
            'published_by' => null,
        ]);
    }

    // =========================================================================
    // Activity Log
    // =========================================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'race_id',
                'category_id',
                'title',
                'status',
                'visibility',
                'current_version',
                'published_at',
                'published_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
