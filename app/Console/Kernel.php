<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run backup every day at midnight (00:00)
        $schedule->command('backup:create')->dailyAt('00:00');
        
        // Also run backup at 12:00 (noon) for extra safety
        // $schedule->command('backup:create')->dailyAt('12:00');
        
        // Clean old backups every day at 01:00 (keep 30 latest)
        $schedule->command('backup:clean --keep=30')->dailyAt('01:00');

        $schedule->command('stock:sync')->daily(); // or ->hourly() etc.

        // Run backup on specific days (e.g., Monday, Wednesday, Friday)
        // $schedule->command('backup:create')->days([1, 3, 5])->at('02:00');
        
        // Run backup every hour for critical systems
        // $schedule->command('backup:create')->hourly();
        
        // Run backup every 6 hours
        // $schedule->command('backup:create')->everySixHours();
        
        // Keep only 7 backups for weekly schedule
        // $schedule->command('backup:clean --keep=7')->weekly();
        
        // Notify admin if backup fails
        $schedule->command('backup:create')
            ->dailyAt('00:00')
            ->onFailure(function () {
                // Send notification or log error
                \Log::error('Scheduled backup failed at ' . now());
                
                // Optional: Send email notification
                // Mail::to('admin@example.com')->send(new BackupFailedNotification());
            })
            ->onSuccess(function () {
                \Log::info('Scheduled backup completed successfully at ' . now());
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}