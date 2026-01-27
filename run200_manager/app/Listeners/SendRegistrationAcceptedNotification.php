<?php

namespace App\Listeners;

use App\Events\RegistrationAccepted;
use App\Mail\RegistrationAccepted as RegistrationAcceptedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendRegistrationAcceptedNotification implements ShouldQueue
{
    public function handle(RegistrationAccepted $event): void
    {
        $registration = $event->registration;

        if ($registration->pilot && $registration->pilot->user) {
            Mail::to($registration->pilot->user->email)
                ->send(new RegistrationAcceptedMail($registration));
        }
    }
}
