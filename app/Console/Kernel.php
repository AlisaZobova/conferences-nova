<?php

namespace App\Console;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(
            function () {
                $files = Storage::disk('export')->allFiles();
                foreach ($files as $file) {
                    if (Storage::disk('export')->lastModified($file) < now()->subMinutes(15)->getTimestamp()) {
                        Storage::disk('export')->delete($file);
                    }
                }
            }
        )->everyFifteenMinutes();

        $schedule->call(
            function () {
                $users = User::all();
                foreach ($users as $user) {
                    if (!$user->getActiveSubscriptionAttribute() && !$user->hasRole('Admin')) {
                        $user->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();
                    }
                }
            }
        )->daily();

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
