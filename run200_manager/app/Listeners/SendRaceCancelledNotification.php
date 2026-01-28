<?php

namespace App\Listeners;

use App\Events\RaceCancelled;
use App\Jobs\SendBulkEmailJob;
use App\Mail\RaceCancelledMail;
use App\Models\RaceRegistration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Sends notification to all registered pilots when a race is cancelled.
 */
class SendRaceCancelledNotification implements ShouldQueue
{
    public function handle(RaceCancelled $event): void
    {
        $race = $event->race;
        $reason = $event->reason;

        // Get all registrations for this race (excluding already cancelled)
        $registrations = $race->registrations()
            ->whereNotIn('status', ['CANCELLED', 'REFUSED'])
            ->with(['pilot.user'])
            ->get();

        $sentCount = 0;

        /** @var RaceRegistration $registration */
        foreach ($registrations as $registration) {
            if ($registration->pilot && $registration->pilot->user && $registration->pilot->user->email) {
                SendBulkEmailJob::dispatch(
                    $registration->pilot->user,
                    new RaceCancelledMail($race, $registration, $reason),
                    "Race cancelled notification for race #{$race->id}"
                );
                $sentCount++;
            }
        }

        Log::info("Race cancelled notification queued for {$sentCount} pilots", [
            'race_id' => $race->id,
            'race_name' => $race->name,
            'reason' => $reason,
        ]);
    }
}
