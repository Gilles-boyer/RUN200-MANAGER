<?php

namespace App\Events;

use App\Models\Race;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a race is cancelled.
 * Used to notify registered pilots about the cancellation.
 */
class RaceCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Race $race,
        public ?string $reason = null
    ) {}
}
