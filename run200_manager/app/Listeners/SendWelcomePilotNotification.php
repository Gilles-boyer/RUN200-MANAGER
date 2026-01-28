<?php

namespace App\Listeners;

use App\Events\PilotRegistered;
use App\Mail\WelcomePilotMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Sends welcome email when a pilot profile is created.
 */
class SendWelcomePilotNotification implements ShouldQueue
{
    public function handle(PilotRegistered $event): void
    {
        $pilot = $event->pilot;

        if ($pilot->user && $pilot->user->email) {
            Mail::to($pilot->user->email)
                ->send(new WelcomePilotMail($pilot));
        }
    }
}
