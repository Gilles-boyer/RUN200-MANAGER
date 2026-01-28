<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| These scheduled tasks will run automatically via the Laravel scheduler.
| To activate, add this cron entry to your server:
| * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Process queued jobs (for o2switch without supervisor)
// Only runs if QUEUE_CONNECTION is not 'sync'
if (config('queue.default') !== 'sync') {
    Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
        ->everyMinute()
        ->withoutOverlapping()
        ->description('Process queued jobs');
}

// Send race reminders J-3 every day at 9:00 AM
Schedule::command('send:race-reminders --days=3')
    ->dailyAt('09:00')
    ->description('Send J-3 race reminders to registered pilots');

// Send tech inspection reminders every day at 10:00 AM (J-1)
Schedule::command('send:tech-reminders')
    ->dailyAt('10:00')
    ->description('Send VA/VT reminders for pilots with inspections tomorrow');
