<?php

namespace App\Events;

use App\Models\Race;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a race status changes to OPEN.
 * Used to notify all active pilots that registrations are open.
 */
class RaceOpened
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Race $race
    ) {}
}
