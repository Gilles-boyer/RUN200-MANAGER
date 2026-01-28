<?php

namespace App\Listeners;

use App\Events\RaceOpened;
use App\Jobs\SendBulkEmailJob;
use App\Mail\RaceOpenedMail;
use App\Models\Pilot;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Sends notification to all active pilots when a race opens for registration.
 */
class SendRaceOpenedNotification implements ShouldQueue
{
    public function handle(RaceOpened $event): void
    {
        $race = $event->race;

        // Get all active pilots with verified email
        $pilots = Pilot::with('user')
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email_verified_at');
            })
            ->get();

        $sentCount = 0;

        foreach ($pilots as $pilot) {
            /** @var User|null $user */
            $user = $pilot->user;
            if ($user && $user->email) {
                SendBulkEmailJob::dispatch(
                    $user,
                    new RaceOpenedMail($race, $pilot->fullName),
                    "Race opened notification for race #{$race->id}"
                );
                $sentCount++;
            }
        }

        Log::info("Race opened notification queued for {$sentCount} pilots", [
            'race_id' => $race->id,
            'race_name' => $race->name,
        ]);
    }
}
