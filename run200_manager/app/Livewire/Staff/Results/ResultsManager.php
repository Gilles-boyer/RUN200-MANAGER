<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Results;

use App\Application\Results\UseCases\ImportRaceResults;
use App\Application\Results\UseCases\PublishRaceResults;
use App\Models\Race;
use App\Models\RaceResult;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ResultsManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public Race $race;

    public $csvFile = null;

    public string $searchQuery = '';

    public bool $showUploadModal = false;

    public bool $showPublishConfirmation = false;

    public bool $showUnpublishConfirmation = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    protected array $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:5120|extensions:csv,txt',
    ];

    protected array $messages = [
        'csvFile.required' => 'Veuillez sélectionner un fichier CSV.',
        'csvFile.file' => 'Le fichier n\'est pas valide.',
        'csvFile.mimes' => 'Le fichier doit être au format CSV (.csv ou .txt).',
        'csvFile.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        'csvFile.extensions' => 'L\'extension du fichier doit être .csv ou .txt.',
    ];

    public function mount(Race $race): void
    {
        $this->race = $race->load(['season', 'results', 'resultImports.uploader']);
    }

    #[Computed]
    public function results()
    {
        return $this->race->results()
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('pilot_name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('bib', 'like', "%{$this->searchQuery}%")
                        ->orWhere('car_description', 'like', "%{$this->searchQuery}%")
                        ->orWhere('category_name', 'like', "%{$this->searchQuery}%");
                });
            })
            ->orderBy('position')
            ->paginate(20);
    }

    #[Computed]
    public function imports()
    {
        return $this->race->resultImports()
            ->with('uploader')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function canImport(): bool
    {
        return $this->race->canImportResults();
    }

    #[Computed]
    public function canPublish(): bool
    {
        return $this->race->canPublishResults() && $this->race->results()->count() > 0;
    }

    #[Computed]
    public function canUnpublish(): bool
    {
        return $this->race->isPublished();
    }

    public function openUploadModal(): void
    {
        $this->resetValidation();
        $this->csvFile = null;
        $this->errorMessage = null;
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->csvFile = null;
    }

    public function uploadCsv(): void
    {
        $this->validate();
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            // Additional security: verify file extension is actually csv or txt
            $extension = strtolower($this->csvFile->getClientOriginalExtension());
            if (! in_array($extension, ['csv', 'txt'])) {
                $this->errorMessage = 'Extension de fichier non autorisée. Utilisez .csv ou .txt';

                return;
            }

            // Limit number of lines to prevent DoS
            $content = file_get_contents($this->csvFile->getRealPath());
            $lineCount = substr_count($content, "\n") + 1;
            if ($lineCount > 10000) {
                $this->errorMessage = 'Le fichier CSV ne peut pas contenir plus de 10 000 lignes.';

                return;
            }

            $useCase = app(ImportRaceResults::class);
            $import = $useCase->execute($this->race, $this->csvFile, Auth::user());

            if ($import->isImported()) {
                $this->successMessage = "Import réussi ! {$import->row_count} résultats importés.";
                $this->race->refresh();
            } else {
                $errors = $import->errors ?? [];
                $errorMessages = array_map(fn ($e) => "Ligne {$e['row']}: {$e['message']}", array_slice($errors, 0, 5));
                $this->errorMessage = "L'import a échoué :\n".implode("\n", $errorMessages);
                if (count($errors) > 5) {
                    $this->errorMessage .= "\n... et ".(count($errors) - 5).' autres erreurs.';
                }
            }
        } catch (\Exception $e) {
            $this->errorMessage = "Erreur lors de l'import : ".$e->getMessage();
        }

        $this->closeUploadModal();
    }

    public function confirmPublish(): void
    {
        $this->showPublishConfirmation = true;
    }

    public function cancelPublish(): void
    {
        $this->showPublishConfirmation = false;
    }

    public function publishResults(): void
    {
        $this->showPublishConfirmation = false;
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $useCase = app(PublishRaceResults::class);
            $useCase->execute($this->race, Auth::user());

            $this->successMessage = 'Résultats publiés avec succès !';
            $this->race->refresh();
        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors de la publication : '.$e->getMessage();
        }
    }

    public function confirmUnpublish(): void
    {
        $this->showUnpublishConfirmation = true;
    }

    public function cancelUnpublish(): void
    {
        $this->showUnpublishConfirmation = false;
    }

    public function unpublishResults(): void
    {
        $this->showUnpublishConfirmation = false;
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $useCase = app(PublishRaceResults::class);
            $useCase->unpublish($this->race, Auth::user());

            $this->successMessage = 'Résultats dépubliés. Ils ne sont plus visibles par les pilotes.';
            $this->race->refresh();
        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors de la dépublication : '.$e->getMessage();
        }
    }

    public function deleteResult(int $resultId): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $result = RaceResult::find($resultId);
        if ($result && $result->race_id === $this->race->id) {
            $result->delete();
            $this->successMessage = 'Résultat supprimé.';
        }
    }

    public function render()
    {
        return view('livewire.staff.results.results-manager');
    }
}
