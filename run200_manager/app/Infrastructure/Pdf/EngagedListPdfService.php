<?php

namespace App\Infrastructure\Pdf;

use App\Infrastructure\Qr\QrTokenService;
use App\Models\Race;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class EngagedListPdfService
{
    protected QrTokenService $qrTokenService;

    public function __construct(?QrTokenService $qrTokenService = null)
    {
        $this->qrTokenService = $qrTokenService ?? new QrTokenService();
    }

    /**
     * Générer le PDF de la liste des engagés pour une course
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(Race $race)
    {
        $registrations = $race->registrations()
            ->with(['pilot', 'car.category', 'qrToken'])
            ->where('status', 'ACCEPTED')
            ->join('pilots', 'race_registrations.pilot_id', '=', 'pilots.id')
            ->orderBy('pilots.last_name', 'asc')
            ->orderBy('pilots.first_name', 'asc')
            ->select('race_registrations.*')
            ->get();

        // Générer les codes d'inscription et les QR codes
        $registrations->each(function ($registration) use ($race) {
            $registration->registration_code = sprintf(
                '%s-%s-%04d',
                strtoupper(substr($race->name, 0, 3)),
                str_pad($registration->pilot->license_number ?? $registration->pilot_id, 6, '0', STR_PAD_LEFT),
                $registration->id
            );

            // Générer le QR code (token sécurisé)
            $token = $this->qrTokenService->getOrGenerateToken($registration);
            $registration->qr_code_data_uri = $this->qrTokenService->generateQrCodeDataUri($token, 80);
        });

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
