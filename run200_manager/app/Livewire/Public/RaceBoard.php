<?php

namespace App\Livewire\Public;

use App\Models\DocumentCategory;
use App\Models\Race;
use App\Models\RaceDocument;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Tableau d'affichage numérique public d'une course
 * Affiche tous les documents officiels publiés, groupés par catégorie
 */
class RaceBoard extends Component
{
    public Race $race;

    /**
     * Indicateur de complétude des documents obligatoires
     */
    public bool $isComplete = false;

    /**
     * Liste des catégories obligatoires manquantes
     *
     * @var array<string>
     */
    public array $missingRequired = [];

    public function mount(Race $race): void
    {
        $this->race = $race;
        $this->checkCompleteness();
    }

    /**
     * Documents groupés par catégorie (computed property)
     *
     * @return Collection<int|string, EloquentCollection<int, RaceDocument>>
     */
    #[Computed]
    public function documentsByCategory(): Collection
    {
        $documents = RaceDocument::with(['category', 'latestVersion'])
            ->forRace($this->race->id)
            ->published()
            ->public()
            ->ordered()
            ->get();

        return $documents->groupBy('category_id');
    }

    /**
     * Catégories actives qui ont des documents (computed property)
     *
     * @return EloquentCollection<int, DocumentCategory>
     */
    #[Computed]
    public function categories(): EloquentCollection
    {
        $categoryIds = $this->documentsByCategory->keys();

        return DocumentCategory::active()
            ->whereIn('id', $categoryIds)
            ->ordered()
            ->get();
    }

    /**
     * Récupérer les documents d'une catégorie
     *
     * @return Collection<int, RaceDocument>
     */
    public function getDocumentsForCategory(int $categoryId): Collection
    {
        return $this->documentsByCategory->get($categoryId, collect());
    }

    /**
     * Vérifier la complétude des documents obligatoires
     */
    private function checkCompleteness(): void
    {
        $requiredCategories = DocumentCategory::required()->pluck('id', 'name');

        // Récupérer les catégories des documents publiés
        $publishedCategoryIds = RaceDocument::forRace($this->race->id)
            ->published()
            ->public()
            ->pluck('category_id')
            ->unique();

        $this->missingRequired = [];

        foreach ($requiredCategories as $name => $id) {
            if (! $publishedCategoryIds->contains($id)) {
                $this->missingRequired[] = $name;
            }
        }

        $this->isComplete = empty($this->missingRequired);
    }

    /**
     * Générer l'URL du QR code pour cette page
     */
    public function getQrCodeUrlProperty(): string
    {
        return route('board.show', $this->race->slug);
    }

    public function render()
    {
        return view('livewire.public.race-board')
            ->layout('components.layouts.racing-public', ['title' => 'Tableau d\'affichage - '.$this->race->name.' - RUN200']);
    }
}
