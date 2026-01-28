<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RaceDocumentVersion extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Désactiver updated_at car on n'a que created_at
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'document_id',
        'version',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'checksum',
        'uploaded_by',
        'notes',
    ];

    protected $casts = [
        'version' => 'integer',
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Document parent
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(RaceDocument::class, 'document_id');
    }

    /**
     * Utilisateur ayant uploadé cette version
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Taille formatée du fichier
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' Mo';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' Ko';
        }

        return $bytes.' octets';
    }

    /**
     * Nom du fichier avec version
     */
    public function getVersionedFilenameAttribute(): string
    {
        $extension = pathinfo($this->original_filename, PATHINFO_EXTENSION);
        $basename = pathinfo($this->original_filename, PATHINFO_FILENAME);

        return "{$basename}_v{$this->version}.{$extension}";
    }

    /**
     * Vérifie si le fichier existe dans le storage
     */
    public function fileExists(): bool
    {
        return Storage::disk('race-documents')->exists($this->file_path);
    }

    /**
     * Obtenir le chemin complet du fichier
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk('race-documents')->path($this->file_path);
    }

    /**
     * Obtenir le contenu du fichier
     */
    public function getFileContent(): ?string
    {
        if (! $this->fileExists()) {
            return null;
        }

        return Storage::disk('race-documents')->get($this->file_path);
    }

    /**
     * Obtenir un stream du fichier
     *
     * @return resource|null
     */
    public function getFileStream()
    {
        if (! $this->fileExists()) {
            return null;
        }

        return Storage::disk('race-documents')->readStream($this->file_path);
    }

    // =========================================================================
    // Activity Log
    // =========================================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'document_id',
                'version',
                'original_filename',
                'file_size',
                'uploaded_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
