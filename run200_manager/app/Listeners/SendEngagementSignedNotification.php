<?php

namespace App\Listeners;

use App\Events\EngagementFormSigned;
use App\Mail\EngagementFormSigned as EngagementFormSignedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendEngagementSignedNotification implements ShouldQueue
{
    public function handle(EngagementFormSigned $event): void
    {
        $engagementForm = $event->engagementForm;

        if ($engagementForm->registration && $engagementForm->registration->pilot && $engagementForm->registration->pilot->user) {
            Mail::to($engagementForm->registration->pilot->user->email)
                ->send(new EngagementFormSignedMail($engagementForm));
        }
    }
}
