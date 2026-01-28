<?php

namespace App\Http\Controllers;

use App\Models\RaceDocument;
use App\Models\RaceDocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contrôleur pour l'accès public aux documents du tableau d'affichage
 * Gère le streaming sécurisé des fichiers PDF
 */
class RaceBoardController extends Controller
{
    /**
     * Afficher/prévisualiser un document (inline dans le navigateur)
     */
    public function view(string $slug): StreamedResponse
    {
        $document = $this->getAccessibleDocument($slug);

        /** @var RaceDocumentVersion|null $version */
        $version = $document->latestVersion;

        if (! $version instanceof RaceDocumentVersion || ! $version->fileExists()) {
            abort(404, 'Fichier non trouvé');
        }

        return $this->streamFile($version, 'inline');
    }

    /**
     * Télécharger un document
     */
    public function download(string $slug): StreamedResponse
    {
        $document = $this->getAccessibleDocument($slug);

        /** @var RaceDocumentVersion|null $version */
        $version = $document->latestVersion;

        if (! $version instanceof RaceDocumentVersion || ! $version->fileExists()) {
            abort(404, 'Fichier non trouvé');
        }

        // Log le téléchargement pour statistiques (optionnel)
        activity()
            ->performedOn($document)
            ->withProperties([
                'version' => $version->version,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('document_downloaded');

        return $this->streamFile($version, 'attachment');
    }

    /**
     * Récupérer un document accessible publiquement
     */
    private function getAccessibleDocument(string $slug): RaceDocument
    {
        $document = RaceDocument::where('slug', $slug)
            ->with(['latestVersion', 'race', 'category'])
            ->first();

        if (! $document) {
            abort(404, 'Document non trouvé');
        }

        // Vérifier que le document est publié
        if (! $document->isPublished()) {
            abort(404, 'Document non disponible');
        }

        // Vérifier la visibilité (pour l'instant, on ne gère que PUBLIC)
        // Si REGISTERED_ONLY, il faudrait vérifier l'auth
        if ($document->visibility === 'REGISTERED_ONLY') {
            // Pour l'instant, on bloque si pas auth
            // À améliorer en P1 avec vérification inscription
            if (! auth()->check()) {
                abort(403, 'Accès réservé aux pilotes inscrits');
            }
        }

        return $document;
    }

    /**
     * Stream le fichier avec les headers appropriés
     */
    private function streamFile(RaceDocumentVersion $version, string $disposition): StreamedResponse
    {
        $disk = Storage::disk('race-documents');
        $stream = $disk->readStream($version->file_path);

        if (! $stream) {
            abort(500, 'Erreur lors de la lecture du fichier');
        }

        $filename = $version->original_filename;

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $version->mime_type,
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                'Content-Length' => $version->file_size,
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
            ]
        );
    }
}
