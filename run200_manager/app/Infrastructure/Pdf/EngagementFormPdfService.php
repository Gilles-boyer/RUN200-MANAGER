<?php

namespace App\Infrastructure\Pdf;

use App\Models\EngagementForm;
use Barryvdh\DomPDF\Facade\Pdf;

class EngagementFormPdfService
{
    /**
     * Générer le PDF de la feuille d'engagement
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(EngagementForm $form)
    {
        // Charger les relations nécessaires pour afficher toutes les informations
        $form->load(['witness', 'adminValidator', 'registration.techInspection.inspector']);

        $data = [
            'form' => $form,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.engagement-form', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Télécharger le PDF
     */
    public function download(EngagementForm $form)
    {
        $filename = $this->generateFilename($form);

        return $this->generate($form)->download($filename);
    }

    /**
     * Streamer le PDF (affichage dans le navigateur)
     */
    public function stream(EngagementForm $form)
    {
        $filename = $this->generateFilename($form);

        return $this->generate($form)->stream($filename);
    }

    /**
     * Sauvegarder le PDF sur le disque
     */
    public function save(EngagementForm $form, ?string $path = null): string
    {
        $filename = $this->generateFilename($form);
        $path = $path ?? storage_path("app/engagements/{$filename}");

        // Ensure directory exists
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->generate($form)->save($path);

        return $path;
    }

    /**
     * Générer le nom de fichier
     */
    protected function generateFilename(EngagementForm $form): string
    {
        $raceName = str_replace(' ', '_', $form->race_name);
        $pilotName = str_replace(' ', '_', $form->pilot_name);
        $raceNumber = $form->car_race_number;
        $date = $form->race_date->format('Y-m-d');

        return "engagement_{$raceName}_{$pilotName}_{$raceNumber}_{$date}.pdf";
    }
}
