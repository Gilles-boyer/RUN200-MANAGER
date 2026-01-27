<?php

namespace App\Providers;

use App\Events\EngagementFormSigned as EngagementFormSignedEvent;
use App\Events\PaymentConfirmed as PaymentConfirmedEvent;
use App\Events\RegistrationAccepted as RegistrationAcceptedEvent;
use App\Events\RegistrationCreated as RegistrationCreatedEvent;
use App\Events\RegistrationRefused as RegistrationRefusedEvent;
use App\Events\TechInspectionCompleted as TechInspectionCompletedEvent;
use App\Listeners\SendEngagementSignedNotification;
use App\Listeners\SendPaymentConfirmation;
use App\Listeners\SendRegistrationAcceptedNotification;
use App\Listeners\SendRegistrationCreatedNotification;
use App\Listeners\SendRegistrationRefusedNotification;
use App\Listeners\SendTechInspectionNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Registration events
        RegistrationCreatedEvent::class => [
            SendRegistrationCreatedNotification::class,
        ],
        RegistrationAcceptedEvent::class => [
            SendRegistrationAcceptedNotification::class,
        ],
        RegistrationRefusedEvent::class => [
            SendRegistrationRefusedNotification::class,
        ],

        // Payment events
        PaymentConfirmedEvent::class => [
            SendPaymentConfirmation::class,
        ],

        // Tech inspection events
        TechInspectionCompletedEvent::class => [
            SendTechInspectionNotification::class,
        ],

        // Engagement form events
        EngagementFormSignedEvent::class => [
            SendEngagementSignedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
