<?php

namespace App\Events;

use App\Models\Pilot;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a new pilot profile is created.
 * Used to send welcome email with platform information.
 */
class PilotRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Pilot $pilot
    ) {}
}
