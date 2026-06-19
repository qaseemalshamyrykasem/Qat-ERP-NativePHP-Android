<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Mark overdue debts daily at 1 AM
        $schedule->command('qat:mark-overdue-debts')->dailyAt('01:00');

        // Purge expired stock reservations every 10 minutes
        $schedule->command('qat:purge-expired-reservations')->everyTenMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
