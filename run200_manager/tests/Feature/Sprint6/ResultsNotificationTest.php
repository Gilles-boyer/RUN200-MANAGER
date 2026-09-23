<?php

declare(strict_types=1);

use App\Events\ResultsPublished;
use App\Jobs\SendBulkEmailJob;
use App\Listeners\SendResultsPublishedNotification;
use App\Mail\ResultsPublishedMail;
use App\Models\Car;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('renders result notification with valid public links and imported result fields', function () {
    $race = Race::factory()->published()->create();
    $pilot = Pilot::factory()->create();
    $car = Car::factory()->for($pilot)->create();
    $registration = RaceRegistration::factory()->for($race)->for($pilot)->for($car)->accepted()->create();
    $result = RaceResult::factory()->forRegistration($registration)->position(2)->timeMs(165123)->create();

    $html = (new ResultsPublishedMail($race, $result))->render();

    expect($html)
        ->toContain(route('public.results.race', $race))
        ->toContain(route('public.standings'))
        ->toContain($pilot->user->name)
        ->toContain('2:45.123');
});

it('queues results emails for engaged pilots and matches results through registrations', function () {
    Bus::fake();
    $race = Race::factory()->published()->create();
    $pilot = Pilot::factory()->create();
    $car = Car::factory()->for($pilot)->create();
    $registration = RaceRegistration::factory()->for($race)->for($pilot)->for($car)->accepted()->create();
    $result = RaceResult::factory()->forRegistration($registration)->position(1)->create();

    (new SendResultsPublishedNotification)->handle(new ResultsPublished($race));

    Bus::assertDispatched(SendBulkEmailJob::class, function (SendBulkEmailJob $job) use ($pilot, $result) {
        return $job->recipient->is($pilot->user)
            && $job->mailable instanceof ResultsPublishedMail
            && $job->mailable->pilotResult?->is($result);
    });
});
