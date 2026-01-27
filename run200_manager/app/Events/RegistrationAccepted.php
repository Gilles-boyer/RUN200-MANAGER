<?php

namespace App\Events;

use App\Models\RaceRegistration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistrationAccepted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RaceRegistration $registration
    ) {}
}
