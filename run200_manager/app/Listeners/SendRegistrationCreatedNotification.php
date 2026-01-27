<?php

namespace App\Listeners;

use App\Events\RegistrationCreated;
use App\Mail\RegistrationCreated as RegistrationCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendRegistrationCreatedNotification implements ShouldQueue
{
    public function handle(RegistrationCreated $event): void
    {
        $registration = $event->registration;

        if ($registration->pilot && $registration->pilot->user) {
            Mail::to($registration->pilot->user->email)
                ->send(new RegistrationCreatedMail($registration));
        }
    }
}
