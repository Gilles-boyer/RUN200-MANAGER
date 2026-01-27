<?php

namespace App\Listeners;

use App\Events\RegistrationRefused;
use App\Mail\RegistrationRefused as RegistrationRefusedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendRegistrationRefusedNotification implements ShouldQueue
{
    public function handle(RegistrationRefused $event): void
    {
        $registration = $event->registration;

        if ($registration->pilot && $registration->pilot->user) {
            Mail::to($registration->pilot->user->email)
                ->send(new RegistrationRefusedMail($registration));
        }
    }
}
