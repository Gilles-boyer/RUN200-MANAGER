<?php

namespace App\Livewire\Admin\Races;

use App\Infrastructure\Documents\DocumentUploadService;
use App\Models\DocumentCategory;
use App\Models\Race;
use App\Models\RaceDocument;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Gestion des documents officiels d'une course (Tableau d'affichage)
 */
class Documents extends Component
{
    use WithFileUploads;

    public Race $race;

    /**
     * État du formulaire d'upload
     */
    public bool $showUploadModal = false;

    public ?int $selectedCategoryId = null;

    public string $documentTitle = '';

    public string $documentDescription = '';

    public $uploadedFile = null;

    public string $visibility = 'PUBLIC';

    /**
     * Document sélectionné pour actions
     */
    public ?RaceDocument $selectedDocument = null;

    /**
     * Modal de confirmation de suppression
     */
    public bool $showDeleteModal = false;

    /**
     * Modal des versions
     */
    public bool $showVersionsModal = false;

    /**
     * Catégories obligatoires manquantes
     */
    public array $missingRequired = [];

    protected $listeners = ['refreshDocuments' => '$refresh'];

    protected function rules(): array
    {
        return [
            'selectedCategoryId' => 'required|exists:document_categories,id',
            'documentTitle' => 'required|string|max:255',
            'documentDescription' => 'nullable|string|max:1000',
            'uploadedFile' => 'required|file|mimes:pdf|max:10240', // 10 Mo
            'visibility' => 'required|in:PUBLIC,REGISTERED_ONLY',
        ];
    }

    protected $messages = [
        'selectedCategoryId.required' => 'Veuillez sélectionner une catégorie.',
        'documentTitle.required' => 'Le titre est obligatoire.',
        'uploadedFile.required' => 'Veuillez sélectionner un fichier PDF.',
        'uploadedFile.mimes' => 'Seuls les fichiers PDF sont autorisés.',
        'uploadedFile.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
    ];

    public function mount(Race $race): void
    {
        $this->race = $race;
        $this->checkCompleteness();
    }

    /**
     * Documents de la course groupés par catégorie (computed property)
     *
     * @return Collection<int, EloquentCollection<int, RaceDocument>>
     */
    #[Computed]
    public function documents(): Collection
    {
        return RaceDocument::with(['category', 'latestVersion', 'publisher'])
            ->forRace($this->race->id)
            ->ordered()
            ->get()
            ->groupBy('category_id');
    }

    /**
     * Catégories disponibles (computed property)
     *
     * @return EloquentCollection<int, DocumentCategory>
     */
    #[Computed]
    public function categories(): EloquentCollection
    {
        return DocumentCategory::active()->ordered()->get();
    }

    /**
     * Vérifier la complétude des documents obligatoires
     */
    private function checkCompleteness(): void
    {
        $requiredCategories = DocumentCategory::required()->pluck('id', 'name');

        // Vérifier les documents publiés uniquement
        $publishedCategoryIds = RaceDocument::forRace($this->race->id)
            ->published()
            ->pluck('category_id')
            ->unique();

        $this->missingRequired = [];

        foreach ($requiredCategories as $name => $id) {
            if (! $publishedCategoryIds->contains($id)) {
                $this->missingRequired[] = $name;
            }
        }
    }

    /**
     * Ouvrir le modal d'upload
     */
    public function openUploadModal(?int $categoryId = null): void
    {
        $this->resetUploadForm();
        $this->selectedCategoryId = $categoryId;

        // Pré-remplir le titre si catégorie sélectionnée
        if ($categoryId) {
            /** @var DocumentCategory|null $category */
            $category = $this->categories->firstWhere('id', $categoryId);
            if ($category) {
                $this->documentTitle = $category->name;
            }
        }

        $this->showUploadModal = true;
    }

    /**
     * Fermer le modal d'upload
     */
    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->resetUploadForm();
    }

    /**
     * Réinitialiser le formulaire d'upload
     */
    private function resetUploadForm(): void
    {
        $this->selectedCategoryId = null;
        $this->documentTitle = '';
        $this->documentDescription = '';
        $this->uploadedFile = null;
        $this->visibility = 'PUBLIC';
        $this->resetValidation();
    }

    /**
     * Upload d'un nouveau document
     */
    public function uploadDocument(): void
    {
        $this->validate();

        $uploadService = new DocumentUploadService();

        try {
            // Vérifier si un document existe déjà pour cette catégorie (si pas multiple)
            $category = DocumentCategory::find($this->selectedCategoryId);
            $existingDocument = null;

            if (! $category->is_multiple) {
                $existingDocument = RaceDocument::forRace($this->race->id)
                    ->where('category_id', $this->selectedCategoryId)
                    ->first();
            }

            if ($existingDocument) {
                // Ajouter une nouvelle version au document existant
                $uploadService->upload(
                    $this->uploadedFile,
                    $existingDocument,
                    Auth::user(),
                    'Nouvelle version uploadée'
                );

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Nouvelle version ajoutée au document existant.',
                ]);
            } else {
                // Créer un nouveau document
                $document = RaceDocument::create([
                    'race_id' => $this->race->id,
                    'category_id' => $this->selectedCategoryId,
                    'title' => $this->documentTitle,
                    'description' => $this->documentDescription,
                    'visibility' => $this->visibility,
                    'status' => 'DRAFT',
                ]);

                $uploadService->upload(
                    $this->uploadedFile,
                    $document,
                    Auth::user()
                );

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Document uploadé avec succès.',
                ]);
            }

            $this->closeUploadModal();
            $this->checkCompleteness();
        } catch (\InvalidArgumentException $e) {
            $this->addError('uploadedFile', $e->getMessage());
        } catch (\Exception $e) {
            $this->addError('uploadedFile', 'Erreur lors de l\'upload : '.$e->getMessage());
        }
    }

    /**
     * Publier un document
     */
    public function publishDocument(int $documentId): void
    {
        $document = RaceDocument::findOrFail($documentId);

        if ($document->publish(Auth::user())) {
            session()->flash('success', 'Document publié avec succès.');
            $this->checkCompleteness();
            unset($this->documents); // Invalider le cache
        } else {
            session()->flash('error', 'Impossible de publier ce document.');
        }
    }

    /**
     * Dépublier un document
     */
    public function unpublishDocument(int $documentId): void
    {
        $document = RaceDocument::findOrFail($documentId);

        if ($document->unpublish()) {
            session()->flash('success', 'Document retiré du tableau d\'affichage.');
            $this->checkCompleteness();
            unset($this->documents); // Invalider le cache
        } else {
            session()->flash('error', 'Impossible de dépublier ce document.');
        }
    }

    /**
     * Archiver un document
     */
    public function archiveDocument(int $documentId): void
    {
        $document = RaceDocument::findOrFail($documentId);

        if ($document->archive()) {
            session()->flash('success', 'Document archivé avec succès.');
            $this->checkCompleteness();
            unset($this->documents); // Invalider le cache
        } else {
            session()->flash('error', 'Impossible d\'archiver ce document.');
        }
    }

    /**
     * Confirmer la suppression
     */
    public function confirmDelete(int $documentId): void
    {
        $this->selectedDocument = RaceDocument::findOrFail($documentId);
        $this->showDeleteModal = true;
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument(): void
    {
        if (! $this->selectedDocument || ! $this->selectedDocument->canBeDeleted()) {
            session()->flash('error', 'Ce document ne peut pas être supprimé.');
            $this->showDeleteModal = false;

            return;
        }

        $uploadService = new DocumentUploadService();
        $uploadService->deleteAllVersions($this->selectedDocument);

        $this->selectedDocument->delete();

        session()->flash('success', 'Document supprimé avec succès.');
        $this->showDeleteModal = false;
        $this->selectedDocument = null;
        $this->checkCompleteness();
        unset($this->documents); // Invalider le cache
    }

    /**
     * Afficher les versions d'un document
     */
    public function showVersions(int $documentId): void
    {
        $this->selectedDocument = RaceDocument::with('versions.uploader')->findOrFail($documentId);
        $this->showVersionsModal = true;
    }

    /**
     * Fermer le modal des versions
     */
    public function closeVersionsModal(): void
    {
        $this->showVersionsModal = false;
        $this->selectedDocument = null;
    }

    /**
     * Copier l'URL du tableau d'affichage
     */
    public function getBoardUrl(): string
    {
        return route('board.show', $this->race->slug);
    }

    public function render()
    {
        return view('livewire.admin.races.documents')
            ->title('Documents - '.$this->race->name);
    }
}
