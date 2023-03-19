<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $delimeter = PHP_OS_FAMILY === 'Windows' ? '\\' : '/';
            $files = glob(public_path('export') . $delimeter . "*.csv");
            foreach ($files as $file) {
                if (filemtime($file) < now()->subMinutes(15)->getTimestamp()) {
                    unlink($file);
                }
            }
        })->everyFifteenMinutes();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        include base_path('routes/console.php');
    }
}
