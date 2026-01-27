<?php

namespace App\Listeners;

use App\Events\TechInspectionCompleted;
use App\Mail\TechInspectionCompleted as TechInspectionCompletedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendTechInspectionNotification implements ShouldQueue
{
    public function handle(TechInspectionCompleted $event): void
    {
        $techInspection = $event->techInspection;

        if ($techInspection->registration && $techInspection->registration->pilot && $techInspection->registration->pilot->user) {
            Mail::to($techInspection->registration->pilot->user->email)
                ->send(new TechInspectionCompletedMail($techInspection));
        }
    }
}
