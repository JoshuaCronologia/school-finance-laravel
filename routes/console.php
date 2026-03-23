<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Process recurring journal entries daily at midnight
Schedule::command('journal-entries:process-recurring')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer();

// Generate aging reports weekly
Schedule::command('ar:generate-aging')
    ->weeklyOn(1, '06:00')
    ->withoutOverlapping();

// Cleanup old audit trail entries (older than 2 years)
Schedule::command('audit:cleanup --days=730')
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping();
