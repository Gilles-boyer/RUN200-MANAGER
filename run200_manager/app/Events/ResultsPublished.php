<?php

namespace App\Events;

use App\Models\Race;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when race results are published.
 * Used to notify participants that results are available.
 */
class ResultsPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Race $race
    ) {}
}
