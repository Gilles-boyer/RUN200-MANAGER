<?php

declare(strict_types=1);

namespace App\Infrastructure\Documents;

use App\Models\RaceDocument;
use App\Models\RaceDocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service pour l'upload sécurisé des documents de course
 * Gère la validation, le stockage et le versioning des fichiers PDF
 */
class DocumentUploadService
{
    /**
     * Taille maximale autorisée en bytes (10 Mo par défaut)
     */
    public const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Types MIME autorisés
     */
    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
    ];

    /**
     * Extensions autorisées
     */
    public const ALLOWED_EXTENSIONS = ['pdf'];

    /**
     * Magic bytes pour validation PDF (signature %PDF-)
     */
    private const PDF_MAGIC_BYTES = '%PDF-';

    /**
     * Disk de stockage
     */
    private string $disk = 'race-documents';

    /**
     * Upload un nouveau fichier pour un document
     *
     * @throws InvalidArgumentException Si le fichier n'est pas valide
     * @throws RuntimeException Si l'upload échoue
     */
    public function upload(
        UploadedFile $file,
        RaceDocument $document,
        User $uploader,
        ?string $notes = null
    ): RaceDocumentVersion {
        // Validation du fichier
        $this->validateFile($file);

        // Calcul du checksum
        $checksum = hash_file('sha256', $file->getRealPath());

        // Vérifier si le même fichier existe déjà (éviter doublons)
        $existingVersion = $document->versions()
            ->where('checksum', $checksum)
            ->first();

        if ($existingVersion) {
            throw new InvalidArgumentException(
                'Ce fichier a déjà été uploadé (version '.$existingVersion->version.')'
            );
        }

        // Déterminer le numéro de version
        $nextVersion = ($document->versions()->max('version') ?? 0) + 1;

        // Construire le chemin de stockage
        $storagePath = $this->buildStoragePath($document, $file, $nextVersion);

        // Stocker le fichier
        $stored = Storage::disk($this->disk)->putFileAs(
            dirname($storagePath),
            $file,
            basename($storagePath)
        );

        if (! $stored) {
            throw new RuntimeException('Impossible de stocker le fichier');
        }

        // Créer l'enregistrement de version
        $version = RaceDocumentVersion::create([
            'document_id' => $document->id,
            'version' => $nextVersion,
            'file_path' => $storagePath,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'checksum' => $checksum,
            'uploaded_by' => $uploader->id,
            'notes' => $notes,
        ]);

        // Mettre à jour le document avec la version courante
        $document->update(['current_version' => $nextVersion]);

        return $version;
    }

    /**
     * Remplacer un fichier existant (créer nouvelle version)
     */
    public function replace(
        UploadedFile $file,
        RaceDocument $document,
        User $uploader,
        ?string $notes = null
    ): RaceDocumentVersion {
        return $this->upload($file, $document, $uploader, $notes);
    }

    /**
     * Supprimer toutes les versions d'un document
     */
    public function deleteAllVersions(RaceDocument $document): bool
    {
        $versions = $document->versions;

        /** @var RaceDocumentVersion $version */
        foreach ($versions as $version) {
            $this->deleteVersionFile($version);
        }

        // Les enregistrements seront supprimés par cascade SQL
        return true;
    }

    /**
     * Supprimer le fichier d'une version spécifique
     */
    public function deleteVersionFile(RaceDocumentVersion $version): bool
    {
        if (Storage::disk($this->disk)->exists($version->file_path)) {
            return Storage::disk($this->disk)->delete($version->file_path);
        }

        return true;
    }

    /**
     * Valider un fichier uploadé
     *
     * @throws InvalidArgumentException Si le fichier n'est pas valide
     */
    public function validateFile(UploadedFile $file): void
    {
        // Vérifier que le fichier est valide
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Le fichier uploadé est invalide');
        }

        // Vérifier la taille
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException(
                'Le fichier dépasse la taille maximale autorisée ('.
                $this->formatBytes(self::MAX_FILE_SIZE).')'
            );
        }

        // Vérifier l'extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new InvalidArgumentException(
                'Extension non autorisée. Seuls les fichiers PDF sont acceptés.'
            );
        }

        // Vérifier le type MIME
        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new InvalidArgumentException(
                'Type de fichier non autorisé. Seuls les fichiers PDF sont acceptés.'
            );
        }

        // Vérifier les magic bytes (signature PDF)
        $this->validatePdfMagicBytes($file);
    }

    /**
     * Vérifier les magic bytes du fichier PDF
     *
     * @throws InvalidArgumentException Si le fichier n'est pas un vrai PDF
     */
    private function validatePdfMagicBytes(UploadedFile $file): void
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            throw new InvalidArgumentException('Impossible de lire le fichier');
        }

        $header = fread($handle, 8);
        fclose($handle);

        if (! str_starts_with($header, self::PDF_MAGIC_BYTES)) {
            throw new InvalidArgumentException(
                'Le fichier n\'est pas un PDF valide (signature incorrecte)'
            );
        }
    }

    /**
     * Construire le chemin de stockage
     */
    private function buildStoragePath(
        RaceDocument $document,
        UploadedFile $file,
        int $version
    ): string {
        $raceId = $document->race_id;
        $documentSlug = $document->slug;
        $extension = strtolower($file->getClientOriginalExtension());

        // Nom de fichier sécurisé
        $safeFilename = Str::slug(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        );

        return "race-{$raceId}/{$documentSlug}/v{$version}-{$safeFilename}.{$extension}";
    }

    /**
     * Formater une taille en bytes de façon lisible
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' Mo';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' Ko';
        }

        return $bytes.' octets';
    }

    /**
     * Obtenir le chemin complet d'un fichier
     */
    public function getFullPath(RaceDocumentVersion $version): string
    {
        return Storage::disk($this->disk)->path($version->file_path);
    }

    /**
     * Vérifier si un fichier existe
     */
    public function fileExists(RaceDocumentVersion $version): bool
    {
        return Storage::disk($this->disk)->exists($version->file_path);
    }

    /**
     * Obtenir l'URL signée temporaire pour téléchargement (si S3)
     */
    public function getTemporaryUrl(RaceDocumentVersion $version, int $expiresInMinutes = 5): ?string
    {
        $disk = Storage::disk($this->disk);

        // Vérifier si le driver supporte les URLs temporaires (S3, etc.)
        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl(
                $version->file_path,
                now()->addMinutes($expiresInMinutes)
            );
        }

        return null;
    }
}
