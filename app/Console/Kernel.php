<?php

namespace App\Console;

use App\Jobs\ProcessImportJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // start the queue worker in order to process the jobs
        $schedule->command('queue:work --timeout=900 --tries=3 --stop-when-empty')->everyMinute()->withoutOverlapping();

        // start processing the jobs without overlapping
        $schedule->job(new ProcessImportJob)->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
