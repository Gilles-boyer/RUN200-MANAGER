<?php

namespace App\Events;

use App\Models\EngagementForm;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EngagementFormSigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EngagementForm $engagementForm
    ) {}
}
