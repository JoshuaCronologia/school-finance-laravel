<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Process recurring journal entries daily at midnight
        $schedule->command('journal-entries:process-recurring')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Process recurring disbursement requests daily at 00:05
        $schedule->command('disbursements:process-recurring')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->onOneServer();

        // Generate aging reports weekly
        $schedule->command('ar:generate-aging')
            ->weeklyOn(1, '06:00')
            ->withoutOverlapping();

        // Cleanup old audit trail entries (older than 2 years)
        $schedule->command('audit:cleanup --days=730')
            ->monthlyOn(1, '02:00')
            ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
