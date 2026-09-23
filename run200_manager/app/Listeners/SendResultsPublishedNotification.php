<?php

namespace App\Listeners;

use App\Events\ResultsPublished;
use App\Jobs\SendBulkEmailJob;
use App\Mail\ResultsPublishedMail;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Sends notification to all participants when race results are published.
 */
class SendResultsPublishedNotification implements ShouldQueue
{
    public function handle(ResultsPublished $event): void
    {
        $race = $event->race;

        // Get all accepted registrations with their results
        $registrations = $race->registrations()
            ->whereIn('status', RaceRegistration::engagedStatuses())
            ->with(['pilot.user'])
            ->get();

        // The pilot belongs to the registration, not to the result itself.
        $raceResults = $race->results()
            ->with('registration')
            ->orderBy('position')
            ->get();
        $results = [];
        foreach ($raceResults as $result) {
            /** @var RaceResult $result */
            $pilotId = $result->registration?->pilot_id;
            if ($pilotId !== null && ! isset($results[$pilotId])) {
                $results[$pilotId] = $result;
            }
        }

        $sentCount = 0;

        /** @var RaceRegistration $registration */
        foreach ($registrations as $registration) {
            if ($registration->pilot && $registration->pilot->user && $registration->pilot->user->email) {
                // Get the pilot's result if exists
                /** @var RaceResult|null $pilotResult */
                $pilotResult = $results[$registration->pilot_id] ?? null;

                SendBulkEmailJob::dispatch(
                    $registration->pilot->user,
                    new ResultsPublishedMail($race, $pilotResult),
                    "Results published notification for race #{$race->id}"
                );
                $sentCount++;
            }
        }

        Log::info("Results published notification queued for {$sentCount} pilots", [
            'race_id' => $race->id,
            'race_name' => $race->name,
        ]);
    }
}
