<?php

namespace App\Infrastructure\Pdf;

use App\Models\Race;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class EngagedListPdfService
{
    /**
     * Générer le PDF de la liste des engagés pour une course
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(Race $race)
    {
        $registrations = $race->registrations()
            ->with(['pilot', 'car.category'])
            ->where('status', 'ACCEPTED')
            ->orderBy('paddock')
            ->get();

        $data = [
            'race' => $race,
            'registrations' => $registrations,
            'generatedAt' => now(),
            'totalEngaged' => $registrations->count(),
            'categoryCounts' => $this->getCategoryCounts($registrations),
        ];

        return Pdf::loadView('pdf.engaged-list', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Télécharger le PDF
     */
    public function download(Race $race)
    {
        $filename = 'engages_'.str_replace(' ', '_', $race->name).'_'.$race->race_date->format('Y-m-d').'.pdf';

        return $this->generate($race)->download($filename);
    }

    /**
     * Streamer le PDF (affichage dans le navigateur)
     */
    public function stream(Race $race)
    {
        $filename = 'engages_'.str_replace(' ', '_', $race->name).'_'.$race->race_date->format('Y-m-d').'.pdf';

        return $this->generate($race)->stream($filename);
    }

    /**
     * Compter les inscriptions par catégorie
     */
    protected function getCategoryCounts(Collection $registrations): array
    {
        return $registrations->groupBy(fn ($r) => $r->car->category->name ?? 'Non catégorisé')
            ->map(fn ($group) => $group->count())
            ->sortKeys()
            ->toArray();
    }
}
