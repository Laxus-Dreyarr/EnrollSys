<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    // protected function schedule(Schedule $schedule)
    // {
    //     // Clean up expired passkeys daily at 3:00 AM
    //     $schedule->command('passkeys:cleanup')->dailyAt('22:37');
        
    //     // Alternatively, you can run it every hour:
    //     // $schedule->command('passkeys:cleanup')->hourly();
    // }

    protected function schedule(Schedule $schedule)
    {
        // Send qualification emails - run every 6 hours
        $schedule->command('emails:send-qualified --batch=100')
                ->everySixHours()
                ->between('8:00', '20:00'); // Only during business hours
    }

    /**
     * Register the commands for the application.
     */
    // protected function commands()
    // {
    //     $this->load(__DIR__.'/Commands');

    //     require base_path('routes/console.php');
    // }
}
