<?php

use App\Console\Commands\RetryFailedReminders;
use App\Console\Commands\SendSavingReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('logs:clear', function () {
    $logFile = storage_path('logs/laravel.log'); // Specify the file path

    if (file_exists($logFile)) {
        file_put_contents($logFile, ''); // Clear the file content
        $this->comment('The content of storage/logs/laravel.log has been cleared!');
    } else {
        $this->error('The file storage/logs/laravel.log does not exist.');
    }
})->describe('Clear the content of storage/logs/laravel.log');


// Environment-aware scheduling configuration
if (app()->environment('local', 'development')) {
    // Development configuration only
    Schedule::command('app:send-saving-reminders')
        ->hourly()
        ->appendOutputTo(storage_path('logs/scheduler.log'))
        ->description('Send saving reminders to users (development hourly check)');

    Schedule::command(RetryFailedReminders::class)
        ->twiceDaily(6, 18) // Run at 6 AM and 6 PM
        ->appendOutputTo(storage_path('logs/scheduler.log'))
        ->description('Retry failed saving reminders (development twice daily)');
} else {
    // Production AND Staging configuration, change everyTenMinutes() to hourly() once done with testing
    Schedule::command(SendSavingReminders::class)
        ->everyTenMinutes()
        ->appendOutputTo(storage_path('logs/scheduler.log'))
        ->description('Send saving reminders to users (hourly check)');

    Schedule::command(RetryFailedReminders::class)
        ->twiceDaily(6, 18) // Run at 6 AM and 6 PM
        ->appendOutputTo(storage_path('logs/scheduler.log'))
        ->description('Retry failed saving reminders (twice daily)');
}

