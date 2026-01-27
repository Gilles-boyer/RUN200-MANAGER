<?php

namespace App\Events;

use App\Models\PaddockSpot;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaddockSpotAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RaceRegistration $registration,
        public PaddockSpot $spot,
        public User $assignedBy
    ) {}
}
